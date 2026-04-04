<div>
    <!-- Modal header -->
    <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">THÊM SẢN PHẨM VÀO ĐƠN HÀNG</h3>
        <button type="button" wire:click="$dispatch('closeModal')" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="modal-order-product">
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
            <span class="sr-only">Close modal</span>
        </button>
    </div>
    <!-- Modal body -->
    <form class="p-4 md:p-5" wire:submit.prevent="storeOrderProduct" onsubmit="return false">
        <div class="grid gap-4 mb-4 grid-cols-2">
            <div class="col-span-2">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Sản phẩm <span class="text-red-700">*</span></label>
                <div class="mt-2">
                    <select id="product_id" wire:model.live="product_id" wire:change="loadProductAttributes" class="convert-to-dropdown block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <option value="">-</option>
                        @foreach($products as $product)
                            <option value="{{$product["id"]}}">{{$product["name"]}}</option>
                        @endforeach
                    </select>
                </div>  
                @error('product_id')
                    <p class="text-red-500 text-xs italic">{{$message}}</p>
                @enderror
            </div>
            <div class="col-span-2">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Mẫu (Màu) <span class="text-red-700">*</span></label>
                <div class="mt-2">
                    <select id="product_detail_id" wire:model.live="product_detail_id" class="convert-to-dropdown block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <option value="">-</option>
                        @foreach($product_details as $product_detail)
                            <option value="{{$product_detail["id"]}}">{{$product_detail["title"]}}</option>
                        @endforeach
                    </select>
                </div>
                @error('product_detail_id')
                    <p class="text-red-500 text-xs italic">{{$message}}</p>
                @enderror
            </div>

            @if(count($size_items) > 0)
            <div class="col-span-2">
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Size</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tồn kho</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Số lượng</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Giá</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($size_items as $index => $item)
                            <tr class="{{ $item['quantity'] > 0 ? 'bg-green-50 dark:bg-green-900/20' : '' }}">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $item['size_name'] }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <span class="{{ $item['stock'] == 0 ? 'text-red-500 font-semibold' : 'text-green-600 dark:text-green-400' }}">
                                        {{ $item['stock'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" wire:click="decrementQuantity({{ $index }})" class="w-8 h-8 rounded bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-white font-bold" {{ $item['quantity'] <= 0 ? 'disabled' : '' }}>-</button>
                                        <input type="number" wire:model.live="size_items.{{ $index }}.quantity" min="0" max="{{ $item['stock'] > 0 ? $item['stock'] : '' }}" class="w-16 text-center rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                        <button type="button" wire:click="incrementQuantity({{ $index }})" class="w-8 h-8 rounded bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-white font-bold" {{ $item['stock'] > 0 && $item['quantity'] >= $item['stock'] ? 'disabled' : '' }}>+</button>
                                    </div>
                                    @error("size_items.$index.quantity")
                                        <p class="text-red-500 text-xs text-center mt-1">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <input type="number" wire:model.live="size_items.{{ $index }}.price" min="0" class="w-28 text-right rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    </div>
                                    @error("size_items.$index.price")
                                        <p class="text-red-500 text-xs text-center mt-1">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <button type="button" wire:click="copyPriceToAll({{ $index }})" class="text-xs px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 rounded hover:bg-blue-200 dark:hover:bg-blue-800" title="Sao chép giá này cho tất cả size">
                                        Áp dụng tất cả
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">Tổng cộng</td>
                                <td class="px-4 py-3 text-center font-semibold text-gray-900 dark:text-white">{{ $this->total_quantity }}</td>
                                <td colspan="3" class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">Thành tiền: {{ number_format($this->total_amount, 0, ',', '.') }} đ</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @error('size_items')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>
            @endif

            <div class="col-span-2">
                <label for="note" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Ghi chú</label>
                <textarea wire:model="note" id="note" rows="3" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder=""></textarea>                    
            </div>
        </div>

        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                <span class="inline-block w-3 h-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded mr-1"></span>
                Đã chọn size
            </div>
            <button type="submit" class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                <svg class="me-1 -ms-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 110 2h-3v3a1 1 11-2 0v-3H6a1 1 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
                + Thêm vào đơn hàng
            </button>
        </div>
    </form>
    @script
    <script>
        Livewire.hook('element.init', ({ component, el }) => {
            setTimeout(() => {
                convertSelectsToDropdowns()
            }, 100);
        })
    </script>
    @endscript
</div>