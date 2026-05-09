<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'عناصر الطلب';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('quantity')
                    ->label('الكمية')
                    ->numeric()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('المنتج')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('product.main_image')
                    ->disk('public')
                    ->label('الصورة')
                    ->square()
                    ->size(50),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('الكمية')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('SYP')
                    ->label('سعر الوحدة'),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('المجموع الفرعي')
                    ->state(fn($record) => number_format($record->price * $record->quantity, 2) . ' ل.س'),
            ])
            ->filters([])
            ->headerActions([])  // No adding items after order is placed
            ->actions([])
            ->bulkActions([]);
    }
}
