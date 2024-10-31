<?php

declare(strict_types=1);

namespace Presentation\Admin\Resources;

use Doctrine\DBAL\ConnectionException;
use Domain\Integrations\Iiko\IikoConnectorInterface;
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
use Illuminate\Validation\Rule;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetExternalMenusWithPriceCategoriesRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetExternalMenusWithPriceCategoriesResponse\ExternalMenuData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetExternalMenusWithPriceCategoriesResponse\GetExternalMenusWithPriceCategoriesResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetExternalMenusWithPriceCategoriesResponse\PriceCategoryData;
use Infrastructure\Integrations\IIko\Exceptions\IIkoIntegrationException;
use Infrastructure\Integrations\IIko\IikoAuthenticator;
use Infrastructure\Integrations\IIko\Requests\GetExternalMenusWithPriceCategoriesRequest;
use Infrastructure\Persistence\Eloquent\Settings\OrganizationSetting;
use Presentation\Admin\Resources\OrganizationSettingResource\Pages;

final class OrganizationSettingResource extends Resource
{
    protected static ?string $navigationLabel = 'Организация';

    protected static ?string $title = 'Организация';

    protected ?string $heading = 'Организация';

    protected static ?string $label = 'организацию';

    protected static ?string $navigationGroup = 'Настройки';

    protected static ?string $model = OrganizationSetting::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('iiko_api_key')
                            ->label('Iiko API Key')
                            ->string()
                            ->required()
                            ->live(),
                        Forms\Components\TextInput::make('iiko_restaurant_id')
                            ->label('ID ресторана Iiko')
                            ->string()
                            ->required()
                            ->live(),
                        Forms\Components\TextInput::make('welcome_group_restaurant_id')
                            ->label('ID ресторана Welcome Group')
                            ->integer()
                            ->required(),
                        Forms\Components\TextInput::make('welcome_group_default_workshop_id')
                            ->label('ID цеха Welcome Group применяемого по умолчанию')
                            ->integer()
                            ->required(),
                        Forms\Components\TextInput::make('order_delivery_type_id')
                            ->label('ID типа заказа на доставку')
                            ->string()
                            ->required(),
                        Forms\Components\TextInput::make('order_pickup_type_id')
                            ->label('ID типа заказа на самовывоз')
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
                            ->label('Код типа оплаты Iiko')
                            ->maxLength(5)
                            ->string()
                            ->required(),
                        Forms\Components\TextInput::make('welcome_group_payment_code')
                            ->label('Код типа оплаты Welcome Group')
                            ->string()
                            ->required(),
                    ])
                    ->columns()
                    ->reorderable(false)
                    ->collapsible()
                    ->addActionLabel('Добавить тип оплаты')
                    ->required(),

                Select::make('external_menu')
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
                        static fn (Get $get): bool => ! self::hasValidApiKey($get('iiko_api_key')) || ! self::hasValidRestaurantId($get('iiko_restaurant_id')),
                    )
                    ->hint(static function (Get $get): string {
                        if (! self::hasValidApiKey($get('iiko_api_key')) || ! self::hasValidRestaurantId($get('iiko_restaurant_id'))) {
                            return 'Для выбора меню необходимо верно ввести Iiko API Key и ID ресторана Iiko';
                        }

                        return 'Iiko API Key и ID ресторана Iiko введены верно, можете выбрать меню';
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
                            ->unique(OrganizationSetting::class, 'prefix')
                            ->rules([
                                Rule::unique('organization_settings', 'prefix'),
                            ]),
                    ])
                    ->columns()
                    ->reorderable(false)
                    ->collapsible()
                    ->addActionLabel('Добавить категорию')
                    ->disabled(
                        static fn (Get $get): bool => ! self::hasValidApiKey(
                            $get('iiko_api_key'),
                        ) || ! self::hasValidRestaurantId($get('iiko_restaurant_id')),
                    )
                    ->hint(static function (Get $get): string {
                        if (! self::hasValidApiKey($get('iiko_api_key')) || ! self::hasValidRestaurantId(
                            $get('iiko_restaurant_id'),
                        )) {
                            return 'Для добавления ценовых категорий необходимо верно ввести Iiko API Key и ID ресторана Iiko';
                        }

                        return 'Iiko API Key и ID ресторана Iiko введены верно, можете добавить ценовые категории';
                    })
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('iiko_api_key')
                    ->label('Iiko API Key'),
                Tables\Columns\TextColumn::make('iiko_restaurant_id')
                    ->label('ID ресторана Iiko'),
                Tables\Columns\TextColumn::make('welcome_group_restaurant_id')
                    ->label('ID ресторана Welcome Group'),
                Tables\Columns\TextColumn::make('order_delivery_type_id')
                    ->label('ID типа доставки'),
                Tables\Columns\TextColumn::make('order_pickup_type_id')
                    ->label('ID типа самовывоза'),
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
