<?php

declare(strict_types=1);

namespace Application\Iiko\Factories;

use Domain\Iiko\Enums\WebhookEventType;
use Domain\Iiko\Exceptions\IikoEventTypeNotFoundException;
use Exception;
use Illuminate\Contracts\Events\Dispatcher;
use Infrastructure\Persistence\Eloquent\Settings\Models\OrganizationSetting;
use Presentation\Api\Requests\IikoWebhookRequest;

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
//        $organizationSetting = OrganizationSetting::query()
//            ->where('iiko_restaurant_id', $request->organizationId)
//            ->first();

        $eventMap = WebhookEventType::eventMap();

        if (! array_key_exists($request->eventType->value, $eventMap)) {
            throw new IikoEventTypeNotFoundException();
        }

        $this->dispatcher->dispatch(
            new $eventMap[$request->eventType->value](
                $this->dataFactory->fromRequest($request),
            ),
        );
    }
}
