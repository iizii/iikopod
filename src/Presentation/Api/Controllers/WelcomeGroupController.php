<?php

declare(strict_types=1);

namespace Presentation\Api\Controllers;

use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\CreateAddressRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\CreateClientRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\CreatePhoneRequestData;
use Infrastructure\Integrations\WelcomeGroup\Requests\CreateAddressRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\CreateClientRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\CreatePhoneRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\GetRestaurantRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\GetRestaurantsRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\GetWorkshopRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\GetWorkshopsRequest;
use Shared\Domain\ValueObjects\IntegerId;
use Spatie\RouteAttributes\Attributes\Route;

final readonly class WelcomeGroupController
{
    public function __construct(private ResponseFactory $responseFactory, private WelcomeGroupConnectorInterface $connector) {}

    /**
     * @throws ConnectionException
     * @throws RequestException
     */
    #[Route(methods: 'GET', uri: '/wg/get_restaurant', name: 'wg.get_restaurant')]
    public function getRestaurant(Request $request): JsonResponse
    {
        $req = new GetRestaurantRequest(new IntegerId((int) $request->input('id')));
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'GET', uri: '/wg/get_restaurants', name: 'wg.get_restaurants')]
    public function getRestaurants(Request $request): JsonResponse
    {
        $req = new GetRestaurantsRequest();
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'GET', uri: '/wg/get_workshop', name: 'wg.get_workshop')]
    public function getWorkshop(Request $request): JsonResponse
    {
        $req = new GetWorkshopRequest(new IntegerId((int) $request->input('id')));
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'GET', uri: '/wg/get_workshops', name: 'wg.get_workshops')]
    public function getWorkshops(Request $request): JsonResponse
    {
        $req = new GetWorkshopsRequest();
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'POST', uri: '/wg/create_client', name: 'wg.create_client')]
    public function createClient(Request $request): JsonResponse
    {
        $req = new CreateClientRequest(new CreateClientRequestData($request->input('name')));
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'POST', uri: '/wg/create_phone', name: 'wg.create_phone')]
    public function createPhone(Request $request): JsonResponse
    {
        $req = new CreatePhoneRequest(new CreatePhoneRequestData($request->input('number')));
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'POST', uri: '/wg/create_address', name: 'wg.create_address')]
    public function createAddress(Request $request): JsonResponse
    {
        $req = new CreateAddressRequest(
            new CreateAddressRequestData(
                $request->input('city'),
                $request->input('street'),
                $request->input('house'),

            )
        );
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }
}
