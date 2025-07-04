<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'base_price',
        'image_url',
        'is_active',
    ];

    protected array $translatable = [
        'name',
        'description',
    ];

    protected $appends = ['image_path'];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected function imagePath(): Attribute
    {
        return Attribute::make(get: fn (): ?string => $this->attributes['image_url'] ? Storage::disk('public')->url($this->attributes['image_url']) : null);
    }
    //
    //    public function optionGroups(): HasMany
    //    {
    //        return $this->hasMany(ProductOptionGroup::class);
    //    }

}
