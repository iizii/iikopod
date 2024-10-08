<?php

declare(strict_types=1);

namespace Shared\Persistence\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractPersistenceRepository
{
    public function __construct(protected Model $model) {}

    protected function query(): Builder
    {
        return $this->model->newQuery();
    }
}
