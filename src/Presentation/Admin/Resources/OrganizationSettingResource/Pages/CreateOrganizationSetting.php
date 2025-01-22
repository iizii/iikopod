<?php

declare(strict_types=1);

namespace Presentation\Admin\Resources\OrganizationSettingResource\Pages;

use Application\Settings\Services\SaveSettingsValidation\SaveSettingsValidationPipeline;
use Domain\Iiko\Exceptions\PaymentTypeNotFoundException;
use Domain\Iiko\Exceptions\PriceCategoriesRepeatedInRepeaterException;
use Domain\Iiko\Exceptions\RestaurantNotFoundException as IIkoRestaurantNotFoundException;
use Domain\Settings\OrganizationSetting;
use Domain\Settings\ValueObjects\PaymentType;
use Domain\Settings\ValueObjects\PaymentTypeCollection;
use Domain\Settings\ValueObjects\PriceCategory;
use Domain\Settings\ValueObjects\PriceCategoryCollection;
use Domain\WelcomeGroup\Exceptions\RestaurantNotFoundException;
use Domain\WelcomeGroup\Exceptions\WorkshopNotFoundException;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Infrastructure\Integrations\IIko\Exceptions\IIkoIntegrationException;
use Presentation\Admin\Resources\OrganizationSettingResource;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

use function Filament\Support\is_app_url;

final class CreateOrganizationSetting extends CreateRecord
{
    protected static string $resource = OrganizationSettingResource::class;

    public function create(bool $another = false): void
    {
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeCreate($data);

            $this->validateBeforeSave($data);

            $this->callHook('beforeCreate');

            $this->record = $this->handleRecordCreation($data);

            $this->form->model($this->getRecord())->saveRelationships();

            $this->callHook('afterCreate');

            $this->commitDatabaseTransaction();
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } catch (
            WorkshopNotFoundException
            |RestaurantNotFoundException
            |IIkoRestaurantNotFoundException
            |PaymentTypeNotFoundException
            |IIkoIntegrationException
            |PriceCategoriesRepeatedInRepeaterException $exception
        ) {
            Notification::make('validationError')
                ->title('Ошибка валидации')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            $this->rollBackDatabaseTransaction();

            return;
        } catch (\Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->rememberData();

        $this->getCreatedNotification()?->send();

        if ($another) {
            // Ensure that the form record is anonymized so that relationships aren't loaded.
            $this->form->model($this->getRecord()::class);
            $this->record = null;

            $this->fillForm();

            return;
        }

        $redirectUrl = $this->getRedirectUrl();

        $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
    }

    /**
     * @param  array<string<array<string>>>  $data
     *
     * @throws \Exception
     */
    private function validateBeforeSave(array $data): void
    {
        $validationPipeLine = resolve(SaveSettingsValidationPipeline::class);

        $validationPipeLine->handle(
            new OrganizationSetting(
                new IntegerId(),
                $data['iiko_api_key'],
                new StringId($data['iiko_restaurant_id']),
                new StringId($data['external_menu_id']),
                new IntegerId((int) $data['welcome_group_restaurant_id']),
                new IntegerId((int) $data['welcome_group_default_workshop_id']),
                new StringId($data['order_delivery_type_id']),
                new StringId($data['order_pickup_type_id']),
                $data['block_orders'],
                new PaymentTypeCollection(
                    array_map(
                        static fn (array $paymentType): PaymentType => new PaymentType(
                            $paymentType['iiko_payment_code'],
                            $paymentType['welcome_group_payment_code'],
                        ),
                        $data['payment_types'],
                    ),
                ),
                new PriceCategoryCollection(
                    array_map(
                        static fn (array $paymentType): PriceCategory => new PriceCategory(
                            new StringId($paymentType['category_id']),
                            $paymentType['prefix'],
                        ),
                        $data['price_categories'],
                    ),
                ),
            ),
        );
    }
}
