<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'price',
        'stock',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    public function scopeSearch(Builder $query, ?string $term, ?string $category): Builder
    {
        return $query
            ->when($term, fn (Builder $q) => $q->where('name', 'like', "%{$term}%"))
            ->when($category, fn (Builder $q) => $q->where('category', $category));
    }
}
