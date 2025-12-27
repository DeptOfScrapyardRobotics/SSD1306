<?php

namespace ScrapyardIO\Displays\Monochrome\SSD1306\Adapters;

use ScrapyardIO\Displays\Adapters\MonochromeDisplayAdapter;
use ScrapyardIO\Displays\Monochrome\SSD1306\Concerns\SSD1306I2CChip;
use ScrapyardIO\Displays\Monochrome\SSD1306\Enums\SSD1306I2CAddress;
use ScrapyardIO\Displays\Monochrome\SSD1306\Concerns\SSD1306BootSequence;

class SSD1306I2CAdapter extends MonochromeDisplayAdapter
{
    use SSD1306I2CChip;
    use SSD1306BootSequence;

    public function bus(int $bus):static
    {
        $this->i2c_ssd1306_bus($bus);
        return $this;
    }

    public function address(SSD1306I2CAddress $address):static
    {
        $this->i2c_ssd1306_address($address->value);
        return $this;
    }

    public function boot(): static
    {
        $this->ssd1306_i2c();

        $this->max_packet_size = intVal(($this->width * $this->height) / 8);
        $this->multiplex_ratio = $this->height - 1;

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
