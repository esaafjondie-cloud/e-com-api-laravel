<?php

namespace App\Filament\Vendor\Resources;

use App\Filament\Vendor\Resources\OrderResource\Pages;
use App\Filament\Vendor\Resources\OrderResource\RelationManagers;
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
    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Update Order Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'unpaid'         => 'Unpaid',
                                'paid'           => 'Paid',
                                'shipped'        => 'Shipped',
                                'delivered'      => 'Delivered',
                                'shipping_issue' => 'Shipping Issue',
                            ])
                            ->required(),
                    ]),
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
                                'delivered'      => 'primary',
                                'shipping_issue' => 'warning',
                                default          => 'secondary',
                            }),
                        Infolists\Components\TextEntry::make('notes'),
                    ])->columns(2),

                Infolists\Components\Section::make('Payment Receipt — Verify Payment')
                    ->schema([
                        Infolists\Components\ImageEntry::make('payment_receipt_image')
                            ->label('Receipt Image uploaded by customer')
                            ->disk('public')
                            ->width(450)
                            ->height(350),
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
                // NO Delete action for Vendor — security constraint
            ])
            ->bulkActions([])
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
            'view'   => Pages\ViewOrder::route('/{record}'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
