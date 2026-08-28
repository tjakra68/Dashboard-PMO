<div class="space-y-6">
    <div>
        <x-input-label for="name" :value="__('Nama Produk')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $product->name ?? '')" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="category" :value="__('Kategori')" />
        <x-text-input id="category" name="category" type="text" class="mt-1 block w-full"
                      :value="old('category', $product->category ?? '')" required />
        <x-input-error class="mt-2" :messages="$errors->get('category')" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <x-input-label for="price" :value="__('Harga (Rp)')" />
            <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="mt-1 block w-full"
                          :value="old('price', $product->price ?? '')" required />
            <x-input-error class="mt-2" :messages="$errors->get('price')" />
        </div>

        <div>
            <x-input-label for="stock" :value="__('Stok')" />
            <x-text-input id="stock" name="stock" type="number" min="0" class="mt-1 block w-full"
                          :value="old('stock', $product->stock ?? 0)" required />
            <x-input-error class="mt-2" :messages="$errors->get('stock')" />
        </div>
    </div>

    <div>
        <x-input-label for="description" :value="__('Deskripsi (opsional)')" />
        <textarea id="description" name="description" rows="4"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $product->description ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
        <a href="{{ route('products.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
            {{ __('Batal') }}
        </a>
    </div>
</div>
