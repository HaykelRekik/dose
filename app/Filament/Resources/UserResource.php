<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->columns(3)
                            ->schema([
                                Forms\Components\ToggleButtons::make('role')
                                    ->label(__('Account Type'))
                                    ->options(UserRole::class)
                                    ->required()
                                    ->live(debounce: 300),

                                Forms\Components\TextInput::make('name')
                                    ->label(__('Name'))
                                    ->required(),
                                PhoneInput::make('phone')
                                    ->label(__('Phone'))
                                    ->required(),
                            ]),

                        Forms\Components\Grid::make()
                            ->columns(3)
                            ->schema([
                                Forms\Components\TextInput::make('email')
                                    ->label(__('Email'))
                                    ->required(fn(Forms\Get $get) => $get('role') !== UserRole::CUSTOMER->value)
                                    ->hidden(fn(Forms\Get $get) => $get('role') === UserRole::CUSTOMER->value)
                                    ->email()
                                    ->unique(ignoreRecord: true),
                                Forms\Components\TextInput::make('password')
                                    ->label(__('Password'))
                                    ->required(fn(Forms\Get $get) => $get('role') !== UserRole::CUSTOMER->value)
                                    ->hidden(fn(Forms\Get $get) => $get('role') === UserRole::CUSTOMER->value)
                                    ->password()
                                    ->visibleOn('create')
                                    ->revealable(),
                                Forms\Components\Select::make('branch_id')
                                    ->label(__('Branch'))
                                    ->relationship(
                                        name: 'branch',
                                        titleAttribute: 'name_' . app()->getLocale()
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required(fn(Forms\Get $get) => $get('role') === UserRole::EMPLOYEE->value)
                                    ->visible(fn(Forms\Get $get) => $get('role') === UserRole::EMPLOYEE->value),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->weight(FontWeight::Medium)
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email'))
                    ->icon('phosphor-envelope-open-duotone')
                    ->placeholder(__('Not specified'))
                    ->searchable()
                    ->visible(fn(Page $livewire): bool => 'customers' !== $livewire->activeTab),
                PhoneColumn::make('phone')
                    ->label(__('Phone'))
                    ->icon('phosphor-phone-call-duotone')
                    ->placeholder(__('Not specified'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->label(__('Account Type'))
                    ->badge()
                    ->hidden(fn(Page $livewire): bool => 'all' !== $livewire->activeTab),
                Tables\Columns\TextColumn::make('branch.name_' . app()->getLocale())
                    ->label(__('Branch'))
                    ->weight(FontWeight::Medium)
                    ->hidden(fn(Page $livewire): bool => 'employees' !== $livewire->activeTab),
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
                Tables\Filters\SelectFilter::make('branch')
                    ->label(__('Branch'))
                    ->relationship('branch', 'name_' . app()->getLocale()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('User');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Users');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Users Management');
    }
}
