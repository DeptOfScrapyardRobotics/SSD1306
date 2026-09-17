<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\Transports;

use GeneralPurposeIO\Contracts\I2C\I2CTransport;

class SSD1306I2CTransport extends SSD1306DataTransport
{
    public function __construct(
        protected I2CTransport $transport,
        int $max_packet_size = 1024
    ) {
        parent::__construct($max_packet_size);
    }

    protected function sendCommand(int $register, array $command_data = []): int
    {
        $payload = [0x00, ...[$register, ...$command_data]];
        return $this->transport->write($payload);
    }

    protected function sendData(array|string $data = []): void
    {
        if (is_string($data)) {
            $length = strlen($data);
            $offset = 0;

            while ($offset < $length) {
                $chunk = substr($data, $offset, $this->max_packet_size);
                $bytes = array_values(unpack('C*', $chunk) ?: []);
                $this->transport->write([0x40, ...$bytes]);
                $offset += $this->max_packet_size;
            }

            return;
        }

        foreach (array_chunk($data, $this->max_packet_size) as $chunk) {
            $this->transport->write([0x40, ...$chunk]);
        }
    }

    protected function closeMain(): void
    {

    }
}