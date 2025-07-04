<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProductOptionGroupType: string implements HasLabel
{
    case SINGLE_SELECT = 'single';
    case MULTI_SELECT = 'multi';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SINGLE_SELECT => __('Single Select'),
            self::MULTI_SELECT => __('Multi Select'),
        };
    }
}
