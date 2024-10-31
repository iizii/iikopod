<?php

declare(strict_types=1);

namespace Presentation\Admin\Resources;

use Doctrine\DBAL\ConnectionException;
use Domain\Integrations\Iiko\IikoConnectorInterface;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\LazyCollection;
use Illuminate\Validation\Rule;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetExternalMenusWithPriceCategoriesRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetExternalMenusWithPriceCategoriesResponse\GetExternalMenusWithPriceCategoriesResponseData;
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
//                            ->rules(['regex:/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/']) // Проверка UUID
//                            ->afterStateUpdated(fn ($state, callable $set) => $set('external_menu', null)) // Обнуляем Select при изменении ID ресторана
                            ->reactive(),
                        Forms\Components\TextInput::make('iiko_restaurant_id')
                            ->label('ID ресторана Iiko')
                            ->string()
                            ->required()
                            ->rules(['regex:/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/']) // Проверка UUID
//                            ->afterStateUpdated(fn ($state, callable $set) => $set('external_menu', null)) // Обнуляем Select при изменении ID ресторана
                            ->reactive(),  // Также делаем поле реактивным
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
                    ->options(function (callable $get) {
                        $apiKey = $get('iiko_api_key') ?? '';
                        $restaurantId = $get('iiko_restaurant_id') ?? '';

                        // Проверка, что apiKey и restaurantId заполнены и корректны
                        if (preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $apiKey) && preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $restaurantId)) {
                            // Выполняем HTTP-запрос

                            /** @var IikoConnectorInterface $connector */
                            $connector = app(IikoConnectorInterface::class);
                            /** @var IikoAuthenticator $auth */
                            $auth = app(IikoAuthenticator::class);

                            $request = new GetExternalMenusWithPriceCategoriesRequest(
                                new GetExternalMenusWithPriceCategoriesRequestData([$restaurantId]),
                                ['Authorization' => 'Bearer ' . $auth->getAuthToken($apiKey)]
                            );

                            try {
                                /** @var GetExternalMenusWithPriceCategoriesResponseData $response */
                                $response = $connector
                                    ->send(
                                        $request
                                    );
                            } catch (IIkoIntegrationException|RequestException|ConnectionException $e) {
                                if ($e->getCode() === 401) {
                                    Notification::make('validationError')
                                        ->title('Неверно введён апи-ключ')
                                        ->body('Меню не было получено в связи с неверным апи ключом')
                                        ->danger()
                                        ->send();
                                }
                                return [];
                            }
                            return collect($response->externalMenus)
                                ->mapWithKeys(fn($externalMenu) => [$externalMenu['id'] => $externalMenu['name']])
                                ->toArray();
                        }

                        Notification::make('menuWarning')
                            ->title('Внимание')
                            ->body('Для выбора меню необходимо верно ввести Iiko API Key и ID ресторана Iiko')
                            ->warning()
                            ->send();

                        return [];
                    })
                    ->disabled(fn (callable $get) => !$get('iiko_api_key') || !$get('iiko_restaurant_id') || !preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $get('iiko_api_key') ?? '') || !preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $get('iiko_restaurant_id') ?? '')) // Блокировка селекта
                    ->reactive()
                    ->hint(function (callable $get) {
                        if (preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $get('iiko_api_key') ?? '') && preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $get('iiko_restaurant_id') ?? '')) {
                            return 'Iiko API Key и ID ресторана Iiko введены верно, можете выбрать меню';
                        } else {
                            return 'Для выбора меню необходимо верно ввести Iiko API Key и ID ресторана Iiko';
                        }
                    })
                    ->required(),
                Repeater::make('price_categories')
                    ->label('Ценовые категории')
                    ->schema([
                        Select::make('category_id')
                            ->label('Категория')
                            ->options(function (callable $get, $livewire) {
                                $apiKey = $get('iiko_api_key') ?? '';
                                $restaurantId = $get('iiko_restaurant_id') ?? '';

                                if (preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $apiKey) && preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $restaurantId)) {
                                    $connector = app(IikoConnectorInterface::class);
                                    $auth = app(IikoAuthenticator::class);

                                    $request = new GetExternalMenusWithPriceCategoriesRequest(
                                        new GetExternalMenusWithPriceCategoriesRequestData([$restaurantId]),
                                        ['Authorization' => 'Bearer ' . $auth->getAuthToken($apiKey)]
                                    );

                                    try {
                                        /** @var GetExternalMenusWithPriceCategoriesResponseData $response */
                                        $response = $connector->send($request);
                                        $priceCategories = collect($response->priceCategories)
                                            ->mapWithKeys(fn($category) => [$category['id'] => $category['name']])
                                            ->toArray();

                                        $selectedCategories = collect($livewire->data['price_categories'])->pluck('category_id')->filter()->toArray();
                                        return array_diff_key($priceCategories, array_flip($selectedCategories));
                                    } catch (IIkoIntegrationException|RequestException|ConnectionException $e) {
                                        Notification::make()->title('Ошибка')->body('Не удалось получить данные ценовых категорий')->danger()->send();
                                        return [];
                                    }
                                }
                                Notification::make('priceCategoriesWarn')
                                    ->title('Внимание')
                                    ->body('Для выбора ценовых категорий необходимо верно ввести Iiko API Key и ID ресторана Iiko')
                                    ->warning()
                                    ->send();
                                return [];
                            })
                            ->reactive()
                            ->required(),
                        Forms\Components\TextInput::make('prefix')
                            ->label('Префикс')
                            ->string()
                            ->required()
                            ->unique(OrganizationSetting::class, 'prefix')
                            ->rules([
                                Rule::unique('organization_settings', 'prefix'), // Проверка уникальности на уровне базы данных
                            ]),
                    ])
                    ->columns()
                    ->reorderable(false)
                    ->collapsible()
                    ->reactive()
                    ->addActionLabel('Добавить категорию')
                    ->disabled(fn (callable $get) => !$get('iiko_api_key') || !$get('iiko_restaurant_id') || !preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $get('iiko_restaurant_id')))
                    ->hint(function (callable $get) {
                        if (preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $get('iiko_api_key') ?? '') && preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $get('iiko_restaurant_id') ?? '')) {
                            return 'Iiko API Key и ID ресторана Iiko введены верно, можете добавить ценовые категории';
                        } else {
                            return 'Для добавления ценовых категорий необходимо верно ввести Iiko API Key и ID ресторана Iiko';
                        }
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
}
