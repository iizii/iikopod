<?php

declare(strict_types=1);

namespace Application\Iiko\Services\Menu;

use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Entities\Menu\ItemGroup;
use Domain\Iiko\Entities\Menu\ItemModifierGroup;
use Domain\Iiko\Entities\Menu\ItemSize;
use Domain\Iiko\Entities\Menu\Menu;
use Domain\Iiko\Entities\Menu\Nutrition;
use Domain\Iiko\Entities\Menu\Price;
use Domain\Iiko\Entities\Menu\ProductCategory;
use Domain\Iiko\Entities\Menu\TaxCategory;
use Domain\Iiko\Repositories\IikoMenuItemGroupRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuItemModifierGroupRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuItemModifierItemPriceRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuItemModifierItemRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuItemNutritionRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuItemPriceRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuItemRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuItemSizeRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuProductCategoryRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuTaxCategoryRepositoryInterface;
use Domain\Integrations\Iiko\IikoConnectorInterface;
use Domain\Settings\Interfaces\OrganizationSettingRepositoryInterface;
use Domain\Settings\OrganizationSetting;
use Domain\Settings\ValueObjects\PriceCategory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuRequestData;
use Infrastructure\Integrations\IIko\IikoAuthenticator;

final readonly class IikoImportMenuService
{
    public function __construct(
        private IikoAuthenticator $authenticator,
        private IikoConnectorInterface $iikoConnector,
        private DatabaseManager $databaseManager,
        private OrganizationSettingRepositoryInterface $organizationSettingRepository,
        private IikoMenuRepositoryInterface $menuRepository,
        private IikoMenuTaxCategoryRepositoryInterface $taxCategoryRepository,
        private IikoMenuProductCategoryRepositoryInterface $productCategoryRepository,
        private IikoMenuItemGroupRepositoryInterface $iikoMenuItemGroupRepository,
        private IikoMenuItemRepositoryInterface $iikoMenuItemRepository,
        private IikoMenuItemSizeRepositoryInterface $iikoMenuItemSizeRepository,
        private IikoMenuItemModifierGroupRepositoryInterface $iikoMenuItemModifierGroupRepository,
        private IikoMenuItemModifierItemRepositoryInterface $iikoMenuItemModifierItemRepository,
        private IikoMenuItemPriceRepositoryInterface $iikoMenuItemPriceRepository,
        private IikoMenuItemNutritionRepositoryInterface $iikoMenuItemNutritionRepository,
        private IikoMenuItemModifierItemPriceRepositoryInterface $iikoMenuItemModifierItemPriceRepository,
    ) {}

    /**
     * @throws ConnectionException
     * @throws RequestException
     * @throws \Throwable
     */
    public function handle(): void
    {
        $organizations = $this->organizationSettingRepository->all();

        $organizations->each(function (OrganizationSetting $organizationSetting): void {
            $organizationSetting->priceCategories->each(
                function (PriceCategory $priceCategory) use ($organizationSetting): void {
                    $responseEntity = $this
                        ->iikoConnector
                        ->getMenu(
                            new GetMenuRequestData(
                                [$organizationSetting->iikoRestaurantId->id],
                                $organizationSetting->externalMenuId->id,
                                $priceCategory->categoryId->id,
                            ),
                            $this->authenticator->getAuthToken($organizationSetting->iikoApiKey),
                        )
                        ->toDomainEntity();

                    $this
                        ->databaseManager
                        ->transaction(function () use ($priceCategory, $responseEntity): void {
                            $menu = Menu::withId(
                                $responseEntity,
                                $this->menuRepository->createOrUpdate($responseEntity)->id,
                            );

                            $this->handleTaxCategories($menu);
                            $this->handleProductCategories($menu, $priceCategory);
                            $this->handleItemGroups($menu);
                        });
                },
            );
        });
    }

    private function handleMenu(Menu $menu): Menu
    {
        return $this->menuRepository->createOrUpdate($menu);
    }

    private function handleTaxCategories(Menu $menu): void
    {
        $menu
            ->taxCategories
            ->each(function (TaxCategory $taxCategory) use ($menu) {
                $this->taxCategoryRepository->createOrUpdate(TaxCategory::withMenuId($taxCategory, $menu->id));
            });
    }

    private function handleProductCategories(Menu $menu, PriceCategory $priceCategory): void
    {
        $menu
            ->productCategories
            ->each(function (ProductCategory $productCategory) use ($priceCategory, $menu) {
                $this->productCategoryRepository->createOrUpdate(
                    ProductCategory::withMenuIdAndPrefix(
                        $productCategory,
                        $menu->id,
                        $priceCategory->prefix,
                    ),
                );
            });
    }

    private function handleItemGroups(Menu $menu): void
    {
        $menu
            ->itemGroups
            ->each(function (ItemGroup $itemGroup) use ($menu) {
                $this->handleItemGroupItems(
                    ItemGroup::withId(
                        $itemGroup,
                        $this
                            ->iikoMenuItemGroupRepository
                            ->createOrUpdate(ItemGroup::withMenuId($itemGroup, $menu->id))
                            ->id,
                    ),
                );
            });
    }

    private function handleItemGroupItems(ItemGroup $itemGroup): void
    {
        $itemGroup
            ->items
            ->each(function (Item $item) use ($itemGroup) {
                $this->handleItemGroupItemSizes(
                    Item::withId(
                        $item,
                        $this
                            ->iikoMenuItemRepository
                            ->createOrUpdate(Item::withItemGroupId($item, $itemGroup->id))
                            ->id,
                    ),
                );
            });
    }

    private function handleItemGroupItemSizes(Item $item): void
    {
        $item
            ->itemSizes
            ->each(function (ItemSize $itemSize) use ($item) {
                $itemSize = ItemSize::withId(
                    $itemSize,
                    $this
                        ->iikoMenuItemSizeRepository
                        ->createOrUpdate(ItemSize::withItemId($itemSize, $item->id))
                        ->id,
                );

                $this->handleItemSizePrices($itemSize);
                $this->handleItemSizeNutritions($itemSize);
                $this->handleItemModifierGroups($itemSize);
            });
    }

    private function handleItemSizePrices(ItemSize $itemSize): void
    {
        $itemSize
            ->prices
            ->each(function (Price $price) use ($itemSize) {
                $this->iikoMenuItemPriceRepository->createOrUpdate(Price::withItemId($price, $itemSize->id));
            });
    }

    private function handleItemSizeNutritions(ItemSize $itemSize): void
    {
        $itemSize
            ->nutritions
            ->each(function (Nutrition $nutrition) use ($itemSize) {
                $this->iikoMenuItemNutritionRepository->createOrUpdate(
                    Nutrition::withItemId($nutrition, $itemSize->id),
                );
            });
    }

    private function handleItemModifierGroups(ItemSize $itemSize): void
    {
        $itemSize
            ->itemModifierGroups
            ->each(function (ItemModifierGroup $itemModifierGroup) use ($itemSize) {
                $this->handleModifierItems(
                    ItemModifierGroup::withId(
                        $itemModifierGroup,
                        $this
                            ->iikoMenuItemModifierGroupRepository
                            ->createOrUpdate(ItemModifierGroup::withItemSizeId($itemModifierGroup, $itemSize->id))
                            ->id,
                    ),
                );
            });
    }

    private function handleModifierItems(ItemModifierGroup $modifierGroup): void
    {
        $modifierGroup
            ->items
            ->each(function (Item $item) use ($modifierGroup) {
                $this->handleModifierItemPrices(
                    Item::withId(
                        $item,
                        $this
                            ->iikoMenuItemModifierItemRepository
                            ->createOrUpdate(Item::withItemGroupId($item, $modifierGroup->id))
                            ->id,
                    ),
                );
            });
    }

    private function handleModifierItemPrices(Item $itemSize): void
    {
        $itemSize
            ->prices
            ->each(function (Price $price) use ($itemSize) {
                $this->iikoMenuItemModifierItemPriceRepository->createOrUpdate(
                    Price::withItemId($price, $itemSize->id),
                );
            });
    }
}
