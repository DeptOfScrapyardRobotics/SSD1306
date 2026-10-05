<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\Providers;

use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306CatalogIc;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306;
use Voyager\NutsAndBolts\ServiceProvider;

/**
 * The panel's wiring config lives under the circuits tree: config('circuits.ssd1306'),
 * published to config/circuits/ssd1306.php, which the config loader keys the same way.
 */
class SSD1306ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__, 2).'/config/ssd1306.php', 'circuits.ssd1306');
    }

    public function boot(): void
    {
        $this->publishes([
            dirname(__DIR__, 2).'/config/ssd1306.php' => $this->app->configPath('circuits/ssd1306.php'),
        ], 'ssd1306-config');

        // With the GPIO catalog installed, the panel is conjurable by slug: app('circuit')->conjure('ssd1306').
        if ($this->app->isBound('circuit')) {
            foreach (SSD1306CatalogIc::cases() as $ic) {
                $this->app->make('circuit')->addCircuit($ic->value, SSD1306::class);
            }
        }
    }
}
