<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::pluck('value', 'key');

        return response()->json([
            'sham_cash_qr' => $settings->get('sham_cash_qr') ? asset('storage/' . $settings->get('sham_cash_qr')) : null,
            'admin_phone' => $settings->get('admin_phone'),
        ]);
    }
}
