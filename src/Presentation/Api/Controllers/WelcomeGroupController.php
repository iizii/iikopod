<?php

declare(strict_types=1);

namespace Presentation\Api\Controllers;

use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Address\CreateAddressRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Client\CreateClientRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Food\CreateFoodRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Food\EditFoodRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodCategory\CreateFoodCategoryRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodCategory\EditFoodCategoryRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodModifier\CreateFoodModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodModifier\EditFoodModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Modifier\CreateModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Modifier\EditModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\CreateModifierTypeRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\EditModifierTypeRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\GetOrdersByRestaurantRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone\CreatePhoneRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantFood\CreateRestaurantFoodRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantFood\EditRestaurantFoodRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantModifier\CreateRestaurantModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantModifier\EditRestaurantModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\Requests\Address\CreateAddressRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Client\CreateClientRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Food\CreateFoodRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Food\EditFoodRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Food\GetFoodRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\FoodCategory\CreateFoodCategoryRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\FoodCategory\EditFoodCategoryRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\FoodCategory\GetFoodCategoryRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\FoodModifier\CreateFoodModifierRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\FoodModifier\EditFoodModifierRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\FoodModifier\GetFoodModifierRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Modifier\CreateModifierRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Modifier\EditModifierRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Modifier\GetModifierRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\ModifierType\CreateModifierTypeRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\ModifierType\EditModifierTypeRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\ModifierType\GetModifierTypeRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Phone\CreatePhoneRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Restaurant\GetRestaurantRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Restaurant\GetRestaurantsRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\RestaurantFood\CreateRestaurantFoodRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\RestaurantFood\EditRestaurantFoodRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\RestaurantFood\GetRestaurantFoodRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\RestaurantModifier\CreateRestaurantModifierRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\RestaurantModifier\EditRestaurantModifierRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\RestaurantModifier\GetRestaurantModifierRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Workshop\GetWorkshopRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Workshop\GetWorkshopsRequest;
use Shared\Domain\ValueObjects\IntegerId;
use Spatie\RouteAttributes\Attributes\Route;

