<?php

declare(strict_types=1);

namespace Application\WelcomeGroup\Services;

use Domain\Iiko\Enums\CustomerType;
use Domain\Integrations\Iiko\IikoConnectorInterface;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Orders\Entities\Order;
use Domain\Orders\Enums\OrderSource;
use Domain\Orders\Enums\OrderStatus;
use Domain\Orders\Repositories\OrderRepositoryInterface;
use Domain\Orders\ValueObjects\Item;
use Domain\Orders\ValueObjects\ItemCollection;
use Domain\Orders\ValueObjects\ItemModifierCollection;
use Domain\Orders\ValueObjects\Modifier;
use Domain\Orders\ValueObjects\Payment;
use Domain\Settings\Interfaces\OrganizationSettingRepositoryInterface;
use Domain\Settings\OrganizationSetting;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\Address;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\CreateOrderRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\CreateOrderSettings;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\Customer;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\DeliveryPoint;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\Items;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\Payments;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetPaymentTypesResponse\GetPaymentTypesResponseData;
use Infrastructure\Integrations\IIko\IikoAuthenticator;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Address\GetAddressResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Client\GetClientResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\GetOrdersByRestaurantRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\GetOrdersByRestaurantResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem\GetOrderItemsResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\GetOrderPaymentRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\GetOrderPaymentResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone\GetPhoneResponseData;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderItem;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderItemModifier;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupFood;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupModifier;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\Coordinates;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Throwable;

