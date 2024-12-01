<?php

declare(strict_types=1);

namespace Domain\Orders\ValueObjects;

use Shared\Domain\ValueObjects\ValueObjectCollection;

/**
 * @extends ValueObjectCollection<array-key, Item>
 */
final class ItemCollection extends ValueObjectCollection {}
