<?php

declare(strict_types=1);

namespace Infrastructure\Laravel\Providers;

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
use Domain\Settings\Interfaces\OrganizationSettingRepositoryInterface;
use Domain\Users\Models\User;
use Domain\Users\Repositories\UserRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodCategoryRepositoryInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenu;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItem;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemGroup;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierGroup;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierItem;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierItemPrice;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemNutrition;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemPrice;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemSize;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuProductCategory;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuTaxCategory;
use Infrastructure\Persistence\Eloquent\IIko\Models\Repositories\IikoMenuItemGroupRepository;
use Infrastructure\Persistence\Eloquent\IIko\Models\Repositories\IikoMenuItemModifierGroupRepository;
use Infrastructure\Persistence\Eloquent\IIko\Models\Repositories\IikoMenuItemModifierItemPriceRepository;
use Infrastructure\Persistence\Eloquent\IIko\Models\Repositories\IikoMenuItemModifierItemRepository;
use Infrastructure\Persistence\Eloquent\IIko\Models\Repositories\IikoMenuItemNutritionRepository;
use Infrastructure\Persistence\Eloquent\IIko\Models\Repositories\IikoMenuItemPriceRepository;
use Infrastructure\Persistence\Eloquent\IIko\Models\Repositories\IikoMenuItemRepository;
use Infrastructure\Persistence\Eloquent\IIko\Models\Repositories\IikoMenuItemSizeRepository;
use Infrastructure\Persistence\Eloquent\IIko\Models\Repositories\IikoMenuProductCategoryRepository;
use Infrastructure\Persistence\Eloquent\IIko\Models\Repositories\IikoMenuRepository;
use Infrastructure\Persistence\Eloquent\IIko\Models\Repositories\IikoMenuTaxCategoryRepository;
use Infrastructure\Persistence\Eloquent\Settings\Models\OrganizationSetting;
use Infrastructure\Persistence\Eloquent\Settings\Repositories\OrganizationSettingRepository;
use Infrastructure\Persistence\Eloquent\Users\Repositories\UserRepository;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupFoodCategory;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Repositories\WelcomeGroupFoodCategoryRepository;

final class PersistenceRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(UserRepositoryInterface::class, static function (Application $application): UserRepository {
            return new UserRepository(
                $application->make(User::class),
            );
        });

        $this->app->scoped(OrganizationSettingRepositoryInterface::class, static function (Application $application): OrganizationSettingRepository {
            return new OrganizationSettingRepository(
                $application->make(OrganizationSetting::class),
            );
        });

        $this->app->scoped(IikoMenuItemGroupRepositoryInterface::class, static function (Application $application): IikoMenuItemGroupRepository {
            return new IikoMenuItemGroupRepository(
                $application->make(IikoMenuItemGroup::class),
            );
        });

        $this->app->scoped(IikoMenuItemModifierGroupRepositoryInterface::class, static function (Application $application): IikoMenuItemModifierGroupRepository {
            return new IikoMenuItemModifierGroupRepository(
                $application->make(IikoMenuItemModifierGroup::class),
            );
        });

        $this->app->scoped(IikoMenuItemModifierItemRepositoryInterface::class, static function (Application $application): IikoMenuItemModifierItemRepository {
            return new IikoMenuItemModifierItemRepository(
                $application->make(IikoMenuItemModifierItem::class),
            );
        });

        $this->app->scoped(IikoMenuItemNutritionRepositoryInterface::class, static function (Application $application): IikoMenuItemNutritionRepository {
            return new IikoMenuItemNutritionRepository(
                $application->make(IikoMenuItemNutrition::class),
            );
        });

        $this->app->scoped(IikoMenuItemPriceRepositoryInterface::class, static function (Application $application): IikoMenuItemPriceRepository {
            return new IikoMenuItemPriceRepository(
                $application->make(IikoMenuItemPrice::class),
            );
        });

        $this->app->scoped(IikoMenuItemRepositoryInterface::class, static function (Application $application): IikoMenuItemRepository {
            return new IikoMenuItemRepository(
                $application->make(IikoMenuItem::class),
            );
        });

        $this->app->scoped(IikoMenuItemSizeRepositoryInterface::class, static function (Application $application): IikoMenuItemSizeRepository {
            return new IikoMenuItemSizeRepository(
                $application->make(IikoMenuItemSize::class),
            );
        });

        $this->app->scoped(IikoMenuProductCategoryRepositoryInterface::class, static function (Application $application): IikoMenuProductCategoryRepository {
            return new IikoMenuProductCategoryRepository(
                $application->make(IikoMenuProductCategory::class),
            );
        });

        $this->app->scoped(IikoMenuRepositoryInterface::class, static function (Application $application): IikoMenuRepository {
            return new IikoMenuRepository(
                $application->make(IikoMenu::class),
            );
        });

        $this->app->scoped(IikoMenuTaxCategoryRepositoryInterface::class, static function (Application $application): IikoMenuTaxCategoryRepository {
            return new IikoMenuTaxCategoryRepository(
                $application->make(IikoMenuTaxCategory::class),
            );
        });

        $this->app->scoped(IikoMenuItemModifierItemPriceRepositoryInterface::class, static function (Application $application): IikoMenuItemModifierItemPriceRepository {
            return new IikoMenuItemModifierItemPriceRepository(
                $application->make(IikoMenuItemModifierItemPrice::class),
            );
        });

        $this->app->scoped(WelcomeGroupFoodCategoryRepositoryInterface::class, static function (Application $application): WelcomeGroupFoodCategoryRepository {
            return new WelcomeGroupFoodCategoryRepository(
                $application->make(WelcomeGroupFoodCategory::class),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
