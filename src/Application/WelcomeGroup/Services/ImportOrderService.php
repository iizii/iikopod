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
use Domain\Orders\ValueObjects\Modifier as DomainModifier;
use Domain\Orders\ValueObjects\Payment;
use Domain\Settings\Interfaces\OrganizationSettingRepositoryInterface;
use Domain\Settings\OrganizationSetting;
use Domain\Settings\ValueObjects\PaymentType;
use Infrastructure\Integrations\IIko\DataTransferObjects\AddOrderItemsRequest\AddOrderItemsRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\CancelOrCloseRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\ChangeDeliveryDriverForOrderRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\Address;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\CreateOrderRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\CreateOrderSettings;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\Customer;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\DeliveryPoint;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\Items;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\Modifier as IikoModifier;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\Payments;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetAvailableTerminalsResponse\GetAvailableTerminalsResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetPaymentTypesResponse\GetPaymentTypesResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\UpdateOrderRequest\UpdateOrderRequestData;
use Infrastructure\Integrations\IIko\IikoAuthenticator;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Address\GetAddressResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Client\GetClientResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\GetOrdersByRestaurantRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\GetOrdersByRestaurantResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem\CreateOrderItemRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem\GetOrderItemsResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\GetOrderPaymentRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\GetOrderPaymentResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone\GetPhoneResponseData;
use Infrastructure\Integrations\WelcomeGroup\Requests\Order\CreateOrderItemRequest as CreateOrderItemRequestClass;
use Infrastructure\Persistence\Eloquent\Orders\Models\EndpointAddress;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderItem;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderItemModifier;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderPayment;
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

    protected function getPaymentTypeFromIikoByCode(OrderPayment $payment, OrganizationSetting $organizationSetting): GetPaymentTypesResponseData
    {
        $paymentTypes = $organizationSetting->paymentTypes;
        /** @var PaymentType $matchedPaymentCode */
        $matchedPaymentCode = $paymentTypes->firstWhere('welcomeGroupPaymentCode', $payment->type);

        if (! $matchedPaymentCode || ! isset($matchedPaymentCode->iikoPaymentCode)) {
            throw new WelcomeGroupNotFoundMatchForPaymentTypeException('Не удалось найти соответствие типа оплаты для кода: '.$payment->type);
        }

        $iikoPaymentCode = $matchedPaymentCode->iikoPaymentCode;

        $iikoPaymentTypes = $this->iikoConnector->getPaymentTypes(
            $organizationSetting->iikoRestaurantId,
            $this->authenticator->getAuthToken($organizationSetting->iikoApiKey)
        );

        $iikoPaymentType = $iikoPaymentTypes->first(static function (GetPaymentTypesResponseData $paymentType) use ($iikoPaymentCode) {
            return $paymentType->code === $iikoPaymentCode;
        });

        if (! $iikoPaymentType) {
            throw new WelcomeGroupNotFoundMatchForPaymentTypeException('Не удалось найти тип оплаты в iiko для кода: '.$iikoPaymentCode);
        }

        return $iikoPaymentType;
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

            // Проверяем изменения в позициях заказа
            $this->handleOrderItemsChanges($internalOrder, $order, $organizationSetting);

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
            $payments = $this
                ->welcomeGroupConnector
                ->getOrderPayment(new GetOrderPaymentRequestData($order->id))
                ->map(static fn (GetOrderPaymentResponseData $payment) => $payment->toDomainEntity());

            //            $totalSum = $payments->sum(static fn (GetOrderPaymentResponseData $payment) => $payment->sum);

            $client = $this->welcomeGroupConnector->getClient(new IntegerId($order->client));
            $phone = $this->welcomeGroupConnector->getPhone(new IntegerId($order->phone));
            $address = null;
            if ($order->address) {
                $address = $this->welcomeGroupConnector->getAddress(new IntegerId($order->address));
            }

            $items = $this->welcomeGroupConnector->getOrderItems(new IntegerId($order->id))->map(static function (GetOrderItemsResponseData $orderItem) {
                $food = WelcomeGroupFood::query()->where('external_id', $orderItem->food)->firstOrFail();
                $item = new Item(
                    new IntegerId($food->iikoMenuItem->id),
                    (int) ($orderItem->foodObject->price),
                    (int) ($orderItem->discount),
                    1, // В поде нет количества у позиции, товар = позиция
                    $orderItem->comment,
                    new ItemModifierCollection(),
                    new IntegerId($orderItem->id) // Добавляем ID позиции из Welcome Group
                );

                foreach ($orderItem->FoodModifiersArray as $foodModifier) {
                    $modifier = WelcomeGroupModifier::query()->where('external_id', $foodModifier['modifier'])->firstOrFail();
                    // Используем доменный класс DomainModifier для работы с доменной моделью Item
                    $item->addModifier(new DomainModifier(
                        new IntegerId($food->iikoMenuItem->id),
                        new IntegerId($modifier->iikoModifier->id)
                    ));
                }

                return $item;
            });

            //            if (blank($payments)) {
            //                $payment = null;
            //            } else {
            //                $payment = new Payment($payments->first()->type, (int) ($totalSum));
            //            }

            $timeCooking = $order->timeCooking ?? 0; // Если null, то используем 0
            $timeWaitingCooking = $order->timeWaitingCooking ?? 0; // Если null, то используем 0
            $estimatedTimeDelivery = $order->estimatedTimeDelivery ?? 0; // Если null, то используем 0
            $totalTimeInSeconds = $timeCooking + $timeWaitingCooking + $estimatedTimeDelivery;

            $deliveryTime = CarbonImmutable::now()->addSeconds($totalTimeInSeconds);

            if ($order->isPreorder) {
                $deliveryTime = $order->timePreorder;
            }

            //            /** @var EndpointAddress $deliveryPoint */
            //            $deliveryPoint = EndpointAddress::query()
            //                ->where('order_id', $order->id)
            //                ->first();

            $newOrder = new Order(
                new IntegerId(),
                new IntegerId($organizationSetting->id->id),
                OrderSource::WELCOME_GROUP,
                OrderStatus::from($order->status),
                new StringId(),
                new IntegerId($order->id),
                $order->comment,
                $payments,
                new \Domain\Orders\ValueObjects\Customer($client->name, CustomerType::NEW, $phone->number),
                new ItemCollection($items),
                isset($address)
                    ? new \Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\DeliveryPoint(
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
                    ) : null,
                $deliveryTime,
            );

            $newOrder->payments->each(static function (Payment $payment) use ($organizationSetting) {
                $paymentTypes = $organizationSetting->paymentTypes;

                // Поиск iiko_payment_code на основе welcome_group_payment_code
                /** @var PaymentType $matchedPaymentCode */
                $matchedPaymentCode = $paymentTypes->first(
                    static fn (PaymentType $paymentType) => $paymentType->welcomeGroupPaymentCode === $payment->type
                );

                if (! $matchedPaymentCode || ! isset($matchedPaymentCode?->iikoPaymentCode)) {
                    throw new WelcomeGroupNotFoundMatchForPaymentTypeException(
                        'Не удалось найти соответствие типа оплаты для кода: '.$payment->type
                    );
                }
            });

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

    private function handleOrderItemsChanges(Order $internalOrder, GetOrdersByRestaurantResponseData $wgOrder, OrganizationSetting $organizationSetting): void
    {
        try {
            $currentItems = \Infrastructure\Persistence\Eloquent\Orders\Models\Order::query()
                ->find($internalOrder->id->id)
                ->items()
                ->with(['iikoMenuItem', 'modifiers.modifier.modifierGroup'])
                ->get();

            // Получаем текущие позиции из Welcome Group
            $wgOrderItems = $this->welcomeGroupConnector->getOrderItems(new IntegerId($wgOrder->id));

            if ($wgOrderItems->isEmpty()) {
                logger()->warning('Позиции заказа в Welcome Group не найдены', [
                    'orderId' => $wgOrder->id,
                ]);

                return;
            }

            // Добавляем подробное логирование для отладки статусов
            foreach ($wgOrderItems as $item) {
                logger()->debug('Статус позиции из Welcome Group', [
                    'orderId' => $wgOrder->id,
                    'itemId' => $item->id,
                    'status' => $item->status,
                    'foodId' => $item->food,
                ]);
            }

            logger()->info('Получены позиции из Welcome Group, обрабатываем', [
                'orderId' => $wgOrder->id,
                'itemsCount' => $wgOrderItems->count(),
                'currentItemsCount' => $currentItems->count(),
                'wgItemIds' => $wgOrderItems->pluck('id')->toArray(),
            ]);

            // Создаем массив для хранения ID позиций, которые уже есть в локальной базе
            $existingWgItemIds = $currentItems->pluck('welcome_group_external_id')->toArray();
            logger()->info('Существующие позиции в заказе', [
                'orderId' => $wgOrder->id,
                'existingWgItemIds' => $existingWgItemIds,
            ]);

            // Разделяем позиции на активные и отмененные
            $activeWgItems = $wgOrderItems->filter(static function ($item) {
                return $item->status !== 'cancelled';
            });

            $cancelledWgItems = $wgOrderItems->filter(static function ($item) {
                return $item->status === 'cancelled';
            });

            // Получаем ID активных позиций в Welcome Group
            $activeWgItemIds = $activeWgItems->pluck('id')->toArray();

            // Группируем позиции в локальной базе по типу блюда (через WelcomeGroupFood)
            $currentItemsGrouped = [];
            foreach ($currentItems as $item) {
                $food = WelcomeGroupFood::query()->where('iiko_menu_item_id', $item->iiko_menu_item_id)->first();
                if ($food) {
                    $foodId = $food->external_id;
                    if (! isset($currentItemsGrouped[$foodId])) {
                        $currentItemsGrouped[$foodId] = [];
                    }
                    $currentItemsGrouped[$foodId][] = $item;
                }
            }

            // Группируем активные позиции из Welcome Group по типу блюда
            $activeWgItemsGrouped = [];
            foreach ($activeWgItems as $item) {
                $foodId = $item->food;
                if (! isset($activeWgItemsGrouped[$foodId])) {
                    $activeWgItemsGrouped[$foodId] = [];
                }
                $activeWgItemsGrouped[$foodId][] = $item;
            }

            // Находим новые позиции, которые есть в Welcome Group, но отсутствуют в локальной базе
            $newItems = $activeWgItems->filter(static function ($item) use ($existingWgItemIds) {
                return ! in_array($item->id, $existingWgItemIds);
            });

            logger()->info('Новые позиции из Welcome Group', [
                'orderId' => $wgOrder->id,
                'newItemsIds' => $newItems->pluck('id')->toArray(),
            ]);

            // Находим позиции, которые есть в локальной базе, но отсутствуют среди активных в Welcome Group
            // и при этом находятся в отмененных - это позиции, которые нужно восстановить
            $itemsToRestore = $currentItems->filter(static function ($item) use ($activeWgItemIds, $cancelledWgItems) {
                return ! in_array($item->welcome_group_external_id, $activeWgItemIds) &&
                       $cancelledWgItems->contains('id', $item->welcome_group_external_id);
            });

            logger()->info('Позиции для восстановления', [
                'orderId' => $wgOrder->id,
                'itemsToRestoreIds' => $itemsToRestore->pluck('welcome_group_external_id')->toArray(),
            ]);

            // Восстанавливаем отмененные позиции
            $restoredItemsCount = 0;

            foreach ($itemsToRestore as $item) {
                try {
                    // Находим соответствующую отмененную позицию в Welcome Group
                    $cancelledItem = $cancelledWgItems->firstWhere('id', $item->welcome_group_external_id);

                    if (! $cancelledItem) {
                        logger()->warning('Не найдена отмененная позиция в Welcome Group', [
                            'orderId' => $wgOrder->id,
                            'itemId' => $item->welcome_group_external_id,
                        ]);

                        continue;
                    }

                    $food = WelcomeGroupFood::query()->where('external_id', $cancelledItem->food)->firstOrFail();

                    // Получаем модификаторы из локальной базы для этой позиции
                    $itemModifiers = $item->modifiers()->with('modifier')->get();
                    $modifiersArray = [];

                    // Подробное логирование модификаторов
                    logger()->info('Модификаторы позиции в локальной базе', [
                        'orderId' => $wgOrder->id,
                        'itemId' => $item->welcome_group_external_id,
                        'foodId' => $cancelledItem->food,
                        'modifiers' => $itemModifiers->map(static function ($mod) {
                            return [
                                'id' => $mod->iiko_menu_item_modifier_item_id,
                                'name' => $mod->modifier->name ?? 'Неизвестно',
                            ];
                        })->toArray(),
                    ]);

                    // Формируем массив модификаторов в формате, требуемом Welcome Group
                    foreach ($itemModifiers as $itemModifier) {
                        // Находим соответствующий модификатор в Welcome Group через связь с таблицей welcome_group_food_modifiers
                        $welcomeGroupModifier = WelcomeGroupModifier::query()
                            ->where('iiko_menu_item_modifier_item_id', $itemModifier->iiko_menu_item_modifier_item_id)
                            ->first();

                        if ($welcomeGroupModifier) {
                            // Находим связь между блюдом и модификатором в таблице welcome_group_food_modifiers
                            $welcomeGroupFoodModifier = \Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupFoodModifier::query()
                                ->where('welcome_group_food_id', $food->id)
                                ->where('welcome_group_modifier_id', $welcomeGroupModifier->id)
                                ->first();

                            if ($welcomeGroupFoodModifier) {
                                logger()->info('Найден связанный модификатор в таблице welcome_group_food_modifiers', [
                                    'orderId' => $wgOrder->id,
                                    'itemId' => $item->welcome_group_external_id,
                                    'foodId' => $cancelledItem->food,
                                    'iikoModifierId' => $itemModifier->iiko_menu_item_modifier_item_id,
                                    'wgModifierId' => $welcomeGroupModifier->external_id,
                                    'wgModifierName' => $welcomeGroupModifier->name,
                                    'wgFoodModifierId' => $welcomeGroupFoodModifier->external_id,
                                ]);

                                // Используем ID из таблицы welcome_group_food_modifiers
                                $modifiersArray[] = [
                                    'modifier' => $welcomeGroupFoodModifier->external_id,
                                    'amount' => 1,
                                ];
                            } else {
                                logger()->warning('Не найдена связь между блюдом и модификатором в таблице welcome_group_food_modifiers', [
                                    'orderId' => $wgOrder->id,
                                    'itemId' => $item->welcome_group_external_id,
                                    'foodId' => $cancelledItem->food,
                                    'iikoModifierId' => $itemModifier->iiko_menu_item_modifier_item_id,
                                    'wgModifierId' => $welcomeGroupModifier->external_id,
                                    'wgModifierName' => $welcomeGroupModifier->name,
                                ]);

                                // Если не нашли связь, используем ID модификатора напрямую
                                $modifiersArray[] = [
                                    'modifier' => $welcomeGroupModifier->external_id,
                                    'amount' => 1,
                                ];
                            }
                        } else {
                            logger()->warning('Не найден соответствующий модификатор в Welcome Group', [
                                'orderId' => $wgOrder->id,
                                'itemId' => $item->welcome_group_external_id,
                                'iikoModifierId' => $itemModifier->iiko_menu_item_modifier_item_id,
                            ]);
                        }
                    }

                    logger()->info('Восстанавливаем позицию с модификаторами', [
                        'orderId' => $wgOrder->id,
                        'itemId' => $item->welcome_group_external_id,
                        'modifiers' => $modifiersArray,
                    ]);

                    // Создаем новую позицию с теми же параметрами и модификаторами
                    $this->welcomeGroupConnector->addOrderItem(
                        new IntegerId($wgOrder->id),
                        new CreateOrderItemRequestClass(
                            new CreateOrderItemRequestData(
                                $wgOrder->id,
                                $cancelledItem->food,
                                $modifiersArray, // Используем модификаторы из локальной базы
                                $cancelledItem->comment ?? ''
                            )
                        )
                    );

                    $restoredItemsCount++;

                    logger()->info('Позиция успешно восстановлена в Welcome Group', [
                        'orderId' => $wgOrder->id,
                        'itemId' => $cancelledItem->id,
                        'foodId' => $cancelledItem->food,
                    ]);
                } catch (\Throwable $e) {
                    logger()->error('Ошибка при восстановлении позиции в Welcome Group', [
                        'orderId' => $wgOrder->id,
                        'itemId' => $item->welcome_group_external_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Обрабатываем новые позиции, добавляя их в локальную базу данных и IIKO
            $addedItemsCount = 0;

            foreach ($newItems as $newItem) {
                try {
                    $foodId = $newItem->food;
                    $food = WelcomeGroupFood::query()->where('external_id', $foodId)->firstOrFail();

                    // Проверяем, не является ли новая позиция заменой удаленной того же типа
                    // Если количество восстановленных позиций этого типа уже соответствует нужному,
                    // то пропускаем добавление новой позиции (она уже учтена в восстановленных)
                    $localItemsOfThisType = isset($currentItemsGrouped[$foodId]) ? count($currentItemsGrouped[$foodId]) : 0;
                    $activePlusRestoredItems = 0;
                    if (isset($activeWgItemsGrouped[$foodId])) {
                        foreach ($activeWgItemsGrouped[$foodId] as $activeItem) {
                            // Учитываем только те активные позиции, которых нет в существующих ИЛИ которые были восстановлены
                            if (! in_array($activeItem->id, $existingWgItemIds) ||
                                $itemsToRestore->pluck('welcome_group_external_id')->contains($activeItem->id)) {
                                $activePlusRestoredItems++;
                            }
                        }
                    }

                    // Добавляем подробное логирование для отладки
                    logger()->debug('Проверка условий для добавления новой позиции', [
                        'orderId' => $wgOrder->id,
                        'itemId' => $newItem->id,
                        'foodId' => $foodId,
                        'itemStatus' => $newItem->status,
                        'localItemsOfThisType' => $localItemsOfThisType,
                        'activePlusRestoredItems' => $activePlusRestoredItems,
                        'existingWgItemIds' => $existingWgItemIds,
                        'activeWgItemIds' => $activeWgItemIds,
                        'itemsToRestoreIds' => $itemsToRestore->pluck('welcome_group_external_id')->toArray(),
                        'condition' => ! ($localItemsOfThisType == 0 && $newItem->status != 'cancelled') || $activePlusRestoredItems >= $localItemsOfThisType ? 'SKIP' : 'ADD',
                        //                        'condition' => ($activePlusRestoredItems >= $localItemsOfThisType) ? 'SKIP' : 'ADD',
                    ]);

                    // Если новая позиция просто заменяет удаленную того же типа, пропускаем
                    // Исправляем условие: если localItemsOfThisType = 0, то это новый тип блюда, которого еще нет в заказе
                    // и его нужно добавить в любом случае
                    //                                        if ($localItemsOfThisType > 0 && $activePlusRestoredItems > 0 &&
                    //                                            ($activePlusRestoredItems + $localItemsOfThisType) <= count($activeWgItemsGrouped[$foodId] ?? [])) {
                    //                    if ($localItemsOfThisType > 0 && $activePlusRestoredItems >= $localItemsOfThisType) {
                    //                    if (! ($localItemsOfThisType == 0 && $newItem->status != 'cancelled') || $activePlusRestoredItems >= $localItemsOfThisType) {

                    $itemsToAdd = count($activeWgItemsGrouped[$foodId] ?? []) - $localItemsOfThisType; // Временное решение, надо перепроверить

                    if ($itemsToAdd <= 0) {
                        // Пропускаем только если количество новых/восстановленных не превышает разницу
                        logger()->info('Пропускаем добавление новой позиции, так как она заменяет удаленную того же типа', [
                            'orderId' => $wgOrder->id,
                            'itemId' => $newItem->id,
                            'foodId' => $foodId,
                            'localItemsOfThisType' => $localItemsOfThisType,
                            'activePlusRestoredItems' => $activePlusRestoredItems,
                        ]);

                        continue;
                    }

                    // Добавляем новую позицию в локальную базу
                    $orderItem = new OrderItem();
                    $orderItem->order_id = $internalOrder->id->id;
                    $orderItem->iiko_menu_item_id = $food->iiko_menu_item_id;
                    $orderItem->welcome_group_external_id = $newItem->id;
                    $orderItem->price = $newItem->price;
                    $orderItem->discount = $newItem->discount;
                    $orderItem->amount = 1; // В Welcome Group нет количества у позиции
                    $orderItem->comment = $newItem->comment;
                    $orderItem->save();

                    // Добавляем модификаторы, если они есть
                    if (! empty($newItem->FoodModifiersArray)) {
                        foreach ($newItem->FoodModifiersArray as $foodModifier) {
                            // Находим модификатор в базе данных
                            $wgFoodModifier = \Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupFoodModifier::query()
                                ->where('external_id', $foodModifier['modifier'])
                                ->first();

                            if ($wgFoodModifier) {
                                $wgModifier = WelcomeGroupModifier::query()->find($wgFoodModifier->welcome_group_modifier_id);

                                if ($wgModifier && $wgModifier->iiko_menu_item_modifier_item_id) {
                                    $orderItemModifier = new OrderItemModifier();
                                    $orderItemModifier->order_item_id = $orderItem->id;
                                    $orderItemModifier->iiko_menu_item_modifier_item_id = $wgModifier->iiko_menu_item_modifier_item_id;
                                    $orderItemModifier->save();

                                    logger()->info('Добавлен модификатор к новой позиции', [
                                        'orderId' => $wgOrder->id,
                                        'itemId' => $newItem->id,
                                        'modifierId' => $wgModifier->iiko_menu_item_modifier_item_id,
                                        'modifierName' => $wgModifier->name,
                                    ]);
                                }
                            }
                        }
                    }

                    // Добавляем позицию в IIKO
                    try {
                        $modifiers = [];
                        $orderItem->load(['modifiers', 'iikoMenuItem']);

                        foreach ($orderItem->modifiers as $modifier) {
                            $modifier->load('modifier');
                            $modifiers[] = new IikoModifier(
                                $modifier->modifier->external_id,
                                (float) number_format($modifier->modifier->prices->first()->price, 2, '.', ''),
                                $modifier->modifier->modifierGroup->external_id
                            );
                        }

                        $this->iikoConnector->addOrderItems(
                            new AddOrderItemsRequestData(
                                $organizationSetting->iikoRestaurantId->id,
                                $internalOrder->iikoExternalId->id,
                                [
                                    new Items(
                                        $orderItem->iikoMenuItem->external_id,
                                        $modifiers,
                                        (float) number_format($orderItem->price, 2, '.', ''),
                                        'Product',
                                        $orderItem->amount,
                                        null,
                                        null,
                                        $orderItem->comment
                                    ),
                                ]
                            ),
                            $this->authenticator->getAuthToken($organizationSetting->iikoApiKey)
                        );

                        logger()->info('Позиция успешно добавлена в IIKO', [
                            'orderId' => $wgOrder->id,
                            'itemId' => $newItem->id,
                        ]);

                        $addedItemsCount++;

                    } catch (\Throwable $e) {
                        logger()->error('Ошибка при добавлении позиции в IIKO', [
                            'orderId' => $wgOrder->id,
                            'itemId' => $newItem->id,
                            'error' => $e->getMessage(),
                        ]);
                    }

                } catch (\Throwable $e) {
                    logger()->error('Ошибка при добавлении новой позиции из Welcome Group', [
                        'orderId' => $wgOrder->id,
                        'itemId' => $newItem->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Выводим статистику по каждому типу блюда для отладки
            foreach ($activeWgItemsGrouped as $foodId => $items) {
                $localCount = isset($currentItemsGrouped[$foodId]) ? count($currentItemsGrouped[$foodId]) : 0;
                $wgCount = count($items);

                logger()->info('Сравнение количества позиций по типу блюда', [
                    'orderId' => $wgOrder->id,
                    'foodId' => $foodId,
                    'localCount' => $localCount,
                    'wgCount' => $wgCount,
                ]);
            }

            logger()->info('Обработка позиций заказа завершена', [
                'orderId' => $wgOrder->id,
                'addedItemsCount' => $addedItemsCount,
                'restoredItemsCount' => $restoredItemsCount,
            ]);
        } catch (\Throwable $e) {
            logger()->error('Ошибка при обработке изменений в позициях заказа', [
                'orderId' => $wgOrder->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * @throws WelcomeGroupImportOrdersGeneralException
     */
    private function createOrderInIiko(Order $order, ?GetAddressResponseData $address, GetClientResponseData $client, GetPhoneResponseData $phone, OrganizationSetting $organizationSetting): void
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
            //            $payments = null;
            //
            //            if ($order->payment) {
            //                $paymentType = $this->getPaymentTypeFromIikoByCode($order);
            //                $payments = [
            //                    new Payments(
            //                        $paymentType->paymentTypeKind,
            //                        (float) number_format($order->payment->amount, 2, '.', ''),
            //                        $paymentType->id,
            //                        true,
            //                        null,
            //                        false,
            //                        true
            //                    ),
            //                ];
            //            }

            $payments = null;

            if ($order->payments->isNotEmpty()) {
                $payments = $order->payments->map(function (OrderPayment $payment) use ($organizationSetting) {
                    $paymentType = $this->getPaymentTypeFromIikoByCode($payment, $organizationSetting);

                    return new Payments(
                        $paymentType->paymentTypeKind,
                        (float) number_format($payment->amount, 2, '.', ''),
                        $paymentType->id,
                        true,
                        null,
                        false,
                        true
                    );
                })->toArray();
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
                        $address
                            ? new DeliveryPoint(
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
                            ) : null,
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
