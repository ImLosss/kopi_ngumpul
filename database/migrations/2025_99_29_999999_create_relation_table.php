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
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('cascade');
        });

        Schema::table('stock_ins', function (Blueprint $table) {
            $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('cascade');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
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
