<?php

declare(strict_types=1);

namespace App\Traits;

trait HasTranslations
{
    public function getAttribute($key)
    {
        if (in_array($key, $this->translatable ?? [])) {
            $locale = app()->getLocale();
            $localeKey = "{$key}_{$locale}";
            $fallbackKey = "{$key}_" . config('app.fallback_locale');

            return $this->attributes[$localeKey]
                ?? $this->attributes[$fallbackKey]
                ?? parent::getAttribute($key);
        }

        return parent::getAttribute($key);
    }
}
