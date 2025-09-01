<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_category_map', function (Blueprint $table) {
            $table->timestamps();
        });
        Schema::table('product_attribute_map', function (Blueprint $table) {
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('product_category_map', function (Blueprint $table) {
            $table->dropTimestamps();
        });
        Schema::table('product_attribute_map', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};