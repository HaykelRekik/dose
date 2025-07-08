<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ProductOptionGroupType;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    /**
     * The main form definition, composed of smaller, reusable component functions.
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            Wizard::make([
                static::getBasicInfoStep(),
                static::getPricingAndDetailsStep(),
                static::getCustomizationOptionsStep(),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label(__('Image'))
                    ->circular(),
                Tables\Columns\TextColumn::make('name_' . app()->getLocale())
                    ->label(__('Product Name'))
                    ->searchable(['name_en', 'name_ar']),
                Tables\Columns\TextColumn::make('categories.name_' . app()->getLocale())
                    ->label(__('Categories'))
                    ->badge()
                    ->color('info')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('Price'))
                    ->numeric(locale: 'en')
                    ->saudiRiyal(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('Availability'))
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getLabel(): ?string
    {
        return __('Product');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Products');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Products Management');
    }

    /**
     * Returns the "Basic Info" wizard step.
     */
    protected static function getBasicInfoStep(): Wizard\Step
    {
        return Wizard\Step::make(__('Basic Info'))
            ->icon('phosphor-squares-four-duotone')
            ->schema([
                Forms\Components\TextInput::make('name_en')
                    ->label(__('Name (English)'))
                    ->required(),
                Forms\Components\TextInput::make('name_ar')
                    ->label(__('Name (Arabic)'))
                    ->required(),
                Forms\Components\Textarea::make('description_en')
                    ->label(__('Description (English)'))
                    ->autosize(),
                Forms\Components\Textarea::make('description_ar')
                    ->label(__('Description (Arabic)'))
                    ->autosize(),
                Forms\Components\FileUpload::make('image_url')
                    ->label(__('Product Image'))
                    ->image()
                    ->required()
                    ->directory('products')
                    ->columnSpanFull(),
            ])->columns(2);
    }

    /**
     * Returns the "Pricing & Details" wizard step.
     */
    protected static function getPricingAndDetailsStep(): Wizard\Step
    {
        return Wizard\Step::make(__('Pricing & Details'))
            ->icon('phosphor-tag-duotone')
            ->schema([
                Forms\Components\TextInput::make('price')
                    ->label(__('Price'))
                    ->required()
                    ->numeric()
                    ->maxLength(null)
                    ->minValue(0)
                    ->saudiRiyal(),
                Forms\Components\TextInput::make('estimated_preparation_time')
                    ->label(__('Estimated preparation time'))
                    ->required()
                    ->numeric()
                    ->suffix(__('mins')),
                Forms\Components\Select::make('categories')
                    ->label(__('Categories'))
                    ->relationship(
                        name: 'categories',
                        titleAttribute: 'name_' . app()->getLocale(),
                        modifyQueryUsing: fn ($query) => $query->active()->orderBy('position', 'asc')
                    )
                    ->multiple()
                    ->preload()
                    ->required(),

                Forms\Components\ToggleButtons::make('is_active')
                    ->label(__('Product availability'))
                    ->required()
                    ->boolean(
                        trueLabel: __('Available'),
                        falseLabel: __('Unavailable'),
                    )
                    ->grouped()
                    ->default(true),
            ])->columns(2);
    }

    /**
     * Returns the "Customization Options" wizard step.
     * This step itself is composed of a repeater for option groups.
     */
    protected static function getCustomizationOptionsStep(): Wizard\Step
    {
        return Wizard\Step::make(__('Customization Options'))
            ->icon('phosphor-sparkle-duotone')
            ->schema([
                static::getOptionGroupsRepeater(),
            ]);
    }

    /**
     * Returns the Repeater component for managing "Option Groups".
     * This is the first level of customization.
     */
    protected static function getOptionGroupsRepeater(): Repeater
    {
        return Repeater::make('optionGroups')
            ->relationship()
            ->itemLabel(fn (array $state): ?string => $state['name_' . app()->getLocale()] ?? null)
            ->reorderable()
            ->schema([
                Forms\Components\Grid::make(4)
                    ->schema([
                        Forms\Components\TextInput::make('name_en')
                            ->label(__('Group Name (EN)'))
                            ->required()
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('name_ar')
                            ->label(__('Group Name (AR)'))
                            ->required(),
                        Forms\Components\ToggleButtons::make('type')
                            ->label(__('Selection Type'))
                            ->options(ProductOptionGroupType::class)
                            ->required()
                            ->default(ProductOptionGroupType::SINGLE_SELECT),
                        Forms\Components\ToggleButtons::make('is_required')
                            ->label(__('Is this group required?'))
                            ->boolean()
                            ->default(true),
                    ]),
                static::getOptionsRepeater(),
            ])
            ->collapsible()
            ->collapsed()
            ->cloneable()
            ->label(__('Option Groups'));
    }

    /**
     * Returns the nested Repeater component for managing "Options" within a group.
     * This is the second level of customization.
     */
    protected static function getOptionsRepeater(): Repeater
    {
        return Repeater::make('options')
            ->relationship()
            ->reorderable()
            ->itemLabel(fn (array $state): ?string => $state['name_' . app()->getLocale()] ?? null)
            ->schema([
                Forms\Components\TextInput::make('name_en')
                    ->label(__('Option Name (EN)'))
                    ->live(onBlur: true)
                    ->required(),
                Forms\Components\TextInput::make('name_ar')
                    ->label(__('Option Name (AR)'))
                    ->required(),
                Forms\Components\TextInput::make('extra_price')
                    ->label(__('Extra Price'))
                    ->numeric()
                    ->saudiRiyal()
                    ->maxLength(null)
                    ->default(0.00),
                Forms\Components\ToggleButtons::make('is_active')
                    ->label(__('Available Options?'))
                    ->required()
                    ->boolean(
                        trueLabel: __('Available'),
                        falseLabel: __('Unavailable'),
                    )
                    ->grouped()
                    ->default(true),
            ])
            ->columns(4)
            ->collapsible()
            ->collapsed()
            ->label(__('Options / Choices'))
            ->defaultItems(1);
    }

    //    public static function getNavigationBadge(): ?string
    //    {
    //        return Cache::flexible(
    //            key: 'products_count',
    //            ttl: [
    //                5,
    //                30,
    //            ],
    //            callback: fn(): string => strval(Product::count())
    //        );
    //    }
}
