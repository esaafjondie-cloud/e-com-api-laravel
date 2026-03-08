<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Orders';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Details')
                    ->schema([
                        Forms\Components\TextInput::make('shipping_address')
                            ->required()
                            ->maxLength(500),
                        Forms\Components\TextInput::make('shipping_phone')
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->rows(2)
                            ->nullable(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'unpaid'         => 'Unpaid',
                                'paid'           => 'Paid',
                                'shipped'        => 'Shipped',
                                'delivered'      => 'Delivered',
                                'shipping_issue' => 'Shipping Issue',
                            ])
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Order Info')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')->label('Order #'),
                        Infolists\Components\TextEntry::make('user.name')->label('Customer'),
                        Infolists\Components\TextEntry::make('shipping_address'),
                        Infolists\Components\TextEntry::make('shipping_phone'),
                        Infolists\Components\TextEntry::make('total_amount')
                            ->money('SYP'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn(string $state): string => match($state) {
                                'unpaid'         => 'danger',
                                'paid'           => 'success',
                                'shipped'        => 'info',
                                'delivered'      => 'success',
                                'shipping_issue' => 'warning',
                                default          => 'secondary',
                            }),
                        Infolists\Components\TextEntry::make('notes'),
                    ])->columns(2),

                Infolists\Components\Section::make('Payment Receipt')
                    ->schema([
                        Infolists\Components\ImageEntry::make('payment_receipt_image')
                            ->label('Receipt Image')
                            ->disk('public')
                            ->width(400)
                            ->height(300),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order #')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('SYP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('shipping_phone'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger'  => 'unpaid',
                        'success' => 'paid',
                        'info'    => 'shipped',
                        'primary' => 'delivered',
                        'warning' => 'shipping_issue',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'unpaid'         => 'Unpaid',
                        'paid'           => 'Paid',
                        'shipped'        => 'Shipped',
                        'delivered'      => 'Delivered',
                        'shipping_issue' => 'Shipping Issue',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view'   => Pages\ViewOrder::route('/{record}'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
