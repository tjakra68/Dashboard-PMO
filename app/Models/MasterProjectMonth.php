<?php

namespace App\Models;

use Database\Factories\MasterProjectMonthFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterProjectMonth extends Model
{
    /** @use HasFactory<MasterProjectMonthFactory> */
    use HasFactory;

    protected $guarded = [];

    /**
     * @return BelongsTo<MasterProject, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(MasterProject::class, 'master_project_id');
    }

    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'forecast' => 'float',
            'target' => 'float',
            'actual' => 'float',
        ];
    }
}
