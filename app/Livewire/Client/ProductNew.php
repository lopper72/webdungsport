<?php

namespace App\Livewire\Client;

use Livewire\Component;
use App\Models\Product;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use App\Models\ProductDetail;
use App\Models\ProductSize;
use Illuminate\Support\Facades\Request;

class ProductNew extends Component
{
    use WithPagination, WithoutUrlPagination; 
    public $slug;

    public function render()
    {
        $products = Product::where('is_active', '=', '1')->orWhereNull('is_active')->orderBy('id', 'desc')->paginate(12);
        return view('livewire.client.product-new', ['products' => $products]);
    }
}
