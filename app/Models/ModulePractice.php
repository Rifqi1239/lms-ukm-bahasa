<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModulePractice extends Model
{
    protected $fillable = [
        'module_id',
        'type',
        'start_at',
        'deadline_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'deadline_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function isAvailable(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->type === 'independent') {
            return true;
        }

        return $this->type === 'assessment'
            && $this->start_at !== null
            && $this->deadline_at !== null
            && now()->greaterThanOrEqualTo($this->start_at)
            && now()->lessThan($this->deadline_at);
    }
}
