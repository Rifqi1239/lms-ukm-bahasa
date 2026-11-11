<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LanguageGroup extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function finalGradeSetting()
    {
        return $this->hasOne(FinalGradeSetting::class);
    }

    public function finalGradeRecords(): HasMany
    {
        return $this->hasMany(FinalGradeRecord::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class);
    }
}
