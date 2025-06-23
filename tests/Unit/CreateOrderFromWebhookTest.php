<?php

declare(strict_types=1);

use Application\Iiko\Services\Order\CreateOrderFromWebhook;
use Application\Orders\Services\StoreOrder;
use Application\Orders\Services\UpdateOrder;
use Domain\Iiko\Repositories\IikoMenuItemModifierItemRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuItemRepositoryInterface;
use Domain\Orders\Repositories\OrderRepositoryInterface;
use Domain\Settings\Interfaces\OrganizationSettingRepositoryInterface;
use Domain\Settings\OrganizationSetting;
use Domain\Settings\ValueObjects\PaymentTypeCollection;
use Domain\Settings\ValueObjects\PaymentType;
use Domain\Settings\ValueObjects\PriceCategory;
use Domain\Settings\ValueObjects\PriceCategoryCollection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\ItemNotFoundException;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\EventData;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Domain\Iiko\ValueObjects\Menu\PriceCollection;
use Domain\Iiko\ValueObjects\Menu\ItemSizeCollection;
use Domain\Iiko\Entities\Menu\Item as IikoItem;

$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$event = include __DIR__ . '/../../tmp_event.php';

test('throws when modifier is missing', function () use ($event) {
    $menuItemRepository = Mockery::mock(IikoMenuItemRepositoryInterface::class);
    $menuItemRepository->shouldReceive('findByExternalIdAndSourceKey')
        ->andReturn(new IikoItem(
            new IntegerId(1),
            new IntegerId(1),
            new StringId('item1'),
            'SKU',
            'Item1',
            null,
            null,
            null,
            null,
            false,
            null,
            new PriceCollection([]),
            new ItemSizeCollection([]),
        ));

    $menuItemModifierRepo = Mockery::mock(IikoMenuItemModifierItemRepositoryInterface::class);
    $menuItemModifierRepo->shouldReceive('findByExternalId')->andReturn(null);

    $organizationRepo = Mockery::mock(OrganizationSettingRepositoryInterface::class);
    $organizationRepo->shouldReceive('findByIIkoId')->andReturn(new OrganizationSetting(
        new IntegerId(1),
        'key',
        new StringId('org1'),
        new StringId('menu'),
        new IntegerId(1),
        new IntegerId(1),
        new StringId('del'),
        new StringId('pick'),
        false,
        new PaymentTypeCollection([new PaymentType(null, null)]),
        new PriceCategoryCollection([new PriceCategory(new StringId('cat'), 'prefix', ['default'])]),
        new StringId('courier'),
        ['ot1']
    ));

    $orderRepo = Mockery::mock(OrderRepositoryInterface::class);
    $orderRepo->shouldReceive('findByIikoId')->andReturn(null);

    $storeOrder = new StoreOrder($orderRepo, Mockery::mock(DatabaseManager::class), Mockery::mock(Dispatcher::class));
    $updateOrder = new UpdateOrder($orderRepo, Mockery::mock(DatabaseManager::class), Mockery::mock(Dispatcher::class));

    $service = new CreateOrderFromWebhook(
        $storeOrder,
        $updateOrder,
        $menuItemRepository,
        $menuItemModifierRepo,
        $organizationRepo,
        $orderRepo
    );

    expect(fn () => $service->handle(EventData::from($event)))
        ->toThrow(ItemNotFoundException::class);
});

