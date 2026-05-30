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
    public $selected_index = [];

    public function search()
    {
        $this->resetPage();
    }

    public function deleteListCheckbox()
    {
        $orders = $this->getOrders();
        foreach ($this->selected_index as $key => $checked) {
            if ($checked == true) {
                $order = $orders->items()[$key] ?? null;
                if ($order) {
                    $this->deleteOrder($order->id);
                }
            }
        }
        $this->selected_index = [];
    }

    public function deleteOrder($id)
    {
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
    }

    private function getOrders()
    {
        if ($this->search_input == '') {
            return Order::with('customer')->orderBy('created_at', 'desc')->paginate(10);
        } else {
            return Order::with('customer')
                ->where('code', 'like', '%' . $this->search_input . '%')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }
    }

    public function render()
    {
        return view('livewire.admin.order.list-order', [
            'orders' => $this->getOrders(),
        ]);
    }
}
