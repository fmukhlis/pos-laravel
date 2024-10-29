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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('store_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');
            $table->foreignId('payment_method_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');
            $table->foreignId('store_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->foreignId('store_id')
                ->constrained()
                ->onDelete('cascade');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('store_id')
                ->constrained()
                ->onDelete('cascade');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('product_category_id')
                ->constrained()
                ->onDelete('cascade');
        });
        Schema::table('product_option_categories', function (Blueprint $table) {
            $table->foreignId('product_id')
                ->constrained()
                ->onDelete('cascade');
        });
        Schema::table('product_options', function (Blueprint $table) {
            $table->foreignId('product_option_category_id')
                ->constrained()
                ->onDelete('cascade');
        });
        Schema::table('product_variants', function (Blueprint $table) {
            $table->foreignId('product_id')
                ->constrained()
                ->onDelete('cascade');
        });
        Schema::table('product_modifier_categories', function (Blueprint $table) {
            $table->foreignId('product_id')
                ->constrained()
                ->onDelete('cascade');
        });
        Schema::table('product_modifiers', function (Blueprint $table) {
            $table->foreignId('product_modifier_category_id')
                ->constrained()
                ->onDelete('cascade');
        });
        Schema::table('stores', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');
        });
        Schema::table('permissions', function (Blueprint $table) {
            $table->foreignId('store_id')
                ->constrained()
                ->onDelete('cascade');
        });
        Schema::table('employee_invites', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');
            $table->foreignId('store_id')
                ->constrained()
                ->onDelete('cascade');
        });
        Schema::table('product_categories', function (Blueprint $table) {
            $table->foreignId('store_id')
                ->constrained()
                ->onDelete('cascade');
        });
        Schema::table('order_product_variant', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('product_variant_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
