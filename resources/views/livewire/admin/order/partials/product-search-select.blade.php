@props([
    'products',
    'selectedId' => '',
    'selectId' => 'product',
    'wireModel' => 'product_id',
    'onChange' => null,
])

@php
    $productOptions = $products
        ->map(fn ($product) => [
            'id' => (string) $product->id,
            'name' => $product->name,
        ])
        ->values();
@endphp

<div
    class="relative"
    wire:ignore
    x-data="{
        products: @js($productOptions),
        selectedId: @js((string) $selectedId),
        search: '',
        open: false,
        activeIndex: 0,
        init() {
            this.syncSearch();
        },
        normalize(value) {
            return (value || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        },
        get filteredProducts() {
            const keyword = this.normalize(this.search);

            if (!keyword) {
                return this.products.slice(0, 80);
            }

            return this.products
                .filter((product) => this.normalize(product.name).includes(keyword))
                .slice(0, 80);
        },
        syncSearch() {
            const selectedProduct = this.products.find((product) => String(product.id) === String(this.selectedId));
            this.search = selectedProduct ? selectedProduct.name : '';
        },
        choose(product) {
            this.selectedId = product.id;
            this.search = product.name;
            this.open = false;
            this.activeIndex = 0;
            this.$refs.select.value = product.id;
            this.$wire.set('{{ $wireModel }}', product.id).then(() => {
                @if($onChange)
                    this.$wire.call('{{ $onChange }}');
                @endif
            });
        },

        chooseActive() {
            const product = this.filteredProducts[this.activeIndex];

            if (product) {
                this.choose(product);
            }
        },
        moveActive(step) {
            const total = this.filteredProducts.length;

            if (!total) {
                this.activeIndex = 0;
                return;
            }

            this.activeIndex = (this.activeIndex + step + total) % total;
        },
    }"
    @click.outside="open = false"
>
    <input
        type="text"
        x-model="search"
        @focus="open = true"
        @input="open = true; activeIndex = 0"
        @keydown.arrow-down.prevent="open = true; moveActive(1)"
        @keydown.arrow-up.prevent="open = true; moveActive(-1)"
        @keydown.enter.prevent="chooseActive()"
        @keydown.escape="open = false; syncSearch()"
        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
        placeholder="Gõ tên sản phẩm để tìm kiếm"
        autocomplete="off"
    >

    <select
        x-ref="select"
        id="{{ $selectId }}"
        wire:model="{{ $wireModel }}"
        name="{{ $wireModel }}"
        class="sr-only"
        tabindex="-1"
        aria-hidden="true"
    >
        <option value="">-</option>
        @foreach($products as $product)
            <option value="{{ $product->id }}">{{ $product->name }}</option>
        @endforeach
    </select>

    <div
        x-show="open"
        class="absolute z-50 mt-1 max-h-60 w-full overflow-y-auto rounded-md bg-white py-1 text-sm shadow-lg ring-1 ring-black ring-opacity-5"
    >
        <template x-if="filteredProducts.length === 0">
            <div class="px-3 py-2 text-gray-500">Không tìm thấy sản phẩm</div>
        </template>

        <template x-for="(product, index) in filteredProducts" :key="product.id">
            <button
                type="button"
                class="block w-full px-3 py-2 text-left"
                :class="{
                    'bg-indigo-600 text-white': index === activeIndex,
                    'bg-indigo-50 text-indigo-700': index !== activeIndex && String(product.id) === String(selectedId),
                    'text-gray-900 hover:bg-indigo-50': index !== activeIndex && String(product.id) !== String(selectedId),
                }"
                @mouseenter="activeIndex = index"
                @click="choose(product)"
                x-text="product.name"
            ></button>
        </template>
    </div>
</div>
