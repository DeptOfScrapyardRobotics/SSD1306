<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\Transports;

use GeneralPurposeIO\Contracts\Digital\DigitalOutTransport;
use GeneralPurposeIO\Contracts\SPI\SPITransport;

class SSD1306SPITransport extends SSD1306DataTransport
{
    public function __construct(
        protected SPITransport $transport,
        protected DigitalOutTransport $dc,
        protected DigitalOutTransport $rst,
        int $max_packet_size = 1024
    ) {
        parent::__construct($max_packet_size);
    }

    protected function sendCommand(int $register, array $command_data = []): int
    {
        $this->dc->low();
        $payload = [$register, ...$command_data];
        return $this->transport->write($payload);
    }

    protected function sendData(array|string $data = []): void
    {
        if (is_string($data)) {
            $length = strlen($data);
            $offset = 0;

            while ($offset < $length) {
                $this->dc->high();
                $this->transport->write(substr($data, $offset, $this->max_packet_size));
                $offset += $this->max_packet_size;
            }

            return;
        }

        foreach (array_chunk($data, $this->max_packet_size) as $chunk) {
            $this->dc->high();
            $this->transport->write($chunk);
        }
    }

    public function reset(): void
    {
        $this->rst->high();
        usleep(3000);

        $this->rst->low();
        usleep(3000);

        $this->rst->high();
        usleep(3000);
    }

    protected function closeMain(): void
    {
        $this->dc->close();
        $this->rst->close();
    }
}