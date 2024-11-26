<?php

declare(strict_types=1);

namespace Application\Iiko\Services\Webhook;

use Application\Iiko\Factories\WebhookEventFactory;
use Domain\Settings\Interfaces\OrganizationSettingRepositoryInterface;
use Domain\Settings\OrganizationSetting;
use Illuminate\Support\LazyCollection;
use Presentation\Api\Requests\IikoWebhookRequest;

final readonly class WebhookHandler
{
    public function __construct(
        private WebhookEventFactory $webhookEventFactory,
        private OrganizationSettingRepositoryInterface $organizationSettingRepository,
    ) {}

    /**
     * @param  LazyCollection<array-key, IikoWebhookRequest>  $events
     */
    public function handle(LazyCollection $events): void
    {
        $organizationSettings = $this->organizationSettingRepository->all();

        $iikoRestaurantIds = $organizationSettings->map(
            static fn (OrganizationSetting $organizationSetting) => $organizationSetting->iikoRestaurantId->id,
        );

        $events = $events->filter(
            static fn (IikoWebhookRequest $iikoWebhookRequest): bool => $iikoRestaurantIds->contains(
                $iikoWebhookRequest->organizationId,
            ),
        );

        $this->webhookEventFactory->fromEventCollection($events);
    }
}
