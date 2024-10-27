<?php

declare(strict_types=1);

namespace Presentation\Admin\Pages;

use Application\Settings\Services\SaveSettingsValidation\SaveSettingsValidationPipeline;
use Domain\Settings\OrganizationSetting;
use Domain\Settings\ValueObjects\PaymentType;
use Domain\Settings\ValueObjects\PaymentTypeCollection;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Throwable;

use function Filament\Support\is_app_url;

final class ManageOrganization extends SettingsPage
{
    protected static ?string $navigationLabel = 'Организация';

    protected static ?string $title = 'Организация';

    protected ?string $heading = 'Организация';

    protected static ?string $navigationGroup = 'Настройки';

    protected static string $settings = OrganizationSetting::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Fieldset::make('Организация')
                    ->schema([
                        Components\TextInput::make('iiko_api_key')
                            ->label('Iiko API Key')
                            ->required(),
                        Components\TextInput::make('iiko_restaurant_id')
                            ->label('ID ресторана Iiko')
                            ->required(),
                        Components\TextInput::make('welcome_group_restaurant_id')
                            ->label('ID ресторана Welcome Group')
                            ->required(),
                        Components\TextInput::make('default_workshop_id')
                            ->label('ID цеха применяемого по умолчанию')
                            ->required(),
                        Components\Repeater::make('payment_types')
                            ->label('Типы оплат')
                            ->schema([
                                Components\TextInput::make('iiko_payment_code')
                                    ->label('Код типа оплаты Iiko')
                                    ->required(),
                                Components\TextInput::make('welcome_group_payment_code')
                                    ->label('Код типа оплаты Welcome Group')
                                    ->required(),
                            ])
                            ->columns()
                            ->reorderable(false)
                            ->collapsible()
                            ->addActionLabel('Добавить тип оплаты')
                            ->required(),
                    ]),
            ]);
    }

    /**
     * @throws Throwable
     */
    public function save(): void
    {
        $validationPipeline = app()->make(SaveSettingsValidationPipeline::class);

        $this->callHook('beforeValidate');

        $data = $this->form->getState();

        $this->callHook('afterValidate');

        $data = $this->mutateFormDataBeforeSave($data);

        $this->callHook('beforeSave');

        $settings = app(self::getSettings());

        $settings->fill($data);

        $validationPipeline->handle($settings);

        try {
            $this->beginDatabaseTransaction();

            $settings->save();

            $this->callHook('afterSave');

            $this->commitDatabaseTransaction();
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->rememberData();

        $this->getSavedNotification()?->send();

        if ($redirectUrl = $this->getRedirectUrl()) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $paymentTypesCollection = new PaymentTypeCollection();
        $paymentTypes = is_array($data['payment_types']) ? $data['payment_types'] : [];

        foreach ($paymentTypes as $paymentType) {
            $paymentTypesCollection->add(PaymentType::from($paymentType));
        }

        $data['payment_types'] = $paymentTypesCollection;

        return $data;
    }
}
