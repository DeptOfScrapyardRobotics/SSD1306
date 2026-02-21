<?php

namespace ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Concerns;

use ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Enums\CommandRegister\SSD1306AddressingCommand;
use ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Enums\CommandRegister\SSD1306FundamentalCommand;
use ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Enums\CommandRegister\SSD1306HWConfigCommand;
use ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Enums\CommandRegister\SSD1306StartLineCommand;
use ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Enums\CommandRegister\SSD1306TimingCommand;
use ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Exceptions\SSD1306Exception;

trait SSD1306WriteRegisters
{
    protected function writeMuxRatio(int $value): bool
    {
        return $this->command(SSD1306HWConfigCommand::SET_MULTIPLEX_RATIO->value, [$value]);
    }

    protected function writeDisplayOffset(int $value): bool
    {
        return $this->command(SSD1306HWConfigCommand::SET_DISPLAY_OFFSET->value, [$value]);
    }

    protected function writeDisplayStartLine(SSD1306StartLineCommand $value): bool
    {
        return $this->command($value->value);
    }

    /**
     * @param int $command
     * @return bool
     * @throws SSD1306Exception
     */
    protected function writeSegmentRemap(int $command): bool
    {
        if(($command != 0xA0) && ($command != 0xA1)) throw SSD1306Exception::invalidSegmentRemapAddress($command);
        return $this->command($command);
    }

    /**
     * @param int $command
     * @return bool
     * @throws SSD1306Exception
     */
    protected function writeCOMOutputScanDirection(int $command): bool
    {
        if(($command != 0xC0) && ($command != 0xC8)) throw SSD1306Exception::invalidCOMOutputScanDirection($command);
        return $this->command($command);
    }

    protected function writeCOMPinsHardwareConfiguration(int $value): bool
    {
        return $this->command(SSD1306HWConfigCommand::SET_COM_PINS->value, [$value]);
    }

    protected function writeContrast(int $value): bool
    {
        return $this->command(SSD1306FundamentalCommand::CONTRAST_CONTROL->value, [$value]);
    }

    protected function writeDisplayShowWhateverWasLastInRam(): bool
    {
        return $this->command(SSD1306FundamentalCommand::DISPLAY_ON_RESUME->value, [bitsbyte([0,0,1,0,0,1,0,1])]);
    }
    protected function writeDisplayFlushWhateverWasLastInRam(): bool
    {
        return $this->command(SSD1306FundamentalCommand::DISPLAY_ON_FLUSH->value, [bitsbyte([0,0,1,0,0,1,0,1])]);
    }

    protected function writeInvertDisplay(): bool
    {
        return $this->command(SSD1306FundamentalCommand::DISPLAY_INVERSION_CONTROL->value, [bitsbyte([0,1,1,0,0,1,0,1])]);
    }

    protected function writeNormalDisplay(): bool
    {
        return $this->command(SSD1306FundamentalCommand::NORMAL_DISPLAY_CONTROL->value, [bitsbyte([1,0,1,0,0,1,1,0])]);
    }

    protected function writeDataClockOscillationFrequency(int $value): bool
    {
        return $this->command(SSD1306TimingCommand::SET_DISPLAY_CLOCK_DIVIDE_RATIO_AND_OSC_FREQ->value, [$value]);
    }

    protected function writeChargePumpRegulator(bool $flag = true): bool
    {
        return $this->command(SSD1306HWConfigCommand::SET_CHARGE_PUMP->value, [$flag ? 0x14 : 0x10]);
    }

    protected function writeToggleDisplay(bool $flag = true): bool
    {
        return $this->command($flag
            ? SSD1306FundamentalCommand::TOGGLE_DISPLAY_ON->value
            : SSD1306FundamentalCommand::TOGGLE_DISPLAY_OFF->value
        );
    }

    protected function writePageRange(array $bytes): bool
    {
        return $this->command(SSD1306AddressingCommand::SET_PAGE_RANGE->value, $bytes);
    }

    protected function writeColumnRange(array $bytes): bool
    {
        return $this->command(SSD1306AddressingCommand::SET_COLUMN_RANGE->value, $bytes);
    }

    /**
     * Set memory address mode. 0x00 = horizontal, 0x01 = vertical, 0x02 = page.
     */
    protected function writeMemoryAddressMode(int $mode): bool
    {
        return $this->command(SSD1306AddressingCommand::SET_MEMORY_ADDRESS_MODE->value, [$mode]);
    }
}
