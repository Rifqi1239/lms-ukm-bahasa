<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Module extends Model
{
    protected $fillable = [
        'language_group_id',
        'title',
        'description',
        'cover_image',
        'pdf_file',
        'has_exercise',
        'question_count',
    ];

    public function languageGroup(): BelongsTo
    {
        return $this->belongsTo(LanguageGroup::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function practices(): HasMany
    {
        return $this->hasMany(ModulePractice::class);
    }
}
