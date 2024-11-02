<?php

declare(strict_types=1);

namespace Domain\Iiko\ValueObjects\Menu;

use Domain\Iiko\Entities\Menu\TaxCategory;
use Shared\Domain\ValueObjects\ValueObjectCollection;

/**
 * @extends ValueObjectCollection<array-key, TaxCategory>
 */
final class TaxCategoryCollection extends ValueObjectCollection {}
