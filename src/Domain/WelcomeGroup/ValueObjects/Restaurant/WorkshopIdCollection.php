<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\ValueObjects\Restaurant;

use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\ValueObjectCollection;

/**
 * @extends ValueObjectCollection<array-key, IntegerId>
 */
final class WorkshopIdCollection extends ValueObjectCollection {}
