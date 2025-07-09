<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case CASH = 'cash';
    case CREDIT_CARD = 'credit card';

    public function getLabel(): ?string
    {
        return __($this->value);
    }
}
