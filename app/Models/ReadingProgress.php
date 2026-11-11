<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingProgress extends Model
{
    protected $fillable = [
        'user_id',
        'module_id',
        'current_page',
        'total_pages',
        'progress_percentage',
        'last_read_at',
    ];

    protected function casts(): array
    {
        return [
            'current_page' => 'integer',
            'total_pages' => 'integer',
            'progress_percentage' => 'integer',
            'last_read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}