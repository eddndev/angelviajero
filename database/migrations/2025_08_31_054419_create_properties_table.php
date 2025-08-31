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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');
            $table->foreignId('sku_id')->nullable()->constrained('skus')->onDelete('cascade');
            $table->string('property_name', 100);
            $table->string('property_value', 255);
            $table->timestamps();

            // Constraint para asegurar que solo uno de product_id o sku_id esté lleno
            // DB::statement('ALTER TABLE properties ADD CONSTRAINT chk_entity_id CHECK ((product_id IS NOT NULL AND sku_id IS NULL) OR (product_id IS NULL AND sku_id IS NOT NULL));');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
