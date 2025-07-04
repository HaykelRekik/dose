<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductOptionGroupType;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOptionGroup extends Model
{
    use HasTranslations;

    protected $fillable = [
        'product_id',
        'name_en',
        'name_ar',
        'type',
        'is_required',
    ];

    protected array $translatable = ['name'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductOption::class);
    }

    protected function casts(): array
    {
        return [
            'type' => ProductOptionGroupType::class,
            'is_required' => 'boolean',
        ];
    }
}
