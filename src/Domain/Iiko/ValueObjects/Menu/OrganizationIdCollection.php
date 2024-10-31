<?php

declare(strict_types=1);

namespace Domain\Iiko\ValueObjects\Menu;

use Shared\Domain\ValueObjects\StringId;
use Shared\Domain\ValueObjects\ValueObjectCollection;

/**
 * @extends ValueObjectCollection<array-key, StringId>
 */
final class OrganizationIdCollection extends ValueObjectCollection {}
