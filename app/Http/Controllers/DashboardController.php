<?php

namespace App\Http\Controllers;

use App\Models\ProjectDistribution;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the dashboard with the project distribution chart and target table.
     */
    public function index(): View
    {
        $distributions = ProjectDistribution::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $chart = [
            'labels' => $distributions->pluck('name'),
            'so' => $distributions->pluck('total_so'),
            'collection' => $distributions->pluck('collection'),
            'outstanding' => $distributions->map(fn (ProjectDistribution $d) => $d->outstanding),
        ];

        $totals = [
            'total_so' => $distributions->sum('total_so'),
            'taxation' => $distributions->sum('taxation'),
            'collection' => $distributions->sum('collection'),
            'remaining' => $distributions->sum(fn (ProjectDistribution $d) => $d->remaining),
            'july_target' => $distributions->sum('july_target'),
            'july_actual' => $distributions->sum('july_actual'),
            'forecast_aug' => $distributions->sum('forecast_aug'),
            'forecast_sep' => $distributions->sum('forecast_sep'),
            'forecast_oct' => $distributions->sum('forecast_oct'),
            'forecast_nov' => $distributions->sum('forecast_nov'),
            'forecast_dec' => $distributions->sum('forecast_dec'),
        ];

        return view('dashboard', compact('distributions', 'chart', 'totals'));
    }
}
