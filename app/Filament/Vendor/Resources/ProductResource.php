<?php

namespace App\Filament\Vendor\Resources;

use App\Filament\Vendor\Resources\ProductResource\Pages;
use App\Filament\Vendor\Resources\ProductResource\RelationManagers;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'المنتجات';
    protected static ?string $modelLabel = 'منتج';
    protected static ?string $pluralModelLabel = 'المنتجات';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات المنتج')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('category_id')
                            ->label('القسم')
                            ->options(Category::pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('التسعير والمخزون')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('السعر')
                            ->required()
                            ->numeric()
                            ->prefix('ل.س')
                            ->step(0.01),
                        Forms\Components\TextInput::make('stock')
                            ->label('المخزون')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true)
                            ->inline(false),
                    ])->columns(2),

                Forms\Components\Section::make('المقاسات والأحجام')
                    ->schema([
                        Forms\Components\Repeater::make('sizes')
                            ->label('المقاسات')
                            ->schema([
                                Forms\Components\Select::make('size')
                                    ->label('المقاس')
                                    ->options([
                                        'XS'   => 'XS - صغير جداً',
                                        'S'    => 'S - صغير',
                                        'M'    => 'M - وسط',
                                        'L'    => 'L - كبير',
                                        'XL'   => 'XL - كبير جداً',
                                        'XXL'  => 'XXL - كبير جداً جداً',
                                        '3XL'  => '3XL',
                                        '36'   => '36',
                                        '37'   => '37',
                                        '38'   => '38',
                                        '39'   => '39',
                                        '40'   => '40',
                                        '41'   => '41',
                                        '42'   => '42',
                                        '43'   => '43',
                                        '44'   => '44',
                                        '45'   => '45',
                                        '46'   => '46',
                                        '128GB'  => '128GB',
                                        '256GB'  => '256GB',
                                        '512GB'  => '512GB',
                                        '1TB'    => '1TB',
                                        'صغير'   => 'صغير',
                                        'وسط'    => 'وسط',
                                        'كبير'   => 'كبير',
                                    ])
                                    ->searchable()
                                    ->required(),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('الكمية المتوفرة')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required(),
                            ])
                            ->columns(2)
                            ->addActionLabel('إضافة مقاس')
                            ->collapsible()
                            ->defaultItems(0)
                            ->columnSpanFull(),
                        Forms\Components\TagsInput::make('colors')
                            ->label('الألوان المتوفرة')
                            ->placeholder('أضف لوناً واضغط Enter (مثال: أحمر، فستان بني، أخضر)')
                            ->visible(function (Forms\Get $get) {
                                $categoryId = $get('category_id');
                                if (!$categoryId) return false;
                                $category = \App\Models\Category::find($categoryId);
                                return $category && (str_contains($category->name, 'لبسة') || str_contains($category->name, 'ملابس'));
                            })
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('الصورة الرئيسية')
                    ->schema([
                        Forms\Components\FileUpload::make('main_image')
                            ->label('صورة المنتج الرئيسية')
                            ->image()
                            ->disk('public')
                            ->directory('products')
                            ->required(),
                        Forms\Components\Select::make('main_image_color')
                            ->label('لون الصورة الرئيسية')
                            ->options(function (Forms\Get $get) {
                                $colors = $get('colors');
                                if (!$colors || !is_array($colors)) return [];
                                return array_combine($colors, $colors);
                            })
                            ->searchable()
                            ->visible(function (Forms\Get $get) {
                                $categoryId = $get('category_id');
                                if (!$categoryId) return false;
                                $category = \App\Models\Category::find($categoryId);
                                return $category && (str_contains($category->name, 'لبسة') || str_contains($category->name, 'ملابس'));
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->label('الصورة')
                    ->disk('public')
                    ->square(),
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('القسم')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('السعر')
                    ->money('SYP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('المخزون')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('نشط'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('القسم')
                    ->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')->label('نشط'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductImagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
