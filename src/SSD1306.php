<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306;

use DeptOfScrapyardRobotics\Displays\SSD1306\Breakouts\SSD1306COMPinsHWConfig;
use DeptOfScrapyardRobotics\Displays\SSD1306\Concerns\SSD1306API;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306AddressingMode;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306I2CAddress;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306VoltageCommonHigh;
use Exception;
use Fabricate\Contracts\Circuits\Attributes\IntegratedCircuit;
use Fabricate\Contracts\Circuits\IntegratedCircuit as CircuitContract;
use Fabricate\Contracts\Displays\Interfaces\MonochromeDisplay;
use Fabricate\Contracts\Displays\Interfaces\PartiallyRefreshable;
use Fabricate\Contracts\Framebuffers\Enums\BitDepth;
use Fabricate\Contracts\Framebuffers\Enums\BitOrder;
use Fabricate\Contracts\Framebuffers\Enums\PageAxis;
use Fabricate\Contracts\Framebuffers\Enums\PixelFormat;
use Fabricate\Contracts\Framebuffers\Enums\ScanDirection;
use Fabricate\Contracts\NutsAndBolts\BootSequence;
use Fabricate\Framebuffers\DataObjects\DumpedBuffer;
use Fabricate\Framebuffers\FormatSpec;
use GeneralPurposeIO\Digital\DigitalIO;
use GeneralPurposeIO\Digital\DigitalOutputPin;
use GeneralPurposeIO\I2C\I2C;
use GeneralPurposeIO\I2C\I2CSlave;
use GeneralPurposeIO\SPI\SPI;
use GeneralPurposeIO\SPI\SPIDevice;

/**
 * @property bool $display_on
 * @property int $offset
 * @property int $contrast
 * @property int $start_line
 * @property bool $charge_pump
 * @property bool $flip_line_0_and_127
 * @property bool $flip_line_scan_dir
 * @property SSD1306COMPinsHWConfig $com_pins_config
 * @property bool $powered_by_host_device
 * @property SSD1306VoltageCommonHigh $v_com_h
 * @property SSD1306AddressingMode $addressing_mode
 * @property bool $fill_overlay_on
 * @property-write bool $invert_display
 */
#[IntegratedCircuit('I2C', 'SPI')]
class SSD1306 implements CircuitContract, BootSequence, MonochromeDisplay, PartiallyRefreshable
{
    use SSD1306API;

    protected FormatSpec $format_spec;

    /**
     * @throws Exception
     */
    public function __construct(
        protected SSD1306CarrierTransport $transport,
        protected int $width,
        protected int $height,
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
        $this->_com_pins_config = new SSD1306COMPinsHWConfig(
            $this->_enable_com_lr_remap,
            $this->_sequential_com_pin_config
        );

        $this->format_spec = $this->_generateFormatSpec();

        if($boot_now) {
            $this->boot();
        }
    }

    public function width(): int
    {
        return $this->width;
    }

    public function height(): int
    {
        return $this->height;
    }

    public function formatSpec(): FormatSpec
    {
        return $this->format_spec;
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
        $this->format_spec = $this->_generateFormatSpec();

        return $this->format_spec;
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

    public function close(): void
    {
        $this->transport->close();
    }

    /**
     * @throws SSD1306Exception
     */
    protected function _generateFormatSpec(): FormatSpec
    {
        return match ($this->_addressing_mode) {
            SSD1306AddressingMode::HORIZONTAL_ADDRESSING_MODE,
            SSD1306AddressingMode::PAGE_ADDRESSING_MODE => new FormatSpec(
                PixelFormat::MONO_VERTICAL_PAGE,
                BitDepth::B1,
                ScanDirection::TOP_TO_BOTTOM,
                BitOrder::LSB_FIRST,
                page_axis: PageAxis::VERTICAL,
            ),
            default => throw SSD1306Exception::unsupportedAddressingModeForFormatSpec($this->_addressing_mode->name),
        };
    }

    /**
     * @throws SSD1306Exception
     */
    public static function i2c(
        string|int $device,
        ?string $adapter = null,
        int $slave = SSD1306I2CAddress::SAO_GROUNDED->value,
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
        bool $boot_now = true,
    ): static
    {
        $i2c = I2C::adapter($adapter)
            ->device($device)
            ->bus()
            ->slave($slave);

        return static::fromI2CBus($i2c,
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
     * @throws SSD1306Exception
     * @throws Exception
     */
    public static function fromI2CBus(
        I2CSlave $i2c,
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
        bool $boot_now = true,
    ): static
    {
        $transport = new SSD1306CarrierTransport(i2c: $i2c);

        return new static(
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
            $boot_now,
        );
    }

    /**
     * @throws SSD1306Exception
     */
    public static function spi(
        string|int $spi_device,
        string|int $chip_select,
        string|int $digital_device,
        int $dc_pin,
        int $rst_pin,
        ?string $spi_adapter = null,
        ?string $digital_adapter = null,
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
        bool $boot_now = true,
    ): static
    {
        $bus = SPI::adapter($spi_adapter)->device($spi_device)
            ->mode(3)->speed(1000000)->bus();

        $spi = $bus->select($chip_select);

        if(!$bus->canServeDigitalPins())
        {
            $bus = DigitalIO::adapter($digital_adapter)->device($digital_device)->bus();
        }

        $dc = $bus->output($dc_pin);
        $rst = $bus->output($rst_pin);

        return static::fromSPIBus($spi, $dc, $rst,
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
     * @throws SSD1306Exception
     * @throws Exception
     */
    public static function fromSPIBus(
        SPIDevice $spi,
        DigitalOutputPin $dc,
        DigitalOutputPin $rst,
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
        bool $boot_now = true,
    ): static
    {
        $transport = new SSD1306CarrierTransport(spi: $spi, dc: $dc, rst: $rst);

        return new static(
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
            $boot_now,
        );
    }
}