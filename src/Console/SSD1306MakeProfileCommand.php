<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\Console;

use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306CatalogIc;
use Fabricate\Console\Command;
use GeneralPurposeIO\Circuits\CircuitRegistry;
use GeneralPurposeIO\Circuits\Console\Concerns\ScaffoldsCircuitProfiles;
use GeneralPurposeIO\Circuits\Support\CircuitAttributeInspector;
use GeneralPurposeIO\Contracts\Circuits\CircuitException;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'ssd1306:make-profile')]
class SSD1306MakeProfileCommand extends Command
{
    use ScaffoldsCircuitProfiles;

    protected ?string $signature = 'ssd1306:make-profile
                    {ic? : Catalog IC slug (ssd1306)}
                    {name? : Profile key to write into config/circuits.php}
                    {--protocol= : Protocol option label or factory name when non-interactive}';

    protected string $description = 'Scaffold a circuits.php profile for an SSD1306 OLED';

    public function handle(CircuitRegistry $registry): int
    {
        $available = array_values(array_filter(
            SSD1306CatalogIc::slugs(),
            static fn (string $ic): bool => isset($registry->listCircuits()[$ic]),
        ));

        if ($available === []) {
            $this->components->error('No SSD1306 ICs are registered.');

            return self::FAILURE;
        }

        $ic = $this->argument('ic');
        if (is_null($ic) || $ic === '') {
            $ic = count($available) === 1 ? $available[0] : $this->choice('Which SSD1306 IC?', $available);
        }

        $ic = (string) $ic;

        if (is_null(SSD1306CatalogIc::tryFrom($ic))) {
            $this->components->error("IC [{$ic}] is not SSD1306.");

            return self::FAILURE;
        }

        try {
            $options = CircuitAttributeInspector::protocolOptions($registry->resolveClass($ic));
        } catch (CircuitException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $selected = $this->resolveProtocolOption($options);
        if (is_null($selected)) {
            return self::FAILURE;
        }

        $name = $this->argument('name');
        if (is_null($name) || $name === '') {
            $name = $this->ask('Profile name', $ic);
        }

        return $this->writePromptedProfile($ic, (string) $name, $selected);
    }
}
