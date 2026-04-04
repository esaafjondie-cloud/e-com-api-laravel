<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;

class SettingsController extends Controller
{
    /**
     * Get System Settings
     *
     * Retrieves public system settings: admin contact phone and Sham Cash QR code.
     *
     * @group Settings
     * @unauthenticated
     *
     * @response 200 {
     *   "data": {
     *     "admin_phone": "+963999999999",
     *     "sham_cash_qr": "http://localhost/storage/qr_codes/shamcash_demo.png"
     *   }
     * }
     */
    public function index()
    {
        $settings = SystemSetting::first();

        return response()->json([
            'data' => [
                'admin_phone'  => $settings?->admin_phone,
                'sham_cash_qr' => $settings?->sham_cash_qr
                    ? asset('storage/' . $settings->sham_cash_qr)
                    : null,
            ],
        ]);
    }
}

