<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\Concerns;

use DeptOfScrapyardRobotics\Displays\SSD1306\Breakouts\SSD1306DataClock;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306Exception;

trait SSD1306Bootstrap
{
    use SSD1306API;

    /**
     * @throws SSD1306Exception
     */
    public function __get(string $name): mixed
    {
        return match ($name) {
            'display_on' => $this->config()->get('display_on'),
            'offset' => $this->config()->get('display_offset'),
            'contrast' => $this->config()->get('contrast'),
            'start_line' => $this->config()->get('start_line'),
            'charge_pump' => $this->config()->get('charge_pump'),
            'flip_line_0_and_127' => $this->config()->get('map_line_0_to_line_127'),
            'flip_line_scan_dir' => $this->config()->get('reverse_line_scan_direction'),
            'com_pins_config' => $this->config()->get('com_pins_config'),
            'powered_by_host_device' => $this->config()->get('powered_by_host_device'),
            'v_com_h' => $this->config()->get('v_com_h'),
            'addressing_mode' => $this->config()->get('addressing_mode'),
            'fill_overlay_on' => $this->config()->get('fill_overlay_on'),
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

    protected function _boot(): void
    {
        $this->transport()->maxPacketSize($this->config()->get('max_packet_size'));

        $this->deviceReset();
        $this->displayOff();
        $this->setDataClockOscillationFrequency(new SSD1306DataClock);
        $this->setMultiplexRatio($this->config()->get('height') - 1);
        $this->setDisplayOffset($this->config()->get('display_offset'));
        $this->setDisplayStartLine($this->config()->get('start_line'));
        $this->setChargePumpRegulator(true);
        $this->setMemoryAddressingMode($this->config()->get('addressing_mode'));
        $this->setSegmentRemap($this->config()->get('map_line_0_to_line_127'));
        $this->setCOMOutputScanDirection($this->config()->get('reverse_line_scan_direction'));
        $this->setCOMPinsHardwareConfiguration($this->config()->get('com_pins_config'));
        $this->setContrast($this->config()->get('contrast'));
        $this->setPrechargePeriod($this->config()->get('powered_by_host_device'));
        $this->setVoltageCommonHigh($this->config()->get('v_com_h'));
        $this->setFillOverlay(false);
        $this->setInvertDisplay($this->config()->get('invert_display'));
        $this->unsetScroll();
        $this->displayOn();
    }

    protected function deviceReset(): void
    {
        $this->transport()->reset();
    }
}