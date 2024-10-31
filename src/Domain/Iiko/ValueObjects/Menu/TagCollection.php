<?php

declare(strict_types=1);

namespace Domain\Iiko\ValueObjects\Menu;

use Domain\Iiko\Entities\Menu\Tag;
use Shared\Domain\ValueObjects\ValueObjectCollection;

/**
 * @extends ValueObjectCollection<array-key, Tag>
 */
final class TagCollection extends ValueObjectCollection {}
