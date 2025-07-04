<?php

declare(strict_types=1);

namespace App\Providers;

use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class UtilityServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Table::$defaultDateTimeDisplayFormat = 'd M Y , h:i A';
        /*
         * Always use 2 columns for form sections
         */
        Section::configureUsing(modifyUsing: function (Section $section): void {
            $section->columns(2);
        });
        /**
         * Language Switcher Config
         */
        LanguageSwitch::configureUsing(modifyUsing: function (LanguageSwitch $switch): void {
            $switch
                ->locales(config('app.supported_locales'))
                ->circular();
        });

        /**
         * Disable "create and create another" for all resource forms
         */
        CreateRecord::disableCreateAnother();

        /**
         * Add icon to create new record
         */
        CreateAction::configureUsing(modifyUsing: fn (CreateAction $action): CreateAction => $action->icon('phosphor-plus-circle-duotone'));

        /**
         * Set All Selects to use choices.js
         */
        Select::configureUsing(modifyUsing: fn (Select $select): Select => $select->native(false));

        /**
         * Change empty table state message
         */
        Table::configureUsing(modifyUsing: fn (Table $table): Table => $table->emptyStateHeading(trans('No records found.')));
        /**
         * Customize default table pagination options
         */
        Table::configureUsing(modifyUsing: fn (Table $table): Table => $table->paginated([5, 10])->defaultPaginationPageOption(10));
        /**
         * Get the latest records by default
         */
        // Table::configureUsing(modifyUsing: fn (Table $table): Table => $table->modifyQueryUsing(fn (Builder $query) => $query->latest()));

        /**
         * Add lazy loading to all table image columns
         */
        ImageColumn::configureUsing(modifyUsing: fn (ImageColumn $column): ImageColumn => $column->extraImgAttributes(['loading' => 'lazy']));

        /**
         * Phone input config
         */
        PhoneInput::configureUsing(modifyUsing: fn (PhoneInput $phoneInput): PhoneInput => $phoneInput->strictMode(true)
            ->formatAsYouType()
            ->onlyCountries(['SA'])
            ->validateFor(['SA'])
            ->autoPlaceholder('aggressive'));

        /**
         * Text Input config max length
         */
        TextInput::configureUsing(modifyUsing: fn (TextInput $input): TextInput => $input->maxLength(255));

        /**
         * Form Wizard config
         */
        Wizard::configureUsing(modifyUsing: fn (Wizard $wizard): Wizard => $wizard->skippable(app()->environment('local')));

        /** Configure Edit  Action Color */
        EditAction::configureUsing(modifyUsing: fn (EditAction $action): EditAction => $action->color(Color::hex('#80249f')));
    }
}
