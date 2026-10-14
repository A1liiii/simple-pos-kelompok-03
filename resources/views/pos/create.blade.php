
@extends('layouts.app')

@section('title', 'Kasir')

@section('content')

    {{-- Pesan sukses --}}
    @if (session('success'))
        <div style="background-color: #dcfce7; color: #166534; border: 1px solid #86efac; padding: 12px 16px; margin-bottom: 16px; border-radius: 6px;">
            <strong>Berhasil!</strong>
            <p style="margin-top: 4px;">
                {{ session('success') }}
            </p>
        </div>
    @endif

    {{-- Pesan error validasi --}}
    @if ($errors->has('items'))
        <div style="background-color: #fee2e2; color: #b91c1c; border: 1px solid #f87171; padding: 12px 16px; margin-bottom: 16px; border-radius: 6px;">
            <strong>Validasi Gagal!</strong>
            <p style="margin-top: 4px;">
                {{ $errors->first('items') }}
            </p>
        </div>
    @endif

    <h1 class="mb-4 text-lg font-semibold">Transaksi Kasir</h1>

    <div
        x-data="{
            lastClickedId: null,
            cart: [],

            addToCart(id, name, price) {
                this.lastClickedId = id;

                const existingItem = this.cart.find(item => item.id === id);

                if (existingItem) {
                    existingItem.quantity += 1;
                } else {
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
                return this.cart.reduce(
                    (sum, item) => sum + (item.price * item.quantity),
                    0
                );
            }
        }"
    >
        {{-- Daftar Produk --}}
        <div class="grid grid-cols-3 gap-4">
            @foreach ($products as $product)
                <div
                    class="cursor-pointer select-none rounded-md border p-3 transition hover:shadow-sm"
                    :class="{ 'ring-2 ring-blue-500': lastClickedId === {{ $product->id }} }"
                    @click="addToCart(
                        {{ $product->id }},
                        {{ Js::from($product->name) }},
                        {{ $product->price }}
                    )"
                >
                    <p class="font-medium">
                        {{ $product->name }}
                    </p>

                    <p class="text-sm text-slate-500">
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    </p>

                    @if ($product->stock < 10)
                        <span class="mt-2 inline-block rounded bg-amber-100 px-2 py-1 text-xs text-amber-700">
                            Stok Menipis
                        </span>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $products->links() }}
        </div>

        {{-- Keranjang Belanja --}}
        <div class="mt-6 border-t pt-4">
            <h2 class="mb-2 text-md font-semibold">
                Keranjang Belanja
            </h2>

            <template x-if="cart.length === 0">
                <p class="text-sm italic text-slate-400">
                    Keranjang masih kosong.
                </p>
            </template>

            {{-- Form transaksi --}}
            <form action="{{ route('transactions.store') }}" method="POST">
                @csrf

                <div class="divide-y">
                    <template x-for="(item, index) in cart" :key="item.id">
                        <div class="flex items-center justify-between py-2">

                            {{-- ID produk --}}
                            <input
                                type="hidden"
                                :name="'items[' + index + '][product_id]'"
                                :value="item.id"
                            >

                            {{-- Jumlah produk --}}
                            <input
                                type="hidden"
                                :name="'items[' + index + '][qty]'"
                                :value="item.quantity"
                            >

                            <div>
                                <p class="font-medium">
                                    <span x-text="item.name"></span>

                                    <span
                                        class="text-sm font-semibold text-blue-600"
                                        x-text="' (x' + item.quantity + ')'"
                                    ></span>
                                </p>

                                <p class="text-xs text-slate-500">
                                    @ Rp
                                    <span
                                        x-text="item.price.toLocaleString('id-ID')"
                                    ></span>

                                    &bull; Total: Rp
                                    <span
                                        x-text="(item.price * item.quantity).toLocaleString('id-ID')"
                                    ></span>
                                </p>
                            </div>

                            {{-- Tombol hapus --}}
                            <button
                                type="button"
                                @click="removeFromCart(item.id)"
                                class="text-sm text-red-600 hover:text-red-800 hover:underline"
                            >
                                Hapus
                            </button>
                        </div>
                    </template>
                </div>

                {{-- Subtotal --}}
                <p class="mt-4 text-base font-semibold">
                    Subtotal: Rp
                    <span x-text="subtotal().toLocaleString('id-ID')"></span>
                </p>

                {{-- Tombol simpan transaksi --}}
                <button
                    type="submit"
                    x-show="cart.length > 0"
                    class="mt-3 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                >
                    Simpan Transaksi
                </button>
            </form>
        </div>
    </div>

@endsection
