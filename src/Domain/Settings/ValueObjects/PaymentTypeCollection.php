<?php

declare(strict_types=1);

namespace Domain\Settings\ValueObjects;

use Shared\Domain\ValueObjects\ValueObjectCollection;

/**
 * @extends ValueObjectCollection<array-key, PaymentType>
 */
final class PaymentTypeCollection extends ValueObjectCollection {}
