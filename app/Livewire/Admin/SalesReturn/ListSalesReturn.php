<?php

namespace App\Livewire\Admin\SalesReturn;

use App\Models\SalesReturn;
use Livewire\Component;
use Livewire\WithPagination;

class ListSalesReturn extends Component
{
    use WithPagination;

    public $search_input = '';

    public function search()
    {
        $this->resetPage();
    }

    public function render()
    {
        $salesReturns = SalesReturn::with('customer')
            ->when($this->search_input !== '', function ($query) {
                $search = '%' . $this->search_input . '%';
                $query->where('code', 'like', $search)
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', $search);
                    });
            })
            ->orderByDesc('return_date')
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.admin.sales-return.list-sales-return', [
            'salesReturns' => $salesReturns,
        ]);
    }
}
