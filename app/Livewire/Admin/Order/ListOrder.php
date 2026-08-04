<?php

namespace App\Livewire\Admin\Order;

use Livewire\Component;
use App\Models\Order;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use App\Models\OrderDetail;

class ListOrder extends Component
{
    use WithPagination, WithoutUrlPagination;
    public $search_input = '';
    public $list_order = [];
    public $selected_index = [];

    public function search()
    {
        $this->resetPage();
    }

    public function deleteListCheckbox()
    {
        foreach ($this->selected_index as $key => $checked) {
            if($checked == true){
                $order_id = $this->list_order[$key]['id'];
                $this->deleteOrder($order_id);
            }
        }
        $this->selected_index = [];
        $this->render();
    }

    public function deleteOrder($id){
        $order_detail = OrderDetail::where('order_id', $id)->get();
        foreach ($order_detail as $item) {
            $item->delete();
        }
        $order = Order::find($id);
        $order->delete();
        session()->flash('success', 'Order deleted successfully');
    }

    public function handleDetele($id)
    {
        $this->deleteOrder($id);
        $this->render();
    }

    public function render()
    {
        $query = Order::with([
            'customer',
            'salesReturnDetails' => function ($query) {
                $query->whereHas('salesReturn', function ($returnQuery) {
                    $returnQuery->where('status', '<>', 'canceled');
                })->with('salesReturn');
            },
        ])->orderBy('created_at', 'desc');

        if($this->search_input == ''){
            $orders = $query->paginate(10);
        } else {
            $search = $this->search_input;

            $orders = $query
                ->where(function ($query) use ($search) {
                    $query->where('code', 'like', '%'.$search.'%')
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('name', 'like', '%'.$search.'%');
                        })
                        ->orWhereHas('salesReturnDetails.salesReturn', function ($returnQuery) use ($search) {
                            $returnQuery->where('code', 'like', '%'.$search.'%');
                        });
                })
                ->paginate(10);
        }
        $this->list_order = collect($orders->items());
        return view('livewire.admin.order.list-order', ['orders' => $orders]);
    }
}
