<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\ValueObjects;

use Domain\WelcomeGroup\Entities\OrderItem;
use Shared\Domain\ValueObjects\ValueObjectCollection;

/**
 * @extends ValueObjectCollection<array-key, OrderItem>
 */
final class OrderItemCollection extends ValueObjectCollection {}
