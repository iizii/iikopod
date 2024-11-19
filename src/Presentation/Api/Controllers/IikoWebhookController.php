<?php

declare(strict_types=1);

namespace Presentation\Api\Controllers;

use Application\Iiko\Factories\WebhookEventFactory;
use Application\Iiko\Requests\IikoWebhookRequest;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Route;

final readonly class IikoWebhookController
{
    public function __construct(private ResponseFactory $responseFactory, private WebhookEventFactory $webhookEventFactory) {}

    #[Route(methods: 'POST', uri: '/iiko/webhook', name: 'iiko.webhook')]
    public function __invoke(Request $request): JsonResponse
    {
        logger()->channel('iiko_webhook')->info('Полученный реквест', [
            $request,
        ]);
        $this->webhookEventFactory->fromEventCollection(IikoWebhookRequest::collect($request->all()));

        return $this->responseFactory->json();
    }
}
