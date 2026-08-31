@use('App\Models\ProjectDistribution')
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Data Distribusi Proyek') }}
            </h2>
            <a href="{{ route('project-distributions.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                + {{ __('Tambah Proyek') }}
            </a>
        </div>
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 border border-green-200 p-4 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total SO</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Taxation</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Collection</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Remaining</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Outstanding</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($distributions as $d)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $d->name }}</td>
                                    <td class="px-4 py-3 text-right">{{ ProjectDistribution::formatAmount($d->total_so) }}</td>
                                    <td class="px-4 py-3 text-right">{{ ProjectDistribution::formatAmount($d->taxation) }}</td>
                                    <td class="px-4 py-3 text-right">{{ ProjectDistribution::formatAmount($d->collection) }}</td>
                                    <td class="px-4 py-3 text-right">{{ ProjectDistribution::formatAmount($d->remaining) }}</td>
                                    <td class="px-4 py-3 text-right">{{ ProjectDistribution::formatAmount($d->outstanding) }}</td>
                                    <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('project-distributions.edit', $d) }}"
                                           class="text-indigo-600 hover:text-indigo-900 font-medium">Edit</a>
                                        <form action="{{ route('project-distributions.destroy', $d) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Yakin ingin menghapus data proyek ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                        {{ __('Belum ada data proyek.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
