<?php

namespace ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Concerns;

use ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Exceptions\SSD1306Exception;
use ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Enums\Properties\SSD1306ComPinConfig;
use ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Enums\CommandRegister\SSD1306HWConfigCommand;
use ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Enums\CommandRegister\SSD1306StartLineCommand;

trait SSD1306API
{
    use SSD1306WriteRegisters;

    /**
     * @param int $value
     * @return void
     * @throws SSD1306Exception
     */
    public function setMuxRatio(int $value): void
    {
        if(($value < 16) || ($value > 63)) throw SSD1306Exception::invalidMuxRatio($value);
        $this->writeMuxRatio($value);
    }

    /**
     * @param int $value
     * @return void
     * @throws SSD1306Exception
     */
    public function setDisplayVerticalOffset(int $value): void
    {
        if(($value < 0) || ($value > 63)) throw SSD1306Exception::invalidMuxRatio($value);
        $this->writeDisplayOffset($value);
    }

    public function setDisplayStartLine(int $value): void
    {
        if(($value < 0) || ($value > 63)) throw SSD1306Exception::invalidMuxRatio($value);
        $this->writeDisplayStartLine(SSD1306StartLineCommand::fromInt($value));
    }

    /**
     * @param bool $flag
     * @return void
     * @throws SSD1306Exception
     */
    public function setSegmentRemap(bool $flag): void
    {
        $this->writeSegmentRemap($flag
             ? SSD1306HWConfigCommand::MAP_SEGMENT_0_TO_127->value
             : SSD1306HWConfigCommand::MAP_SEGMENT_0_TO_0->value
        );
    }

    /**
     * @param bool $flag
     * @return void
     * @throws SSD1306Exception
     */
    public function setCOMOutputScanDirection(bool $flag): void
    {
        $this->writeCOMOutputScanDirection($flag
            ? SSD1306HWConfigCommand::REMAPPED_ROW_SCANNING->value
            : SSD1306HWConfigCommand::NORMAL_ROW_SCANNING->value
        );
    }

    public function setCOMPinsHardwareConfiguration(): void
    {
        $config = SSD1306ComPinConfig::forHeight($this->height());
        $this->writeCOMPinsHardwareConfiguration($config->value);
    }

    public function setContrast(int $value): void
    {
        if(($value < 0) || ($value > 255)) throw SSD1306Exception::invalidContrast($value);
        $this->writeContrast($value);
    }

    public function setInvertDisplay(): void
    {
        $this->writeInvertDisplay();
    }

    public function setNormalDisplay(): void
    {
        $this->writeNormalDisplay();
    }

    public function setDataClockOscillationFrequency(): void
    {
        $this->writeDataClockOscillationFrequency(
            bitsbyte(array_values(array_reverse($this->display_clock_register)))
        );
    }

    public function setChargePumpRegulator(): void
    {
        $this->writeChargePumpRegulator();
    }

    public function turnDisplayOff(): void
    {
        $this->writeToggleDisplay(false);
    }

    public function turnDisplayOn(): void
    {
        $this->writeToggleDisplay();
    }

    public function setYRange(int $min, int $max): void
    {
        $this->writePageRange([$min, $max]);
    }

    public function setXRange(int $min, int $max): void
    {
        $this->writeColumnRange([$min, $max]);
    }
}
