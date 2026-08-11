<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\Sketches;

use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306CatalogIc;
use Fabricate\Contracts\Sketches\Attributes\Sketch as SketchAttribute;
use Fabricate\Contracts\Sketches\SketchLoopResult;
use Fabricate\Sketches\Sketch;
use GeneralPurposeIO\Circuits\Types\DisplayPanel;
use GeneralPurposeIO\Contracts\Circuits\IntegratedCircuit;
use GeneralPurposeIO\Core\MagicAliases\Circuit;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Throwable;

#[SketchAttribute('ssd1306-smoke')]
class SSD1306Smoke extends Sketch
{
    protected string $description = 'Smoke-test a provisioned SSD1306 profile (Ctrl-C to end)';

    protected ?IntegratedCircuit $panel = null;

    protected ?string $profileName = null;

    protected bool $stopRequested = false;

    protected bool $announced = false;

    protected bool $inverted = false;

    protected int $lastPaintNs = 0;

    public function configureCommand(Command $command): void
    {
        $command->addOption(
            'profile',
            null,
            InputOption::VALUE_OPTIONAL,
            'circuits.php profile name (ic must be ssd1306)',
        );
    }

    public function boot(): void
    {
        if (extension_loaded('pcntl')) {
            pcntl_async_signals(true);
            $stop = function (): void {
                $this->stopRequested = true;
            };
            pcntl_signal(SIGINT, $stop);
            pcntl_signal(SIGTERM, $stop);
        }

        $profiles = $this->ssd1306Profiles();
        if ($profiles === []) {
            $this->error('No SSD1306 profiles in config/circuits.php. Run: php workshop ssd1306:make-profile');

            return;
        }

        $requested = $this->option('profile');
        if (is_string($requested) && $requested !== '') {
            if (! isset($profiles[$requested])) {
                $this->error("Profile [{$requested}] is missing or not an SSD1306 ic.");

                return;
            }
            $this->profileName = $requested;
        } elseif (count($profiles) === 1) {
            $this->profileName = array_key_first($profiles);
        } else {
            $this->profileName = $this->choice('Which SSD1306 profile?', array_keys($profiles));
        }

        try {
            $this->panel = Circuit::profile($this->profileName);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            $this->panel = null;
        }
    }

    public function loop(): SketchLoopResult
    {
        if ($this->stopRequested) {
            $this->info('SSD1306 smoke stopped.');

            return SketchLoopResult::STOP;
        }

        if (is_null($this->panel) || is_null($this->profileName)) {
            return SketchLoopResult::STOP;
        }

        if (! $this->announced) {
            $this->info("SSD1306 smoke via Circuit::profile('{$this->profileName}')");
            $this->line('  Toggling invert — Ctrl-C to end.');
            $this->announced = true;
        }

        $now = hrtime(true);
        if ($this->lastPaintNs !== 0 && ($now - $this->lastPaintNs) < 750_000_000) {
            usleep(20_000);

            return SketchLoopResult::CONTINUE;
        }

        $this->inverted = ! $this->inverted;

        try {
            if (method_exists($this->panel, 'setInvertDisplay')) {
                $this->panel->setInvertDisplay($this->inverted);
            }
            $this->line('  invert '.($this->inverted ? 'on' : 'off'));
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return SketchLoopResult::STOP;
        }

        $this->lastPaintNs = $now;

        return SketchLoopResult::CONTINUE;
    }

    public function shutdown(): void
    {
        if ($this->panel instanceof DisplayPanel || $this->panel instanceof IntegratedCircuit) {
            try {
                $this->panel->close();
            } catch (Throwable) {
                //
            }
        }
        $this->panel = null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function ssd1306Profiles(): array
    {
        $all = config('circuits', []);
        if (! is_array($all)) {
            return [];
        }

        $matched = [];
        foreach ($all as $name => $recipe) {
            if (! is_string($name) || ! is_array($recipe)) {
                continue;
            }
            $ic = $recipe['ic'] ?? null;
            if (is_string($ic) && ! is_null(SSD1306CatalogIc::tryFrom($ic))) {
                $matched[$name] = $recipe;
            }
        }

        return $matched;
    }
}
