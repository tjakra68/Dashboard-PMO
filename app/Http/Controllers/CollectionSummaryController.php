<?php

namespace App\Http\Controllers;

use App\Models\MasterProject;
use App\Models\MasterProjectMonth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CollectionSummaryController extends Controller
{
    /**
     * Display the PMO collection summary dashboard.
     */
    public function index(Request $request): View
    {
        $years = MasterProject::query()
            ->select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->all();

        $year = (int) $request->integer('year', $years[0] ?? (int) date('Y'));
        $month = min(12, max(1, (int) $request->integer('month', (int) date('n'))));

        $account = $this->pickFromList($request->string('account')->toString(), MasterProject::ACCOUNTS);
        $project = $this->pickFromList($request->string('project')->toString(), MasterProject::PROJECTS);

        $monthly = $this->monthlyTotals($year, $account, $project);
        $projectTotals = $this->projectTotals($year, $account, $project);

        $soValue = (float) $projectTotals->sum('so_value');
        $taxation = (float) $monthly->sum('forecast');
        $collection = (float) $monthly->where('month', '<=', $month)->sum('actual');

        return view('dashboard', [
            'years' => $years ?: [$year],
            'year' => $year,
            'month' => $month,
            'account' => $account,
            'project' => $project,
            'accounts' => MasterProject::ACCOUNTS,
            'projects' => MasterProject::PROJECTS,
            'months' => MasterProject::MONTHS,
            'summary' => [
                'so_value' => $soValue,
                'collection' => $collection,
                'outstanding' => $soValue - $collection,
                'taxation' => $taxation,
                'remaining' => $taxation - $collection,
            ],
            'chart' => $this->buildChart($monthly, $month),
            'accountRows' => $this->buildAccountRows($monthly),
            'projectRows' => $this->buildProjectRows($monthly, $projectTotals, $month),
        ]);
    }

    /**
     * Monthly forecast/actual totals per account and project.
     *
     * @return Collection<int, object{account: string, project: string, month: int, forecast: float, actual: float}>
     */
    protected function monthlyTotals(int $year, ?string $account, ?string $project): Collection
    {
        return MasterProjectMonth::query()
            ->join('master_projects', 'master_projects.id', '=', 'master_project_months.master_project_id')
            ->tap(fn (Builder $query) => $this->applyFilters($query, $year, $account, $project))
            ->groupBy('master_projects.account', 'master_projects.project', 'master_project_months.month')
            ->selectRaw('master_projects.account as account')
            ->selectRaw('master_projects.project as project')
            ->selectRaw('master_project_months.month as month')
            ->selectRaw('SUM(master_project_months.forecast) as forecast')
            ->selectRaw('SUM(master_project_months.actual) as actual')
            ->get()
            ->map(fn (MasterProjectMonth $row) => (object) [
                'account' => (string) $row->getAttribute('account'),
                'project' => (string) $row->getAttribute('project'),
                'month' => (int) $row->getAttribute('month'),
                'forecast' => (float) $row->getAttribute('forecast'),
                'actual' => (float) $row->getAttribute('actual'),
            ]);
    }

    /**
     * SO value totals per project.
     *
     * @return Collection<int, object{project: string, so_value: float}>
     */
    protected function projectTotals(int $year, ?string $account, ?string $project): Collection
    {
        return MasterProject::query()
            ->filter($year, $account, $project)
            ->groupBy('project')
            ->selectRaw('project')
            ->selectRaw('SUM(so_value) as so_value')
            ->get()
            ->map(fn (MasterProject $row) => (object) [
                'project' => (string) $row->getAttribute('project'),
                'so_value' => (float) $row->getAttribute('so_value'),
            ]);
    }

    /**
     * @param  Builder<MasterProjectMonth>  $query
     */
    protected function applyFilters(Builder $query, int $year, ?string $account, ?string $project): void
    {
        $query
            ->where('master_projects.year', $year)
            ->when($account, fn (Builder $q) => $q->where('master_projects.account', $account))
            ->when($project, fn (Builder $q) => $q->where('master_projects.project', $project));
    }

    /**
     * @param  Collection<int, object{account: string, project: string, month: int, forecast: float, actual: float}>  $monthly
     * @return array{forecast: list<float>, cumulative_forecast: list<float>, collection: list<float>, cumulative_collection: list<float>, collected_to_date: float}
     */
    protected function buildChart(Collection $monthly, int $month): array
    {
        $forecast = [];
        $cumulativeForecast = [];
        $collection = [];
        $cumulativeCollection = [];
        $runningForecast = 0.0;
        $runningCollection = 0.0;

        foreach (range(1, 12) as $index) {
            $rows = $monthly->where('month', $index);
            $monthlyForecast = (float) $rows->sum('forecast');
            $monthlyCollection = (float) $rows->sum('actual');
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
     * @param  Collection<int, object{account: string, project: string, month: int, forecast: float, actual: float}>  $monthly
     * @return list<array{label: string, values: list<float>, total: float}>
     */
    protected function buildAccountRows(Collection $monthly): array
    {
        $rows = [];

        foreach (MasterProject::ACCOUNTS as $account) {
            $accountRows = $monthly->where('account', $account);

            if ($accountRows->isEmpty()) {
                continue;
            }

            $values = [];

            foreach (range(1, 12) as $index) {
                $values[] = round((float) $accountRows->where('month', $index)->sum('forecast'), 2);
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
     * @param  Collection<int, object{account: string, project: string, month: int, forecast: float, actual: float}>  $monthly
     * @param  Collection<int, object{project: string, so_value: float}>  $projectTotals
     * @return list<array{label: string, so_value: float, collection: float, outstanding: float, taxation: float, remaining: float}>
     */
    protected function buildProjectRows(Collection $monthly, Collection $projectTotals, int $month): array
    {
        $rows = [];

        foreach (MasterProject::PROJECTS as $project) {
            $projectRows = $monthly->where('project', $project);
            $soValue = (float) $projectTotals->where('project', $project)->sum('so_value');

            if ($projectRows->isEmpty() && $soValue === 0.0) {
                continue;
            }

            $taxation = (float) $projectRows->sum('forecast');
            $collection = (float) $projectRows->where('month', '<=', $month)->sum('actual');

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
