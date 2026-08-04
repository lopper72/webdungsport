<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $legacyStatuses = ['pending', 'confirmed', 'shipping', 'delivered'];
        $now = now();

        $orders = DB::table('orders')
            ->whereIn('status', $legacyStatuses)
            ->select('id', 'status')
            ->get();

        if ($orders->isEmpty()) {
            return;
        }

        DB::table('order_status')->insert($orders->map(function ($order) use ($now) {
            return [
                'order_id' => $order->id,
                'status' => 'completed',
                'note' => 'Chuan hoa trang thai don ban tai quay tu ' . $order->status . ' sang completed.',
                'action_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->all());

        DB::table('orders')
            ->whereIn('status', $legacyStatuses)
            ->update([
                'status' => 'completed',
                'updated_at' => $now,
            ]);
    }

    public function down(): void
    {
        //
    }
};
