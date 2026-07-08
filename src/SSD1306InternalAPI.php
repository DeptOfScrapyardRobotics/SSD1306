<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306;

use BareMetal\Contracts\Circuits\BootScaffolding;
use DeptOfScrapyardRobotics\Displays\SSD1306\Breakouts\SSD1306DataClock;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306StartLineCommand;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306OpCode;


trait SSD1306InternalAPI
{
    use BootScaffolding;

    protected function command(SSD1306OpCode|SSD1306StartLineCommand $register, array $command_data = []): int
    {
        return $this->transport->command($register->value, $command_data);
    }

    protected function data(array $data = []): void
    {
        $this->transport->data($data);
    }

    protected function deviceReset(): void
    {
        $this->transport->reset();
    }

    /**
     * @throws SSD1306Exception
     */
    protected function _boot(): void
    {
        $this->transport = $this->transport->maxPacketSize($this->max_packet_size);

        $this->deviceReset();
        $this->displayOff();
        $this->setDataClockOscillationFrequency(new SSD1306DataClock);
        $this->setMultiplexRatio($this->height - 1);
        $this->setDisplayOffset($this->_display_offset);
        $this->setDisplayStartLine($this->_start_line);
        $this->setChargePumpRegulator(true);
        $this->setMemoryAddressingMode($this->_addressing_mode);
        $this->setSegmentRemap($this->_map_line_0_to_line_127);
        $this->setCOMOutputScanDirection($this->_reverse_line_scan_direction);
        $this->setCOMPinsHardwareConfiguration($this->_com_pins_config);
        $this->setContrast($this->_contrast);
        $this->setPrechargePeriod($this->_powered_by_host_device);
        $this->setVoltageCommonHigh($this->_v_com_h);
        $this->setFillOverlay(false);
        $this->setInvertDisplay($this->_invert_display);
        $this->unsetScroll();
        $this->displayOn();
    }
}
