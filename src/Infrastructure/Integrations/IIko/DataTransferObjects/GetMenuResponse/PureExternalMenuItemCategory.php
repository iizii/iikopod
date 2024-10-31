<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

final class PureExternalMenuItemCategory extends ResponseData
{
    /**
     * @param  DataCollection<array-key, Item>  $items
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $description,
        public readonly ?string $buttonImageUrl,
        public readonly ?string $headerImageUrl,
        public readonly ?string $iikoGroupId,
        #[DataCollectionOf(Item::class)]
        public readonly DataCollection $items,
        public readonly ?string $scheduleId,
        public readonly ?string $scheduleName,
        public readonly bool $isHidden
    ) {}
}
