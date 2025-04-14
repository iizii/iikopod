<?php

declare(strict_types=1);

namespace Infrastructure\Observers;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Str;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItem;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemGroup;
use Infrastructure\Persistence\Eloquent\Settings\Models\OrganizationSetting;

final readonly class OrganizationSettingObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private Dispatcher $dispatcher) {}

    /**
     * Handle the IikoMenuItem "created" event.
     */
    public function created(OrganizationSetting $organizationSetting): void {}

    /**
     * Handle the IikoMenuItem "updated" event.
     */
    public function updated(OrganizationSetting $organizationSetting): void
    {
        logger('сработал обсервер');

        $old = $organizationSetting->getOriginal('price_categories')->toArray();
        $new = $organizationSetting->price_categories->toArray();

        $oldById = collect($old)->keyBy('category_id');
        $newById = collect($new)->keyBy('category_id');

        foreach ($newById as $categoryId => $newItem) {
            $oldItem = $oldById->get($categoryId);

            if ($oldItem && ($oldItem['prefix'] ?? null) !== ($newItem['prefix'] ?? null)) {
                IikoMenuItemGroup::query()
                    ->whereHas('iikoMenu', static function ($query) use ($organizationSetting) {
                        $query->where('organization_setting_id', $organizationSetting->id);
                    })
                    ->where('external_id', 'LIKE', '%'.$oldItem['prefix'].':%')
                    ->get()
                    ->each(static function (IikoMenuItemGroup $item) use ($newItem, $oldItem) {
                        if (Str::startsWith($item->external_id, $oldItem['prefix'].':')) {
                            $externalId = Str::after($item->external_id, $oldItem['prefix'].':');

                            $item->update([
                                'external_id' => $newItem['prefix'].':'.$externalId,
                            ]);
                        }

                    });
            }
        }
    }

    /**
     * Handle the IikoMenuItem "deleted" event.
     */
    public function deleted(OrganizationSetting $organizationSetting): void {}

    /**
     * Handle the IikoMenuItem "restored" event.
     */
    public function restored(OrganizationSetting $organizationSetting): void
    {
        //
    }

    /**
     * Handle the IikoMenuItem "force deleted" event.
     */
    public function forceDeleted(OrganizationSetting $organizationSetting): void
    {
        //
    }
}
