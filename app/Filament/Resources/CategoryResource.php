<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'phosphor-bookmarks-duotone';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name_en')
                            ->label(__('Name (English)'))
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state): void {
                                $set('slug', Str::slug($state));
                            }),

                        Forms\Components\TextInput::make('name_ar')
                            ->label(__('Name (Arabic)'))
                            ->required(),

                        Forms\Components\TextInput::make('slug')
                            ->label(__('Slug'))
                            ->required()
                            ->readOnly()
                            ->unique(Category::class, 'slug', ignoreRecord: true)
                            ->helperText(__('A unique, URL-friendly identifier.')),

                        Forms\Components\ToggleButtons::make('is_active')
                            ->label(__('Activated ?'))
                            ->boolean()
                            ->default(true)
                            ->helperText(__('If activated, the category will be visible in the application.')),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('position')
            ->columns([
                Tables\Columns\TextColumn::make('name_en')
                    ->label(__('Name (English)'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('name_ar')
                    ->label(__('Name (Arabic)'))
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('Status'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('products_count')
                    ->label(__('Products count'))
                    ->counts('products')
                    ->suffix(' ' . __('product')),

            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([

            ])
            ->defaultSort('position', 'asc');
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Category');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Categories');
    }

    public static function getNavigationLabel(): string
    {
        return __('Product Categories');
    }
}
