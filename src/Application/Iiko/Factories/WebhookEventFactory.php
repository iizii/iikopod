<?php

declare(strict_types=1);

namespace Application\Iiko\Factories;

use Application\Iiko\Requests\IikoWebhookRequest;
use Domain\Iiko\Enums\WebhookEventType;
use Domain\Iiko\Exceptions\IikoEventTypeNotFountException;
use Illuminate\Contracts\Events\Dispatcher;

final readonly class WebhookEventFactory
{
    public function __construct(private Dispatcher $dispatcher, private WebhookEventDataFactory $dataFactory) {}

    /**
     * @param  iterable<IikoWebhookRequest>  $events
     */
    public function fromEventCollection(iterable $events): void
    {
        foreach ($events as $event) {
            $this->dispatchEventFromRequest($event);
        }
    }

    public function dispatchEventFromRequest(IikoWebhookRequest $request): void
    {
        $eventMap = WebhookEventType::eventMap();

        if (! array_key_exists($request->eventType->value, $eventMap)) {
            throw new IikoEventTypeNotFountException();
        }

        $this->dispatcher->dispatch(
            new $eventMap[$request->eventType->value](
                $this->dataFactory->fromRequest($request),
            ),
        );
    }
}
