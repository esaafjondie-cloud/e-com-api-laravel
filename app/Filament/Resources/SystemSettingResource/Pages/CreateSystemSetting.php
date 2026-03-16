<?php

namespace App\Filament\Resources\SystemSettingResource\Pages;

use App\Filament\Resources\SystemSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSystemSetting extends CreateRecord
{
    protected static string $resource = SystemSettingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (str_contains($data['key'] ?? '', 'qr')) {
            $qrValue = $data['qr_value'] ?? null;
            $data['value'] = is_array($qrValue) ? reset($qrValue) : $qrValue;
        } else {
            $data['value'] = $data['text_value'] ?? null;
        }

        unset($data['qr_value'], $data['text_value']);

        return $data;
    }
}
