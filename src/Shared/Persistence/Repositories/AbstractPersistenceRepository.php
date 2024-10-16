<?php

declare(strict_types=1);

namespace Shared\Persistence\Repositories;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
abstract class AbstractPersistenceRepository
{
    public function __construct(protected Model $model, protected DatabaseManager $databaseManager) {}

    /**
     * @return Builder<TModel>
     */
    protected function query(): Builder
    {
        /** @var Builder<TModel> $builder */
        $builder = $this->model->newModelQuery();

        return $builder;
    }

    protected function push(Model $model): void
    {
        $model->push();
    }
}
