<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests\Food;

use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Food\EditFoodRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Food\EditFoodResponseData;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class EditFoodRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(private EditFoodRequestData $data, private IntegerId $id) {}

    public function method(): RequestMethod
    {
        return RequestMethod::PATCH;
    }

    public function endpoint(): string
    {
        return sprintf('/api/food/%s', $this->id->id);
    }

    /**
     * @return array<string, string>
     */
    public function data(): array
    {
        return $this->data->toArray();
    }

    public function createDtoFromResponse(Response $response): EditFoodResponseData
    {
        return EditFoodResponseData::from($response->json());
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return [];
    }
}
