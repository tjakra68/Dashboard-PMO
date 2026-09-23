@php
    use App\Support\NumberFormat;

    $cards = [
        ['label' => 'SO Value', 'value' => $summary['so_value'], 'class' => 'from-purple-600 to-fuchsia-500'],
        ['label' => 'Collection', 'value' => $summary['collection'], 'class' => 'from-green-600 to-emerald-400'],
        ['label' => 'Outstanding', 'value' => $summary['outstanding'], 'class' => 'from-orange-500 to-amber-400'],
        ['label' => 'Taxation', 'value' => $summary['taxation'], 'class' => 'from-blue-600 to-sky-400'],
        ['label' => 'Remaining', 'value' => $summary['remaining'], 'class' => 'from-teal-700 to-emerald-500'],
    ];

    $chartPayload = [
        'labels' => $months,
        'forecast' => $chart['forecast'],
        'cumulativeForecast' => $chart['cumulative_forecast'],
        'monthIndex' => $month - 1,
        'collected' => $chart['collected_to_date'],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Collection Summary') }}</h2>
                <p class="text-sm text-gray-500">
                    {{ __('Per') }} : {{ $months[$month - 1] }} {{ $year }}
                </p>
            </div>

            <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-2">
                <select name="account" onchange="this.form.submit()"
                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('Semua Account') }}</option>
                    @foreach ($accounts as $option)
                        <option value="{{ $option }}" @selected($account === $option)>{{ $option }}</option>
                    @endforeach
                </select>

                <select name="project" onchange="this.form.submit()"
                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('Semua Project') }}</option>
                    @foreach ($projects as $option)
                        <option value="{{ $option }}" @selected($project === $option)>{{ $option }}</option>
                    @endforeach
                </select>

                <select name="month" onchange="this.form.submit()"
                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach ($months as $index => $label)
                        <option value="{{ $index + 1 }}" @selected($month === $index + 1)>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="year" onchange="this.form.submit()"
                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach ($years as $option)
                        <option value="{{ $option }}" @selected($year === $option)>{{ $option }}</option>
                    @endforeach
                </select>

                <noscript>
                    <x-primary-button type="submit">{{ __('Terapkan') }}</x-primary-button>
                </noscript>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ($cards as $card)
                    <div class="rounded-2xl bg-gradient-to-br {{ $card['class'] }} p-5 text-white shadow-lg">
                        <div class="text-2xl font-bold tracking-tight">{{ NumberFormat::compact($card['value']) }}</div>
                        <div class="mt-3 text-sm font-medium text-white/85">{{ $card['label'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-indigo-700">{{ __('Taxation') }} {{ $year }}</h3>
                <p class="text-sm text-gray-500">{{ __('Monthly forecast & cumulative collection overview') }}</p>

                <div class="mt-6 h-80">
                    <canvas data-collection-chart data-chart='@json($chartPayload)'></canvas>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-blue-700 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">{{ __('Account') }}</th>
                                @foreach ($months as $index => $label)
                                    <th @class([
                                        'px-4 py-3 text-right font-semibold',
                                        'bg-blue-900' => $month === $index + 1,
                                    ])>{{ $label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($accountRows as $row)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 font-medium text-gray-700">{{ $row['label'] }}</td>
                                    @foreach ($row['values'] as $index => $value)
                                        <td @class([
                                            'px-4 py-2 text-right text-gray-600',
                                            'bg-blue-50 font-medium' => $month === $index + 1,
                                        ])>{{ NumberFormat::compact($value, '') }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="px-4 py-10 text-center text-gray-500">
                                        {{ __('Belum ada data collection untuk filter ini.') }}
                                    </td>
                                </tr>
                            @endforelse

                            @if ($accountRows)
                                <tr class="bg-blue-50/70 font-semibold text-gray-800">
                                    <td class="px-4 py-2">{{ __('Forecast') }}</td>
                                    @foreach ($chart['forecast'] as $value)
                                        <td class="px-4 py-2 text-right">{{ NumberFormat::compact($value, '') }}</td>
                                    @endforeach
                                </tr>
                                <tr class="bg-blue-100/70 font-semibold text-gray-800">
                                    <td class="px-4 py-2">{{ __('Cumulative Forecast') }}</td>
                                    @foreach ($chart['cumulative_forecast'] as $value)
                                        <td class="px-4 py-2 text-right">{{ NumberFormat::compact($value, '') }}</td>
                                    @endforeach
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-800">{{ __('Ringkasan per Project') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-6 py-3 text-left">{{ __('Project') }}</th>
                                <th class="px-6 py-3 text-right">{{ __('SO Value') }}</th>
                                <th class="px-6 py-3 text-right">{{ __('Collection') }}</th>
                                <th class="px-6 py-3 text-right">{{ __('Outstanding') }}</th>
                                <th class="px-6 py-3 text-right">{{ __('Taxation') }}</th>
                                <th class="px-6 py-3 text-right">{{ __('Remaining') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($projectRows as $row)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-medium text-gray-800">{{ $row['label'] }}</td>
                                    <td class="px-6 py-3 text-right text-gray-600">{{ NumberFormat::compact($row['so_value']) }}</td>
                                    <td class="px-6 py-3 text-right text-green-700">{{ NumberFormat::compact($row['collection']) }}</td>
                                    <td class="px-6 py-3 text-right text-orange-600">{{ NumberFormat::compact($row['outstanding']) }}</td>
                                    <td class="px-6 py-3 text-right text-blue-700">{{ NumberFormat::compact($row['taxation']) }}</td>
                                    <td class="px-6 py-3 text-right text-teal-700">{{ NumberFormat::compact($row['remaining']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                        {{ __('Belum ada data project untuk filter ini.') }}
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
