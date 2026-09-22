@extends('layouts.app')

@section('title', 'Kasir')

@section('content')
<h1 class="mb-4 text-lg font-semibold">Transaksi Kasir</h1>

<div
    x-data="{
        lastClickedId: null,
        cart: [],

        addToCart(id, name, price) {
            this.cart.push({ id, name, price });
            this.lastClickedId = id;
        },

        removeFromCart(id) {
            const index = this.cart.findIndex(item => item.id === id);

            if (index !== -1) {
                this.cart.splice(index, 1);
            }
        },

        subtotal() {
            return this.cart.reduce((sum, item) => sum + item.price, 0);
        }
    }"
>
    <div class="grid grid-cols-3 gap-4">
        @foreach ($products as $product)
            <div
                class="border rounded-md p-3 cursor-pointer"
                :class="{ 'ring-2 ring-blue-500': lastClickedId === {{ $product->id }} }"
                @click="addToCart({{ $product->id }}, '{{ $product->name }}', {{ $product->price }})"
            >
                <p class="font-medium">{{ $product->name }}</p>
                <p class="text-sm text-slate-500">
                    Rp {{ number_format($product->price) }}
                </p>
                @if ($product->stock < 10)
                    <span class="inline-block mt-2 px-2 py-1 text-xs rounded bg-amber-100 text-amber-700">
                        Stok Menipis
                    </span>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-4 border-t pt-3">
        <template x-for="item in cart" :key="item.id">
            <div class="flex items-center justify-between py-1">
                <p x-text="item.name + ' - Rp ' + item.price"></p>

                <button
                    type="button"
                    @click="removeFromCart(item.id)"
                    class="text-sm text-red-600 hover:underline"
                >
                    Hapus
                </button>
            </div>
        </template>

        <p class="mt-2 font-semibold">
            Subtotal: Rp <span x-text="subtotal()"></span>
        </p>
    </div>
</div>
@endsection
