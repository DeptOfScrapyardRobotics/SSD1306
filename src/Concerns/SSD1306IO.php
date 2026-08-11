<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\Concerns;

trait SSD1306IO
{
    public function i2cCommand(int $register, array $command_data = []): int
    {
        $payload = [0x00, ...[$register, ...$command_data]];
        return $this->i2c->write($payload);
    }

    /**
     * @param  array<int, int>|string  $data
     */
    public function i2cData(array|string $data = []): void
    {
        if (is_string($data)) {
            $length = strlen($data);
            $offset = 0;

            while ($offset < $length) {
                $chunk = substr($data, $offset, $this->max_packet_size);
                $bytes = array_values(unpack('C*', $chunk) ?: []);
                $this->i2c->write([0x40, ...$bytes]);
                $offset += $this->max_packet_size;
            }

            return;
        }

        foreach (array_chunk($data, $this->max_packet_size) as $chunk) {
            $this->i2c->write([0x40, ...$chunk]);
        }
    }

    public function spiCommand(int $register, array $command_data = []): int
    {
        $this->dc->low();
        $payload = [$register, ...$command_data];
        return $this->spi->write($payload);
    }

    /**
     * @param  array<int, int>|string  $data
     */
    public function spiData(array|string $data = []): void
    {
        if (is_string($data)) {
            $length = strlen($data);
            $offset = 0;

            while ($offset < $length) {
                $this->dc->high();
                $this->spi->write(substr($data, $offset, $this->max_packet_size));
                $offset += $this->max_packet_size;
            }

            return;
        }

        foreach (array_chunk($data, $this->max_packet_size) as $chunk) {
            $this->dc->high();
            $this->spi->write($chunk);
        }
    }
}