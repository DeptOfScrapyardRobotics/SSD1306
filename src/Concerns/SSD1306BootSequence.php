<?php

namespace ScrapyardIO\Displays\Monochrome\SSD1306\Concerns;

use ScrapyardIO\Displays\Monochrome\SSD1306\Enums\SSD1306Command;
use ScrapyardIO\Displays\Monochrome\SSD1306\Enums\SSD1306Contrast;
use ScrapyardIO\Displays\Monochrome\SSD1306\Enums\SSD1306Rotation;
use ScrapyardIO\Displays\Monochrome\SSD1306\Enums\SSD1306Precharge;
use ScrapyardIO\Displays\Monochrome\SSD1306\Enums\SSD1306ChargePump;
use ScrapyardIO\Displays\Monochrome\SSD1306\Enums\SSD1306MemoryMode;
use ScrapyardIO\Displays\Monochrome\SSD1306\Enums\SSD1306ComPinConfig;
use ScrapyardIO\Displays\Monochrome\SSD1306\Enums\SSD1306DisplayClock;
use ScrapyardIO\Displays\Monochrome\SSD1306\Enums\SSD1306VoltageCommonHigh;

trait SSD1306BootSequence
{
    protected int $start_line = 0;
    protected int $multiplex_ratio;
    protected int $display_offset = 0;
    protected bool $charge_pump = true;
    protected bool $powered_by_device = true;
    protected SSD1306Contrast $contrast  = SSD1306Contrast::HIGH;
    protected SSD1306Rotation $rotation  = SSD1306Rotation::INVERTED_PORTRAIT;
    protected SSD1306DisplayClock $clock_rate = SSD1306DisplayClock::DEFAULT;
    protected SSD1306MemoryMode $scan_direction = SSD1306MemoryMode::HORIZONTAL;
    protected SSD1306VoltageCommonHigh $v_com_h = SSD1306VoltageCommonHigh::LEVEL_077_ALT;

    abstract public function wait(int $ms): void;
    abstract public function sendCommand(array $bytes): void;

    protected function turnDisplayOff(): void
    {
        $this->sendCommand([SSD1306Command::DISPLAY_OFF->value]);
    }

    protected function setDisplayClock(): void
    {
        $this->sendCommand([
            SSD1306Command::SET_DISPLAY_CLOCK->value,
            $this->clock_rate->value
        ]);
    }

    protected function setMultiplexRatio(): void
    {
        $this->sendCommand([
            SSD1306Command::SET_MULTIPLEX_RATIO->value,
            $this->multiplex_ratio
        ]);
    }

    protected function setDisplayOffset(): void
    {
        $this->sendCommand([
            SSD1306Command::SET_DISPLAY_OFFSET->value,
            $this->display_offset
        ]);
    }

    protected function setStartLine(): void
    {
        $this->sendCommand([
            SSD1306Command::DISPLAY_START_LINE->value | $this->start_line
        ]);
    }

    protected function setChargePump(): void
    {
        $this->sendCommand([
            SSD1306Command::CHARGE_PUMP_SETTING->value,
            SSD1306ChargePump::enabled($this->charge_pump)
        ]);
    }

    protected function setMemoryMode(): void
    {
        $this->sendCommand([
            SSD1306Command::SET_MEMORY_MODE->value,
            $this->scan_direction->value
        ]);
    }

    protected function setSegmentRemap(): void
    {
        $rot = $this->rotation->toRemap();
        $this->sendCommand([$rot[0]]);
    }

    protected function setComScanRemap(): void
    {
        $rot = $this->rotation->toRemap();
        $this->sendCommand([$rot[1]]);
    }

    protected function setCommPins(): void
    {
        $this->sendCommand([
            SSD1306Command::SET_COM_PINS->value,
            SSD1306ComPinConfig::forHeight($this->height)->value
        ]);
    }

    protected function setContrast(): void
    {
        $this->sendCommand([
            SSD1306Command::SET_CONTRAST->value,
            $this->contrast->value
        ]);
    }

    protected function setPrechargePeriod(): void
    {
        $period = $this->powered_by_device ? SSD1306Precharge::RECOMMENDED : SSD1306Precharge::DEFAULT;
        $this->sendCommand([
            SSD1306Command::SET_PRECHARGE_PERIOD->value,
            $period->value
        ]);
    }

    protected function setVoltageCommonHigh(): void
    {
        $this->sendCommand([
            SSD1306Command::SET_VOLTAGE_COMMON_HIGH->value,
            $this->v_com_h->value
        ]);
    }

    protected function displayResumeState(): void
    {
        $this->sendCommand([SSD1306Command::ENTIRE_DISPLAY_ON_RESUME->value]);
    }

    protected function setNormalDisplayMode(): void
    {
        $this->sendCommand([SSD1306Command::NORMAL_DISPLAY->value]);
    }

    protected function unsetScroll(): void
    {
        $this->sendCommand([SSD1306Command::DEACTIVATE_SCROLL->value]);
    }

    protected function turnDisplayOn(): void
    {
        $this->sendCommand([SSD1306Command::DISPLAY_ON->value]);
    }

    public function display(): static
    {
        $this->setAddressWindow($this->min_y, $this->max_y, $this->min_x, $this->max_x);

        if($this->scan_direction == SSD1306MemoryMode::VERTICAL)
        {
            $payload = $this->wire->toColumns($this->width);
        }
        else $payload = $this->wire->toRows();

        foreach(array_chunk($payload, $this->max_packet_size) as $chunk)
        {
            $this->sendData($chunk);
        }

        return $this;
    }

    protected function setAddressWindow(int $y_min, int $y_max, int $x_min, int $x_max): void
    {
        $this->setYRange($y_min, $y_max);
        $this->setXRange($x_min, $x_max);
    }

    public function setYRange(int $min, int $max): void
    {
        $this->sendCommand([
            SSD1306Command::SET_PAGE_RANGE->value, $min, $max,
        ]);
    }

    public function setXRange(int $min, int $max): void
    {
        $this->sendCommand([
            SSD1306Command::SET_COLUMN_RANGE->value, $min, $max,
        ]);
    }
}
