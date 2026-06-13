<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Factory;

use BareMetal\CircuitFactory;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Adapters\SSD1306I2CAdapter;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Adapters\SSD1306SPIAdapter;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\DataObjects\SSD1306COMPinsHWConfig;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Enums\SSD1306AddressingMode;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Enums\SSD1306VoltageCommonHigh;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\SSD1306;
use Exception;
use Waveforms\Carriers\GPIO\Factory\GPIOConnectionBuilder;
use Waveforms\Carriers\GPIO\GPIOPin;
use Waveforms\Carriers\I2C\Factory\I2CConnectionBuilder;
use Waveforms\Carriers\I2C\I2CDevice;
use Waveforms\Carriers\SPI\Enums\SPIMode;
use Waveforms\Carriers\SPI\Factory\SPIConnectionBuilder;

class SSD1306Factory extends CircuitFactory
{
    protected bool $has_dc = false;

    protected bool $has_rst = false;

    protected int $width = 128;

    protected int $height = 64;

    protected int $contrast = 191;

    protected int $start_line = 0;

    protected int $display_offset = 0;

    protected int $max_packet_size = 1024;

    protected bool $invert_display = false;

    protected bool $enable_com_lr_remap = false;

    protected bool $powered_by_host_device = true;

    protected bool $map_line_0_to_line_127 = false;

    protected bool $sequential_com_pin_config = true;

    protected bool $reverse_line_scan_direction = false;

    protected SSD1306VoltageCommonHigh $v_com_h = SSD1306VoltageCommonHigh::LEVEL_077_ALT;

    protected SSD1306AddressingMode $addressing_mode = SSD1306AddressingMode::HORIZONTAL_ADDRESSING_MODE;

    public string $consumer = 'ssd1306';

    public null|I2CConnectionBuilder|SPIConnectionBuilder $connection = null;

    public function __construct(
        public I2CConnectionBuilder $i2c_connection,
        public SPIConnectionBuilder $spi_connection,
        public GPIOConnectionBuilder $gpio_connection
    ) {}

    public function i2c(string|int $chip_device, int $slave_address): static
    {
        $this->connection = $this->i2c_connection->firstly($chip_device)
            ->slaveAddress($slave_address);

        return $this;
    }

    public function spi(string|int $master, int $chip_select): static
    {
        $this->connection = $this->spi_connection->firstly($master)
            ->chip($chip_select)
            ->speed(8000000)
            ->mode(SPIMode::MODE_0);

        return $this;
    }

    public function gpiochip(int|string $chip): static
    {
        $this->gpio_connection = $this->gpio_connection->firstly($chip);

        return $this;
    }

    /**
     * @throws Exception
     */
    public function dc(int $pin): static
    {
        if (! $this->has_dc) {
            $gpio_output = GPIOPin::createOutput($this->connection->connection(), $pin, 'dc');
            $this->gpio_connection = $this->gpio_connection->addOutput($gpio_output);
            $this->has_dc = true;
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    public function rst(int $pin): static
    {
        if (! $this->has_rst) {
            $gpio_output = GPIOPin::createOutput($this->connection->connection(), $pin, 'rst');
            $this->gpio_connection = $this->gpio_connection->addOutput($gpio_output);
            $this->has_rst = true;
        }

        return $this;
    }

    public function consumer(string $consumer): static
    {
        $this->consumer = $consumer;

        return $this;
    }

    public function width(int $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function height(int $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function maxPacketSize(int $max_packet_size): static
    {
        $this->max_packet_size = $max_packet_size;

        return $this;
    }

    public function offset(int $offset): static
    {
        $this->display_offset = $offset;

        return $this;
    }

    public function startLine(int $pos): static
    {
        $this->start_line = $pos;

        return $this;
    }

    public function invertDisplay(bool $flag): static
    {
        $this->invert_display = $flag;

        return $this;
    }

    public function addressingMode(SSD1306AddressingMode $mode): static
    {
        $this->addressing_mode = $mode;

        return $this;
    }

    public function flipLine0And127(bool $flip): static
    {
        $this->map_line_0_to_line_127 = $flip;

        return $this;
    }

    public function flipLineScanDir(bool $flip): static
    {
        $this->reverse_line_scan_direction = $flip;

        return $this;
    }

    public function startingContrast(int $contrast): static
    {
        $this->contrast = $contrast;

        return $this;
    }

    public function notPoweredByHostDevice(): static
    {
        $this->powered_by_host_device = false;

        return $this;
    }

    public function voltageCommonHigh(SSD1306VoltageCommonHigh $v_com_h): static
    {
        $this->v_com_h = $v_com_h;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function create(): SSD1306
    {
        $carrier = $this->connection?->boot();
        if (is_null($carrier)) {
            throw new Exception('A connection was not registered.');
        }

        if ($carrier instanceof I2CDevice) {
            $carrier = new SSD1306I2CAdapter($carrier, $this->max_packet_size);
        } else {
            $gpio = $this->gpio_connection
                ->shareConnectionWith($carrier)
                ->consumer($this->consumer)
                ->boot();
            $carrier = new SSD1306SPIAdapter($carrier, $gpio, $this->max_packet_size);
        }

        return new SSD1306(
            $carrier,
            $this->width,
            $this->height,
            $this->contrast,
            $this->display_offset,
            $this->start_line,
            $this->addressing_mode,
            $this->map_line_0_to_line_127,
            $this->reverse_line_scan_direction,
            new SSD1306COMPinsHWConfig(
                $this->enable_com_lr_remap,
                $this->sequential_com_pin_config
            ),
            $this->powered_by_host_device,
            $this->v_com_h,
            $this->invert_display
        );
    }
}
