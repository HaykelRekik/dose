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
                    ->schema([
                        Forms\Components\TextInput::make('name_en')->label(__('Name (English)'))->required(),
                        Forms\Components\TextInput::make('name_ar')->label(__('Name (Arabic)'))->required(),
                        Forms\Components\Textarea::make('description_en')->label(__('Description (English)'))->columnSpanFull(),
                        Forms\Components\Textarea::make('description_ar')->label(__('Description (Arabic)'))->columnSpanFull(),
                    ])->columns(2),

                Wizard\Step::make(__('Pricing & Details'))
                    ->schema([
                        Forms\Components\TextInput::make('price')->label(__('Base Price'))->required()->numeric()->prefix('SAR'),
                        Forms\Components\TextInput::make('estimated_preparation_time')->label(__('Prep Time (minutes)'))->required()->numeric()->suffix('min'),
                        Forms\Components\Select::make('categories')->label(__('Categories'))->relationship('categories', 'name_en')->multiple()->preload()->required(),
                        Forms\Components\FileUpload::make('image_url')->image()->directory('products')->label(__('Product Image')),
                        Forms\Components\Toggle::make('is_active')->label(__('Active'))->default(true),
                    ])->columns(2),

                Wizard\Step::make(__('Customization Options'))
                    ->schema([
                        Repeater::make('optionGroups')->relationship()->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('name_en')->label(__('Group Name (EN)'))->required(),
                                Forms\Components\TextInput::make('name_ar')->label(__('Group Name (AR)'))->required(),
                            ]),
                            Forms\Components\Toggle::make('is_required')->label(__('Is this group required?'))->default(true),
                            Forms\Components\Select::make('type')->label(__('Selection Type'))->options(ProductOptionGroupType::class)->required()->default(ProductOptionGroupType::SINGLE_SELECT),

                            Repeater::make('options')->relationship()->schema([
                                Forms\Components\TextInput::make('name_en')->label(__('Option Name (EN)'))->required(),
                                Forms\Components\TextInput::make('name_ar')->label(__('Option Name (AR)'))->required(),
                                Forms\Components\TextInput::make('extra_price')->label(__('Extra Price'))->numeric()->prefix('SAR')->default(0.00),
                                Forms\Components\Toggle::make('is_available')->label(__('Available'))->default(true),
                            ])->columns(2)->collapsible()->label(__('Options / Choices'))->defaultItems(1),
                        ])->collapsible()->cloneable()->label(__('Option Groups')),
                    ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')->label(__('Image'))->circular(),
                Tables\Columns\TextColumn::make('name_en')->label(__('Name'))->searchable(),
                Tables\Columns\TextColumn::make('price')->money('SAR')->sortable(),
                Tables\Columns\TextColumn::make('estimated_preparation_time')->label('Prep Time')->suffix(' min')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
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
