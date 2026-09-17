<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\Providers;

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
    }
}
