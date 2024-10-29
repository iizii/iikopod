<?php

declare(strict_types=1);

namespace Presentation\Admin\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
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
                            ->required(),
                        Forms\Components\TextInput::make('iiko_restaurant_id')
                            ->label('ID ресторана Iiko')
                            ->integer()
                            ->required(),
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
                            ->integer()
                            ->required(),
                        Forms\Components\TextInput::make('order_pickup_type_id')
                            ->label('ID типа заказа на самовывоз')
                            ->integer()
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

                Forms\Components\Repeater::make('price_categories')
                    ->label('Ценовые категории')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Категория')
                            ->options([
                                'default' => 'default',
                            ])
                            ->string()
                            ->required(),
                        Forms\Components\TextInput::make('prefix')
                            ->label('Префикс')
                            ->string()
                            ->required(),
                    ])
                    ->columns()
                    ->reorderable(false)
                    ->collapsible()
                    ->addActionLabel('Добавить категорию')
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
