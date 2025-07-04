<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ProductOptionGroupType: string implements HasLabel, HasIcon

{
    case SINGLE_SELECT = 'single';
    case MULTI_SELECT = 'multi';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SINGLE_SELECT => __('Single'),
            self::MULTI_SELECT => __('Multiple'),
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::SINGLE_SELECT => 'phosphor-check',
            self::MULTI_SELECT => 'phosphor-checks',
        };
    }
}
