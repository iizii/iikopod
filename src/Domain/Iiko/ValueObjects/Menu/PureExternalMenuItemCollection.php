<?php

declare(strict_types=1);

namespace Domain\Iiko\ValueObjects\Menu;

use Domain\Iiko\Entities\Menu\PureExternalMenuItemCategory;
use Shared\Domain\ValueObjects\ValueObjectCollection;

/**
 * @extends ValueObjectCollection<array-key, PureExternalMenuItemCategory>
 */
final class PureExternalMenuItemCollection extends ValueObjectCollection {}
