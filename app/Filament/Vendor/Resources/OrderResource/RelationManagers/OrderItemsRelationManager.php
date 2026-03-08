<?php

namespace App\Filament\Vendor\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Order Items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('quantity')
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
                    ->label('Product')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('product.main_image')
                    ->disk('public')
                    ->label('Image')
                    ->square()
                    ->size(50),
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('SYP')
                    ->label('Unit Price'),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->state(fn($record) => number_format($record->price * $record->quantity, 2) . ' SYP'),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
