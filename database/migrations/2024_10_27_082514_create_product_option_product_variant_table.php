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
        Schema::create('product_option_product_variant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_option_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('product_variant_id')
                ->constrained()
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_option_product_variant');
    }
};
