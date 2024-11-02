<?php

declare(strict_types=1);

namespace Presentation\Api\Controllers;

use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Entities\Menu\ItemGroup;
use Domain\Iiko\Entities\Menu\ItemModifierGroup;
use Domain\Iiko\Entities\Menu\ItemSize;
use Domain\Iiko\Entities\Menu\Menu;
use Domain\Iiko\Entities\Menu\Nutrition;
use Domain\Iiko\Entities\Menu\Price;
use Domain\Iiko\Entities\Menu\ProductCategory;
use Domain\Iiko\Entities\Menu\TaxCategory;
use Domain\Integrations\Iiko\IikoConnectorInterface;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Infrastructure\Integrations\IIko\DataTransferObjects\AuthorizationResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetExternalMenusWithPriceCategoriesRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetExternalMenusWithPriceCategoriesResponse\GetExternalMenusWithPriceCategoriesResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetOrganizationRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetPaymentTypesRequestData;
use Infrastructure\Integrations\IIko\Requests\AuthorizationRequest;
use Infrastructure\Integrations\IIko\Requests\GetExternalMenusWithPriceCategoriesRequest;
use Infrastructure\Integrations\IIko\Requests\GetMenuRequest;
use Infrastructure\Integrations\IIko\Requests\GetOrganizationsRequest;
use Infrastructure\Integrations\IIko\Requests\GetPaymentTypesRequest;
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
use Spatie\RouteAttributes\Attributes\Route;

