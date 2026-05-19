<?php

namespace App\Filament\Vendor\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProductImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';
    protected static ?string $title = 'معرض صور المنتج';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        $category = $ownerRecord->category;
        return $category && (str_contains($category->name, 'لبسة') || str_contains($category->name, 'ملابس'));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image_path')
                    ->label('الصورة')
                    ->image()
                    ->disk('public')
                    ->directory('products/gallery')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('color')
                    ->label('اللون المرتبط بالصورة')
                    ->options(function () {
                        $colors = $this->ownerRecord->colors;
                        if (!$colors || !is_array($colors)) return [];
                        return array_combine($colors, $colors);
                    })
                    ->searchable()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('image_path')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->disk('public')
                    ->label('الصورة')
                    ->square()
                    ->size(80),
                Tables\Columns\TextColumn::make('color')
                    ->label('اللون')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('إضافة صورة'),
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
}
