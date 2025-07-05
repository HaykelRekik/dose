<?php

declare(strict_types=1);

namespace App\Filament\Resources\BranchResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';

    public static function getModelLabel(): ?string
    {
        return __('employee');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Branch employees management');
    }

    protected static function getPluralModelLabel(): ?string
    {
        return __('Employees');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Employee Name'))
                    ->required(),
                PhoneInput::make('phone')
                    ->label(__('Employee Phone'))
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->label(__('Employee Email'))
                    ->required()
                    ->email()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->label(__('Password'))
                    ->password()
                    ->visibleOn('create')
                    ->revealable()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Employees'))
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->weight(FontWeight::Medium)
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email'))
                    ->icon('phosphor-envelope-open-duotone')
                    ->placeholder(__('Not specified'))
                    ->searchable(),
                PhoneColumn::make('phone')
                    ->label(__('Phone'))
                    ->icon('phosphor-phone-call-duotone')
                    ->placeholder(__('Not specified'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label(__('Last login at'))
                    ->dateTime()
                    ->placeholder(__('Not specified'))
                    ->sinceTooltip(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Registered at'))
                    ->dateTime(),
            ])
            ->filters([

            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
