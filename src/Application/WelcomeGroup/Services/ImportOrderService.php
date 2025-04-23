<?php

declare(strict_types=1);

namespace Application\WelcomeGroup\Services;

use Application\Orders\Builders\OrderBuilder;
use Carbon\CarbonImmutable;
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
use Domain\Settings\ValueObjects\PaymentType;
use Infrastructure\Integrations\IIko\DataTransferObjects\CancelOrCloseRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\ChangeDeliveryDriverForOrderRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\Address;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\CreateOrderRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\CreateOrderSettings;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\Customer;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\DeliveryPoint;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\Items;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\Payments;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetAvailableTerminalsResponse\GetAvailableTerminalsResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetPaymentTypesResponse\GetPaymentTypesResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\UpdateOrderRequest\UpdateOrderRequestData;
use Infrastructure\Integrations\IIko\IikoAuthenticator;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Address\GetAddressResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Client\GetClientResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\GetOrdersByRestaurantRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\GetOrdersByRestaurantResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem\GetOrderItemsResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\GetOrderPaymentRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\GetOrderPaymentResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone\GetPhoneResponseData;
use Infrastructure\Persistence\Eloquent\Orders\Models\EndpointAddress;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderItem;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderItemModifier;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupFood;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupModifier;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\City;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\Coordinates;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\Region;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\Street;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\StreetTwo;
use Shared\Domain\Exceptions\WelcomeGroupImportOrdersGeneralException;
use Shared\Domain\Exceptions\WelcomeGroupNotFoundMatchForPaymentTypeException;
use Shared\Domain\Exceptions\WelcomeGroupOrderItemsNotFoundForOrderException;
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
                    'error' => $e,
                ]);

                throw new WelcomeGroupImportOrdersGeneralException("Не удалось инициировать процесс сбора заказов из ПОД в IIKO. Заведение: {$organizationSetting->welcomeGroupRestaurantId->id}. Причина: {$e->getMessage()}");
            }
        });
    }

    protected function getPaymentTypeFromIikoByCode(\Infrastructure\Persistence\Eloquent\Orders\Models\Order $order): GetPaymentTypesResponseData
    {
        // Извлечение типов оплат из коллекции payment_types
        $paymentTypes = $order->organizationSetting->payment_types;

        // Поиск iiko_payment_code на основе welcome_group_payment_code
        $matchedPaymentCode = $paymentTypes->firstWhere('welcome_group_payment_code', $order->payment->type);

        if (! $matchedPaymentCode || ! isset($matchedPaymentCode['iiko_payment_code'])) {
            throw new WelcomeGroupNotFoundMatchForPaymentTypeException('Не удалось найти соответствие типа оплаты для кода: '.$order->payment->type);
        }

        $iikoPaymentCode = $matchedPaymentCode['iiko_payment_code'];

        // Запрос типов оплат из iiko
        $iikoPaymentTypes = $this->iikoConnector->getPaymentTypes(
            new StringId($order->organizationSetting->iiko_restaurant_id),
            $this->authenticator->getAuthToken($order->organizationSetting->iiko_api_key)
        );

        // Фильтрация по коду iiko
        /** @var GetPaymentTypesResponseData $iikoPaymentType */
        $iikoPaymentType = $iikoPaymentTypes->first(static function (GetPaymentTypesResponseData $paymentType) use ($iikoPaymentCode) {
            return $paymentType->code === $iikoPaymentCode;
        });

        if (! $iikoPaymentType) {
            throw new WelcomeGroupNotFoundMatchForPaymentTypeException('Не удалось найти тип оплаты в iiko для кода: '.$iikoPaymentCode);
        }

        return $iikoPaymentType; // Возвращаем ID типа оплаты из iiko
    }

    protected function formatPhoneNumber($phoneNumber): string
    {
        // Удаляем все пробелы, тире, скобки и другие символы, кроме цифр и знака "+"
        $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);

        if (strpos($phoneNumber, '+7') === 0) {
            // Если номер уже начинается с +7, возвращаем его без изменений
            return $phoneNumber;
        } elseif (strpos($phoneNumber, '8') === 0) {
            // Если номер начинается с 8, заменяем 8 на +7
            return '+7'.substr($phoneNumber, 1);
        } elseif (strpos($phoneNumber, '9') === 0) {
            // Если номер начинается с 9, добавляем +7 перед цифрой 9
            return '+7'.$phoneNumber;
        } elseif (strpos($phoneNumber, '7') === 0) {
            // Если номер начинается с 7, добавляем + перед 7
            return '+'.$phoneNumber;
        } else {
            // Если формат не подходит, возвращаем исходный номер
            return $phoneNumber;
        }
    }

    private function processOrder(GetOrdersByRestaurantResponseData $order, OrganizationSetting $organizationSetting, int $timestamp): void
    {
        if ($order->id < config('app.order_before_which_to_skip_all_orders')) {
            return;
        }

        $wgStatus = \Domain\WelcomeGroup\Enums\OrderStatus::from($order->status);

        if ($wgStatus === \Domain\WelcomeGroup\Enums\OrderStatus::NEW) {
            return; // Заказы со статусом новый не обрабатываются по указанию Сергея
        }

        $internalOrder = $this->orderRepository->findByWelcomeGroupId(new IntegerId($order->id));

        if ($internalOrder) {
            logger()->channel('import_orders_from_wg_to_iiko')->warning('Внутренний заказ существует, синхронизация статусов.', [
                'orderId' => $order->id,
                'timestamp' => $timestamp,
            ]);

            if (OrderStatus::fromWelcomeGroupStatus($wgStatus) == OrderStatus::FINISHED) {
                $this
                    ->iikoConnector
                    ->closeOrder(
                        new CancelOrCloseRequestData(
                            $organizationSetting->iikoRestaurantId->id,
                            $internalOrder->iikoExternalId->id
                        ),
                        $this
                            ->authenticator
                            ->getAuthToken($organizationSetting->iikoApiKey)
                    );
            } elseif (OrderStatus::fromWelcomeGroupStatus($wgStatus) == OrderStatus::REJECTED || OrderStatus::fromWelcomeGroupStatus($wgStatus) == OrderStatus::CANCELLED) {
                $this
                    ->iikoConnector
                    ->rejectOrder(
                        new CancelOrCloseRequestData(
                            $organizationSetting->iikoRestaurantId->id,
                            $internalOrder->iikoExternalId->id
                        ),
                        $this
                            ->authenticator
                            ->getAuthToken($organizationSetting->iikoApiKey)
                    );
            } elseif (OrderStatus::checkDeliveredStatus($internalOrder->status)) {
                if (OrderStatus::toIikoStatus(OrderStatus::fromWelcomeGroupStatus($wgStatus)) === \Domain\Iiko\Enums\OrderStatus::ON_WAY) {
                    // указываем курьера к заказу которого выбрали в админке
                    $this
                        ->iikoConnector
                        ->changeDeliveryDriverForOrder(
                            new ChangeDeliveryDriverForOrderRequestData(
                                $organizationSetting->iikoRestaurantId->id,
                                $internalOrder->iikoExternalId->id,
                                $organizationSetting->iikoCourierId->id
                            ),
                            $this
                                ->authenticator
                                ->getAuthToken($organizationSetting->iikoApiKey)
                        );
                }

                $this->iikoConnector->updateDeliveryStatus(
                    new UpdateOrderRequestData(
                        $organizationSetting->iikoRestaurantId->id,
                        $internalOrder->iikoExternalId->id,
                        OrderStatus::toIikoStatus(OrderStatus::fromWelcomeGroupStatus($wgStatus))->value,
                    ),
                    $this->authenticator->getAuthToken($organizationSetting->iikoApiKey)
                );
            }

            $orderBuilder = OrderBuilder::fromExisted($internalOrder)
                ->setStatus(OrderStatus::fromWelcomeGroupStatus($wgStatus));

            $this->orderRepository->update($orderBuilder->build());

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
                    (int) ($orderItem->foodObject->price),
                    (int) ($orderItem->discount),
                    1, // В поде нет количества у позиции, товар = позиция
                    $orderItem->comment,
                    new ItemModifierCollection()
                );

                foreach ($orderItem->FoodModifiersArray as $foodModifier) {
                    $modifier = WelcomeGroupModifier::query()->where('external_id', $foodModifier['modifier'])->firstOrFail();
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
                $payment = new Payment($payments->first()->type, (int) ($totalSum));
            }

            $timeCooking = $order->timeCooking ?? 0; // Если null, то используем 0
            $timeWaitingCooking = $order->timeWaitingCooking ?? 0; // Если null, то используем 0
            $estimatedTimeDelivery = $order->estimatedTimeDelivery ?? 0; // Если null, то используем 0
            $totalTimeInSeconds = $timeCooking + $timeWaitingCooking + $estimatedTimeDelivery;

            $deliveryTime = CarbonImmutable::now()->addSeconds($totalTimeInSeconds);

            /** @var EndpointAddress $deliveryPoint */
            $deliveryPoint = EndpointAddress::query()
                ->where('order_id', $order->id)
                ->first();

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
                new \Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\DeliveryPoint(
                    new Coordinates(
                        $address->latitude,
                        $address->longitude
                    ),
                    new \Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\Address(
                        new Street(
                            null,
                            null,
                            $address->street,
                            new City(
                                null,
                                $address->city,
                            ),
                        ),
                        null, //$deliveryPoint->index,
                        $address->house,
                        $address->building,
                        $address->flat,
                        $address->entry,
                        $address->floor,
                        null, //$address->doorphone,
                        new Region(
                            null,
                            $address->city,
                        ),
                        null, //$deliveryPoint->line1,

                    ),
                    null,
                    null,
                ),
                $deliveryTime,
            );

            if ($newOrder->payment) {
                $paymentTypes = $organizationSetting->paymentTypes;

                // Поиск iiko_payment_code на основе welcome_group_payment_code
                $matchedPaymentCode = $paymentTypes->first(
                    static fn (PaymentType $paymentType) => $paymentType->welcomeGroupPaymentCode === $newOrder->payment->type
                );

                if (! $matchedPaymentCode || ! isset($matchedPaymentCode?->iikoPaymentCode)) {
                    throw new WelcomeGroupNotFoundMatchForPaymentTypeException('Не удалось найти соответствие типа оплаты для кода: '.$newOrder->payment->type);
                }
            }

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

            // Было некогда создавать отдельный ексепшн, поэтому заюзал неподходящий по неймингу, но подкходящий по функционал
            throw new WelcomeGroupImportOrdersGeneralException("Ошибка при создании заказа в IIKO. Заказ: {$order->number}. Причина: {$e->getMessage()}");
        }
    }

    /**
     * @throws WelcomeGroupImportOrdersGeneralException
     */
    private function createOrderInIiko(Order $order, GetAddressResponseData $address, GetClientResponseData $client, GetPhoneResponseData $phone, OrganizationSetting $organizationSetting): void
    {
        try {
            /** @var GetAvailableTerminalsResponseData $terminals */
            $terminals = $this->iikoConnector
                ->getAvailableTerminals($organizationSetting->iikoRestaurantId, $this->authenticator->getAuthToken($organizationSetting->iikoApiKey))
                ->first();

            $terminalId = $terminals->items[0]->id;
            $order = \Infrastructure\Persistence\Eloquent\Orders\Models\Order::query()->find($order->id->id);

            $orderItems = [];

            $order->items->each(static function (OrderItem $item) use (&$orderItems) {
                $modifiers = [];
                $item->load(['modifiers', 'iikoMenuItem'])->modifiers->each(static function (OrderItemModifier $modifier) use (&$modifiers) {
                    $modifier = $modifier->load('modifier');
                    array_push(
                        $modifiers,
                        new \Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\Modifier(
                            $modifier->modifier->external_id,
                            (float) number_format($modifier->modifier->prices->first()->price, 2, '.', ''),
                            $modifier->modifier->modifierGroup->external_id,
                        ));
                });

                array_push(
                    $orderItems,
                    new Items(
                        $item->iikoMenuItem->external_id,
                        $modifiers,
                        (float) number_format($item->price, 2, '.', ''), // сюда внимание
                        'Product',
                        1, // Кажется тут логично указывать 1, ведь в поде нет количества
                        null,
                        null,
                        ''
                    )
                );
            });
            $payments = null;

            if ($order->payment) {
                $paymentType = $this->getPaymentTypeFromIikoByCode($order);
                $payments = [
                    new Payments(
                        $paymentType->paymentTypeKind,
                        (float) number_format($order->payment->amount, 2, '.', ''),
                        $paymentType->id,
                        true,
                        null,
                        false,
                        true
                    ),
                ];
            }
            if ($order->items()->count() == 0) {
                throw new WelcomeGroupOrderItemsNotFoundForOrderException("Не найдены товары в заказе $order->welcome_group_external_id . Из-за этого создание внутри ПОД отменено и необходимо обработать вручную данную ситуацию");
            }

            $createOrderResponse = $this->iikoConnector->createOrder(
                new CreateOrderRequestData(
                    $organizationSetting->iikoRestaurantId->id,
                    $terminalId,
                    new CreateOrderSettings(),
                    new \Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\Order(
                        (string) $order->id,
                        $order->complete_before->format('Y-m-d H:i:s.v'),
                        $this->formatPhoneNumber($order->customer->phone),
                        $order->organizationSetting->order_delivery_type_id, // Должно быть или pickup_type_id
                        null,
                        new DeliveryPoint(
                            new Coordinates(
                                $address->latitude,
                                $address->longitude
                            ),
                            new Address(
                                $address->flat,
                                $address->entry,
                                $address->floor,
                                new StreetTwo(
                                    null,
                                    null,
                                    $address->street,
                                    $address->city
                                ),
                                null,
                                $address->house,
                                null,
                                $address->building
                            ),
                            null,
                            $address->comment,
                        ),
                        $order?->comment ?? '',
                        new Customer($client->name),
                        $orderItems,
                        $payments,
                        null,
                        null
                    )
                ),
                $this->authenticator->getAuthToken($organizationSetting->iikoApiKey),
            );

            $order->iiko_external_id = $createOrderResponse->orderInfo->id;
            $order->save();
            logger()->channel('import_orders_from_wg_to_iiko')->info('Заказ успешно создан в iiko.', [
                'orderId' => $order->id,
            ]);
        } catch (Throwable $e) {
            logger()->error('Ошибка при создании заказа в iiko.', [
                'orderId' => $order->id,
                'error' => $e,
            ]);

            throw new WelcomeGroupImportOrdersGeneralException("Ошибка при создании заказа $order->welcome_group_external_id в iiko. Ошибка: {$e->getMessage()}");
        }
    }
}
