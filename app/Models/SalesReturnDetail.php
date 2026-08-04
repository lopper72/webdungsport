<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturnDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_return_id',
        'order_id',
        'order_detail_id',
        'product_id',
        'product_detail_id',
        'size_id',
        'warehouse_id',
        'quantity',
        'unit_price',
        'total_amount',
        'note',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class, 'sales_return_id', 'id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class, 'order_detail_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function productDetail()
    {
        return $this->belongsTo(ProductDetail::class, 'product_detail_id', 'id');
    }

    public function productSize()
    {
        return $this->belongsTo(ProductSize::class, 'size_id', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }
}
