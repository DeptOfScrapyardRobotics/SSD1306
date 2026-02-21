<?php

namespace ScrapyardIO\Libraries\Displays\Drivers\SSD1306;

use ScrapyardIO\Libraries\Displays\Displays\MonochromeDisplay;
use ScrapyardIO\Libraries\Displays\Contracts\AddressWindowSetting;
use ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Concerns\SSD1306API;
use ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Exceptions\SSD1306Exception;
use ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Enums\Properties\SSD1306DataClock;
use ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Enums\Properties\SSD1306OscillatorFrequency;


class SSD1306 extends MonochromeDisplay implements AddressWindowSetting
{
    use SSD1306API;
    protected int $width = 128;
    protected int $height = 64;
    protected int $contrast = 128;
    protected int $start_line = 0;
    protected int $display_offset = 0;
    protected bool $invert_display = true;
    protected bool $map_line_0_to_127 = true;
    protected bool $reverse_line_scan_direction = true;
    protected SSD1306DataClock $data_clock = SSD1306DataClock::DIVIDE_BY_9;
    protected SSD1306OscillatorFrequency $osc_freq = SSD1306OscillatorFrequency::FREQ_0;

    protected array $display_clock_register = [
        'OSC3' => true,
        'OSC2' => false,
        'OSC1' => false,
        'OSC0' => false,
        'DCLK3' => false,
        'DCLK2' => false,
        'DCLK1' => false,
        'DCLK0' => false
    ];

    public function small(): void
    {
        $this->width = 128;
        $this->height = 32;
    }

    public function normal(): void
    {
        $this->width = 128;
        $this->height = 64;
    }

    public function displayOffset(int $value): void
    {
        $this->display_offset = $value;
    }

    public function startLine(int $value): void
    {
        $this->start_line = $value;
    }

    public function invertLines(): void
    {
        $this->map_line_0_to_127 = true;
    }

    public function normalLines(): void
    {
        $this->map_line_0_to_127 = false;
    }

    public function invertDisplay(): void
    {
        $this->invert_display = true;
    }

    public function normalDisplay(): void
    {
        $this->invert_display = false;
    }

    public function invertLineScan(): void
    {
        $this->reverse_line_scan_direction = true;
    }

    public function normalLineScan(): void
    {
        $this->reverse_line_scan_direction = false;
    }

    public function contrast(int $value): void
    {
        $this->contrast = $value;
    }

    public function dataClock(SSD1306DataClock $data_clock): void
    {
        $this->data_clock = $data_clock;
        $vals = $this->data_clock->getFlags();
        $this->display_clock_register['DCLK3'] = $vals['DCLK3'];
        $this->display_clock_register['DCLK2'] = $vals['DCLK2'];
        $this->display_clock_register['DCLK1'] = $vals['DCLK1'];
        $this->display_clock_register['DCLK0'] = $vals['DCLK0'];
    }

    public function oscillationFrequency(SSD1306OscillatorFrequency $frequency): void
    {
        $this->osc_freq = $frequency;
        $vals = $this->osc_freq->getFlags();
        $this->display_clock_register['OSC3'] = $vals['OSC3'];
        $this->display_clock_register['OSC2'] = $vals['OSC2'];
        $this->display_clock_register['OSC1'] = $vals['OSC1'];
        $this->display_clock_register['OSC0'] = $vals['OSC0'];
    }

    /**
     * @return $this
     * @throws SSD1306Exception
     */
    public function start(): static
    {
        if($this->io_control->is() == 'spi') $this->resetDisplay();

        $this->setMuxRatio($this->height -1);
        $this->setDisplayVerticalOffset($this->display_offset);
        $this->setDisplayStartLine($this->start_line);
        $this->setSegmentRemap($this->map_line_0_to_127);
        $this->setCOMOutputScanDirection($this->reverse_line_scan_direction);
        $this->setCOMPinsHardwareConfiguration();
        $this->setContrast($this->contrast);
        //$this->writeDisplayShowWhateverWasLastInRam();
        $this->writeDisplayFlushWhateverWasLastInRam();
        $this->invert_display ? $this->setInvertDisplay() : $this->setNormalDisplay();
        $this->setDataClockOscillationFrequency();
        $this->setChargePumpRegulator();
        $this->turnDisplayOn();

        return $this;
    }

    protected function resetDisplay(): void
    {
        $this->rstHigh();
        $this->wait(3);

        $this->rstLow();
        $this->wait(3);

        $this->rstHigh();
        $this->wait(3);
    }

    public function setAddressWindow(int $x_min, int $x_max, int $y_min, int $y_max): void
    {
        $page_min = (int) floor($y_min / 8);
        $page_max = (int) floor($y_max / 8);
        $this->setYRange($page_min, $page_max);
        $this->setXRange($x_min, $x_max);
    }

}
