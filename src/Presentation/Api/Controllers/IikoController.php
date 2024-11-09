<?php

declare(strict_types=1);

namespace Presentation\Api\Controllers;

use Domain\Integrations\Iiko\IikoConnectorInterface;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Infrastructure\Integrations\IIko\DataTransferObjects\AuthorizationResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetExternalMenusWithPriceCategoriesRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetExternalMenusWithPriceCategoriesResponse\GetExternalMenusWithPriceCategoriesResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetOrganizationRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetPaymentTypesRequestData;
use Infrastructure\Integrations\IIko\Requests\AuthorizationRequest;
use Infrastructure\Integrations\IIko\Requests\GetExternalMenusWithPriceCategoriesRequest;
use Infrastructure\Integrations\IIko\Requests\GetMenuRequest;
use Infrastructure\Integrations\IIko\Requests\GetOrganizationsRequest;
use Infrastructure\Integrations\IIko\Requests\GetPaymentTypesRequest;
use Spatie\RouteAttributes\Attributes\Route;

final readonly class IikoController
{
    public function __construct(private ResponseFactory $responseFactory, private IikoConnectorInterface $connector) {}

    /**
     * @throws ConnectionException
     * @throws RequestException
     */
    #[Route(methods: 'POST', uri: '/iiko/auth', name: 'iiko.auth')]
    public function auth(Request $request): JsonResponse
    {
        $req = new AuthorizationRequest((string) $request->input('token'));
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'POST', uri: '/iiko/get_organizations', name: 'iiko.get_organizations')]
    public function getOrganizations(Request $request): JsonResponse
    {
        $authReq = new AuthorizationRequest((string) $request->input('token'));

        /** @var AuthorizationResponseData $authRes */
        $authRes = $this->connector->send($authReq);

        $getOrganizationsData = new GetOrganizationRequestData([], true, false, []);
        $req = new GetOrganizationsRequest($getOrganizationsData, $authRes->token);
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'POST', uri: '/iiko/get_payment_types', name: 'iiko.get_payment_types')]
    public function getPaymentTypes(Request $request): JsonResponse
    {
        $authReq = new AuthorizationRequest((string) $request->input('token'));

        /** @var AuthorizationResponseData $authRes */
        $authRes = $this->connector->send($authReq);

        $getPaymentTypesData = new GetPaymentTypesRequestData($request->input('organizationIds'));
        $req = new GetPaymentTypesRequest($getPaymentTypesData, $authRes->token);
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'POST', uri: '/iiko/get_external_menus_with_price_categories', name: 'iiko.get_external_menus_with_price_categories')]
    public function getExternalMenusWithPriceCategories(Request $request): JsonResponse
    {
        $authReq = new AuthorizationRequest((string) $request->input('token'));

        /** @var AuthorizationResponseData $authRes */
        $authRes = $this->connector->send($authReq);

        $getExternalMenusWithPriceCategoriesData = new GetExternalMenusWithPriceCategoriesRequestData(
            $request->input('organizationIds'),
        );
        $req = new GetExternalMenusWithPriceCategoriesRequest(
            $getExternalMenusWithPriceCategoriesData, $authRes->token,
        );
        /** @var GetExternalMenusWithPriceCategoriesResponseData $response */
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'POST', uri: '/iiko/get_menu', name: 'iiko.get_menu')]
    public function getMenu(Request $request): JsonResponse
    {
        $authReq = new AuthorizationRequest((string) $request->input('token'));

        /** @var AuthorizationResponseData $authRes */
        $authRes = $this->connector->send($authReq);

        $getExternalMenusWithPriceCategoriesData = new GetMenuRequestData([$request->input('organizationId')],
            $request->input('externalMenuId'),
            $request->input('priceCategoryId'));
        $req = new GetMenuRequest($getExternalMenusWithPriceCategoriesData, $authRes->token);
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }
}
