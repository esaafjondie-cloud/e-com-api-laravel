<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Convert system_settings from key/value rows to a single record
     * with dedicated columns: admin_phone and sham_cash_qr.
     */
    public function up(): void
    {
        // Step 1: Drop old key/value table
        Schema::dropIfExists('system_settings');

        // Step 2: Create new single-record table
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('admin_phone')->nullable()->comment('Admin contact phone number');
            $table->string('sham_cash_qr')->nullable()->comment('Path to Sham Cash QR code image');
            $table->timestamps();
        });

        // Step 3: Insert the single default record
        DB::table('system_settings')->insert([
            'admin_phone'  => '+963999999999',
            'sham_cash_qr' => 'qr_codes/shamcash_demo.png',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    /**
     * Reverse the migration: restore old key/value structure.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }
};
