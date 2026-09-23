<?php

namespace Database\Seeders;

use App\Models\CollectionEntry;
use Illuminate\Database\Seeder;

class CollectionEntrySeeder extends Seeder
{
    /**
     * Annual SO value (in rupiah) per account and project.
     *
     * @var array<string, array<string, float>>
     */
    protected array $annualSoValue = [
        'SIS' => [
            'TLKM' => 285_000_000_000,
            'IOH' => 96_000_000_000,
            'XL' => 74_000_000_000,
            'ESS' => 38_000_000_000,
            'MS' => 27_000_000_000,
            'ND' => 21_000_000_000,
            'EPS' => 16_000_000_000,
        ],
        'ASTEL' => [
            'TLKM' => 118_000_000_000,
            'IOH' => 64_000_000_000,
            'XL' => 52_000_000_000,
            'ESS' => 24_000_000_000,
            'MS' => 18_000_000_000,
            'ND' => 12_000_000_000,
            'EPS' => 9_000_000_000,
        ],
        'AST' => [
            'TLKM' => 174_000_000_000,
            'IOH' => 88_000_000_000,
            'XL' => 69_000_000_000,
            'ESS' => 42_000_000_000,
            'MS' => 31_000_000_000,
            'ND' => 23_000_000_000,
            'EPS' => 15_000_000_000,
        ],
    ];

    /**
     * Relative weight of each month within the yearly forecast.
     *
     * @var list<float>
     */
    protected array $monthlyWeight = [0.05, 0.06, 0.09, 0.07, 0.07, 0.11, 0.15, 0.04, 0.07, 0.08, 0.12, 0.09];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $year = (int) date('Y');
        $currentMonth = (int) date('n');

        foreach ($this->annualSoValue as $account => $projects) {
            foreach ($projects as $project => $annualSoValue) {
                foreach (range(1, 12) as $month) {
                    $weight = $this->monthlyWeight[$month - 1];
                    $forecast = $annualSoValue * $weight * 0.92;
                    $collection = $month <= $currentMonth
                        ? $forecast * $this->achievementRate($account, $month)
                        : 0.0;

                    CollectionEntry::updateOrCreate(
                        [
                            'year' => $year,
                            'month' => $month,
                            'account' => $account,
                            'project' => $project,
                        ],
                        [
                            'so_value' => round($annualSoValue * $weight, 2),
                            'collection' => round($collection, 2),
                            'forecast' => round($forecast, 2),
                        ],
                    );
                }
            }
        }
    }

    /**
     * Deterministic achievement rate so seeded data stays stable between runs.
     */
    protected function achievementRate(string $account, int $month): float
    {
        $base = match ($account) {
            'SIS' => 0.94,
            'ASTEL' => 0.81,
            default => 0.88,
        };

        return $base + (($month % 4) * 0.02);
    }
}
