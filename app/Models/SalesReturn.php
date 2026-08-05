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
        'cancelled_by',
        'cancelled_date',
    ];

    protected $casts = [
        'return_date' => 'date',
        'total_amount' => 'decimal:2',
        'debt_adjustment_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'cancelled_date' => 'datetime',
    ];


    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by', 'id');
    }

    public function details()
    {
        return $this->hasMany(SalesReturnDetail::class, 'sales_return_id', 'id');
    }

}
