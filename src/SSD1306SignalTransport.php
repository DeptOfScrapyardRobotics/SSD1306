<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306;

use BareMetal\Contracts\Displays\DataCommandTransfers;
use GPIO\Contracts\I2C\I2CAPI;
use GPIO\Contracts\SPI\SPIAPI;
use GPIO\Common\SignalTransporter;
use GPIO\Digital\Output\DigitalOutput;

class SSD1306SignalTransport extends SignalTransporter implements DataCommandTransfers
{
    use SSD1306IO;

    protected int $max_packet_size = 1024;

    /**
     * @throws SSD1306Exception
     */
    public function __construct(
        protected ?I2CAPI $i2c = null,
        protected ?SPIAPI $spi = null,
        protected ?DigitalOutput $dc = null,
        protected ?DigitalOutput $rst = null,
    ) {
        parent::__construct($this->detectTransport());
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
        if ($this->active_transport == 'spi')
        {
            $this->rst->high();
            usleep(3000);

            $this->rst->low();
            usleep(3000);

            $this->rst->high();
            usleep(3000);
        }
    }

    /**
     * @throws SSD1306Exception
     */
    protected function detectTransport(): string
    {
        if(!is_null($this->i2c)) {
            return 'i2c';
        }
        elseif(!is_null($this->spi)) {
            if((!is_null($this->dc)) && (!is_null($this->rst))) {
                return 'spi';
            }

            throw SSD1306Exception::missingDigitalPins();
        }

        throw SSD1306Exception::transportMissingProtocol();
    }
}
