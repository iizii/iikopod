<?php

declare(strict_types=1);

namespace Domain\Iiko\ValueObjects\Order;

use Domain\Iiko\Entities\Order\Payment;
use Shared\Domain\ValueObjects\ValueObjectCollection;

/**
 * @extends ValueObjectCollection<array-key, Payment>
 */
final class PaymentCollection extends ValueObjectCollection {}
