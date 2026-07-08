<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306;

use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306OpCode;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306StartLineCommand;

trait SSD1306IO
{
    public function i2cCommand(int $register, array $command_data = []): int
    {
        $payload = [0x00, ...[$register, ...$command_data]];
        return $this->i2c->write($payload);
    }

    public function i2cData(array $data = []): void
    {
        foreach (array_chunk($data, $this->max_packet_size) as $chunk) {
            $payload = [0x40, ...$chunk];
            $this->i2c->write($payload);
        }
    }

    public function spiCommand(int $register, array $command_data = []): int
    {
        $this->dc->low();
        $payload = [$register, ...$command_data];
        return $this->spi->write($payload);
    }

    public function spiData(array $data = []): void
    {
        foreach (array_chunk($data, $this->max_packet_size) as $chunk) {
            $this->dc->high();
            $this->spi->write($chunk);
        }
    }
}
