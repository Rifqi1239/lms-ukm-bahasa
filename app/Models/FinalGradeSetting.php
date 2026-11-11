<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalGradeSetting extends Model
{
    protected $fillable = [
        'language_group_id',
        'passing_score',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'passing_score' => 'decimal:2',
        ];
    }

    public function languageGroup(): BelongsTo
    {
        return $this->belongsTo(LanguageGroup::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
