<?php

namespace App\Models;

use Database\Factories\CollectionEntryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectionEntry extends Model
{
    /** @use HasFactory<CollectionEntryFactory> */
    use HasFactory;

    public const ACCOUNTS = ['SIS', 'ASTEL', 'AST'];

    public const PROJECTS = ['TLKM', 'IOH', 'XL', 'ESS', 'MS', 'ND', 'EPS'];

    public const MONTHS = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];

    protected $fillable = [
        'year',
        'month',
        'account',
        'project',
        'so_value',
        'collection',
        'forecast',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'so_value' => 'float',
            'collection' => 'float',
            'forecast' => 'float',
        ];
    }

    public function scopeFilter(Builder $query, int $year, ?string $account, ?string $project): Builder
    {
        return $query
            ->where('year', $year)
            ->when($account, fn (Builder $q) => $q->where('account', $account))
            ->when($project, fn (Builder $q) => $q->where('project', $project));
    }
}