final readonly class IikoController
{
    public function __construct(private ResponseFactory $responseFactory, private IikoConnectorInterface $connector) {}

    /**
     * @throws ConnectionException
     * @throws RequestException
     */
    #[Route(methods: 'POST', uri: '/iiko/auth', name: 'iiko.auth')]
    public function auth(Request $request): JsonResponse
    {
        $req = new AuthorizationRequest((string) $request->input('token'));
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'POST', uri: '/iiko/get_organizations', name: 'iiko.get_organizations')]
    public function getOrganizations(Request $request): JsonResponse
    {
        $authReq = new AuthorizationRequest((string) $request->input('token'));

        /** @var AuthorizationResponseData $authRes */
        $authRes = $this->connector->send($authReq);

        $getOrganizationsData = new GetOrganizationRequestData([], true, false, []);
        $req = new GetOrganizationsRequest($getOrganizationsData, $authRes->token);
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'POST', uri: '/iiko/get_payment_types', name: 'iiko.get_payment_types')]
    public function getPaymentTypes(Request $request): JsonResponse
    {
        $authReq = new AuthorizationRequest((string) $request->input('token'));

        /** @var AuthorizationResponseData $authRes */
        $authRes = $this->connector->send($authReq);

        $getPaymentTypesData = new GetPaymentTypesRequestData($request->input('organizationIds'));
        $req = new GetPaymentTypesRequest($getPaymentTypesData, $authRes->token);
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'POST', uri: '/iiko/get_external_menus_with_price_categories', name: 'iiko.get_external_menus_with_price_categories')]
    public function getExternalMenusWithPriceCategories(Request $request): JsonResponse
    {
        $authReq = new AuthorizationRequest((string) $request->input('token'));

        /** @var AuthorizationResponseData $authRes */
        $authRes = $this->connector->send($authReq);

        $getExternalMenusWithPriceCategoriesData = new GetExternalMenusWithPriceCategoriesRequestData(
            $request->input('organizationIds'),
        );
        $req = new GetExternalMenusWithPriceCategoriesRequest(
            $getExternalMenusWithPriceCategoriesData, $authRes->token,
        );
        /** @var GetExternalMenusWithPriceCategoriesResponseData $response */
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'POST', uri: '/iiko/get_menu', name: 'iiko.get_menu')]
    public function getMenu(Request $request): JsonResponse
    {
        $authReq = new AuthorizationRequest((string) $request->input('token'));

        /** @var AuthorizationResponseData $authRes */
        $authRes = $this->connector->send($authReq);

        $getExternalMenusWithPriceCategoriesData = new GetMenuRequestData([$request->input('organizationId')],
            $request->input('externalMenuId'),
            $request->input('priceCategoryId'));
        $req = new GetMenuRequest($getExternalMenusWithPriceCategoriesData, $authRes->token);
        $response = $this->connector->send($req);

        /** @var Menu $entity */
        $entity = $response->toDomainEntity();

        $this->test($entity);

        return $this->responseFactory->json($entity, 200);
    }

    private function test(Menu $entity): void
    {
        $iikoMenu = IikoMenu::query()
            ->where('external_id', $entity->externalId->id)
            ->first() ?? new IikoMenu();

        $iikoMenu->external_id = $entity->externalId->id;
        $iikoMenu->revision = $entity->revision;
        $iikoMenu->name = $entity->name;
        $iikoMenu->description = $entity->description;

        $iikoMenu->save();

        $entity
            ->taxCategories
            ->each(static function (TaxCategory $category) use ($iikoMenu) {
                $iikoMenuTaxCategory = IikoMenuTaxCategory::query()
                    ->where('iiko_menu_id', $iikoMenu->id)
                    ->where('external_id', $category->externalId->id)
                    ->first() ?? new IikoMenuTaxCategory();

                $iikoMenuTaxCategory->iikoMenu()->associate($iikoMenu);
                $iikoMenuTaxCategory->external_id = $category->externalId->id;
                $iikoMenuTaxCategory->name = $category->name;
                $iikoMenuTaxCategory->percentage = $category->percentage;

                $iikoMenuTaxCategory->save();
            });

        $entity
            ->productCategories
            ->each(static function (ProductCategory $category) use ($iikoMenu) {
                $iikoMenuProductCategory = IikoMenuProductCategory::query()
                    ->where('iiko_menu_id', $iikoMenu->id)
                    ->where('external_id', $category->externalId->id)
                    ->first() ?? new IikoMenuProductCategory();

                $iikoMenuProductCategory->iikoMenu()->associate($iikoMenu);
                $iikoMenuProductCategory->external_id = $category->externalId->id;
                $iikoMenuProductCategory->name = $category->name;
                $iikoMenuProductCategory->is_deleted = $category->isDeleted;
                $iikoMenuProductCategory->percentage = $category->percentage;

                $iikoMenuProductCategory->save();
            });

        $entity
            ->itemGroups
            ->each(static function (ItemGroup $itemGroup) use ($iikoMenu) {
                $iikoMenuItemGroup = IikoMenuItemGroup::query()
                    ->where('iiko_menu_id', $iikoMenu->id)
                    ->where('external_id', $itemGroup->externalId->id)
                    ->first() ?? new IikoMenuItemGroup();

                $iikoMenuItemGroup->iikoMenu()->associate($iikoMenu);
                $iikoMenuItemGroup->external_id = $itemGroup->externalId->id;
                $iikoMenuItemGroup->name = $itemGroup->name;
                $iikoMenuItemGroup->description = $itemGroup->description;
                $iikoMenuItemGroup->is_hidden = $itemGroup->isHidden;

                $iikoMenuItemGroup->save();

                $itemGroup
                    ->items
                    ->each(static function (Item $item) use ($iikoMenuItemGroup) {
                        $iikoMenuItem = IikoMenuItem::query()
                            ->where('iiko_menu_item_group_id', $iikoMenuItemGroup->id)
                            ->where('external_id', $item->externalId->id)
                            ->first() ?? new IikoMenuItem();

                        $iikoMenuItem->itemGroup()->associate($iikoMenuItemGroup);
                        $iikoMenuItem->external_id = $item->externalId->id;
                        $iikoMenuItem->sku = $item->sku;
                        $iikoMenuItem->name = $item->name;
                        $iikoMenuItem->description = $item->description;
                        $iikoMenuItem->type = $item->type;
                        $iikoMenuItem->measure_unit = $item->measureUnit;
                        $iikoMenuItem->payment_subject = $item->paymentSubject;
                        $iikoMenuItem->is_hidden = $item->isHidden;

                        $iikoMenuItem->save();

                        $item
                            ->itemSizes
                            ->each(static function (ItemSize $itemSize) use ($iikoMenuItem) {
                                $iikoMenuItemSize = IikoMenuItemSize::query()
                                    ->where('iiko_menu_item_id', $iikoMenuItem->id)
                                    ->where('external_id', $itemSize->externalId->id)
                                    ->first() ?? new IikoMenuItemSize();

                                $iikoMenuItemSize->menuItem()->associate($iikoMenuItem);
                                $iikoMenuItemSize->external_id = $itemSize->externalId->id;
                                $iikoMenuItemSize->sku = $itemSize->sku;
                                $iikoMenuItemSize->is_default = $itemSize->isDefault;
                                $iikoMenuItemSize->weight = $itemSize->weight;
                                $iikoMenuItemSize->measure_unit_type = $itemSize->measureUnitType;

                                $iikoMenuItemSize->save();

                                $itemSize
                                    ->itemModifierGroups
                                    ->each(
                                        static function (ItemModifierGroup $itemModifierGroup) use ($iikoMenuItemSize) {
                                            $iikoMenuItemModifierGroup = IikoMenuItemModifierGroup::query()
                                                ->where('iiko_menu_item_size_id', $iikoMenuItemSize->id)
                                                ->where('external_id', $itemModifierGroup->externalId->id)
                                                ->first() ?? new IikoMenuItemModifierGroup();

                                            $iikoMenuItemModifierGroup->itemSize()->associate($iikoMenuItemSize);
                                            $iikoMenuItemModifierGroup->external_id = $itemModifierGroup->externalId->id;
                                            $iikoMenuItemModifierGroup->name = $itemModifierGroup->name;
                                            $iikoMenuItemModifierGroup->description = $itemModifierGroup->description;
                                            $iikoMenuItemModifierGroup->splittable = $itemModifierGroup->splittable;
                                            $iikoMenuItemModifierGroup->is_hidden = $itemModifierGroup->isHidden;
                                            $iikoMenuItemModifierGroup->child_modifiers_have_min_max_restrictions = $itemModifierGroup->childModifiersHaveMinMaxRestrictions;
                                            $iikoMenuItemModifierGroup->sku = $itemModifierGroup->sku;

                                            $iikoMenuItemModifierGroup->save();

                                            $itemModifierGroup
                                                ->items
                                                ->each(static function (Item $itemModifierGroup) use ($iikoMenuItemModifierGroup) {
                                                    $iikoMenuItemModifierItem = IikoMenuItemModifierItem::query()
                                                        ->where('iiko_menu_item_modifier_group_id', $iikoMenuItemModifierGroup->id)
                                                        ->where('external_id', $itemModifierGroup->externalId->id)
                                                        ->first() ?? new IikoMenuItemModifierItem();

                                                    $iikoMenuItemModifierItem->modifierGroup()->associate($iikoMenuItemModifierGroup);
                                                    $iikoMenuItemModifierItem->external_id = $itemModifierGroup->externalId->id;
                                                    $iikoMenuItemModifierItem->sku = $itemModifierGroup->sku;
                                                    $iikoMenuItemModifierItem->name = $itemModifierGroup->name;
                                                    $iikoMenuItemModifierItem->description = $itemModifierGroup->description;
                                                    $iikoMenuItemModifierItem->type = $itemModifierGroup->type;
                                                    $iikoMenuItemModifierItem->measure_unit = $itemModifierGroup->measureUnit;
                                                    $iikoMenuItemModifierItem->payment_subject = $itemModifierGroup->paymentSubject;
                                                    $iikoMenuItemModifierItem->is_hidden = $itemModifierGroup->isHidden;

                                                    $iikoMenuItemModifierItem->save();
                                                });

                                        },
                                    );

                                $itemSize
                                    ->prices
                                    ->each(static function (Price $price) use ($iikoMenuItemSize) {
                                        $iikoMenuItemPrice = IikoMenuItemPrice::query()
                                            ->where('price', $price->price)
                                            ->where('iiko_menu_item_size_id', $iikoMenuItemSize->id)
                                            ->first() ?? new IikoMenuItemPrice();

                                        $iikoMenuItemPrice->itemSize()->associate($iikoMenuItemSize);
                                        $iikoMenuItemPrice->price = $price->price;

                                        $iikoMenuItemPrice->save();
                                    });

                                $itemSize
                                    ->nutritions
                                    ->each(static function (Nutrition $nutrition) use ($iikoMenuItemSize) {
                                        $iikoMenuItemNutrition = IikoMenuItemNutrition::query()
                                            ->where('iiko_menu_item_size_id', $iikoMenuItemSize->id)
                                            ->first() ?? new IikoMenuItemNutrition();

                                        $iikoMenuItemNutrition->itemSize()->associate($iikoMenuItemSize);
                                        $iikoMenuItemNutrition->fats = $nutrition->fats;
                                        $iikoMenuItemNutrition->proteins = $nutrition->proteins;
                                        $iikoMenuItemNutrition->carbs = $nutrition->carbs;
                                        $iikoMenuItemNutrition->energy = $nutrition->energy;
                                        $iikoMenuItemNutrition->saturated_fatty_acid = $nutrition->saturatedFattyAcid;
                                        $iikoMenuItemNutrition->salt = $nutrition->salt;
                                        $iikoMenuItemNutrition->sugar = $nutrition->sugar;

                                        $iikoMenuItemNutrition->save();
                                    });
                            });
                    });
            });
    }
}
