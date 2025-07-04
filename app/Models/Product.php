<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use App\Traits\IsActivable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, HasTranslations, IsActivable;

    protected array $translatable = ['name', 'description'];

    protected $fillable = [
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'price',
        'estimated_preparation_time',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $appends = ['image_path'];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function optionGroups(): HasMany
    {
        return $this->hasMany(ProductOptionGroup::class);
    }

    protected function imagePath(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => Storage::disk('public')->url($this->attributes['image_url'])
        );
    }
}
