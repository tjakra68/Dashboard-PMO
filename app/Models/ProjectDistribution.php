<?php

namespace App\Models;

use Database\Factories\ProjectDistributionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;

class ProjectDistribution extends Model
{
    /** @use HasFactory<ProjectDistributionFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'total_so',
        'taxation',
        'collection',
        'july_target',
        'july_actual',
        'forecast_aug',
        'forecast_sep',
        'forecast_oct',
        'forecast_nov',
        'forecast_dec',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'total_so' => 'float',
            'taxation' => 'float',
            'collection' => 'float',
            'july_target' => 'float',
            'july_actual' => 'float',
            'forecast_aug' => 'float',
            'forecast_sep' => 'float',
            'forecast_oct' => 'float',
            'forecast_nov' => 'float',
            'forecast_dec' => 'float',
            'sort_order' => 'integer',
        ];
    }

    public function getOutstandingAttribute(): float
    {
        return $this->total_so - $this->collection;
    }

    public function getRemainingAttribute(): float
    {
        return $this->taxation - $this->collection;
    }

    /**
     * Format an amount using B (billions) / M (millions) suffixes, e.g. 570.54B, 996.41M.
     */
    public static function formatAmount(float $value): string
    {
        $abs = abs($value);

        if ($abs >= 1_000_000_000) {
            return Number::format($value / 1_000_000_000, precision: 2).'B';
        }

        if ($abs >= 1_000_000) {
            return Number::format($value / 1_000_000, precision: 2).'M';
        }

        return Number::format($value, precision: 2);
    }
}
