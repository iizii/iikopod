<?php

declare(strict_types=1);

namespace Domain\Iiko\Events;

use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItem;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final readonly class ItemDeletedEvent implements ShouldDispatchAfterCommit
{
    public function __construct(public IikoMenuItem $item) {}
}
