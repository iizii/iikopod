<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Domain\Iiko\Entities\Menu\Menu;
use Domain\Iiko\Entities\Menu\ProductCategory as DomainProductCategory;
use Domain\Iiko\Entities\Menu\PureExternalMenuItemCategory as DomainPureExternalMenuItemCategory;
use Domain\Iiko\ValueObjects\Menu\ProductCategoryCollection;
use Domain\Iiko\ValueObjects\Menu\PureExternalMenuItemCollection;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

final class GetMenuResponseData extends ResponseData
{
    /**
     * @param  DataCollection<array-key, ProductCategory>  $productCategories
     * @param  DataCollection<array-key, PureExternalMenuItemCategory>  $pureExternalMenuItemCategories
     */
    public function __construct(
        public readonly int $id,
        public readonly int $revision,
        public readonly string $name,
        public readonly string $description,
        public readonly ?string $buttonImageUrl,
        #[DataCollectionOf(ProductCategory::class)]
        public readonly DataCollection $productCategories,
        #[DataCollectionOf(PureExternalMenuItemCategory::class)]
        public readonly DataCollection $pureExternalMenuItemCategories,
    ) {}

    public function toDomainEntity(): Menu
    {
        return new Menu(
            new IntegerId($this->id),
            $this->revision,
            $this->name,
            $this->description,
            $this->buttonImageUrl,
            new ProductCategoryCollection(
                $this
                    ->productCategories
                    ->toCollection()
                    ->map(static fn (ProductCategory $productCategory): DomainProductCategory => $productCategory->toDomainEntity())
            ),
            new PureExternalMenuItemCollection(
                $this
                    ->pureExternalMenuItemCategories
                    ->toCollection()
                    ->map(static fn (PureExternalMenuItemCategory $category): DomainPureExternalMenuItemCategory => $category->toDomainEntity()),
            ),
        );
    }
}
