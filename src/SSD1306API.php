<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306;

use DeptOfScrapyardRobotics\Displays\SSD1306\Breakouts\SSD1306ChargePump;
use DeptOfScrapyardRobotics\Displays\SSD1306\Breakouts\SSD1306COMPinsHWConfig;
use DeptOfScrapyardRobotics\Displays\SSD1306\Breakouts\SSD1306COMScanDirection;
use DeptOfScrapyardRobotics\Displays\SSD1306\Breakouts\SSD1306DataClock;
use DeptOfScrapyardRobotics\Displays\SSD1306\Breakouts\SSD1306SegmentRemap;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306AddressingMode;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306Precharge;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306StartLineCommand;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306VoltageCommonHigh;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306OpCode;

trait SSD1306API
{
    use SSD1306InternalAPI;

    protected bool $display_on = false;

    protected bool $_charge_pump = true;

    protected bool $fill_overlay_on = false;

    protected SSD1306COMPinsHWConfig $_com_pins_config;

    public function displayOn(): void
    {
        $this->command(SSD1306OpCode::TOGGLE_DISPLAY_ON);
        $this->display_on = true;
    }

    public function displayOff(): void
    {
        $this->command(SSD1306OpCode::TOGGLE_DISPLAY_OFF);
        $this->display_on = false;
    }

    public function setDataClockOscillationFrequency(SSD1306DataClock $freq): void
    {
        $this->command(SSD1306OpCode::DISPLAY_CLOCK_REGISTER, [$freq->toByte()]);
    }

    /**
     * @throws SSD1306Exception
     */
    public function setMultiplexRatio(int $ratio): void
    {
        if (($ratio < 16) || ($ratio > 63)) {
            throw SSD1306Exception::invalidMux($ratio);
        }

        $this->command(SSD1306OpCode::MUX_REGISTER, [$ratio]);
    }

    /**
     * @throws SSD1306Exception
     */
    public function setDisplayOffset(int $offset): void
    {
        if (($offset < 0) || ($offset > 63)) {
            throw SSD1306Exception::invalidOffset($offset);
        }

        $this->command(SSD1306OpCode::VERTICAL_OFFSET_REGISTER, [$offset]);
        $this->_display_offset = $offset;
    }

    /**
     * @throws SSD1306Exception
     */
    public function setDisplayStartLine(int $pos): void
    {
        if (($pos < 0) || ($pos > 63)) {
            throw SSD1306Exception::invalidStartLine($pos);
        }
        $this->command(SSD1306StartLineCommand::fromInt($pos));
        $this->_start_line = $pos;
    }

    public function setChargePumpRegulator(bool $flag): void
    {
        $register = new SSD1306ChargePump($flag);

        $this->command(SSD1306OpCode::CP_REGULATOR_REGISTER, [$register->toByte()]);
        $this->_charge_pump = $flag;
    }

    /**
     * @throws SSD1306Exception
     */
    public function setMemoryAddressingMode(SSD1306AddressingMode $mode): void
    {
        $this->command(SSD1306OpCode::ADDRESS_MODE_REGISTER, [$mode->value]);

        $this->_addressing_mode = $mode;
        $this->format_spec = $this->generateFormatSpec();
    }

    public function setSegmentRemap(bool $flag): void
    {
        $register = new SSD1306SegmentRemap($flag);

        $this->command($register->toOpCode());
        $this->_map_line_0_to_line_127 = $flag;
    }

    public function setCOMOutputScanDirection(bool $flag): void
    {
        $register = new SSD1306COMScanDirection($flag);

        $this->command($register->toOpCode());
        $this->_reverse_line_scan_direction = $flag;
    }

    public function setCOMPinsHardwareConfiguration(SSD1306COMPinsHWConfig $config): void
    {
        $this->command(SSD1306OpCode::COM_PINS_HW_CONFIG_REGISTER, [$config->toByte()]);
        $this->_com_pins_config = $config;
    }

    public function setContrast(int $contrast): void
    {
        if (($contrast < 0) || ($contrast > 255)) {
            throw SSD1306Exception::invalidContrast($contrast);
        }

        $this->command(SSD1306OpCode::CONTRAST_REGISTER, [$contrast]);
        $this->_contrast = $contrast;
    }

    public function setPrechargePeriod(bool $powered_by_host_device): void
    {
        $period = $powered_by_host_device ? SSD1306Precharge::RECOMMENDED : SSD1306Precharge::DEFAULT;
        $this->command(SSD1306OpCode::SET_PRECHARGE_PERIOD, [$period->value]);
        $this->_powered_by_host_device = $powered_by_host_device;
    }

    public function setVoltageCommonHigh(SSD1306VoltageCommonHigh $v_com_h): void
    {
        $this->command(SSD1306OpCode::SET_V_COM_H_DESELECT_LEVEL, [$v_com_h->value]);
        $this->_v_com_h = $v_com_h;
    }

    public function setFillOverlay(bool $flag): void
    {
        if ($flag) {
            $this->command(SSD1306OpCode::FILLED_SCREEN_MODE);
        } else {
            $this->command(SSD1306OpCode::NORMAL_OPERATION_MODE);
        }
    }

    public function setInvertDisplay(bool $flag): void
    {
        if ($flag) {
            $this->command(SSD1306OpCode::INVERT_DISPLAY_ON);
        } else {
            $this->command(SSD1306OpCode::INVERT_DISPLAY_OFF);
        }
    }

    public function unsetScroll(): void
    {
        $this->command(SSD1306OpCode::STOP_SCROLLING);
    }

    public function getAddressingMode(): SSD1306AddressingMode
    {
        return $this->addressing_mode;
    }

    /**
     * Point the auto-incrementing RAM pointer at a column/page rectangle.
     *
     * Valid for horizontal/vertical addressing modes (the 0x21/0x22 registers).
     * Page addressing mode (0x02) would need per-page B0-B7 commands instead.
     */
    public function setAddressWindow(int $x, int $y, int $width, int $height): void
    {
        $this->command(SSD1306OpCode::SET_COLUMN_ADDRESS, [$x, ($x + $width) - 1]);
        $this->command(SSD1306OpCode::SET_PAGE_ADDRESS, [$y >> 3, (($y + $height) - 1) >> 3]);
    }

    public function setDisplay(bool $on): void
    {
        $on ? $this->displayOn() : $this->displayOff();
    }
}
