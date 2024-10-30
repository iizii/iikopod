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
use Infrastructure\Integrations\IIko\DataTransferObjects\GetOrganizationRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetOrganizationResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseData;
use Shared\Infrastructure\Integrations\ResponseDataInterface;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;

final readonly class GetOrganizationsRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(private GetOrganizationRequestData $getOrganizationRequestData, private array $headers = []) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return '/api/1/organizations';
    }

    /**
     * @return array|Arrayable|string[]
     */
    public function data(): array|Arrayable
    {
        return $this->getOrganizationRequestData;
    }

    /**
     * @return array|string[]
     */
    public function headers(): array
    {
        return $this->headers;
    }

    public function createDtoFromResponse(Response $response): Paginator|ResponseData|Enumerable|array|Response|Collection|PaginatedDataCollection|LazyCollection|AbstractCursorPaginator|CursorPaginatedDataCollection|DataCollection|AbstractPaginator|CursorPaginator
    {
        return GetOrganizationResponseData::collect((array) $response->json()['organizations']);
    }
}
