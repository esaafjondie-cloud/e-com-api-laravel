<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SystemSetting — Single record model (not key/value).
 * Always use SystemSetting::first() to retrieve settings.
 *
 * @property string|null $admin_phone   Admin contact phone number
 * @property string|null $sham_cash_qr  Path to Sham Cash QR image in storage
 */
class SystemSetting extends Model
{
    protected $fillable = [
        'admin_phone',
        'sham_cash_qr',
    ];
}

