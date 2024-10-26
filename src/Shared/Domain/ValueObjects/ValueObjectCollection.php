<?php

declare(strict_types=1);

namespace Shared\Domain\ValueObjects;

use Illuminate\Support\Collection;

/**
 * @template TKey of array-key
 * @template TValue of object
 *
 * @extends Collection<TKey, TValue>
 */
abstract class ValueObjectCollection extends Collection {}
