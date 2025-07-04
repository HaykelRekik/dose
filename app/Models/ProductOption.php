<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use App\Traits\IsActivable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOption extends Model
{
    use HasTranslations, IsActivable;

    protected $fillable = [
        'product_option_group_id',
        'name_en',
        'name_ar',
        'extra_price',
        'is_active',
    ];

    protected array $translatable = ['name'];

    protected $casts = [
        'extra_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ProductOptionGroup::class, 'product_option_group_id');
    }
}
