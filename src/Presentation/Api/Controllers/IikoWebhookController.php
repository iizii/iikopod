<?php

declare(strict_types=1);

namespace Presentation\Api\Controllers;

use Application\Iiko\Services\Webhook\WebhookHandler;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\LazyCollection;
use Presentation\Api\Requests\IikoWebhookRequest;
use Shared\Presentation\Middleware\ContextualLogMiddleware;
use Spatie\RouteAttributes\Attributes\Route;

final readonly class IikoWebhookController
{
    public function __construct(
        private ResponseFactory $responseFactory,
        private WebhookHandler $webhookHandler,
    ) {}

    #[Route(methods: 'POST', uri: '/iiko/webhook', name: 'iiko.webhook', middleware: [ContextualLogMiddleware::class])]
    public function __invoke(Request $request): JsonResponse
    {
        $this->webhookHandler->handle(IikoWebhookRequest::collect($request->all(), LazyCollection::class));

        return $this->responseFactory->json();
    }
}
