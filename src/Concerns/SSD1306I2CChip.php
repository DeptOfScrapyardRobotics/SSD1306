<?php

namespace ScrapyardIO\Displays\Monochrome\SSD1306\Concerns;

use ScrapyardIO\Transports\I2CTransport;

trait SSD1306I2CChip
{
    protected ?I2CTransport $ssd1306_i2c = null;
    protected int $ssd1306_i2c_bus = 1;
    protected int $ssd1306_i2c_address = 0;
    protected int $max_packet_size = 0;

    protected function i2c_ssd1306_bus(?int $bus = null): int
    {
        if($bus)
        {
            $this->ssd1306_i2c_bus = $bus;
        }
        return $this->ssd1306_i2c_bus;
    }

    protected function i2c_ssd1306_address(?int $address = null): int
    {
        if($address)
        {
            $this->ssd1306_i2c_address = $address;
        }
        return $this->ssd1306_i2c_address;
    }

    protected function ssd1306_i2c(): ?I2CTransport
    {
        if(empty($this->ssd1306_i2c))
        {
            $this->ssd1306_i2c = new I2CTransport(
                $this->i2c_ssd1306_address(),
                $this->i2c_ssd1306_bus()
            );
        }

        return $this->ssd1306_i2c;
    }

    public function sendData(array $bytes): void
    {
        $payload = [0x40, ...$bytes];
        $this->ssd1306_i2c()->send($payload);
    }

    public function sendCommand(array $bytes): void
    {
        $payload = [0x00, ...$bytes];
        $this->ssd1306_i2c()->send($payload);
    }
}
