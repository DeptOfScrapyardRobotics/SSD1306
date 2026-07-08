<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306;

use BareMetal\Contracts\Displays\MonochromeDisplay;
use BareMetal\Contracts\Framebuffers\DTO\DumpedBuffer;
use BareMetal\Contracts\Framebuffers\DTO\FormatSpec;
use BareMetal\Contracts\Framebuffers\Enums\BitDepth;
use BareMetal\Contracts\Framebuffers\Enums\BitOrder;
use BareMetal\Contracts\Framebuffers\Enums\PageAxis;
use BareMetal\Contracts\Framebuffers\Enums\PixelFormat;
use BareMetal\Contracts\Framebuffers\Enums\ScanDirection;
use BareMetal\Displays\Display;
use BareMetal\Contracts\Circuits\BootSequence;
use DeptOfScrapyardRobotics\Displays\SSD1306\Breakouts\SSD1306COMPinsHWConfig;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306AddressingMode;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306VoltageCommonHigh;
use GPIO\Contracts\I2C\I2CAPI;
use GPIO\Contracts\SPI\SPIAPI;
use GPIO\Digital\Output\DigitalOutput;
use ScrapyardIO\NutsAndBolts\ScrapyardIOException;

/**
 * @property bool $display_on
 * @property int $offset
 * @property int $contrast
 * @property int $start_line
 * @property bool $charge_pump
 * @property bool $flip_line_0_and_127
 * @property bool $flip_line_scan_dir
 * @property SSD1306VoltageCommonHigh $com_pins_config
 * @property bool $powered_by_host_device
 * @property SSD1306VoltageCommonHigh $v_com_h
 * @property SSD1306AddressingMode $addressing_mode
 * @property bool $fill_overlay_on
 * @property-write  bool $invert_display
 */
class SSD1306 extends Display implements BootSequence, MonochromeDisplay
{
    use SSD1306API;

    /**
     * @throws ScrapyardIOException
     */
    public function __construct(
        protected SSD1306SignalTransport $transport,
        int $width,
        int $height,
        protected int $_contrast,
        protected int $_start_line,
        protected int $_display_offset,
        protected int $max_packet_size,
        protected bool $_invert_display,
        protected bool $_enable_com_lr_remap,
        protected bool $_powered_by_host_device,
        protected bool $_map_line_0_to_line_127,
        protected bool $_sequential_com_pin_config,
        protected bool $_reverse_line_scan_direction,
        protected SSD1306VoltageCommonHigh $_v_com_h,
        protected SSD1306AddressingMode $_addressing_mode,
        bool $boot_now = false,
    ) {
        parent::__construct($width, $height);

        $this->_com_pins_config = new SSD1306CompinsHWConfig(
            $this->_enable_com_lr_remap,
            $this->_sequential_com_pin_config
        );

        if($boot_now) {
            $this->boot();
        }
    }

    /**
     * @throws SSD1306Exception
     */
    public function __get(string $name): mixed
    {
        return match ($name) {
            'display_on' => $this->display_on,
            'offset' => $this->_display_offset,
            'contrast' => $this->_contrast,
            'start_line' => $this->_start_line,
            'charge_pump' => $this->_charge_pump,
            'flip_line_0_and_127' => $this->_map_line_0_to_line_127,
            'flip_line_scan_dir' => $this->_reverse_line_scan_direction,
            'com_pins_config' => $this->_com_pins_config,
            'powered_by_host_device' => $this->_powered_by_host_device,
            'v_com_h' => $this->_v_com_h,
            'addressing_mode' => $this->_addressing_mode,
            'fill_overlay_on' => $this->fill_overlay_on,
            default => throw SSD1306Exception::invalidProperty($name, static::class),
        };
    }

    /**
     * @throws SSD1306Exception
     */
    public function __set(string $name, mixed $value): void
    {
        match ($name) {
            'display_on' => $this->setDisplay((bool) $value),
            'offset' => $this->setDisplayOffset((int) $value),
            'charge_pump_regulator' => $this->setChargePumpRegulator((bool) $value),
            'addressing_mode' => $this->setMemoryAddressingMode($value),
            'segment_remap' => $this->setSegmentRemap((bool) $value),
            'reverse_com_scan_dir' => $this->setCOMOutputScanDirection((bool) $value),
            'com_pins_hw_config' => $this->setCOMPinsHardwareConfiguration($value),
            'contrast' => $this->setContrast((int) $value),
            'powered_by_host_device' => $this->setPrechargePeriod((bool) $value),
            'v_com_h' => $this->setVoltageCommonHigh($value),
            'toggle_fill_overlay' => $this->setFillOverlay((bool) $value),
            'invert_display' => $this->setInvertDisplay((bool) $value),
            default => throw SSD1306Exception::invalidProperty($name, static::class),
        };
    }


