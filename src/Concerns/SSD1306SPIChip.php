<?php

namespace ScrapyardIO\Displays\Monochrome\SSD1306\Concerns;

use ScrapyardIO\Transports\SPITransport;
use ScrapyardIO\Transports\Concerns\ResetPin;
use ScrapyardIO\Transports\Concerns\DataCommandPin;

trait SSD1306SPIChip
{
    use DataCommandPin, ResetPin;

    protected ?SPITransport $ssd1306_spi = null;
    protected int $ssd1306_spi_bus = 1;
    protected int $spi_ssd1306_chip_select = 0;
    protected int $max_packet_size = 0;

    abstract public function wait(int $ms): void;

    protected function spi_ssd1306_bus(?int $bus = null): int
    {
        if(!is_null($bus))
        {
            $this->ssd1306_spi_bus = $bus;
        }
        return $this->ssd1306_spi_bus;
    }

    protected function spi_ssd1306_chip_select(?int $cs = null): int
    {
        if($cs)
        {
            $this->spi_ssd1306_chip_select = $cs;
        }
        return $this->spi_ssd1306_chip_select;
    }

    protected function ssd1306_spi(): ?SPITransport
    {
        if(empty($this->ssd1306_spi))
        {
            $this->ssd1306_spi = new SPITransport(
                $this->spi_ssd1306_bus(),
                $this->spi_ssd1306_chip_select(),
                0,
                8000000,
                0
            );
        }

        return $this->ssd1306_spi;
    }

    public function sendData(array $bytes): void
    {
        $this->dcHigh();
        $this->ssd1306_spi()->send($bytes);
    }

    public function sendCommand(array $bytes): void
    {
        $this->dcLow();
        $this->ssd1306_spi()->send($bytes);
    }

    protected function resetSequence(): void
    {
        $this->rstHigh();
        $this->wait(3);

        $this->rstLow();
        $this->wait(3);

        $this->rstHigh();
        $this->wait(3);

        $this->dcLow();
    }
}
