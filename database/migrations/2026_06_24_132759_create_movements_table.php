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
        Schema::create('movements', function (Blueprint $table) {
            $table->id('movement_id');
            $table->foreignId('inventory_id')->constrained('inventory', 'inventory_id');
            $table->foreignId('transaction_id')->constrained('transactions', 'transaction_id');
            $table->enum('transaction_type', ['ORDER', 'ORDER_CANCELLATION', 'GOODS_RECEIPT', 'ADJUSTMENT']);
            $table->integer('quantity_delta');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
