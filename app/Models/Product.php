<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Product extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;
    protected $fillable = ['id'];
    public function productDetails()
    {
        return $this->hasMany(ProductDetail::class);
    }
    public function importProducts()
    {
        return $this->hasMany(ImportProductDetail::class, 'product_id', 'id');
    }
    public function productSizes()
    {
        return $this->hasMany(ProductSize::class);
    }
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
    public function productBrand()
    {
        return $this->HasOne(Brand::class, 'id', 'brand_id');
    }
    public function productCategory()
    {
        return $this->HasOne(Category::class, 'id', 'category_id');
    }
    public function totalImported()
    {
        return $this->importProducts()->sum('quantity');
    }
    public function totalSold()
    {
        return OrderDetail::query()
            ->join('orders', 'order_detail.order_id', '=', 'orders.id')
            ->where('order_detail.product_id', $this->id)
            ->where('orders.status', 'completed')
            ->sum('order_detail.quantity');
    }
    public function totalReturned()
    {
        return SalesReturnDetail::query()
            ->join('sales_returns', 'sales_return_details.sales_return_id', '=', 'sales_returns.id')
            ->where('sales_return_details.product_id', $this->id)
            ->where('sales_returns.status', '<>', 'canceled')
            ->sum('sales_return_details.quantity');
    }
    public function totalAvailable()
    {
        return $this->totalImported() - $this->totalSold() + $this->totalReturned();
    }
    public function warehouses(){
        $listImportId = $this->importProducts()->pluck('import_product_id')->toArray();
        $listWarehouseId = ImportProduct::whereIn('id', $listImportId)->pluck('warehouse_id')->toArray();
        $listWarehouseId = array_unique($listWarehouseId);
        return Warehouse::whereIn('id', $listWarehouseId)->get();
    }
    public function hasOrder(){
        return $this->totalSold() > 0;
    }
    public function hasImport(){
        return $this->importProducts()->count() > 0;
    }
}
