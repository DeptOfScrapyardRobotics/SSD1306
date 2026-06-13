<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Adapters;

use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Enums\SSD1306OpCode;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Enums\SSD1306StartLineCommand;
use Waveforms\Carriers\I2C\I2CDevice;

class SSD1306I2CAdapter extends SSD1306DataCarrier
{
    public function __construct(
        I2CDevice $carrier,
        protected int $max_packet_size
    ) {
        parent::__construct($carrier);
    }

    public function data(array $data): void
    {
        foreach (array_chunk($data, $this->max_packet_size) as $chunk) {
            $payload = [0x40, ...$chunk];
            $this->carrier->write($payload);
        }
    }

    public function command(SSD1306OpCode|SSD1306StartLineCommand $register_hex, array $command_data = []): void
    {
        $payload = [0x00, ...[$register_hex->value, ...$command_data]];
        $this->carrier->write($payload);
    }
}
