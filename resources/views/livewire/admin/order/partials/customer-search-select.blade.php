@props([
    'customers',
    'selectedId' => '',
    'selectId' => 'customer',
])

@php
    $customerOptions = $customers
        ->map(fn ($customer) => [
            'id' => (string) $customer->id,
            'name' => $customer->name,
        ])
        ->values();
@endphp

<div
    class="relative"
    wire:ignore
    x-data="{
        customers: @js($customerOptions),
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
        get filteredCustomers() {
            const keyword = this.normalize(this.search);

            if (!keyword) {
                return this.customers.slice(0, 80);
            }

            return this.customers
                .filter((customer) => this.normalize(customer.name).includes(keyword))
                .slice(0, 80);
        },
        syncSearch() {
            const selectedCustomer = this.customers.find((customer) => String(customer.id) === String(this.selectedId));
            this.search = selectedCustomer ? selectedCustomer.name : '';
        },
        choose(customer) {
            this.selectedId = customer.id;
            this.search = customer.name;
            this.open = false;
            this.activeIndex = 0;
            this.$refs.select.value = customer.id;
            this.$wire.call('setCustomerId', customer.id);
        },
        chooseActive() {
            const customer = this.filteredCustomers[this.activeIndex];

            if (customer) {
                this.choose(customer);
            }
        },
        moveActive(step) {
            const total = this.filteredCustomers.length;

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
        placeholder="Gõ tên khách hàng"
        autocomplete="off"
    >

    <select
        x-ref="select"
        id="{{ $selectId }}"
        wire:model.change="customer_id"
        name="customer"
        class="sr-only"
        tabindex="-1"
        aria-hidden="true"
    >
        <option value="">-</option>
        @foreach($customers as $customer)
            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
        @endforeach
    </select>

    <div
        x-cloak
        x-show="open"
        class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-sm shadow-lg ring-1 ring-black ring-opacity-5"
    >
        <template x-if="filteredCustomers.length === 0">
            <div class="px-3 py-2 text-gray-500">Không tìm thấy khách hàng</div>
        </template>

        <template x-for="(customer, index) in filteredCustomers" :key="customer.id">
            <button
                type="button"
                class="block w-full px-3 py-2 text-left"
                :class="{
                    'bg-indigo-600 text-white': index === activeIndex,
                    'bg-indigo-50 text-indigo-700': index !== activeIndex && String(customer.id) === String(selectedId),
                    'text-gray-900 hover:bg-indigo-50': index !== activeIndex && String(customer.id) !== String(selectedId),
                }"
                @mouseenter="activeIndex = index"
                @click="choose(customer)"
                x-text="customer.name"
            ></button>
        </template>
    </div>
</div>
