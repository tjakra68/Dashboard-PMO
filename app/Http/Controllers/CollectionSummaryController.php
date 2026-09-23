<?php

namespace App\Http\Controllers;

use App\Models\CollectionEntry;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CollectionSummaryController extends Controller
{
    /**
     * Display the PMO collection summary dashboard.
     */
    public function index(Request $request): View
    {
        $years = CollectionEntry::query()
            ->select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->all();

        $year = (int) $request->integer('year', $years[0] ?? (int) date('Y'));
        $month = min(12, max(1, (int) $request->integer('month', (int) date('n'))));

        $account = $this->pickFromList($request->string('account')->toString(), CollectionEntry::ACCOUNTS);
        $project = $this->pickFromList($request->string('project')->toString(), CollectionEntry::PROJECTS);

        /** @var Collection<int, CollectionEntry> $entries */
        $entries = CollectionEntry::query()
            ->filter($year, $account, $project)
            ->get();

        $soValue = (float) $entries->sum('so_value');
        $taxation = (float) $entries->sum('forecast');
        $collection = (float) $entries->where('month', '<=', $month)->sum('collection');

        $summary = [
            'so_value' => $soValue,
            'collection' => $collection,
            'outstanding' => $soValue - $collection,
            'taxation' => $taxation,
            'remaining' => $taxation - $collection,
        ];

        return view('dashboard', [
            'years' => $years ?: [$year],
            'year' => $year,
            'month' => $month,
            'account' => $account,
            'project' => $project,
            'accounts' => CollectionEntry::ACCOUNTS,
            'projects' => CollectionEntry::PROJECTS,
            'months' => CollectionEntry::MONTHS,
            'summary' => $summary,
            'chart' => $this->buildChart($entries, $month),
            'accountRows' => $this->buildAccountRows($entries),
            'projectRows' => $this->buildProjectRows($entries, $month),
        ]);
    }

    /**
     * @param  Collection<int, CollectionEntry>  $entries
     * @return array{forecast: list<float>, cumulative_forecast: list<float>, collection: list<float>, cumulative_collection: list<float>, collected_to_date: float}
     */
    protected function buildChart(Collection $entries, int $month): array
    {
        $forecast = [];
        $cumulativeForecast = [];
        $collection = [];
        $cumulativeCollection = [];
        $runningForecast = 0.0;
        $runningCollection = 0.0;

        foreach (range(1, 12) as $index) {
            $monthly = $entries->where('month', $index);
            $monthlyForecast = (float) $monthly->sum('forecast');
            $monthlyCollection = (float) $monthly->sum('collection');
            $runningForecast += $monthlyForecast;
            $runningCollection += $monthlyCollection;

            $forecast[] = round($monthlyForecast, 2);
            $cumulativeForecast[] = round($runningForecast, 2);
            $collection[] = round($monthlyCollection, 2);
            $cumulativeCollection[] = round($runningCollection, 2);
        }

        return [
            'forecast' => $forecast,
            'cumulative_forecast' => $cumulativeForecast,
            'collection' => $collection,
            'cumulative_collection' => $cumulativeCollection,
            'collected_to_date' => $cumulativeCollection[$month - 1],
        ];
    }

    /**
     * @param  Collection<int, CollectionEntry>  $entries
     * @return list<array{label: string, values: list<float>, total: float}>
     */
    protected function buildAccountRows(Collection $entries): array
    {
        $rows = [];

        foreach (CollectionEntry::ACCOUNTS as $account) {
            $accountEntries = $entries->where('account', $account);

            if ($accountEntries->isEmpty()) {
                continue;
            }

            $values = [];

            foreach (range(1, 12) as $index) {
                $values[] = round((float) $accountEntries->where('month', $index)->sum('forecast'), 2);
            }

            $rows[] = [
                'label' => $account,
                'values' => $values,
                'total' => round(array_sum($values), 2),
            ];
        }

        return $rows;
    }

    /**
     * @param  Collection<int, CollectionEntry>  $entries
     * @return list<array{label: string, so_value: float, collection: float, outstanding: float, taxation: float, remaining: float}>
     */
    protected function buildProjectRows(Collection $entries, int $month): array
    {
        $rows = [];

        foreach (CollectionEntry::PROJECTS as $project) {
            $projectEntries = $entries->where('project', $project);

            if ($projectEntries->isEmpty()) {
                continue;
            }

            $soValue = (float) $projectEntries->sum('so_value');
            $taxation = (float) $projectEntries->sum('forecast');
            $collection = (float) $projectEntries->where('month', '<=', $month)->sum('collection');

            $rows[] = [
                'label' => $project,
                'so_value' => round($soValue, 2),
                'collection' => round($collection, 2),
                'outstanding' => round($soValue - $collection, 2),
                'taxation' => round($taxation, 2),
                'remaining' => round($taxation - $collection, 2),
            ];
        }

        return $rows;
    }

    /**
     * @param  list<string>  $allowed
     */
    protected function pickFromList(string $value, array $allowed): ?string
    {
        return in_array($value, $allowed, true) ? $value : null;
    }
}
