<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306;

use DeptOfScrapyardRobotics\Displays\SSD1306\Breakouts\SSD1306COMPinsHWConfig;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306AddressingMode;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306VoltageCommonHigh;

class SSD1306Configuration
{
    protected bool $display_on = false;

    protected bool $charge_pump = true;

    protected bool $fill_overlay_on = false;

    protected SSD1306COMPinsHWConfig $com_pins_config;

    public function __construct(
        protected int $width = 128,
        protected int $height = 64,
        protected int $contrast = 191,
        protected int $start_line = 0,
        protected int $display_offset = 0,
        protected int $max_packet_size = 1024,
        protected bool $invert_display = false,
        protected bool $enable_com_lr_remap = false,
        protected bool $powered_by_host_device = true,
        protected bool $map_line_0_to_line_127 = false,
        protected bool $sequential_com_pin_config = true,
        protected bool $reverse_line_scan_direction = false,
        protected SSD1306VoltageCommonHigh $v_com_h = SSD1306VoltageCommonHigh::LEVEL_077_ALT,
        protected SSD1306AddressingMode $addressing_mode = SSD1306AddressingMode::HORIZONTAL_ADDRESSING_MODE,
    ) {
        $this->com_pins_config = new SSD1306COMPinsHWConfig(
            $this->enable_com_lr_remap,
            $this->sequential_com_pin_config
        );
    }

    public function get(string $var): mixed
    {
        if(isset($this->$var))
        {
            return $this->$var;
        }

        throw SSD1306Exception::invalidProperty($var, static::class);
    }

    public function set(string $var, mixed $value): void
    {
        if(isset($this->$var))
        {
            $this->$var = $value;

            return;
        }

        throw SSD1306Exception::invalidProperty($var, static::class);
    }
}