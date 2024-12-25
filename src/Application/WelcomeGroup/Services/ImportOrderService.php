<?php

declare(strict_types=1);

namespace Application\WelcomeGroup\Services;

use Domain\Integrations\Iiko\IikoConnectorInterface;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Orders\Repositories\OrderRepositoryInterface;
use Domain\Settings\Interfaces\OrganizationSettingRepositoryInterface;
use Domain\Settings\OrganizationSetting;
use Illuminate\Database\DatabaseManager;
use Infrastructure\Integrations\IIko\IikoAuthenticator;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\GetOrdersByRestaurantRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\GetOrdersByRestaurantResponseData;
use Throwable;

final readonly class ImportOrderService
{
    public function __construct(
        private IikoAuthenticator $authenticator,
        private DatabaseManager $databaseManager,
        private IikoConnectorInterface $iikoConnector,
        private WelcomeGroupConnectorInterface $welcomeGroupConnector,
        private OrderRepositoryInterface $orderRepository,
//        private WelcomeGroupReposit

        private OrganizationSettingRepositoryInterface $organizationSettingRepository,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $t = $this;
        $organizations = $this->organizationSettingRepository->all();

        $organizations->each(static function (OrganizationSetting $organizationSetting) use ($t): void {
            $orders = $t
                ->welcomeGroupConnector
                ->getOrdersByRestaurantId(
                    new GetOrdersByRestaurantRequestData($organizationSetting->welcomeGroupRestaurantId->id)
                );



            $orders->each(static function (GetOrdersByRestaurantResponseData $order) use ($t): void {
                //                $this
                //                    ->iikoConnector

                $order = $t
                    ->orderRepository
                    ->findByWelcomeGroupId(new IntegerId($order->id));

                if (!$order) {
                    $t
                        ->orderRepository
                        ->store()
                }

            });
        });
    }
}
