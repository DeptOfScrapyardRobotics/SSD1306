<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\Providers;

use Fabricate\NutsAndBolts\ServiceProvider;
use Fabricate\NutsAndBolts\MagicAliases\Circuit;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306;

class SSD1306ServiceProvider extends ServiceProvider
{
    public function register(): void {}
    public function boot(): void {
        Circuit::addCircuit('ssd1306', SSD1306::class);
    }
}