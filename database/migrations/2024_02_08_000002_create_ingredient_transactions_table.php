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
        Schema::create('ingredient_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_stock');
            $table->integer('gram_ml');
            $table->enum('type', ['masuk', 'keluar']);
            $table->integer('modal')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredient_transactions');
    }
};
