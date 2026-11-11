<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\LanguageGroup;


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

    public function languageGroup()
    {
        return $this->belongsTo(LanguageGroup::class);
    }


    public function questions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Question::class);
    }
}
