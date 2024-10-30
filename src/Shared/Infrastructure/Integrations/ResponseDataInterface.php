<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Integrations;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Client\Response;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;

interface ResponseDataInterface
{
    /**
     * @param Response $response
     * @return Response|ResponseData|array|CursorPaginator|Paginator|AbstractCursorPaginator|AbstractPaginator|Collection|Enumerable|LazyCollection|CursorPaginatedDataCollection|DataCollection|PaginatedDataCollection
     */
    public function createDtoFromResponse(Response $response): Response|ResponseData|array|CursorPaginator|Paginator|AbstractCursorPaginator|AbstractPaginator|Collection|Enumerable|LazyCollection|CursorPaginatedDataCollection|DataCollection|PaginatedDataCollection;
}
