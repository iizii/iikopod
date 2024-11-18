<?php

declare(strict_types=1);

namespace Domain\Iiko\ValueObjects\Order;

use Domain\Iiko\Entities\Order\Item;
use Shared\Domain\ValueObjects\ValueObjectCollection;

/**
 * @extends ValueObjectCollection<array-key, Item>
 */
final class ItemCollection extends ValueObjectCollection {}
