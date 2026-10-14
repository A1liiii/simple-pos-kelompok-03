@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('content')
    <h1 class="text-lg font-semibold mb-4">
        Tambah Produk
    </h1>

    <form
        method="POST"
        action="{{ route('products.store') }}"
        class="max-w-md"
    >
        @csrf

        <label class="block mb-3">
            <span class="text-sm font-medium">Nama</span>

            <input
                type="text"
                name="name"
                value="{{ old('name') }}"
                class="mt-1 block w-full rounded-md border-gray-300"
            >

            @error('name')
                <p class="text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </label>

        <label class="block mb-3">
            <span class="text-sm font-medium">Kategori</span>

            <select
                name="category_id"
                class="mt-1 block w-full rounded-md border-gray-300"
            >
                <option value="">Pilih kategori</option>

                @foreach ($categories as $category)
                    <option
                        value="{{ $category->id }}"
                        @selected(old('category_id') == $category->id)
                    >
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            @error('category_id')
                <p class="text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </label>

        <label class="block mb-3">
            <span class="text-sm font-medium">Harga</span>

            <input
                type="number"
                name="price"
                step="1"
                value="{{ old('price') }}"
                class="mt-1 block w-full rounded-md border-gray-300"
            >

            @error('price')
                <p class="text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </label>

        <label class="block mb-3">
            <span class="text-sm font-medium">Stok</span>

            <input
                type="number"
                name="stock"
                step="1"
                value="{{ old('stock') }}"
                class="mt-1 block w-full rounded-md border-gray-300"
            >

            @error('stock')
                <p class="text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </label>

        <div class="flex gap-2">
            <button
                type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded-md"
            >
                Simpan
            </button>

            <a
                href="{{ route('products.index') }}"
                class="bg-gray-200 px-4 py-2 rounded-md"
            >
                Kembali
            </a>
        </div>
    </form>
@endsection
