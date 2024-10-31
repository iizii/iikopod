<?php

declare(strict_types=1);

namespace Domain\Iiko\ValueObjects\Menu;

use Domain\Iiko\Entities\Menu\ItemModifierGroup;
use Shared\Domain\ValueObjects\ValueObjectCollection;

/**
 * @extends ValueObjectCollection<array-key, ItemModifierGroup>
 */
final class ItemModifierGroupCollection extends ValueObjectCollection {}
