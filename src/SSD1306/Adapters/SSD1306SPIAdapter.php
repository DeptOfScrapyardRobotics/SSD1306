<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Adapters;

use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Enums\SSD1306OpCode;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Enums\SSD1306StartLineCommand;
use Waveforms\Carriers\GPIO\GPIOBus;
use Waveforms\Carriers\SPI\SPIDevice;

class SSD1306SPIAdapter extends SSD1306DataCarrier
{
    public function __construct(
        SPIDevice $carrier,
        protected GPIOBus $gpio,
        protected int $max_packet_size
    ) {
        parent::__construct($carrier);
    }

    public function reset(): void
    {
        $this->gpio->rst()->high();
        usleep(3000);

        $this->gpio->rst()->low();
        usleep(3000);

        $this->gpio->rst()->high();
        usleep(3000);
    }

    public function data(array $data): void
    {
        foreach (array_chunk($data, $this->max_packet_size) as $chunk) {
            $this->gpio->dc()->high();
            $this->carrier->write($chunk);
        }
    }

    public function command(SSD1306OpCode|SSD1306StartLineCommand $register_hex, array $command_data = []): void
    {
        $this->gpio->dc()->low();
        $payload = [$register_hex->value, ...$command_data];
        $this->carrier->write($payload);
    }
}
