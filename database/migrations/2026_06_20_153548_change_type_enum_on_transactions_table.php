<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('ORDER', 'GOODS_RECEIPT', 'ADJUSTMENT') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('ORDER', 'ORDER_CANCELLATION', 'GOODS_RECEIPT', 'ADJUSTMENT') NULL");
    }
};
