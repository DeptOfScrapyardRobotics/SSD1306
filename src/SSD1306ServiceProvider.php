<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306;

use DeptOfScrapyardRobotics\Displays\SSD1306\Console\SSD1306MakeProfileCommand;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306CatalogIc;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306ConsoleCommand;
use DeptOfScrapyardRobotics\Displays\SSD1306\Sketches\SSD1306Smoke;
use Fabricate\Contracts\Sketches\SketchRegistry;
use Fabricate\NutsAndBolts\ServiceProvider;
use GeneralPurposeIO\Core\MagicAliases\Circuit;

class SSD1306ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(SSD1306MakeProfileCommand::class);
        $this->commands([
            SSD1306MakeProfileCommand::class,
        ]);
    }

    public function boot(): void
    {
        Circuit::addCircuit(SSD1306CatalogIc::SSD1306->value, SSD1306::class);
        Circuit::registerProfileCommand(
            SSD1306CatalogIc::SSD1306->value,
            SSD1306ConsoleCommand::MAKE_PROFILE->value,
        );

        $this->registerSketch();
    }

    protected function registerSketch(): void
    {
        if (! $this->container->bound(SketchRegistry::class)) {
            return;
        }

        /** @var SketchRegistry $registry */
        $registry = $this->container->make(SketchRegistry::class);

        if (! $registry->has('ssd1306-smoke')) {
            $registry->registerConvention('ssd1306-smoke', SSD1306Smoke::class);
        }
    }
}
