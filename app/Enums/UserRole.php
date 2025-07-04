<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasColor, HasIcon, HasLabel
{
    case ADMIN = 'admin';
    case STORE = 'store';
    case USER = 'user';

    public function getLabel(): ?string
    {
        return __($this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ADMIN => Color::Rose,
            self::STORE => Color::Blue,
            self::USER => Color::Purple,
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::ADMIN => 'phosphor-shield-star-duotone',
            self::STORE => 'phosphor-storefront-duotone',
            self::USER => 'phosphor-user-duotone',
        };
    }
}
