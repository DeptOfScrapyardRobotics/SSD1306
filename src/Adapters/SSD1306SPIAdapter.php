<?php

namespace ScrapyardIO\Displays\Monochrome\SSD1306\Adapters;

use ScrapyardIO\Displays\Adapters\MonochromeDisplayAdapter;
use ScrapyardIO\Displays\Monochrome\SSD1306\Concerns\SSD1306SPIChip;
use ScrapyardIO\Displays\Monochrome\SSD1306\Concerns\SSD1306BootSequence;

class SSD1306SPIAdapter extends MonochromeDisplayAdapter
{
    use SSD1306SPIChip;
    use SSD1306BootSequence;

    public function bus(int $bus):static
    {
        $this->spi_ssd1306_bus($bus);
        return $this;
    }

    public function chipSelect(int $cs):static
    {
        $this->spi_ssd1306_chip_select($cs);
        return $this;
    }

    public function dcPin(int $chip, int $line): static
    {
        $this->dc_chip($chip);
        $this->dc_line($line);
        $this->dc_gpio();

        return $this;
    }

    public function rstPin(int $chip, int $line): static
    {
        $this->rst_chip($chip);
        $this->rst_line($line);
        $this->rst_gpio();

        return $this;
    }

    public function boot(): static
    {
        $this->ssd1306_spi();

        $this->max_packet_size = intVal(($this->width * $this->height) / 8);
        $this->multiplex_ratio = $this->height - 1;

        $this->resetSequence();
        $this->turnDisplayOff();
        $this->setDisplayClock();
        $this->setMultiplexRatio();
        $this->setDisplayOffset();
        $this->setStartLine();
        $this->setChargePump();
        $this->setMemoryMode();
        $this->setSegmentRemap();
        $this->setComScanRemap();
        $this->setCommPins();
        $this->setContrast();
        $this->setPrechargePeriod();
        $this->setVoltageCommonHigh();
        $this->displayResumeState();
        $this->setNormalDisplayMode();
        $this->unsetScroll();
        $this->turnDisplayOn();

        return $this;
    }


}
