<?php

declare(strict_types=1);

namespace Presentation\Api\Controllers;

use Application\Iiko\Services\Webhook\WebhookHandler;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use Presentation\Api\Requests\IikoWebhookRequest;
use Spatie\RouteAttributes\Attributes\Route;

final readonly class IikoWebhookTestController
{
    public function __construct(
        private ResponseFactory $responseFactory,
        private WebhookHandler $webhookHandler,
    ) {}

    #[Route(methods: 'GET', uri: '/iiko/webhook-test', name: 'iiko.webhook-test')]
    public function __invoke(Request $request): JsonResponse
    {
        $this->webhookHandler->handle(
            IikoWebhookRequest::collect(
                [
                    [
                        'eventType' => 'DeliveryOrderUpdate',
                        'eventTime' => '2024-11-17 09:40:07.150',
                        'organizationId' => '7540c976-5894-496c-b01b-816366df27da',
                        'correlationId' => '9ed64af5-d2d4-4486-be7d-c0cfe511064c',
                        'eventInfo' => [
                            'id' => Str::uuid(),
                            'posId' => '0b2437f5-e60b-43c8-8acf-f8559ce4402e',
                            'externalNumber' => '241117-8100018',
                            'organizationId' => '7540c976-5894-496c-b01b-816366df27da',
                            'timestamp' => 1731836407121,
                            'creationStatus' => 'Success',
                            'errorInfo' => null,
                            'order' => [
                                'parentDeliveryId' => null,
                                'customer' => [
                                    'type' => 'one-time',
                                    'name' => 'Яндекс.Еда',
                                ],
                                'phone' => '+78006001310',
                                'deliveryPoint' => null,
                                'status' => 'Closed',
                                'cancelInfo' => null,
                                'courierInfo' => null,
                                'completeBefore' => '2024-11-17 13:54:45.000',
                                'whenCreated' => '2024-11-17 13:36:35.129',
                                'whenConfirmed' => '2024-11-17 13:36:35.129',
                                'whenPrinted' => '2024-11-17 13:36:51.945',
                                'whenCookingCompleted' => null,
                                'whenSended' => null,
                                'whenDelivered' => null,
                                'comment' => '#241117-8100018.;; Доставка курьерами Яндекса;YANDEX_FOOD: #241117-8100018.',
                                'problem' => null,
                                'operator' => [
                                    'id' => 'e1f21b5c-4450-41c2-bc41-ca6f7840ee28',
                                    'name' => 'Гуломова Н.',
                                    'phone' => null,
                                ],
                                'marketingSource' => null,
                                'deliveryDuration' => 15,
                                'indexInCourierRoute' => null,
                                'cookingStartTime' => '2024-11-17 13:39:45.000',
                                'isDeleted' => false,
                                'whenReceivedByApi' => '2024-11-17 09:39:48.609',
                                'whenReceivedFromFront' => '2024-11-17 09:39:48.966',
                                'movedFromDeliveryId' => null,
                                'movedFromTerminalGroupId' => null,
                                'movedFromOrganizationId' => null,
                                'externalCourierService' => null,
                                'movedToDeliveryId' => null,
                                'movedToTerminalGroupId' => null,
                                'movedToOrganizationId' => null,
                                'menuId' => null,
                                'deliveryZone' => null,
                                'estimatedTime' => null,
                                'isAsap' => null,
                                'whenPacked' => null,
                                'sum' => 1620,
                                'number' => 6482,
                                'sourceKey' => 'yandex_food',
                                'whenBillPrinted' => null,
                                'whenClosed' => null,
                                'conception' => [
                                    'id' => 'b0ac772c-cde2-465e-8539-e380bc31bc59',
                                    'name' => 'Тольятти - Ресторан',
                                    'code' => '31',
                                ],
                                'guestsInfo' => [
                                    'count' => 1,
                                    'splitBetweenPersons' => false,
                                ],
                                'items' => [
                                    [
                                        'type' => 'Product',
                                        'product' => [
                                            'id' => '7ee115e3-309f-4a36-b3d4-4be0823e70de',
                                            'name' => 'Чикен филе сладко-острый',
                                        ],
                                        'modifiers' => [
                                        ],
                                        'price' => 750,
                                        'cost' => 750,
                                        'pricePredefined' => true,
                                        'positionId' => '71d1eedd-5481-449c-9e72-04652ce0b36e',
                                        'taxPercent' => null,
                                        'resultSum' => 750,
                                        'status' => 'CookingStarted',
                                        'deleted' => null,
                                        'amount' => 1,
                                        'comment' => null,
                                        'whenPrinted' => '2024-11-17 13:36:51.945',
                                        'size' => null,
                                        'comboInformation' => null,
                                    ],
                                ],
                                'combos' => [
                                ],
                                'payments' => [
                                    [
                                        'paymentType' => [
                                            'id' => '010a22e4-db52-4438-ae9a-2f6dcb763656',
                                            'name' => 'Яндекс.Еда',
                                            'kind' => 'Card',
                                        ],
                                        'sum' => 750,
                                        'isPreliminary' => false,
                                        'isExternal' => true,
                                        'isProcessedExternally' => true,
                                        'isFiscalizedExternally' => false,
                                        'isPrepay' => false,
                                    ],
                                ],
                                'tips' => [
                                ],
                                'discounts' => [
                                ],
                                'orderType' => [
                                    'id' => '6b5ffbde-1006-4d0e-b2e4-c21d22ef4e07',
                                    'name' => 'Яндекс Еда Курьеры Я.Еды',
                                    'orderServiceType' => 'DeliveryByClient',
                                ],
                                'terminalGroupId' => 'a4bbe43c-e4bf-4d45-9189-e31b1cff624c',
                                'processedPaymentsSum' => 1630,
                                'loyaltyInfo' => [
                                    'coupon' => null,
                                    'appliedManualConditions' => null,
                                ],
                                'externalData' => null,
                            ],
                        ],
                    ],
                ],
                LazyCollection::class,
            ),
        );

        return $this->responseFactory->json();
    }
}
