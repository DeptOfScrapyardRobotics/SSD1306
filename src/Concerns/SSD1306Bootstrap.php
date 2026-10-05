<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\Concerns;

use DeptOfScrapyardRobotics\Displays\SSD1306\Breakouts\SSD1306DataClock;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306Exception;

trait SSD1306Bootstrap
{
    use SSD1306API;

    /**
     * Every run-time setting under its config key: reads come from config(), writes go to the chip and then config().
     *
     * @throws SSD1306Exception
     */
    public function __get(string $name): mixed
    {
        return match ($name) {
            'display_on', 'display_offset', 'contrast', 'start_line', 'charge_pump', 'addressing_mode',
            'map_line_0_to_line_127', 'reverse_line_scan_direction', 'com_pins_config', 'powered_by_host_device',
            'v_com_h', 'fill_overlay_on', 'invert_display' => $this->config()->get($name),
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
            'display_offset' => $this->setDisplayOffset((int) $value),
            'contrast' => $this->setContrast((int) $value),
            'start_line' => $this->setDisplayStartLine((int) $value),
            'charge_pump' => $this->setChargePumpRegulator((bool) $value),
            'addressing_mode' => $this->setMemoryAddressingMode($value),
            'map_line_0_to_line_127' => $this->setSegmentRemap((bool) $value),
            'reverse_line_scan_direction' => $this->setCOMOutputScanDirection((bool) $value),
            'com_pins_config' => $this->setCOMPinsHardwareConfiguration($value),
            'powered_by_host_device' => $this->setPrechargePeriod((bool) $value),
            'v_com_h' => $this->setVoltageCommonHigh($value),
            'fill_overlay_on' => $this->setFillOverlay((bool) $value),
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