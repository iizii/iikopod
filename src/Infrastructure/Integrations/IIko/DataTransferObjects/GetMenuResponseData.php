<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects;

use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Data;

final class GetMenuResponseData extends ResponseData
{
    /**
     * @param  TaxCategoryData[]  $taxCategories
     * @param  ProductCategoryData[]  $productCategories
     * @param  AllergenGroupData[]  $allergenGroups
     * @param  CustomerTagGroupData[]  $customerTagGroups
     * @param  IntervalData[]  $intervals
     * @param  PureExternalMenuItemCategoryData[]  $pureExternalMenuItemCategories
     */
    public function __construct(
        public readonly array $taxCategories,
        public readonly array $productCategories,
        public readonly array $allergenGroups,
        public readonly array $customerTagGroups,
        public readonly int $revision,
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $buttonImageUrl,
        public readonly array $intervals,
        public readonly array $pureExternalMenuItemCategories,
        //        public readonly ?string $scheduleId,
        //        public readonly ?string $scheduleName,
        //        public readonly array   $schedules,
        //        public readonly bool $isHidden
    ) {}
}

final class TaxCategoryData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $name
    ) {}
}

final class ProductCategoryData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly bool $deleted,
        public readonly ?float $vatPercent = null
    ) {}
}

/**
 * @param  AllergenData[]  $allergens
 */
final class AllergenGroupData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly array $allergens = [] // Array of AllergenData
    ) {}
}

final class AllergenData extends Data
{
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $name,
        public readonly ?string $code
    ) {}
}

/**
 * @param  CustomerTagData[]  $customerTags
 */
final class CustomerTagGroupData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly array $customerTags // Array of CustomerTagData
    ) {}
}

final class CustomerTagData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $name
    ) {}
}

final class IntervalData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $startTime,
        public readonly string $endTime
    ) {}
}

/**
 * @param  MenuItemData[]  $items
 * @param  IntervalData[]  $schedules
 */
final class PureExternalMenuItemCategoryData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $buttonImageUrl,
        public readonly ?string $headerImageUrl,
        public readonly array $items, // Array of MenuItemData
        public readonly ?string $scheduleId,
        public readonly ?string $scheduleName,
        public readonly array $schedules, // Array of IntervalData
        public readonly bool $isHidden
    ) {}
}


/**
 * @param  AllergenGroupData[]  $allergenGroups
 * @param  TagData[]  $tags
 * @param  LabelData[]  $labels
 * @param  ItemSizeData[]  $itemSizes
 * @param  CustomerTagGroupData[]  $customerTagGroups
 */
final class MenuItemData extends Data
{
    public function __construct(
        public readonly string $sku,
        public readonly string $name,
        public readonly ?string $description,
        public readonly array $allergenGroups, // Array of AllergenGroupData
        public readonly array $tags, // Array of TagData
        public readonly array $labels, // Array of LabelData
        public readonly array $itemSizes, // Array of ItemSizeData
        public readonly ?string $iikoItemId,
        public readonly ?string $iikoModifierSchemaId,
        public readonly ?string $taxCategory,
        public readonly ?string $iikoModifierSchemaName,
        public readonly string $type,
        public readonly bool $canBeDivided,
        public readonly bool $canSetOpenPrice,
        public readonly bool $useBalanceForSell,
        public readonly string $measureUnit,
        public readonly ?string $productCategoryId,
        public readonly array $customerTagGroups, // Array of CustomerTagGroupData
        public readonly string $paymentSubject,
        public readonly ?string $outerEanCode,
        public readonly bool $isHidden,
        public readonly string $orderItemType,
        public readonly ?string $taxCategoryId,
        public readonly array $allergenGroupIds,
        public readonly array $labelNames,
        public readonly array $tagNames
    ) {}
}

/**
 * @param  ModifierGroupData[]  $itemModifierGroups
 * @param  PriceData[]  $prices
 */
final class ItemSizeData extends Data
{
    public function __construct(
        public readonly string $sku,
        public readonly string $sizeCode,
        public readonly string $sizeName,
        public readonly bool $isDefault,
        public readonly int $portionWeightGrams,
        public readonly array $itemModifierGroups, // Array of ModifierGroupData
        public readonly array $prices // Array of PriceData
    ) {}
}

