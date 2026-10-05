<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306;

use DeptOfScrapyardRobotics\Displays\SSD1306\Breakouts\SSD1306COMPinsHWConfig;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306AddressingMode;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306VoltageCommonHigh;
use ReflectionMethod;
use ReflectionParameter;

/**
 * The panel's geometry and settings. A setting the panel can change at run time is also a magic property on the
 * panel under the same key, readable and writable ($panel->contrast, $panel->contrast = 0x40).
 */
class SSD1306Configuration
{
    protected bool $display_on = false;

    protected bool $charge_pump = true;

    protected bool $fill_overlay_on = false;

    protected SSD1306COMPinsHWConfig $com_pins_config;

    /**
     * @param  ?bool  $alternative_com_pins  0xDA bit 4. Null picks by height: alternative above 32 rows (128×64),
     *                                       sequential at 32 rows or fewer (128×32, 96×16). The wrong one blanks or
     *                                       doubles every other row.
     */
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
        ?bool $alternative_com_pins = null,
        protected bool $reverse_line_scan_direction = false,
        protected SSD1306VoltageCommonHigh $v_com_h = SSD1306VoltageCommonHigh::LEVEL_077_ALT,
        protected SSD1306AddressingMode $addressing_mode = SSD1306AddressingMode::HORIZONTAL_ADDRESSING_MODE,
    ) {
        $this->com_pins_config = new SSD1306COMPinsHWConfig(
            $this->enable_com_lr_remap,
            $alternative_com_pins ?? $this->height > 32,
        );
    }

    /**
     * Built from a config entry's `panel` array: constructor arguments by name.
     *
     * @param  array<string, mixed>  $panel
     *
     * @throws SSD1306Exception for a key the constructor does not take
     */
    public static function fromArray(array $panel): static
    {
        $known = array_map(
            static fn (ReflectionParameter $parameter): string => $parameter->getName(),
            (new ReflectionMethod(static::class, '__construct'))->getParameters(),
        );

        foreach (array_keys($panel) as $key) {
            if (! in_array($key, $known, true)) {
                throw SSD1306Exception::invalidProperty((string) $key, static::class);
            }
        }

        return new static(...$panel);
    }

    public function get(string $var): mixed
    {
        if (isset($this->$var)) {
            return $this->$var;
        }

        throw SSD1306Exception::invalidProperty($var, static::class);
    }

    public function set(string $var, mixed $value): void
    {
        if (isset($this->$var)) {
            $this->$var = $value;

            return;
        }

        throw SSD1306Exception::invalidProperty($var, static::class);
    }
}
