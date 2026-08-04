<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Facades\DB;

class Warehouse extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;
    protected $table = 'warehouse';
    protected $fillable = ['code', 'name', 'address', 'phone', 'logo'];

    public function importProducts()
    {
        return $this->hasMany(ImportProduct::class, 'warehouse_id', 'id');
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderDetail::class, 'warehouse_id', 'id')
            ->join('orders', 'order_detail.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->select('order_detail.*');
    }

    public function transferWarehouseFrom()
    {
        return $this->hasMany(TransferWarehouse::class, 'from_warehouse_id', 'id');
    }

    public function transferWarehouseTo()
    {
        return $this->hasMany(TransferWarehouse::class, 'to_warehouse_id', 'id');
    }

    public function totalProductImport($product_id, $product_detail_id, $size_id)
    {
        if($size_id == null){
            return $this->importProducts()
                ->join('import_product_detail', 'import_product.id', '=', 'import_product_detail.import_product_id')
                ->where('import_product_detail.product_id', $product_id)
                ->where('import_product_detail.product_detail_id', $product_detail_id)
                ->sum('import_product_detail.quantity');
        }
        return $this->importProducts()
            ->join('import_product_detail', 'import_product.id', '=', 'import_product_detail.import_product_id')
            ->where('import_product_detail.product_id', $product_id)
            ->where('import_product_detail.product_detail_id', $product_detail_id)
            ->where('import_product_detail.size_id', $size_id)
            ->sum('import_product_detail.quantity');
    }

    public function totalProductTransfer($product_id, $product_detail_id, $size_id)
    {
        if($size_id == null){
            return $this->transferWarehouseFrom()
                ->join('transfer_product_detail', 'transfer_product.id', '=', 'transfer_product_detail.transfer_product_id')
                ->where('transfer_product_detail.product_id', $product_id)
                ->where('transfer_product_detail.product_detail_id', $product_detail_id)
                ->sum('transfer_product_detail.quantity');
        }
        return $this->transferWarehouseFrom()
            ->join('transfer_product_detail', 'transfer_product.id', '=', 'transfer_product_detail.transfer_product_id')
            ->where('transfer_product_detail.product_id', $product_id)
            ->where('transfer_product_detail.product_detail_id', $product_detail_id)
            ->where('transfer_product_detail.size_id', $size_id)
            ->sum('transfer_product_detail.quantity');
    }

    public function totalProductReceive($product_id, $product_detail_id, $size_id)
    {
        if($size_id == null){
            return $this->transferWarehouseTo()
                ->join('transfer_product_detail', 'transfer_product.id', '=', 'transfer_product_detail.transfer_product_id')
                ->where('transfer_product_detail.product_id', $product_id)
                ->where('transfer_product_detail.product_detail_id', $product_detail_id)
                ->sum('transfer_product_detail.quantity');
        }
        return $this->transferWarehouseTo()
            ->join('transfer_product_detail', 'transfer_product.id', '=', 'transfer_product_detail.transfer_product_id')
            ->where('transfer_product_detail.product_id', $product_id)
            ->where('transfer_product_detail.product_detail_id', $product_detail_id)
            ->where('transfer_product_detail.size_id', $size_id)
            ->sum('transfer_product_detail.quantity');
    }

    public function totalProductOrdered($product_id, $product_detail_id, $size_id)
    {
        if($size_id == null){
            return $this->orderProducts()
                ->where('product_id', $product_id)
                ->where('product_detail_id', $product_detail_id)
                ->sum('quantity');
        }
        return $this->orderProducts()
            ->where('product_id', $product_id)
            ->where('product_detail_id', $product_detail_id)
            ->where('size_id', $size_id)
            ->sum('quantity');
    }

    public function totalProductReturned($product_id, $product_detail_id, $size_id)
    {
        $query = DB::table('sales_return_details')
            ->join('sales_returns', 'sales_return_details.sales_return_id', '=', 'sales_returns.id')
            ->where('sales_return_details.warehouse_id', $this->id)
            ->where('sales_return_details.product_id', $product_id)
            ->where('sales_return_details.product_detail_id', $product_detail_id)
            ->where('sales_returns.status', '<>', 'canceled');

        if ($size_id == null) {
            return $query->sum('sales_return_details.quantity');
        }

        return $query->where('sales_return_details.size_id', $size_id)
            ->sum('sales_return_details.quantity');
    }

    public function totalProductAvailable($product_id, $product_detail_id, $size_id)
    {
        $totalImported = $this->totalProductImport($product_id, $product_detail_id, $size_id);
        $totalTransferFrom = $this->totalProductTransfer($product_id, $product_detail_id, $size_id);
        $totalTransferTo = $this->totalProductReceive($product_id, $product_detail_id, $size_id);
        $totalOrdered = $this->totalProductOrdered($product_id, $product_detail_id, $size_id);
        $totalReturned = $this->totalProductReturned($product_id, $product_detail_id, $size_id);

        return $totalImported - $totalOrdered + $totalReturned + $totalTransferTo - $totalTransferFrom;
    }
    public function hasImportProduct()
    {
        return $this->hasMany(ImportProduct::class, 'warehouse_id', 'id')->count() > 0;
    }
}
