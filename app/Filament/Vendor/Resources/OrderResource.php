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
    protected static ?string $navigationLabel = 'الطلبات';
    protected static ?string $modelLabel = 'طلب';
    protected static ?string $pluralModelLabel = 'الطلبات';
    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('تحديث حالة الطلب')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'unpaid'         => 'غير مدفوع',
                                'paid'           => 'مدفوع',
                                'shipped'        => 'تم الشحن',
                                'delivered'      => 'تم التوصيل',
                                'shipping_issue' => 'مشكلة في الشحن',
                            ])
                            ->required(),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('معلومات الطلب')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')->label('طلب رقم'),
                        Infolists\Components\TextEntry::make('user.name')->label('العميل'),
                        Infolists\Components\TextEntry::make('shipping_address')->label('عنوان التوصيل'),
                        Infolists\Components\TextEntry::make('shipping_phone')->label('هاتف التوصيل'),
                        Infolists\Components\TextEntry::make('total_amount')
                            ->label('المبلغ الإجمالي')
                            ->money('SYP'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->color(fn(string $state): string => match($state) {
                                'unpaid'         => 'danger',
                                'paid'           => 'success',
                                'shipped'        => 'info',
                                'delivered'      => 'primary',
                                'shipping_issue' => 'warning',
                                default          => 'secondary',
                            }),
                        Infolists\Components\TextEntry::make('notes')->label('ملاحظات'),
                    ])->columns(2),

                Infolists\Components\Section::make('إيصال الدفع — التحقق من الدفع')
                    ->schema([
                        Infolists\Components\ImageEntry::make('payment_receipt_image')
                            ->label('صورة الإيصال المرفوعة بواسطة العميل')
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
                    ->label('طلب رقم')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('العميل')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('المبلغ الإجمالي')
                    ->money('SYP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('shipping_phone')
                    ->label('هاتف التوصيل'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'danger'  => 'unpaid',
                        'success' => 'paid',
                        'info'    => 'shipped',
                        'primary' => 'delivered',
                        'warning' => 'shipping_issue',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'unpaid'         => 'غير مدفوع',
                        'paid'           => 'مدفوع',
                        'shipped'        => 'تم الشحن',
                        'delivered'      => 'تم التوصيل',
                        'shipping_issue' => 'مشكلة في الشحن',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('عرض'),
                Tables\Actions\EditAction::make()->label('تعديل'),
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
