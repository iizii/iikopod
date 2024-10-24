<?php

declare(strict_types=1);

namespace Presentation\Admin\Pages;

use Domain\Settings\ContactSetting;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

final class ManageContact extends SettingsPage
{
    protected static ?string $navigationLabel = 'Контакты';

    protected static ?string $title = 'Контакты';

    protected ?string $heading = 'Контакты';

    protected static ?string $navigationGroup = 'Настройки';

    protected static string $settings = ContactSetting::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Fieldset::make('Почтовые данные')
                    ->schema([
                        Components\TagsInput::make('call_center_operator_email')
                            ->label('Email операторов колл-центра')
                            ->required(),
                        Components\TagsInput::make('specialist_email')
                            ->label('Email технических специалистов')
                            ->required(),
                    ]),
            ]);
    }
}
