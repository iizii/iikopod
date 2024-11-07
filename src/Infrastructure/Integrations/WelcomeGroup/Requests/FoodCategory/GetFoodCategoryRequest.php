<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests\FoodCategory;

use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodCategory\GetFoodCategoryResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class GetFoodCategoryRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(private int $id, private ?array $data = []) {}

    public function method(): RequestMethod
    {
        return RequestMethod::GET;
    }

    public function endpoint(): string
    {
        return '/api/food_category/'.$this->id;
    }

    /**
     * @return array<string, string>
     */
    public function data(): array
    {
        return $this->data ?? [];
    }

    public function createDtoFromResponse(Response $response): GetFoodCategoryResponseData
    {
        return GetFoodCategoryResponseData::from($response->json());
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return [];
    }
}
