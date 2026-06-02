<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_returns', function (Blueprint $table) {
            $table->decimal('debt_adjustment_amount', 15, 2)->default(0)->after('total_amount');
            $table->decimal('refund_amount', 15, 2)->default(0)->after('debt_adjustment_amount');
        });
    }

    public function down(): void
    {
        Schema::table('sales_returns', function (Blueprint $table) {
            $table->dropColumn(['debt_adjustment_amount', 'refund_amount']);
        });
    }
};
