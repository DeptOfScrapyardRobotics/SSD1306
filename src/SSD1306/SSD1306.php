<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306;

use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Adapters\SSD1306DataCarrier;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Concerns\SSD1306API;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\DataObjects\SSD1306COMPinsHWConfig;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\DataObjects\SSD1306DataClock;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Enums\SSD1306AddressingMode;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Enums\SSD1306VoltageCommonHigh;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Exceptions\SSD1306Exception;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Factory\SSD1306Factory;
use Exception;
use RealityInterface\Displays\Attributes\OutputsOnlyBlackAndWhite;
use RealityInterface\Displays\Contracts\Applied\Monochrome\MonochromeDisplayInterface;
use RealityInterface\Displays\EmbeddedDisplay;
use ScrapyardIO\NutsAndBolts\DataObjects\DumpedBuffer;
use ScrapyardIO\NutsAndBolts\DataObjects\FormatSpec;
use ScrapyardIO\NutsAndBolts\Enums\BitDepth;
use ScrapyardIO\NutsAndBolts\Enums\BitOrder;
use ScrapyardIO\NutsAndBolts\Enums\PageAxis;
use ScrapyardIO\NutsAndBolts\Enums\PixelFormat;
use ScrapyardIO\NutsAndBolts\Enums\ScanDirection;
use Waveforms\Carriers\GPIO\GPIO;
use Waveforms\Carriers\I2C\I2C;
use Waveforms\Carriers\SPI\SPI;

#[OutputsOnlyBlackAndWhite]
class SSD1306 extends EmbeddedDisplay implements MonochromeDisplayInterface
{
    use SSD1306API;

    protected bool $booted = false;

    protected bool $display_on = false;

    protected bool $fill_overlay_on = false;

    protected bool $charge_pump = true;

    /**
     * @throws Exception
     */
    public function __construct(
        protected readonly SSD1306DataCarrier $carrier,
        int $width,
        int $height,
        protected int $_contrast,
        protected int $_offset,
        protected int $_start_line,
        protected SSD1306AddressingMode $addressing_mode,
        protected bool $_map_line_0_to_line_127,
        protected bool $_reverse_line_scan_direction,
        protected SSD1306COMPinsHWConfig $_com_pins_config,
        protected bool $_powered_by_host_device,
        protected SSD1306VoltageCommonHigh $_v_com_h,
        bool $invert_display,
    ) {
        $this->boot(
            $_offset,
            $_contrast,
            $_start_line,
            $addressing_mode,
            $_map_line_0_to_line_127,
            $_reverse_line_scan_direction,
            $_com_pins_config,
            $_powered_by_host_device,
            $_v_com_h,
            $invert_display,
            $height
        );
        parent::__construct($width, $height);

    }

    public function display(DumpedBuffer $buffer): void
    {
        // The buffer already emitted bytes in the panel's FormatSpec layout, so
        // we only point the panel at the region and blast. A whole-frame dump
        // leaves width/height unset and falls back to the full panel; a partial
        // page strip carries its own window.
        $width = $buffer->width ?? $this->width();
        $height = $buffer->height ?? $this->height();

        $this->setAddressWindow($buffer->origin_x, $buffer->origin_y, $width, $height);
        $this->writeFrame($buffer->raw_data);
    }

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
            default => throw SSD1306Exception::invalidProperty($name)
        };
    }

    public function __get(string $name): mixed
    {
        return match ($name) {
            'display_on' => $this->display_on,
            'offset' => $this->_offset,
            'contrast' => $this->_contrast,
            'start_line' => $this->_start_line,
            'charge_pump' => $this->charge_pump,
            'flip_line_0_and_127' => $this->_map_line_0_to_line_127,
            'flip_line_scan_dir' => $this->_reverse_line_scan_direction,
            'com_pins_config' => $this->_com_pins_config,
            'powered_by_host_device' => $this->_powered_by_host_device,
            'v_com_h' => $this->_v_com_h,
            'addressing_mode' => $this->getAddressingMode(),
            'fill_overlay_on' => $this->fill_overlay_on,
            default => throw SSD1306Exception::invalidProperty($name)
        };
    }

    /**
     * @throws Exception
     */
    protected function boot(
        int $offset,
        int $contrast,
        int $start_line,
        SSD1306AddressingMode $addressing_mode,
        bool $map_line_0_to_line_127,
        bool $reverse_line_scan_direction,
        SSD1306COMPinsHWConfig $com_pins_config,
        bool $powered_by_host_device,
        SSD1306VoltageCommonHigh $v_com_h,
        bool $invert_display,
        int $height,
    ): void {
        if (! $this->booted) {
            $this->carrier->reset();

            $this->displayOff();
            $this->setDataClockOscillationFrequency(new SSD1306DataClock);
            $this->setMultiplexRatio($height - 1);
            $this->setDisplayOffset($offset);
            $this->setDisplayStartLine($start_line);
            $this->setChargePumpRegulator(true);
            $this->setMemoryAddressingMode($addressing_mode);
            $this->setSegmentRemap($map_line_0_to_line_127);
            $this->setCOMOutputScanDirection($reverse_line_scan_direction);
            $this->setCOMPinsHardwareConfiguration($com_pins_config);
            $this->setContrast($contrast);
            $this->setPrechargePeriod($powered_by_host_device);
            $this->setVoltageCommonHigh($v_com_h);
            $this->setFillOverlay(false);
            $this->setInvertDisplay($invert_display);
            $this->unsetScroll();
            $this->displayOn();

            $this->booted = true;
        }
    }

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
     * @throws Exception
     */
    public static function connection(string $driver): SSD1306Factory
    {
        return new SSD1306Factory(
            I2C::connection($driver),
            SPI::connection($driver),
            GPIO::connection($driver)
        );
    }
}
