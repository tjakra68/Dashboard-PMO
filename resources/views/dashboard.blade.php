@use('App\Models\ProjectDistribution')
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <a href="{{ route('project-distributions.index') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                {{ __('Kelola Data Proyek') }}
            </a>
        </div>
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-8">
                <h3 class="text-lg font-bold text-gray-800">Collection of Project Distribution</h3>

                <div class="relative h-96">
                    <canvas id="distributionChart"></canvas>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-yellow-300 text-sm text-center">
                        <thead>
                            <tr class="bg-yellow-100 text-gray-700">
                                <th rowspan="3" class="border border-yellow-300 px-3 py-2 bg-yellow-400 text-gray-900 uppercase">Project</th>
                                <th rowspan="3" class="border border-yellow-300 px-3 py-2 bg-yellow-400 text-gray-900 uppercase">Total SO</th>
                                <th colspan="10" class="border border-yellow-300 px-3 py-2 font-bold uppercase">Target 2026</th>
                            </tr>
                            <tr class="bg-yellow-50 text-gray-700">
                                <th rowspan="2" class="border border-yellow-300 px-3 py-2 uppercase">Taxation Y 2026</th>
                                <th rowspan="2" class="border border-yellow-300 px-3 py-2 uppercase">Collection to Jan - W4 Jul</th>
                                <th rowspan="2" class="border border-yellow-300 px-3 py-2 uppercase">Remaining</th>
                                <th colspan="2" class="border border-yellow-300 px-3 py-2 bg-violet-600 text-white uppercase">July</th>
                                <th colspan="5" class="border border-yellow-300 px-3 py-2 uppercase">Monthly Forecast</th>
                            </tr>
                            <tr class="bg-yellow-50 text-gray-700">
                                <th class="border border-yellow-300 px-3 py-2 bg-violet-600 text-white uppercase">Target</th>
                                <th class="border border-yellow-300 px-3 py-2 bg-violet-600 text-white uppercase">Actual</th>
                                <th class="border border-yellow-300 px-3 py-2 uppercase">Aug</th>
                                <th class="border border-yellow-300 px-3 py-2 uppercase">Sep</th>
                                <th class="border border-yellow-300 px-3 py-2 uppercase">Oct</th>
                                <th class="border border-yellow-300 px-3 py-2 uppercase">Nov</th>
                                <th class="border border-yellow-300 px-3 py-2 uppercase">Dec</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($distributions as $d)
                                <tr class="hover:bg-gray-50">
                                    <td class="border border-yellow-300 px-3 py-2 font-medium text-gray-900">{{ $d->name }}</td>
                                    <td class="border border-yellow-300 px-3 py-2">{{ ProjectDistribution::formatAmount($d->total_so) }}</td>
                                    <td class="border border-yellow-300 px-3 py-2">{{ ProjectDistribution::formatAmount($d->taxation) }}</td>
                                    <td class="border border-yellow-300 px-3 py-2">{{ ProjectDistribution::formatAmount($d->collection) }}</td>
                                    <td class="border border-yellow-300 px-3 py-2">{{ ProjectDistribution::formatAmount($d->remaining) }}</td>
                                    <td class="border border-yellow-300 px-3 py-2 bg-violet-700 text-white font-semibold">{{ ProjectDistribution::formatAmount($d->july_target) }}</td>
                                    <td class="border border-yellow-300 px-3 py-2 bg-violet-700 text-white font-semibold">{{ ProjectDistribution::formatAmount($d->july_actual) }}</td>
                                    <td class="border border-yellow-300 px-3 py-2">{{ ProjectDistribution::formatAmount($d->forecast_aug) }}</td>
                                    <td class="border border-yellow-300 px-3 py-2">{{ ProjectDistribution::formatAmount($d->forecast_sep) }}</td>
                                    <td class="border border-yellow-300 px-3 py-2">{{ ProjectDistribution::formatAmount($d->forecast_oct) }}</td>
                                    <td class="border border-yellow-300 px-3 py-2">{{ ProjectDistribution::formatAmount($d->forecast_nov) }}</td>
                                    <td class="border border-yellow-300 px-3 py-2">{{ ProjectDistribution::formatAmount($d->forecast_dec) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="border border-yellow-300 px-3 py-8 text-gray-500">
                                        Belum ada data proyek.
                                        <a href="{{ route('project-distributions.create') }}" class="text-indigo-600 underline">Tambah data</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($distributions->isNotEmpty())
                            <tfoot>
                                <tr class="bg-violet-300 font-bold text-gray-900">
                                    <td class="border border-yellow-300 px-3 py-2">Grand Total</td>
                                    <td class="border border-yellow-300 px-3 py-2">{{ ProjectDistribution::formatAmount($totals['total_so']) }}</td>
                                    <td class="border border-yellow-300 px-3 py-2">{{ ProjectDistribution::formatAmount($totals['taxation']) }}</td>
                                    <td class="border border-yellow-300 px-3 py-2">{{ ProjectDistribution::formatAmount($totals['collection']) }}</td>
                                    <td class="border border-yellow-300 px-3 py-2">{{ ProjectDistribution::formatAmount($totals['remaining']) }}</td>
                                    <td class="border border-yellow-300 px-3 py-2 bg-violet-700 text-white">{{ ProjectDistribution::formatAmount($totals['july_target']) }}</td>
                                    <td class="border border-yellow-300 px-3 py-2 bg-violet-700 text-white">{{ ProjectDistribution::formatAmount($totals['july_actual']) }}</td>
                                    <td class="border border-yellow-300 px-3 py-2">{{ ProjectDistribution::formatAmount($totals['forecast_aug']) }}</td>
                                    <td class="border border-yellow-300 px-3 py-2">{{ ProjectDistribution::formatAmount($totals['forecast_sep']) }}</td>
                                    <td class="border border-yellow-300 px-3 py-2">{{ ProjectDistribution::formatAmount($totals['forecast_oct']) }}</td>
                                    <td class="border border-yellow-300 px-3 py-2">{{ ProjectDistribution::formatAmount($totals['forecast_nov']) }}</td>
                                    <td class="border border-yellow-300 px-3 py-2">{{ ProjectDistribution::formatAmount($totals['forecast_dec']) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
    <script>
        const BILLION = 1_000_000_000;
        const MILLION = 1_000_000;

        function formatAmount(value) {
            const abs = Math.abs(value);
            if (abs >= BILLION) return (value / BILLION).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + 'B';
            if (abs >= MILLION) return (value / MILLION).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + 'M';
            return value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        new Chart(document.getElementById('distributionChart'), {
            type: 'bar',
            plugins: [ChartDataLabels],
            data: {
                labels: @json($chart['labels']),
                datasets: [
                    { label: 'SO', data: @json($chart['so']), backgroundColor: '#3b82f6', borderRadius: 6 },
                    { label: 'Collection', data: @json($chart['collection']), backgroundColor: '#34d399', borderRadius: 6 },
                    { label: 'Outstanding', data: @json($chart['outstanding']), backgroundColor: '#fbbf24', borderRadius: 6 },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 24 } },
                plugins: {
                    legend: { position: 'top' },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        formatter: formatAmount,
                        font: { size: 10, weight: 'bold' },
                        backgroundColor: '#fff',
                        borderColor: '#d1d5db',
                        borderWidth: 1,
                        borderRadius: 4,
                        padding: { top: 2, bottom: 2, left: 4, right: 4 },
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.dataset.label}: ${formatAmount(ctx.parsed.y)}`,
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (v) => (v / BILLION) + 'B' },
                    },
                },
            },
        });
    </script>
</x-app-layout>
