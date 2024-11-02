<?php

declare(strict_types=1);

namespace Domain\Iiko\ValueObjects\Menu;

use Domain\Iiko\Entities\Menu\ItemGroup;
use Shared\Domain\ValueObjects\ValueObjectCollection;

/**
 * @extends ValueObjectCollection<array-key, ItemGroup>
 */
final class ItemGroupCollection extends ValueObjectCollection {}
