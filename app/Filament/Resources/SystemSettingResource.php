<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemSettingResource\Pages;
use App\Models\SystemSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SystemSettingResource extends Resource
{
    protected static ?string $model = SystemSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'System Settings';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Setting Details')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->disabled(fn(string $context) => $context === 'edit')
                            ->helperText("e.g. 'sham_cash_qr', 'contact_phone'"),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(500)
                            ->rows(2),
                    ])->columns(2),

                Forms\Components\Section::make('Value')
                    ->schema([
                        // For QR Code - show FileUpload
                        Forms\Components\FileUpload::make('value')
                            ->label('QR Code Image')
                            ->image()
                            ->disk('public')
                            ->directory('qr_codes')
                            ->visible(fn(Get $get) => str_contains($get('key') ?? '', 'qr'))
                            ->helperText('Upload the Sham Cash QR Code image'),

                        // For text settings - show TextInput
                        Forms\Components\TextInput::make('value')
                            ->label('Value')
                            ->maxLength(1000)
                            ->visible(fn(Get $get) => !str_contains($get('key') ?? '', 'qr'))
                            ->helperText('Text value for this setting'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('value')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->value),
                Tables\Columns\TextColumn::make('description')
                    ->limit(60)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSystemSettings::route('/'),
            'create' => Pages\CreateSystemSetting::route('/create'),
            'edit'   => Pages\EditSystemSetting::route('/{record}/edit'),
        ];
    }
}