    /**
     * @throws SSD1306Exception
     */
    public function generateFormatSpec(): FormatSpec
    {
        return match ($this->addressing_mode) {
            SSD1306AddressingMode::HORIZONTAL_ADDRESSING_MODE,
            SSD1306AddressingMode::PAGE_ADDRESSING_MODE => new FormatSpec(
                PixelFormat::MONO_VERTICAL_PAGE,
                BitDepth::B1,
                ScanDirection::TOP_TO_BOTTOM,
                BitOrder::LSB_FIRST,
                page_axis: PageAxis::VERTICAL,
            ),
            default => throw SSD1306Exception::unsupportedAddressingModeForFormatSpec($this->addressing_mode->name),
        };
    }

    /**
     * Point the RAM pointer at the frame's page/column window, then clock the
     * vertical-page bytes out; the transport chunks them by max_packet_size.
     * Valid for the horizontal/vertical addressing modes (the same ones
     * {@see generateFormatSpec()} supports).
     */
    public function transmit(DumpedBuffer $frame): void
    {
        $width = $frame->width ?? $this->width;
        $height = $frame->height ?? $this->height;

        $this->setAddressWindow($frame->origin_x, $frame->origin_y, $width, $height);
        $this->data($frame->raw_data);
    }

    /**
     * @throws SSD1306Exception|ScrapyardIOException
     */
    public static function i2c(
        I2CAPI $i2c,
        int $width = 128,
        int $height = 64,
        int $contrast = 191,
        int $start_line = 0,
        int $display_offset = 0,
        int $max_packet_size = 1024,
        bool $invert_display = false,
        bool $enable_com_lr_remap = false,
        bool $powered_by_host_device = true,
        bool $map_line_0_to_line_127 = false,
        bool $sequential_com_pin_config = true,
        bool $reverse_line_scan_direction = false,
        SSD1306VoltageCommonHigh $v_com_h = SSD1306VoltageCommonHigh::LEVEL_077_ALT,
        SSD1306AddressingMode $addressing_mode = SSD1306AddressingMode::HORIZONTAL_ADDRESSING_MODE,
        bool $boot_now = false,
    ): static
    {
        $transport = new SSD1306SignalTransport(i2c: $i2c);

        return new self(
            $transport,
            $width,
            $height,
            $contrast,
            $start_line,
            $display_offset,
            $max_packet_size,
            $invert_display,
            $enable_com_lr_remap,
            $powered_by_host_device,
            $map_line_0_to_line_127,
            $sequential_com_pin_config,
            $reverse_line_scan_direction,
            $v_com_h,
            $addressing_mode,
            $boot_now
        );
    }

    /**
     * @throws SSD1306Exception|ScrapyardIOException
     */
    public static function spi(
        SPIAPI $spi,
        DigitalOutput $dc,
        DigitalOutput $rst,
        int $width = 128,
        int $height = 64,
        int $contrast = 191,
        int $start_line = 0,
        int $display_offset = 0,
        int $max_packet_size = 1024,
        bool $invert_display = false,
        bool $enable_com_lr_remap = false,
        bool $powered_by_host_device = true,
        bool $map_line_0_to_line_127 = false,
        bool $sequential_com_pin_config = true,
        bool $reverse_line_scan_direction = false,
        SSD1306VoltageCommonHigh $v_com_h = SSD1306VoltageCommonHigh::LEVEL_077_ALT,
        SSD1306AddressingMode $addressing_mode = SSD1306AddressingMode::HORIZONTAL_ADDRESSING_MODE,
        bool $boot_now = false,
    ): static
    {
        $transport = new SSD1306SignalTransport(spi: $spi, dc: $dc, rst: $rst);
        return new self(
            $transport,
            $width,
            $height,
            $contrast,
            $start_line,
            $display_offset,
            $max_packet_size,
            $invert_display,
            $enable_com_lr_remap,
            $powered_by_host_device,
            $map_line_0_to_line_127,
            $sequential_com_pin_config,
            $reverse_line_scan_direction,
            $v_com_h,
            $addressing_mode,
            $boot_now
        );
    }


}
