<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalGradeRecord extends Model
{
    protected $fillable = [
        'user_id',
        'language_group_id',
        'final_score',
        'passing_score',
        'total_modules',
        'module_scores',
        'status',
        'calculated_by',
        'approved_by',
        'calculated_at',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'final_score' => 'decimal:2',
            'passing_score' => 'decimal:2',
            'total_modules' => 'integer',
            'module_scores' => 'array',
            'calculated_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function languageGroup(): BelongsTo
    {
        return $this->belongsTo(LanguageGroup::class);
    }

    public function calculatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
