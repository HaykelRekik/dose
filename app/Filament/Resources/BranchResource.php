<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\BranchResource\Pages;
use App\Filament\Resources\BranchResource\RelationManagers\EmployeesRelationManager;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $navigationIcon = 'phosphor-git-branch-duotone';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name_en')
                            ->label(__('Name (English)'))
                            ->required(),
                        Forms\Components\TextInput::make('name_ar')
                            ->label(__('Name (Arabic)'))
                            ->required(),
                        Forms\Components\TextInput::make('address_en')
                            ->label(__('Address (English)'))
                            ->required(),
                        Forms\Components\TextInput::make('address_ar')
                            ->label(__('Address (Arabic)'))
                            ->required(),
                        PhoneInput::make('phone')
                            ->label(__('Phone'))
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->unique(ignoreRecord: true),
                        Forms\Components\ToggleButtons::make('is_active')
                            ->label(__('Accepting new orders ?'))
                            ->boolean()
                            ->hintIcon(
                                icon: 'heroicon-o-information-circle',
                                tooltip: __('Toggle this option to indicate whether the branch is currently accepting orders. Turn it off if the branch is temporarily closed (e.g., for maintenance or vacation).')
                            )
                            ->hintColor('info')
                            ->default(true)
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_' . app()->getLocale())
                    ->label(__('Name'))
                    ->searchable(['name_en', 'name_ar']),
                Tables\Columns\TextColumn::make('address_' . app()->getLocale())
                    ->label(__('Address'))
                    ->limit(30)
                    ->searchable(['address_en', 'address_ar']),
                PhoneColumn::make('phone')
                    ->label(__('Phone')),

                Tables\Columns\TextColumn::make('orders_count')
                    ->counts('orders')
                    ->label(__('Orders count'))
                    ->suffix(' ' . __('orders')),

                Tables\Columns\TextColumn::make('orders_sum_total_price')
                    ->sum('orders', 'total_price')
                    ->label(__('Orders Amount'))
                    ->saudiRiyal(),

                Tables\Columns\TextColumn::make('employees_count')
                    ->counts('employees')
                    ->label(__('Employees count'))
                    ->suffix(' ' . __('employee')),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('Accepting orders'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime(),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [
            EmployeesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Branch');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Branches');
    }
}
