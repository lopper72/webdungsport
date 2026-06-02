<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedBigInteger('user_id');
            $table->date('return_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status')->default('completed');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('sales_return_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_return_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('order_detail_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_detail_id');
            $table->unsignedBigInteger('size_id')->nullable();
            $table->unsignedBigInteger('warehouse_id');
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('sales_return_id')->references('id')->on('sales_returns')->cascadeOnDelete();
            $table->foreign('order_id')->references('id')->on('orders');
            $table->foreign('order_detail_id')->references('id')->on('order_detail');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('product_detail_id')->references('id')->on('product_detail');
            $table->foreign('size_id')->references('id')->on('product_size');
            $table->foreign('warehouse_id')->references('id')->on('warehouse');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_return_details');
        Schema::dropIfExists('sales_returns');
    }
};
