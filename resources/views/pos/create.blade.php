@extends('layouts.app')

@section('title', 'Kasir')

@section('content')
<h1 class="mb-4 text-lg font-semibold">Transaksi Kasir</h1>

<div
    x-data="{
        lastClickedId: null,
        cart: [],

        addToCart(id, name, price) {
            this.lastClickedId = id;
            
            // Cek apakah produk sudah ada di keranjang
            const existingItem = this.cart.find(item => item.id === id);

            if (existingItem) {
                // Jika sudah ada, cukup tambahkan qty
                existingItem.quantity += 1;
            } else {
                // Jika belum ada, tambahkan baris baru dengan quantity: 1
                this.cart.push({
                    id: id,
                    name: name,
                    price: price,
                    quantity: 1
                });
            }
        },

        removeFromCart(id) {
            const index = this.cart.findIndex(item => item.id === id);
            if (index !== -1) {
                this.cart.splice(index, 1);
            }
        },

        subtotal() {
            // Hitung harga dikalikan dengan quantity masing-masing item
            return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        }
    }"
>
    <!-- Daftar Produk -->
    <div class="grid grid-cols-3 gap-4">
        @foreach ($products as $product)
            <div
                class="border rounded-md p-3 cursor-pointer select-none transition hover:shadow-sm"
                :class="{ 'ring-2 ring-blue-500': lastClickedId === {{ $product->id }} }"
                @click="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }})"
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

    <div class="mt-4">
        {{ $products->links() }}
    </div>

    <!-- Ringkasan Keranjang / Form Checkout -->
    <div class="mt-6 border-t pt-4">
        <h2 class="text-md font-semibold mb-2">Keranjang Belanja</h2>

        <template x-if="cart.length === 0">
            <p class="text-sm text-slate-400 italic">Keranjang masih kosong.</p>
        </template>

        <!-- Form untuk pengiriman data ke server -->
        <form action="{{ route('transactions.store') }}" method="POST">
            @csrf

            <div class="divide-y">
                <template x-for="(item, index) in cart" :key="item.id">
                    <div class="flex items-center justify-between py-2">
                        <!-- Input tersembunyi yang dikirim ke controller -->
                        <input type="hidden" :name="'items[' + index + '][id]'" :value="item.id">
                        <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">

                        <div>
                            <p class="font-medium">
                                <span x-text="item.name"></span>
                                <span class="text-sm font-semibold text-blue-600" x-text="' (x' + item.quantity + ')'"></span>
                            </p>
                            <p class="text-xs text-slate-500">
                                @ Rp <span x-text="item.price.toLocaleString('id-ID')"></span>
                                &bull; Total: Rp <span x-text="(item.price * item.quantity).toLocaleString('id-ID')"></span>
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="removeFromCart(item.id)"
                            class="text-sm text-red-600 hover:underline hover:text-red-800"
                        >
                            Hapus
                        </button>
                    </div>
                </template>
            </div>

            <p class="mt-4 text-base font-semibold">
                Subtotal: Rp <span x-text="subtotal().toLocaleString('id-ID')"></span>
            </p>

            <button
                type="submit"
                x-show="cart.length > 0"
                class="mt-3 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700"
            >
                Simpan Transaksi
            </button>
        </form>
    </div>
</div>
@endsection