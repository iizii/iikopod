<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\Requests;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\Response;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;
use Infrastructure\Integrations\IIko\DataTransferObjects\AuthorizationResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetExternalMenusWithPriceCategoriesRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetExternalMenusWithPriceCategoriesResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseData;
use Shared\Infrastructure\Integrations\ResponseDataInterface;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;

final class GetMenuRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(private GetMenuRequestData $getMenuRequestData, private array $headers = []) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return '/api/2/menu/by_id';
    }

    public function data(): array|Arrayable
    {
        return $this->getMenuRequestData;
    }

    /**
     * @param Response $response
     * @return array|AbstractCursorPaginator|AbstractPaginator|Collection|CursorPaginatedDataCollection|CursorPaginator|DataCollection|Enumerable|LazyCollection|PaginatedDataCollection|Paginator|Response|ResponseData
     */
    public function createDtoFromResponse(Response $response): array|CursorPaginator|Paginator|Response|AbstractCursorPaginator|AbstractPaginator|Collection|Enumerable|LazyCollection|ResponseData|CursorPaginatedDataCollection|DataCollection|PaginatedDataCollection
    {
        return GetMenuResponseData::from((array) $response->json());
    }

    public function headers(): array|Arrayable
    {
        return $this->headers;
    }
}
