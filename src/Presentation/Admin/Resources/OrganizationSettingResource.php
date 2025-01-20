<?php

declare(strict_types=1);

namespace Presentation\Admin\Resources;

use Closure;
use Doctrine\DBAL\ConnectionException;
use Domain\Integrations\Iiko\IikoConnectorInterface;
use Domain\Settings\Interfaces\OrganizationSettingRepositoryInterface;
use Domain\Settings\OrganizationSetting as DomainOrganizationSetting;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Http\Client\RequestException;
use Illuminate\Validation\ValidationException;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetExternalMenusWithPriceCategoriesRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetExternalMenusWithPriceCategoriesResponse\ExternalMenuData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetExternalMenusWithPriceCategoriesResponse\GetExternalMenusWithPriceCategoriesResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetExternalMenusWithPriceCategoriesResponse\PriceCategoryData;
use Infrastructure\Integrations\IIko\Exceptions\IIkoIntegrationException;
use Infrastructure\Integrations\IIko\IikoAuthenticator;
use Infrastructure\Integrations\IIko\Requests\GetExternalMenusWithPriceCategoriesRequest;
use Infrastructure\Persistence\Eloquent\Settings\Models\OrganizationSetting;
use Presentation\Admin\Resources\OrganizationSettingResource\Pages;

final class OrganizationSettingResource extends Resource
{
    protected static ?string $navigationLabel = 'Организации';

    protected static ?string $title = 'Организация';

    protected ?string $heading = 'Организация';

    protected static ?string $label = 'организацию';

    protected static ?string $pluralLabel = 'Организаций';

    protected static ?string $navigationGroup = 'Настройки';

