<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use App\Traits\IsActivable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasTranslations, IsActivable;

    protected $fillable = [
        'name_en',
        'name_ar',
        'address_ar',
        'address_en',
        'phone',
        'email',
        'opening_hours',
        'is_active',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(User::class, 'branch_id');
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'opening_hours' => 'array',
        ];
    }
}
