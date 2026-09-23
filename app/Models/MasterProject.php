<?php

namespace App\Models;

use Database\Factories\MasterProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterProject extends Model
{
    /** @use HasFactory<MasterProjectFactory> */
    use HasFactory;

    public const ACCOUNTS = ['SIS', 'ASTEL', 'AST'];

    public const PROJECTS = ['TLKM', 'IOH', 'XL', 'ESS', 'MS', 'ND', 'EPS'];

    public const MONTHS = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];

    protected $guarded = [];

    /**
     * @return HasMany<MasterProjectMonth, $this>
     */
    public function months(): HasMany
    {
        return $this->hasMany(MasterProjectMonth::class);
    }

    /**
     * @param  Builder<MasterProject>  $query
     * @return Builder<MasterProject>
     */
    public function scopeFilter(Builder $query, int $year, ?string $account, ?string $project): Builder
    {
        return $query
            ->where('year', $year)
            ->when($account, fn (Builder $q) => $q->where('account', $account))
            ->when($project, fn (Builder $q) => $q->where('project', $project));
    }

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'so_value' => 'float',
            'collection' => 'float',
            'outstanding' => 'float',
            'target' => 'float',
            'remaining' => 'float',
        ];
    }
}
