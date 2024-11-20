<?php

declare(strict_types=1);

namespace Presentation\Api\Controllers;

use Application\Iiko\Factories\WebhookEventFactory;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Presentation\Api\Requests\IikoWebhookRequest;
use Shared\Presentation\Middleware\ContextualLogMiddleware;
use Spatie\RouteAttributes\Attributes\Route;

final readonly class IikoWebhookController
{
    public function __construct(
        private ResponseFactory $responseFactory,
        private WebhookEventFactory $webhookEventFactory,
    ) {}

    #[Route(methods: 'POST', uri: '/iiko/webhook', name: 'iiko.webhook', middleware: [ContextualLogMiddleware::class])]
    public function __invoke(Request $request): JsonResponse
    {
        $this->webhookEventFactory->fromEventCollection(IikoWebhookRequest::collect($request->all()));

        return $this->responseFactory->json();
    }
}
