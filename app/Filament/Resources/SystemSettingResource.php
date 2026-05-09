<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemSettingResource\Pages;
use App\Models\SystemSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * SystemSettingResource — manages the single settings record.
 * Create/Delete are disabled because exactly one record exists.
 * Admin edits phone & QR code from the list/edit page.
 */
class SystemSettingResource extends Resource
{
    protected static ?string $model = SystemSetting::class;

    protected static ?string $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'إعدادات النظام';
    protected static ?string $modelLabel = 'إعداد نظام';
    protected static ?string $pluralModelLabel = 'إعدادات النظام';
    protected static ?int    $navigationSort  = 5;

    // Only one record ever exists — disable create & delete
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات التواصل')
                    ->schema([
                        Forms\Components\TextInput::make('admin_phone')
                            ->label('رقم هاتف المدير')
                            ->required()
                            ->placeholder('+963912345678')
                            ->helperText('رقم التواصل الرئيسي المعروض للعملاء في التطبيق.'),
                    ]),

                Forms\Components\Section::make('الدفع — رمز QR شام كاش')
                    ->schema([
                        Forms\Components\FileUpload::make('sham_cash_qr')
                            ->label('صورة رمز QR')
                            ->image()
                            ->disk('public')
                            ->directory('qr_codes')
                            ->helperText('ارفع صورة رمز QR شام كاش المعروضة للعملاء عند الدفع.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('admin_phone')
                    ->label('هاتف المدير')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('sham_cash_qr')
                    ->label('صورة رمز QR')
                    ->disk('public')
                    ->height(60),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSystemSettings::route('/'),
            'edit'  => Pages\EditSystemSetting::route('/{record}/edit'),
        ];
    }
}
