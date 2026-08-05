<?php

namespace App\Livewire\Admin\SalesReturn;

use App\Models\SalesReturn;
use App\Services\DebtService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    /**
     * Hủy một phiếu trả hàng (soft delete).
     *
     * Phiếu trả hàng là chứng từ tài chính, KHÔNG được xóa vật lý mà chỉ
     * được hủy bằng cách đổi status thành 'canceled' và ghi nhận người/ngày hủy.
     *
     * Mọi ảnh hưởng của phiếu trả (Return Offset, số lượng đã trả, Payable
     * Amount, Outstanding Debt, Payment Status) đều được tính động từ các
     * phiếu trả có status <> 'canceled'. Vì vậy khi hủy phiếu, các giá trị
     * này tự động được hoàn tác trên các đơn hàng liên quan mà không cần
     * thao tác thủ công.
     */
    public function cancelSalesReturn($id)
    {
        $salesReturn = SalesReturn::findOrFail($id);

        // Kiểm tra điều kiện hủy (chưa hủy trước đó, chưa bị tham chiếu bởi chứng từ khác).
        $check = DebtService::canCancelSalesReturn($salesReturn);

        if (!$check['allowed']) {
            session()->flash('error', $check['message']);
            return;
        }

        DB::transaction(function () use ($salesReturn) {
            $salesReturn->status = 'canceled';
            $salesReturn->cancelled_by = Auth::id();
            $salesReturn->cancelled_date = now();
            $salesReturn->save();
        });

        session()->flash('message', 'Đã hủy phiếu trả hàng. Mọi ảnh hưởng đến công nợ, tồn kho và đơn hàng đã được hoàn tác.');
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
