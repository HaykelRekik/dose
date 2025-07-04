<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class EssentialsServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Model::shouldBeStrict( ! $this->app->isProduction() && ! $this->app->runningUnitTests());

        Model::automaticallyEagerLoadRelationships();

    }
}
