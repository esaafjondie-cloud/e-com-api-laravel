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
    protected static ?string $navigationLabel = 'System Settings';
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
                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('admin_phone')
                            ->label('Admin Phone Number')
                            ->required()
                            ->placeholder('+963912345678')
                            ->helperText('The main contact number displayed to customers in the app.'),
                    ]),

                Forms\Components\Section::make('Payment — Sham Cash QR Code')
                    ->schema([
                        Forms\Components\FileUpload::make('sham_cash_qr')
                            ->label('QR Code Image')
                            ->image()
                            ->disk('public')
                            ->directory('qr_codes')
                            ->helperText('Upload the Sham Cash QR Code image shown to customers at checkout.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('admin_phone')
                    ->label('Admin Phone')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('sham_cash_qr')
                    ->label('QR Code')
                    ->disk('public')
                    ->height(60),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
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