final readonly class WelcomeGroupController
{
    public function __construct(
        private ResponseFactory $responseFactory,
        private WelcomeGroupConnectorInterface $connector,
    ) {}

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

            ),
        );
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'POST', uri: '/wg/create_restaurant_food', name: 'wg.create_restaurant_food')]
    public function createRestaurantFood(Request $request): JsonResponse
    {
        $req = new CreateRestaurantFoodRequest(
            new CreateRestaurantFoodRequestData(
                $request->input('restaurant_id'),
                $request->input('food_id'),
            ),
        );
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'GET', uri: '/wg/get_restaurant_food', name: 'wg.get_restaurant_food')]
    public function getRestaurantFood(Request $request): JsonResponse
    {
        $req = new GetRestaurantFoodRequest((int) $request->input('id'));
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'PATCH', uri: '/wg/edit_restaurant_food', name: 'wg.edit_restaurant_food')]
    public function editRestaurantFood(Request $request): JsonResponse
    {
        $req = new EditRestaurantFoodRequest(
            (int) $request->input('id'),
            new EditRestaurantFoodRequestData(
                (int) $request->input('restaurant_id'),
                (int) $request->input('food_id'),
            ),
        );
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'POST', uri: '/wg/create_food_category', name: 'wg.create_food_category')]
    public function createFoodCategory(Request $request): JsonResponse
    {
        $req = new CreateFoodCategoryRequest(
            new CreateFoodCategoryRequestData(
                $request->input('name'),
            ),
        );
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'PATCH', uri: '/wg/edit_food_category', name: 'wg.edit_food_category')]
    public function editFoodCategory(Request $request): JsonResponse
    {
        $req = new EditFoodCategoryRequest(
            (int) $request->input('id'),
            new EditFoodCategoryRequestData(
                $request->input('name'),
            ),
        );
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'GET', uri: '/wg/get_food_category', name: 'wg.get_food_category')]
    public function getFoodCategory(Request $request): JsonResponse
    {
        $req = new GetFoodCategoryRequest(
            (int) $request->input('id'),
        );
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'GET', uri: '/wg/get_food', name: 'wg.get_food')]
    public function getFood(Request $request): JsonResponse
    {
        $req = new GetFoodRequest(
            (int) $request->input('id'),
        );
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'PATCH', uri: '/wg/edit_food', name: 'wg.edit_food')]
    public function editFood(Request $request): JsonResponse
    {
        $req = new EditFoodRequest(
            new EditFoodRequestData(
                $request->input('food_category_id'),
                $request->input('workshop'),
                $request->input('name'),
                $request->input('description'),
                $request->input('weight'),
                $request->input('caloricity'),
                $request->input('price'),
            ),
            new IntegerId($request->integer('id')),
        );
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'POST', uri: '/wg/create_food', name: 'wg.create_food')]
    public function createFood(Request $request): JsonResponse
    {
        $req = new CreateFoodRequest(
            new CreateFoodRequestData(
                $request->input('food_category_id'),
                $request->input('workshop'),
                $request->input('name'),
                $request->input('description'),
                $request->input('weight'),
                $request->input('caloricity'),
                $request->input('price'),
            ),
        );
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'POST', uri: '/wg/create_food_modifier', name: 'wg.create_food_modifier')]
    public function createFoodModifier(Request $request): JsonResponse
    {
        $req = new CreateFoodModifierRequest(
            new CreateFoodModifierRequestData(
                $request->input('food_id'),
                $request->input('modifier_id'),
                $request->input('weight'),
                $request->input('caloricity'),
                $request->input('price'),
                0,
            ),
        );
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'PATCH', uri: '/wg/edit_food_modifier', name: 'wg.edit_food_modifier')]
    public function editFoodModifier(Request $request): JsonResponse
    {
        $req = new EditFoodModifierRequest(
            $request->input('id'),
            new EditFoodModifierRequestData(
                $request->input('food_id'),
                $request->input('modifier_id'),
                $request->input('weight'),
                $request->input('caloricity'),
                $request->input('price'),
                0,
            ),
        );
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'GET', uri: '/wg/get_food_modifier', name: 'wg.get_food_modifier')]
    public function getFoodModifier(Request $request): JsonResponse
    {
        $req = new GetFoodModifierRequest(
            (int) $request->input('id'),
        );
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'GET', uri: '/wg/get_modifier', name: 'wg.get_modifier')]
    public function getModifier(Request $request): JsonResponse
    {
        $req = new GetModifierRequest(
            (int) $request->input('id'),
        );
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'POST', uri: '/wg/create_modifier', name: 'wg.create_modifier')]
    public function createModifier(Request $request): JsonResponse
    {
        $req = new CreateModifierRequest(
            new CreateModifierRequestData(
                $request->input('name'),
                $request->input('modifier_type_id'),
                $request->input('default_option'),

            ),
        );
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'PATCH', uri: '/wg/edit_modifier', name: 'wg.edit_modifier')]
    public function editModifier(Request $request): JsonResponse
    {
        $req = new EditModifierRequest(
            (int) $request->input('id'),
            new EditModifierRequestData(
                $request->input('name'),
                $request->input('modifier_type_id'),
                $request->input('default_option'),

            ),
        );
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'GET', uri: '/wg/get_modifier_type', name: 'wg.get_modifier_type')]
    public function getModifierType(Request $request): JsonResponse
    {
        $req = new GetModifierTypeRequest(
            (int) $request->input('id'),
        );
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'POST', uri: '/wg/create_modifier_type', name: 'wg.create_modifier_type')]
    public function createModifierType(Request $request): JsonResponse
    {
        $req = new CreateModifierTypeRequest(
            new CreateModifierTypeRequestData(
                $request->input('name'),
                $request->input('behaviour'),
            ),
        );
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'PATCH', uri: '/wg/edit_modifier_type', name: 'wg.edit_modifier_type')]
    public function editModifierType(Request $request): JsonResponse
    {
        $req = new EditModifierTypeRequest(
            (int) $request->input('id'),
            new EditModifierTypeRequestData(
                $request->input('name'),
                $request->input('behaviour'),
            ),
        );
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'GET', uri: '/wg/get_restaurant_modifier', name: 'wg.get_restaurant_modifier')]
    public function getRestaurantModifier(Request $request): JsonResponse
    {
        $req = new GetRestaurantModifierRequest(
            (int) $request->input('id'),
        );
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'POST', uri: '/wg/create_restaurant_modifier', name: 'wg.create_restaurant_modifier')]
    public function createRestaurantModifier(Request $request): JsonResponse
    {
        $req = new CreateRestaurantModifierRequest(
            new CreateRestaurantModifierRequestData(
                $request->input('restaurant_id'),
                $request->input('modifier_id'),
            ),
        );
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    #[Route(methods: 'PATCH', uri: '/wg/edit_restaurant_modifier', name: 'wg.edit_restaurant_modifier')]
    public function editRestaurantModifier(Request $request): JsonResponse
    {
        $req = new EditRestaurantModifierRequest(
            (int) $request->input('id'),
            new EditRestaurantModifierRequestData(
                $request->input('restaurant_id'),
                $request->input('modifier_id'),
            ),
        );
        $response = $this->connector->send($req);

        return $this->responseFactory->json($response, 200);
    }

    #[Route(methods: 'GET', uri: '/wg/get_orders_by_restaurant', name: 'wg.get_orders_by_restaurant')]
    public function getOrdersByRestaurant(Request $request, WelcomeGroupConnectorInterface $welcomeGroupConnector): JsonResponse
    {
        $response = $welcomeGroupConnector->getOrdersByRestaurantId(
            new GetOrdersByRestaurantRequestData((int) $request->input('restaurant_id'))
        );

        return $this->responseFactory->json($response, 200);
    }
}