final readonly class ImportOrderService
{
    public function __construct(
        private IikoAuthenticator $authenticator,
        private IikoConnectorInterface $iikoConnector,
        private WelcomeGroupConnectorInterface $welcomeGroupConnector,
        private OrderRepositoryInterface $orderRepository,
        private OrganizationSettingRepositoryInterface $organizationSettingRepository,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $timestamp = now()->unix();
        $organizations = $this->organizationSettingRepository->all();

        $organizations->each(function (OrganizationSetting $organizationSetting) use ($timestamp): void {
            try {
                $orders = $this->welcomeGroupConnector->getOrdersByRestaurantId(
                    new GetOrdersByRestaurantRequestData($organizationSetting->welcomeGroupRestaurantId->id)
                );

                $orders->each(function (GetOrdersByRestaurantResponseData $order) use ($timestamp, $organizationSetting): void {
                    $this->processOrder($order, $organizationSetting, $timestamp);
                });
            } catch (Throwable $e) {
                logger()->error('Ошибка при обработке заказов из Welcome Group', [
                    'organizationId' => $organizationSetting->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    protected function getPaymentTypeIdFromIikoByCode(\Infrastructure\Persistence\Eloquent\Orders\Models\Order $order): string
    {
        // Извлечение типов оплат из коллекции payment_types
        $paymentTypes = $order->organizationSetting->payment_types;

        // Поиск iiko_payment_code на основе welcome_group_payment_code
        $matchedPaymentCode = $paymentTypes->firstWhere('welcome_group_payment_code', $order->payment->type);

        if (! $matchedPaymentCode || ! isset($matchedPaymentCode['iiko_payment_code'])) {
            throw new \RuntimeException('Не удалось найти соответствие типа оплаты для кода: '.$order->payment->type);
        }

        $iikoPaymentCode = $matchedPaymentCode['iiko_payment_code'];

        // Запрос типов оплат из iiko
        $iikoPaymentTypes = $this->iikoConnector->getPaymentTypes(
            new StringId($order->organizationSetting->iiko_restaurant_id),
            $this->authenticator->getAuthToken($order->organizationSetting->iiko_api_key)
        );

        // Фильтрация по коду iiko
        $iikoPaymentType = $iikoPaymentTypes->first(static function (GetPaymentTypesResponseData $paymentType) use ($iikoPaymentCode) {
            return $paymentType->code === $iikoPaymentCode;
        });

        if (! $iikoPaymentType) {
            throw new \RuntimeException('Не удалось найти тип оплаты в iiko для кода: '.$iikoPaymentCode);
        }

        return $iikoPaymentType->id; // Возвращаем ID типа оплаты из iiko
    }

    private function processOrder(GetOrdersByRestaurantResponseData $order, OrganizationSetting $organizationSetting, int $timestamp): void
    {
        $internalOrder = $this->orderRepository->findByWelcomeGroupId(new IntegerId($order->id));

        if ($internalOrder) {
            logger()->channel('import_orders_from_wg_to_iiko')->warning('Внутренний заказ существует, синхронизация статусов.', [
                'orderId' => $order->id,
                'timestamp' => $timestamp,
            ]);

            return;
        }

        try {
            $payments = $this->welcomeGroupConnector->getOrderPayment(new GetOrderPaymentRequestData($order->id));
            $totalSum = $payments->sum(static fn (GetOrderPaymentResponseData $payment) => $payment->sum);

            $client = $this->welcomeGroupConnector->getClient(new IntegerId($order->client));
            $phone = $this->welcomeGroupConnector->getPhone(new IntegerId($order->phone));
            $address = $this->welcomeGroupConnector->getAddress(new IntegerId($order->address));

            $items = $this->welcomeGroupConnector->getOrderItems(new IntegerId($order->id))->map(static function (GetOrderItemsResponseData $orderItem) {
                $food = WelcomeGroupFood::query()->where('external_id', $orderItem->food)->firstOrFail();
                $item = new Item(
                    new IntegerId($food->iikoMenuItem->id),
                    (int) ($orderItem->price * 100),
                    (int) ($orderItem->discount * 100),
                    $orderItem->quantity ?? 1,
                    $orderItem->comment,
                    new ItemModifierCollection()
                );

                foreach ($orderItem->foodModifiersArray as $foodModifier) {
                    $modifier = WelcomeGroupModifier::query()->where('external_id', $foodModifier->modifier)->firstOrFail();
                    $item->addModifier(new Modifier(
                        new IntegerId($food->iikoMenuItem->id),
                        new IntegerId($modifier->iikoModifier->id)
                    ));
                }

                return $item;
            });
            if (blank($payments)) {
                $payment = null;
            } else {
                $payment = new Payment($payments->first()->type, $totalSum);
            }
            $newOrder = new Order(
                new IntegerId(),
                new IntegerId($organizationSetting->id->id),
                OrderSource::WELCOME_GROUP,
                OrderStatus::from($order->status),
                new StringId(),
                new IntegerId($order->id),
                $order->comment,
                $payment,
                new \Domain\Orders\ValueObjects\Customer($client->name, CustomerType::NEW, $phone->number),
                new ItemCollection($items),
                now()->toImmutable(),
            );

            $storedOrder = $this->orderRepository->store($newOrder);

            logger()->channel('import_orders_from_wg_to_iiko')->info('Новый заказ создан.', [
                'orderId' => $storedOrder->id->id,
                'timestamp' => $timestamp,
            ]);

            $this->createOrderInIiko($storedOrder, $address, $client, $phone, $organizationSetting);
        } catch (Throwable $e) {
            logger()->error('Ошибка при создании заказа.', [
                'orderId' => $order->id,
                'error' => $e,
            ]);
        }
    }

    private function createOrderInIiko(Order $order, GetAddressResponseData $address, GetClientResponseData $client, GetPhoneResponseData $phone, OrganizationSetting $organizationSetting): void
    {
        try {
            $terminalId = $this->iikoConnector
                ->getAvailableTerminals($organizationSetting->iikoRestaurantId, $this->authenticator->getAuthToken($organizationSetting->iikoApiKey))
                ->first()
                ->id;
            $order = \Infrastructure\Persistence\Eloquent\Orders\Models\Order::query()->find($order->id->id);

            $orderItems = [];

            $order->items()->each(static function (OrderItem $item) use (&$orderItems) {
                $modifiers = [];

                $item->modifiers->each(static function (OrderItemModifier $modifier) use (&$modifiers) {
                    array_push(
                        $modifiers,
                        new \Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\Modifier(
                            $modifier->modifier->external_id,
                            $modifier->modifier->prices->first()->price,
                        ));
                });

                array_push(
                    $orderItems,
                    new Items(
                        $item->iikoMenuItem->external_id,
                        $modifiers,
                        $item->price,
                        'Product',
                        1, // Кажется тут логично указывать 1, ведь в поде нет количества
                        null,
                        null,
                        ''
                    )
                );
            });

            $this->iikoConnector->createOrder(
                new CreateOrderRequestData(
                    $organizationSetting->iikoRestaurantId->id,
                    $terminalId,
                    new CreateOrderSettings(),
                    new \Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\Order(
                        null,
                        $order->iiko_external_id,
                        $order->id,
                        $order->complete_before,
                        $order->customer->phone,
                        $order->organizationSetting->order_delivery_type_id, // Должно быть или pickup_type_id
                        null,
                        new DeliveryPoint(
                            new Coordinates(
                                (int) $address->latitude,
                                (int) $address->longitude
                            ),
                            new Address(
                                __('г. :city, ул. :street, д. :house, :other', [
                                    'city' => $address->city,
                                    'street' => $address->street,
                                    'house' => $address->house,
                                    'other' => ! blank($address->flat) && ! blank($address->floor) ? 'этаж '.$address->floor.', кв. '.$address->flat : '',
                                ]),
                                $address->flat,
                                $address->entry,
                                $address->floor,
                                null,
                                null,
                                $address->house,
                                null
                            ),
                            null,
                            $address->comment,
                        ),
                        $order->comment,
                        new Customer($client->name, CustomerType::NEW, $phone->number),
                        $orderItems,
                        [
                            new Payments(
                                'External',
                                $order->payment->amount,
                                $this->getPaymentTypeIdFromIikoByCode($order),
                                true,
                                null,
                                false,
                                true
                            ),
                        ],
                        null,
                        null
                    )
                ),
                $this->authenticator->getAuthToken($organizationSetting->iikoApiKey),
            );

            logger()->channel('import_orders_from_wg_to_iiko')->info('Заказ успешно создан в iiko.', [
                'orderId' => $order->id->id,
            ]);
        } catch (Throwable $e) {
            logger()->error('Ошибка при создании заказа в iiko.', [
                'orderId' => $order->id->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
