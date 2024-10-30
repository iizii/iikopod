<?php

declare(strict_types=1);

namespace Presentation\Admin\Resources\OrganizationSettingResource\Pages;

use Application\Settings\Services\SaveSettingsValidation\SaveSettingsValidationPipeline;
use Domain\Iiko\Exceptions\PaymentTypeNotFoundException;
use Domain\Settings\OrganizationSetting;
use Domain\Settings\ValueObjects\PaymentType;
use Domain\Settings\ValueObjects\PaymentTypeCollection;
use Domain\Settings\ValueObjects\PriceCategory;
use Domain\Settings\ValueObjects\PriceCategoryCollection;
use Domain\WelcomeGroup\Exceptions\RestaurantNotFoundException;
use Domain\WelcomeGroup\Exceptions\WorkshopNotFoundException;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Infrastructure\Integrations\IIko\Exceptions\IIkoIntegrationException;
use Presentation\Admin\Resources\OrganizationSettingResource;
use Shared\Domain\ValueObjects\IntegerId;

use Shared\Domain\ValueObjects\StringId;
use function Filament\Support\is_app_url;

final class EditOrganizationSetting extends EditRecord
{
    protected static string $resource = OrganizationSettingResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState(afterValidate: function () {
                $this->callHook('afterValidate');

                $this->callHook('beforeSave');
            });

            $data = $this->mutateFormDataBeforeSave($data);

            $this->validateBeforeSave($data);

            $this->handleRecordUpdate($this->getRecord(), $data);

            $this->callHook('afterSave');

            $this->commitDatabaseTransaction();
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } catch (WorkshopNotFoundException|RestaurantNotFoundException|PaymentTypeNotFoundException|IIkoIntegrationException  $exception) {
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

        if ($shouldSendSavedNotification) {
            $this->getSavedNotification()?->send();
        }

        if ($shouldRedirect && ($redirectUrl = $this->getRedirectUrl())) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string<array<string>>>  $data
     *
     * @throws \Exception
     */
    private function validateBeforeSave(array $data): void
    {
        $validationPipeLine = resolve(SaveSettingsValidationPipeline::class);
        $record = $this->getRecord();

        $validationPipeLine->handle(
            new OrganizationSetting(
                new IntegerId($record->id),
                $data['iiko_api_key'],
                new StringId((string) $data['iiko_restaurant_id']),
                new IntegerId((int) $data['welcome_group_restaurant_id']),
                new IntegerId((int) $data['welcome_group_default_workshop_id']),
                new StringId((string) $data['order_delivery_type_id']),
                new StringId((string) $data['order_pickup_type_id']),
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
                            new IntegerId((int) $paymentType['category_id']),
                            $paymentType['prefix'],
                        ),
                        $data['price_categories'],
                    ),
                ),
            ),
        );
    }
}
