<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Adapters;

use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Enums\SSD1306OpCode;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Enums\SSD1306StartLineCommand;
use Waveforms\Carriers\I2C\I2CDevice;
use Waveforms\Carriers\SPI\SPIDevice;

abstract class SSD1306DataCarrier
{
    public function __construct(
        protected I2CDevice|SPIDevice $carrier
    ) {}

    abstract public function data(array $data): void;

    abstract public function command(SSD1306OpCode|SSD1306StartLineCommand $register_hex, array $command_data = []): void;

    public function reset(): void {}
}
