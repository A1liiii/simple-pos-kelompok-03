@extends('layouts.app')

@section('title', 'Produk')

@section('content')
    <h1 class="text-lg font-semibold mb-4">
        Daftar Produk
    </h1>

    @if (session('success'))
        <div class="bg-green-50 text-green-700 p-3 rounded-md mb-4">
            {{ session('success') }}
        </div>
    @endif

    <a
        href="{{ route('products.create') }}"
        class="inline-block mb-4 bg-blue-600 text-white px-4 py-2 rounded-md"
    >
        Tambah Produk
    </a>

    <table class="w-full text-left border-collapse">
        <thead>
            <tr>
                <th class="border-b p-2">Nama</th>
                <th class="border-b p-2">Kategori</th>
                <th class="border-b p-2">Harga</th>
                <th class="border-b p-2">Stok</th>
                <th class="border-b p-2">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td class="border-b p-2">
                        {{ $product->name }}
                    </td>

                    <td class="border-b p-2">
                        {{ $product->category?->name ?? '-' }}
                    </td>

                    <td class="border-b p-2">
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    </td>

                    <td class="border-b p-2">
                        {{ $product->stock }}
                    </td>

                    <td class="border-b p-2">
                        <a
                            href="{{ route('products.edit', $product) }}"
                            class="text-blue-600"
                        >
                            Edit
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-2 text-gray-500">
                        Belum ada produk.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
@endsection
