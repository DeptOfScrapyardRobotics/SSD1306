<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306;

use DeptOfScrapyardRobotics\Displays\SSD1306\Concerns\SSD1306IO;
use GeneralPurposeIO\Digital\DigitalOutputPin;
use GeneralPurposeIO\I2C\I2CSlave;
use GeneralPurposeIO\SPI\SPIDevice;

class SSD1306CarrierTransport
{
    use SSD1306IO;

    protected int $max_packet_size = 1024;

    public readonly string $active_transport;

    /**
     * @throws SSD1306Exception
     */
    public function __construct(
        protected ?I2CSlave $i2c = null,
        protected ?SPIDevice $spi = null,
        protected ?DigitalOutputPin $dc = null,
        protected ?DigitalOutputPin $rst = null,
    ) {
        $this->active_transport = $this->detectTransport();
    }

    public function command(int $register, array $command_data = []): int
    {
        return $this->active_transport == 'i2c'
            ? $this->i2cCommand($register, $command_data)
            : $this->spiCommand($register, $command_data);
    }

    public function data(array $data = []): void
    {
        $this->active_transport == 'i2c'
            ? $this->i2cData($data)
            : $this->spiData($data);
    }

    public function reset(): void
    {
        if ($this->active_transport == 'spi') {
            $this->rst->high();
            usleep(3000);

            $this->rst->low();
            usleep(3000);

            $this->rst->high();
            usleep(3000);
        }
    }

    public function maxPacketSize(int $size): static
    {
        $this->max_packet_size = $size;

        return $this;
    }

    public function close(): void
    {
        $this->i2c?->close();
        $this->spi?->close();
        $this->dc?->close();
        $this->rst?->close();
    }

    /**
     * @throws SSD1306Exception
     */
    protected function detectTransport(): string
    {
        if (! is_null($this->i2c)) {
            return 'i2c';
        } elseif (! is_null($this->spi)) {
            if ((! is_null($this->dc)) && (! is_null($this->rst))) {
                return 'spi';
            }

            throw SSD1306Exception::missingDigitalPins();
        }

        throw SSD1306Exception::transportMissingProtocol();
    }
}