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

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Wizard::make([
                Wizard\Step::make(__('Basic Info'))
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
                    ])->columns(2),

                Wizard\Step::make(__('Pricing & Details'))
                    ->icon('phosphor-tag-duotone')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label(__('Price'))
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->suffix('SAR'),
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
                                modifyQueryUsing: fn($query) => $query->where('is_active', true)->orderBy('position', 'asc')
                            )
                            ->multiple()
                            ->preload()
                            ->required(),

                        Forms\Components\ToggleButtons::make('is_active')
                            ->label(__('Product availability'))
                            ->required()
                            ->boolean(
                                trueLabel: __('Available'),
                                falseLabel: __('Not Available'),
                            )
                            ->grouped()
                            ->default(true),
                    ])
                    ->columns(2),

                Wizard\Step::make(__('Customization Options'))
                    ->icon('phosphor-sparkle-duotone')
                    ->schema([
                        Repeater::make('optionGroups')
                            ->relationship()
                            ->itemLabel(fn(array $state): ?string => $state['name_en'] ?? null)
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

                                Repeater::make('options')
                                    ->relationship()
                                    ->reorderable()
                                    ->schema([
                                        Forms\Components\TextInput::make('name_en')
                                            ->label(__('Option Name (EN)'))
                                            ->required(),
                                        Forms\Components\TextInput::make('name_ar')
                                            ->label(__('Option Name (AR)'))
                                            ->required(),
                                        Forms\Components\TextInput::make('extra_price')
                                            ->label(__('Extra Price'))
                                            ->numeric()
                                            ->prefix('SAR')
                                            ->default(0.00),
                                        Forms\Components\Toggle::make('is_available')
                                            ->label(__('Available'))
                                            ->default(true),
                                    ])
                                    ->columns(2)
                                    ->collapsible()
                                    ->label(__('Options / Choices'))
                                    ->defaultItems(1),
                            ])
                            ->collapsible()
                            ->cloneable()
                            ->label(__('Option Groups')),
                    ]),
            ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label(__('Image'))
                    ->circular(),
                Tables\Columns\TextColumn::make('name_en')
                    ->label(__('Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('SAR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('estimated_preparation_time')
                    ->label('Prep Time')
                    ->suffix(' min')
                    ->default(5)
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
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
}
