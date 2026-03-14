<?php

namespace App\Livewire\Client;

use Livewire\Component;
use App\Models\Product;
use App\Models\OrderDetail;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use App\Models\ProductDetail;
use App\Models\ProductSize;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class ProductBestSeller extends Component
{
    use WithPagination, WithoutUrlPagination; 
    public $slug;

    public function render()
    {
        $products = OrderDetail::select('product_id', DB::raw('SUM(quantity) as total_quantity'))->groupBy('product_id')->orderBy('total_quantity', 'desc')->paginate(12);
        return view('livewire.client.product-best-seller', ['products' => $products]);
    }
}