<?php

declare(strict_types=1);

namespace Domain\Iiko\Interfaces;

use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenu;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItem;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemGroup;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierGroup;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierItem;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemNutrition;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemPrice;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemSize;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuProductCategory;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuTaxCategory;

interface ImportIikoMenuVisitorInterface
{
    public function visitMenu(IikoMenu $menu): void;

    public function visitTaxCategory(IikoMenuTaxCategory $taxCategory): void;

    public function visitProductCategory(IikoMenuProductCategory $productCategory): void;

    public function visitItemGroup(IikoMenuItemGroup $itemGroup): void;

    public function visitItem(IikoMenuItem $item): void;

    public function visitItemSize(IikoMenuItemSize $itemSize): void;

    public function visitModifierGroup(IikoMenuItemModifierGroup $modifierGroup): void;

    public function visitModifierItem(IikoMenuItemModifierItem $modifierItem): void;

    public function visitPrice(IikoMenuItemPrice $price): void;

    public function visitNutrition(IikoMenuItemNutrition $nutrition): void;
}
