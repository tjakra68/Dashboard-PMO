@php
    $fields = [
        'total_so' => 'Total SO (Rp)',
        'taxation' => 'Taxation Y 2026 (Rp)',
        'collection' => 'Collection to Date (Rp)',
        'july_target' => 'Target Juli (Rp)',
        'july_actual' => 'Aktual Juli (Rp)',
        'forecast_aug' => 'Forecast Aug (Rp)',
        'forecast_sep' => 'Forecast Sep (Rp)',
        'forecast_oct' => 'Forecast Oct (Rp)',
        'forecast_nov' => 'Forecast Nov (Rp)',
        'forecast_dec' => 'Forecast Dec (Rp)',
    ];
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <x-input-label for="name" :value="__('Nama Proyek')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                          :value="old('name', $projectDistribution->name ?? '')" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="sort_order" :value="__('Urutan Tampil')" />
            <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="mt-1 block w-full"
                          :value="old('sort_order', $projectDistribution->sort_order ?? 0)" />
            <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
        </div>

        @foreach ($fields as $field => $label)
            <div>
                <x-input-label :for="$field" :value="$label" />
                <x-text-input :id="$field" :name="$field" type="number" step="0.01" min="0" class="mt-1 block w-full"
                              :value="old($field, $projectDistribution->{$field} ?? 0)" required />
                <x-input-error class="mt-2" :messages="$errors->get($field)" />
            </div>
        @endforeach
    </div>

    <p class="text-sm text-gray-500">
        {{ __('Nilai dalam Rupiah penuh (mis. 570.54B ditulis 570540000000). Remaining dan Outstanding dihitung otomatis.') }}
    </p>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
        <a href="{{ route('project-distributions.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
            {{ __('Batal') }}
        </a>
    </div>
</div>
