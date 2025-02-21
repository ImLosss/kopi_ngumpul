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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('menu');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('order_id');
            $table->bigInteger('jumlah');
            $table->bigInteger('harga');
            $table->bigInteger('total');
            $table->boolean('pembayaran')->default(false);
            $table->string('payment_method')->nullable();
            $table->longText('note')->nullable();
            $table->string('update_payment_by')->nullable();
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