///**
// * @param  ModifierItemData[]  $items
// */
//final class ModifierGroupData extends Data
//{
//    public function __construct(
//        public readonly string $id,
//        public readonly string $name,
//        public readonly ?string $description,
//        public readonly array $items // Array of ModifierItemData
//    ) {}
//}
//
///**
// * @param  PriceData[]  $prices
// */
//final class ModifierItemData extends Data
//{
//    public function __construct(
//        public readonly string $id,
//        public readonly string $name,
//        public readonly bool $isRequired,
//        public readonly int $minAmount,
//        public readonly int $maxAmount,
//        public readonly array $prices // Array of PriceData
//    ) {}
//}
//
/////**
//// * @param  OrganizationData[]  $organizations
//// */
//final class PriceData extends Data
//{
//    public function __construct(
//        public readonly array $organizations, // Array of OrganizationData
//        public readonly ?float $price
//    ) {}
//}
//
////final class OrganizationData extends Data
////{
////    public function __construct(
////        public readonly ?string $id,
////        public readonly ?string $name
////    ) {}
////}
//
//final class TagData extends Data
//{
//    public function __construct(
//        public readonly string $id,
//        public readonly string $name
//    ) {}
//}
//
//final class LabelData extends Data
//{
//    public function __construct(
//        public readonly string $id,
//        public readonly string $name
//    ) {}
//}

/**
 * @property ModifierItemData[] $items
 */
final class ModifierGroupData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly RestrictionsData $restrictions,
        public readonly array $items, // Array of ModifierItemData
        public readonly bool $canBeDivided,
        public readonly ?string $iikoItemGroupId,
        public readonly bool $hidden,
        public readonly bool $childModifiersHaveMinMaxRestrictions,
        public readonly string $sku
    ) {}
}

/**
 * @property PriceData[] $prices
 * @property AllergenGroupData[] $allergenGroups
 * @property TagData[] $tags
 * @property LabelData[] $labels
 */
final class ModifierItemData extends Data
{

    /**
     * @param AllergenGroupData[] $allergenGroups
     * @param TagData[] $tags
     * @param LabelData[] $labels
     * @param PriceData[] $prices
     * @param NutritionData[] $nutritions
     */
    public function __construct(
        public readonly string $sku,
        public readonly string $name,
        public readonly ?string $description,
        public readonly RestrictionsData $restrictions,
        public readonly array $allergenGroups, // Array of AllergenGroupData
        public readonly NutritionData $nutritionPerHundredGrams,
        public readonly int $portionWeightGrams,
        public readonly array $tags, // Array of TagData
        public readonly array $labels, // Array of LabelData
        public readonly ?string $iikoItemId,
        public readonly bool $hidden,
        public readonly array $prices, // Array of PriceData
        public readonly array $nutritions, // Array of NutritionData
        public readonly int $position,
        public readonly ?string $taxCategoryId,
        public readonly bool $independentQuantity,
        public readonly ?string $productCategoryId,
        public readonly array $customerTagGroups, // Array of CustomerTagGroupData
        public readonly ?string $paymentSubject,
        public readonly ?string $outerEanCode,
        public readonly string $measureUnitType,
        public readonly ?string $buttonImageUrl,
        public readonly array $allergenGroupIds,
        public readonly array $labelNames,
        public readonly array $tagNames
    ) {}
}

final class RestrictionsData extends Data
{
    public function __construct(
        public readonly int $minQuantity,
        public readonly int $maxQuantity,
        public readonly int $freeQuantity,
        public readonly int $byDefault,
        public readonly bool $hideIfDefaultQuantity
    ) {}
}

final class NutritionData extends Data
{
    /**
     * @param string[] $organizations
     */
    public function __construct(
        public readonly float $fats,
        public readonly float $proteins,
        public readonly float $carbs,
        public readonly float $energy,
        public readonly array $organizations, // Array of OrganizationData
        public readonly ?float $saturatedFattyAcid,
        public readonly ?float $salt,
        public readonly ?float $sugar
    ) {}
}

/**
 * @param string[] $organizations
 */
final class PriceData extends Data
{
    /**
     * @param string[] $organizations
     */
    public function __construct(
        public readonly array $organizations, // Array of strings representing organization IDs
        public readonly ?float $price
    ) {}
}

final class OrganizationData extends Data
{
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $name
    ) {}
}

final class TagData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $name
    ) {}
}

final class LabelData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $name
    ) {}
}
