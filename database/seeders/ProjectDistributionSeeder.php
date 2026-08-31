<?php

namespace Database\Seeders;

use App\Models\ProjectDistribution;
use Illuminate\Database\Seeder;

class ProjectDistributionSeeder extends Seeder
{
    private const BILLION = 1_000_000_000;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['TLKM', 570.54, 276.09, 176.67, 11.50, 60.41, 12.28, 20.32, 34.81, 14.80, 17.22],
            ['IOH', 136.98, 99.67, 71.17, 13.61, 20.95, 6.86, 4.47, 5.15, 6.01, 6.01],
            ['XL', 196.49, 71.67, 13.31, 2.79, 2.61, 23.70, 11.15, 23.50, 0, 0],
            ['ESS', 332.04, 103.53, 81.43, 8.34, 11.85, 15.85, 2.07, 4.15, 13.24, 13.24],
            ['MS', 55.54, 32.80, 26.08, 3.06, 3.20, 2.63, 813.46 / 1000, 578.46 / 1000, 706.55 / 1000, 1.99],
            ['ND', 29.36, 23.72, 22.65, 1.11, 996.41 / 1000, 278.88 / 1000, 797.25 / 1000, 0, 0, 0],
            ['EPS', 22.95, 6.56, 0, 0, 0, 6.56, 0, 0, 0, 0],
        ];

        foreach ($rows as $index => [$name, $totalSo, $taxation, $collection, $julyTarget, $julyActual, $aug, $sep, $oct, $nov, $dec]) {
            ProjectDistribution::updateOrCreate(['name' => $name], [
                'total_so' => $totalSo * self::BILLION,
                'taxation' => $taxation * self::BILLION,
                'collection' => $collection * self::BILLION,
                'july_target' => $julyTarget * self::BILLION,
                'july_actual' => $julyActual * self::BILLION,
                'forecast_aug' => $aug * self::BILLION,
                'forecast_sep' => $sep * self::BILLION,
                'forecast_oct' => $oct * self::BILLION,
                'forecast_nov' => $nov * self::BILLION,
                'forecast_dec' => $dec * self::BILLION,
                'sort_order' => $index,
            ]);
        }
    }
}
