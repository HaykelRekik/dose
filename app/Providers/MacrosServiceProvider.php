<?php

declare(strict_types=1);

namespace App\Providers;

use Filament\Forms\Components\Field;
use Filament\Tables\Columns\Column;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;

class MacrosServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $saudiRiyalMacro = function (string $position = 'suffix', array $viewParameters = []) {

            /** @var Field|Column $this */
            $defaults = [
                'size' => 'lg',
                'color' => 'gray-500',
            ];

            $html = new HtmlString(
                view('filament.components.saudi-riyal', array_merge($defaults, $viewParameters))
            );

            match ($position) {
                'prefix' => $this->prefix($html),
                default => $this->suffix($html),
            };

            return $this;

        };

        Field::macro('saudiRiyal', $saudiRiyalMacro);
        Column::macro('saudiRiyal', $saudiRiyalMacro);
    }
}
