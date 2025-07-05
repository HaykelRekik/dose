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
    case EMPLOYEE = 'employee';
    case CUSTOMER = 'customer';

    public function getLabel(): ?string
    {
        return __($this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ADMIN => Color::Rose,
            self::EMPLOYEE => Color::Blue,
            self::CUSTOMER => Color::Slate,
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::ADMIN => 'phosphor-shield-star-duotone',
            self::EMPLOYEE => 'phosphor-storefront-duotone',
            self::CUSTOMER => 'phosphor-user-duotone',
        };
    }
}