    protected static ?string $model = OrganizationSetting::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('iiko_api_key')
                            ->label('IIKO API Key')
                            ->string()
                            ->required()
                            ->reactive(),
                        Forms\Components\TextInput::make('iiko_restaurant_id')
                            ->label('ID ресторана IIKO')
                            ->string()
                            ->required()
                            ->rules([
                                'regex:/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/',
                                static fn (Get $get): Closure => static function (
                                    string $attribute,
                                    mixed $value,
                                    Closure $fail,
                                ) use ($get) {
                                    $exists = OrganizationSetting::query()
                                        ->where('iiko_api_key', $get('iiko_api_key'))
                                        ->where('iiko_restaurant_id', $value)
                                        ->exists();

                                    if ($exists) {
                                        $fail('Комбинация IIKO API Key и ID ресторана должна быть уникальной во всём проекте.');
                                    }
                                },
                            ])
                            ->reactive(),  // Также делаем поле реактивным
                        Forms\Components\TextInput::make('welcome_group_restaurant_id')
                            ->label('ID ресторана Welcome Доставка')
                            ->integer()
                            ->required(),
                        Forms\Components\TextInput::make('welcome_group_default_workshop_id')
                            ->label('ID цеха Welcome Доставка применяемого по умолчанию')
                            ->integer()
                            ->required(),
                        Forms\Components\TextInput::make('order_delivery_type_id')
                            ->label('ID типа заказа на доставку в IIKO')
                            ->string()
                            ->required(),
                        Forms\Components\TextInput::make('order_pickup_type_id')
                            ->label('ID типа заказа на самовывоз в IIKO')
                            ->string()
                            ->required(),
                    ])
                    ->columns([
                        'default' => 1,
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 4,
                        'xl' => 2,
                        '2xl' => 2,
                    ]),

                Forms\Components\Repeater::make('payment_types')
                    ->label('Типы оплат')
                    ->schema([
                        Forms\Components\TextInput::make('iiko_payment_code')
                            ->label('Код типа оплаты IIKO')
                            ->maxLength(5)
                            ->string()
                            ->required(),
                        Forms\Components\TextInput::make('welcome_group_payment_code')
                            ->label('Код типа оплаты Welcome Доставка')
                            ->string()
                            ->required(),
                    ])
                    ->columns()
                    ->reorderable(false)
                    ->collapsible()
                    ->addActionLabel('Добавить тип оплаты')
                    ->required(),

                Select::make('external_menu_id')
                    ->label('Меню приложения')
                    ->options(
                        static function (
                            Get $get,
                            IikoAuthenticator $authenticator,
                            IikoConnectorInterface $iikoConnector,
                        ): array {
                            $iikoApiKey = $get('iiko_api_key');
                            $iikoRestaurantId = $get('iiko_restaurant_id');

                            if (! self::hasValidApiKey($iikoApiKey) || ! self::hasValidRestaurantId($iikoRestaurantId)) {
                                return [];
                            }

                            try {
                                /** @var GetExternalMenusWithPriceCategoriesResponseData $response */
                                $response = $iikoConnector->send(
                                    new GetExternalMenusWithPriceCategoriesRequest(
                                        new GetExternalMenusWithPriceCategoriesRequestData([$iikoRestaurantId]),
                                        $authenticator->getAuthToken($iikoApiKey),
                                    ),
                                );
                            } catch (IIkoIntegrationException|RequestException|ConnectionException) {
                                Notification::make('validationError')
                                    ->title('Неверно введён апи-ключ')
                                    ->body('Меню не было получено в связи с неверным апи ключом')
                                    ->danger()
                                    ->send();

                                return [];
                            }

                            return $response->externalMenus
                                ->toCollection()
                                ->mapWithKeys(
                                    static fn (ExternalMenuData $externalMenu,
                                    ): array => [$externalMenu->id => $externalMenu->name],
                                )
                                ->toArray();
                        },
                    )
                    ->disabled(
                        static fn (Get $get): bool => ! self::hasValidApiKey($get('iiko_api_key'))
                            || ! self::hasValidRestaurantId($get('iiko_restaurant_id')),
                    )
                    ->hint(static function (Get $get): string {
                        if (
                            ! self::hasValidApiKey($get('iiko_api_key'))
                            || ! self::hasValidRestaurantId($get('iiko_restaurant_id'))
                        ) {
                            return 'Для выбора меню необходимо верно ввести Iiko API Key и ID ресторана Iiko';
                        }

                        return 'IIKO API Key и ID ресторана Iiko введены верно, можете выбрать меню';
                    })
                    ->required(),

                Repeater::make('price_categories')
                    ->label('Ценовые категории')
                    ->schema([
                        Select::make('category_id')
                            ->label('Категория')
                            ->options(
                                static function (
                                    Get $get,
                                    IikoAuthenticator $authenticator,
                                    IikoConnectorInterface $iikoConnector,
                                ): array {
                                    $iikoApiKey = $get('../../iiko_api_key');
                                    $iikoRestaurantId = $get('../../iiko_restaurant_id');

                                    if (
                                        ! self::hasValidApiKey($iikoApiKey)
                                        || ! self::hasValidRestaurantId($iikoRestaurantId)
                                    ) {
                                        return [];
                                    }

                                    try {
                                        /** @var GetExternalMenusWithPriceCategoriesResponseData $response */
                                        $response = $iikoConnector->send(
                                            new GetExternalMenusWithPriceCategoriesRequest(
                                                new GetExternalMenusWithPriceCategoriesRequestData([$iikoRestaurantId]),
                                                $authenticator->getAuthToken($iikoApiKey),
                                            ),
                                        );
                                    } catch (IIkoIntegrationException|RequestException|ConnectionException) {
                                        Notification::make()
                                            ->title('Ошибка')
                                            ->body('Не удалось получить данные ценовых категорий')
                                            ->danger()
                                            ->send();

                                        return [];
                                    }

                                    return $response->priceCategories
                                        ->toCollection()
                                        ->mapWithKeys(
                                            static fn (PriceCategoryData $priceCategoryData,
                                            ): array => [$priceCategoryData->id => $priceCategoryData->name],
                                        )
                                        ->toArray();
                                },
                            )
                            ->required(),

                        Forms\Components\TextInput::make('prefix')
                            ->label('Префикс')
                            ->string()
                            ->required()
                            ->rules([
                                static fn (): Closure => static function (
                                    string $attribute,
                                    mixed $value,
                                    Closure $fail,
                                ) {
                                    $organizationSettingRepository = resolve(
                                        OrganizationSettingRepositoryInterface::class,
                                    );

                                    $organizationSettingRepository
                                        ->all()
                                        ->each(
                                            static function (DomainOrganizationSetting $organizationSetting) use (
                                                $value,
                                                $fail
                                            ): void {
                                                $duplicates = $organizationSetting
                                                    ->priceCategories
                                                    ->where('prefix', $value);

                                                if ($duplicates->count() > 1) {
                                                    $fail('Поле Префикс должно быть уникальным.');

                                                    return;
                                                }
                                            },
                                        );
                                },
                            ])
                            ->beforeStateDehydrated(static function ($state, $get) {
                                $prefixes = collect($get('../'))->pluck('prefix');

                                $duplicates = $prefixes->duplicates();

                                if ($duplicates->isNotEmpty()) {
                                    Notification::make('validationErrorInRepeater')
                                        ->title('Ошибка валидации')
                                        ->danger()
                                        ->body("При указании ценовых категорий не должно быть повторяющихся значений \n Повторяющиеся значения: ".implode(', ', $duplicates->toArray()))
                                        ->send();

                                    // Не удалось сделать ошибку валидации по красоте. Оставил только для того чтобы прервать сохранение записи
                                    throw ValidationException::withMessages([
                                        'prefix' => 'При указании ценовых категорий не должно быть повторяющихся значений',
                                    ]);
                                }
                            }),
                    ])
                    ->columns()
                    ->reorderable(false)
                    ->collapsible()
                    ->addActionLabel('Добавить категорию')
                    ->disabled(
                        static fn (Get $get): bool => ! self::hasValidApiKey($get('iiko_api_key'))
                            || ! self::hasValidRestaurantId($get('iiko_restaurant_id')),
                    )
                    ->hint(static function (Get $get): string {
                        if (
                            ! self::hasValidApiKey($get('iiko_api_key'))
                            || ! self::hasValidRestaurantId($get('iiko_restaurant_id'))
                        ) {
                            return 'Для добавления ценовых категорий необходимо верно ввести IIKO API Key и ID ресторана IIKO';
                        }

                        return 'IIKO API Key и ID ресторана IIKO введены верно, можете добавить ценовые категории';
                    })
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('iiko_api_key')
                    ->label('IIKO API Key'),
                Tables\Columns\TextColumn::make('iiko_restaurant_id')
                    ->label('ID ресторана IIKO'),
                Tables\Columns\TextColumn::make('welcome_group_restaurant_id')
                    ->label('ID ресторана Welcome Доставка'),
                Tables\Columns\TextColumn::make('order_delivery_type_id')
                    ->label('ID типа доставки в IIKO'),
                Tables\Columns\TextColumn::make('order_pickup_type_id')
                    ->label('ID типа самовывоза в IIKO'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrganizationSettings::route('/'),
            'create' => Pages\CreateOrganizationSetting::route('/create'),
            'edit' => Pages\EditOrganizationSetting::route('/{record}/edit'),
        ];
    }

    private static function hasValidApiKey(?string $apiKey): bool
    {
        if (blank($apiKey)) {
            return false;
        }

        return true;
        return (bool) preg_match(
            '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/',
            $apiKey,
        );
    }

    private static function hasValidRestaurantId(?string $restaurantId): bool
    {
        if (blank($restaurantId)) {
            return false;
        }

        return (bool) preg_match(
            '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/',
            $restaurantId,
        );
    }
}
