<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests\FoodCategory;

use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodCategory\CreateFoodCategoryRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodCategory\CreateFoodCategoryResponseData;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class UpdateFoodCategoryRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(private CreateFoodCategoryRequestData $data, private IntegerId $id) {}

    public function method(): RequestMethod
    {
        return RequestMethod::PATCH;
    }

    public function endpoint(): string
    {
        return sprintf('/api/food_category/%s', $this->id->id);
    }

    /**
     * @return array<string, string>
     */
    public function data(): array
    {
        return $this->data->toArray();
    }

    public function createDtoFromResponse(Response $response): CreateFoodCategoryResponseData
    {
        return CreateFoodCategoryResponseData::from($response->json());
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return [];
    }
}
