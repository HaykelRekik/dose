<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\CategoryObserver;
use App\Traits\HasTranslations;
use App\Traits\IsActivable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ObservedBy(CategoryObserver::class)]
class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory, HasTranslations , IsActivable;

    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
        'position',
        'is_active',
    ];

    protected array $translatable = ['name'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}
