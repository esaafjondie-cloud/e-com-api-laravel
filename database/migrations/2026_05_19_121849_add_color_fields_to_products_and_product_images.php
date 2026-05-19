<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('main_image_color')->nullable()->after('main_image');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->string('color')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('main_image_color');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
