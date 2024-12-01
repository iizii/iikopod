<?php

declare(strict_types=1);

namespace Domain\Orders\ValueObjects;

use Shared\Domain\ValueObjects\ValueObjectCollection;

/**
 * @extends ValueObjectCollection<array-key, Modifier>
 */
final class ItemModifierCollection extends ValueObjectCollection {}
