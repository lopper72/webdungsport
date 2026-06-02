<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'user_id',
        'return_date',
        'total_amount',
        'debt_adjustment_amount',
        'refund_amount',
        'status',
        'note',
    ];

    protected $casts = [
        'return_date' => 'date',
        'total_amount' => 'decimal:2',
        'debt_adjustment_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(SalesReturnDetail::class, 'sales_return_id', 'id');
    }
}
