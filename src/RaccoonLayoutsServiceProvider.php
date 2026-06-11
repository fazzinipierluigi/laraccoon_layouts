<?php

namespace Fazzinipierluigi\LaraccoonLayouts;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RaccoonLayoutsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/raccoon_layouts.php', 'raccoon_layouts');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'raccoon-layouts');

        $this->registerRoutes();
        $this->registerDirectives();
        $this->registerPublishes();
    }

    private function registerRoutes(): void
    {
        Route::middleware(config('raccoon_layouts.middleware'))
            ->prefix(config('raccoon_layouts.route_prefix'))
            ->name('raccoon-layouts.')
            ->group(__DIR__ . '/../routes/api.php');
    }

    private function registerDirectives(): void
    {
        Blade::directive('raccoonLayoutsScripts', function () {
            return "<?php echo view('raccoon-layouts::scripts')->render(); ?>";
        });

        Blade::directive('raccoonLayoutsDropdown', function ($expression) {
            $expression = $expression ?: '[]';
            return "<?php echo view('raccoon-layouts::dropdown', ['params' => {$expression}])->render(); ?>";
        });
    }

    private function registerPublishes(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/raccoon_layouts.php' => config_path('raccoon_layouts.php'),
            ], 'raccoon-layouts-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'raccoon-layouts-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views/' => resource_path('views/vendor/raccoon-layouts'),
            ], 'raccoon-layouts-views');
        }
    }
}
