<?php

declare(strict_types=1);

namespace Shared\Persistence\Repositories;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractPersistenceRepository
{
    public function __construct(protected Model $model, protected DatabaseManager $databaseManager) {}

    protected function query(): Builder
    {
        return $this->model->newModelQuery();
    }

    protected function push(Model $model): void
    {
        $model->push();
    }
}
