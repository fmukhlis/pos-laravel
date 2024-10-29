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
        Schema::create('order_product_variant_product_modifier', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_product_variant_id');
            $table->unsignedBigInteger('product_modifier_id');

            $table->foreign('order_product_variant_id', 'order_product_variant_product_modifier_opv_id_foreign')
                ->references('id')
                ->on('order_product_variant')
                ->onDelete('cascade');
            $table->foreign('product_modifier_id', 'order_product_variant_product_modifier_pm_id_foreign')
                ->references('id')
                ->on('product_modifiers')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_product_variant_product_modifier');
    }
};
