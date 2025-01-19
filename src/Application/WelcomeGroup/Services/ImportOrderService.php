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
use Domain\Orders\ValueObjects\Customer;
use Domain\Orders\ValueObjects\Item;
use Domain\Orders\ValueObjects\ItemCollection;
use Domain\Orders\ValueObjects\ItemModifierCollection;
use Domain\Orders\ValueObjects\Modifier;
use Domain\Orders\ValueObjects\Payment;
use Domain\Settings\Interfaces\OrganizationSettingRepositoryInterface;
use Domain\Settings\OrganizationSetting;
use Illuminate\Database\DatabaseManager;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\CreateOrderRequestData;
use Infrastructure\Integrations\IIko\IikoAuthenticator;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\GetOrdersByRestaurantRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\GetOrdersByRestaurantResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem\GetOrderItemsResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\GetOrderPaymentRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\GetOrderPaymentResponseData;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupFood;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupModifier;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Throwable;

final readonly class ImportOrderService
{
    public function __construct(
        private IikoAuthenticator $authenticator,
        private DatabaseManager $databaseManager,
        private IikoConnectorInterface $iikoConnector,
        private WelcomeGroupConnectorInterface $welcomeGroupConnector,
        private OrderRepositoryInterface $orderRepository,
        //        private WelcomeGroupReposit

        private OrganizationSettingRepositoryInterface $organizationSettingRepository,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $timestamp = now()->unix();
        $t = $this;
        $organizations = $this->organizationSettingRepository->all();

        $organizations->each(static function (OrganizationSetting $organizationSetting) use ($t, $timestamp): void {
            $orders = $t
                ->welcomeGroupConnector
                ->getOrdersByRestaurantId(
                    new GetOrdersByRestaurantRequestData($organizationSetting->welcomeGroupRestaurantId->id)
                );

            $orders->each(static function (GetOrdersByRestaurantResponseData $order) use ($timestamp, $t): void {
                //                $this
                //                    ->iikoConnector

                $internalOrder = $t
                    ->orderRepository
                    ->findByWelcomeGroupId(new IntegerId($order->id));

                if (! $internalOrder) {
                    $payments = $t
                        ->welcomeGroupConnector
                        ->getOrderPayment(
                            new GetOrderPaymentRequestData($order->id)
                        );

                    $currentTotalSumInWelcomeGroup = $payments
                        ->sum(static fn (GetOrderPaymentResponseData $payment) => $payment->sum);

                    $client = $t
                        ->welcomeGroupConnector
                        ->getClient(new IntegerId($order->client));

                    $result = $t
                        ->orderRepository
                        ->store(new Order(
                            new IntegerId(),
                            new IntegerId($order->restaurant),
                            OrderSource::WELCOME_GROUP,
                            OrderStatus::from($order->status),
                            new StringId(),
                            new IntegerId($order->id),
                            $order->comment,
                            new Payment(
                                $payments->first->type,
                                $currentTotalSumInWelcomeGroup,
                            ),
                            new Customer(
                                $client->name,
                                CustomerType::NEW,
                                $t
                                    ->welcomeGroupConnector
                                    ->getPhone(new IntegerId($order->phone))
                                    ->number
                            ),
                            new ItemCollection(
                                $t
                                    ->welcomeGroupConnector
                                    ->getOrderItems($order->id)
                                    ->map(static function (GetOrderItemsResponseData $orderItem) {
                                        $food = WelcomeGroupFood::query()
                                            ->where('external_id', $orderItem->id)
                                            ->first();
                                        $item = new Item(
                                            new IntegerId($food->iikoMenuItem->id),
                                            (int) ($orderItem->price * 100),
                                            (int) ($orderItem->discount * 100),
                                            1, // Я не понимаю как узнать сколько позиций в вг
                                            $orderItem->comment,
                                            new ItemModifierCollection()
                                        );

                                        foreach ($orderItem->foodModifiersArray as $foodModifier) {
                                            $modifier = WelcomeGroupModifier::query()
                                                ->where('external_id', $foodModifier->modifierObject->id)
                                                ->first()
                                                ->iikoModifier;
                                            $item->addModifier(
                                                new Modifier(
                                                    new IntegerId($food->iikoMenuItem->id),
                                                    new IntegerId($modifier->id),
                                                )
                                            );
                                        }

                                        return $item;
                                    })
                            ),
                            now()->toImmutable(),
                        ));

                    logger()->channel('import_orders_from_wg_to_iiko')->info('Заказ был создан в нашей бд из WG, прозводится создание в IIKO', [
                        'gettingOrderFromWG' => $order,
                        'creatingInternalOrder' => $result,
                        'timestamp' => $timestamp,
                    ]);
                    $orderModelInstance = \Infrastructure\Persistence\Eloquent\Orders\Models\Order::query()
                        ->find($result->id->id);

                    $this
                        ->iikoConnector
                        ->createOrder(
                            new CreateOrderRequestData(
                                $orderModelInstance->organizationSetting->iiko_restaurant_id,
                                $this
                                    ->iikoConnector
                                    ->getAvailableTerminals($$orderModelInstance->organizationSetting->iiko_restaurant_id)->first()->id
                            ),
                            $this->authenticator->getAuthToken($orderModelInstance->organizationSetting->iiko_api_key),
                        );

                } else {
                    logger()->channel('import_orders_from_wg_to_iiko')->warning('Внутренний заказ существует, повторного создания не инициализировано. Запущен процесс по проверке и синхронизации статусов', [
                        'gettingOrderFromWG' => $order,
                        'timestamp' => $timestamp,
                    ]);
                }

            });
        });
    }
}
