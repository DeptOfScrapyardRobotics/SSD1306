<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\Breakouts;

use BareMetal\Circuits\DataRegister;

readonly class SSD1306ChargePump extends DataRegister
{
    public function __construct(
        public bool $enabled = false,
    ) {}

    public function toBits(): string
    {
        $bits76543 = '00010';
        $bit2 = $this->enabled ? '1' : '0';
        $bits10 = '00';

        return "{$bits76543}{$bit2}{$bits10}";
    }

    public static function fromByte(int $byte): static
    {
        $bits = byte2bits($byte);

        return new static(
            $bits[2],
        );
    }

    public static function none(): static
    {
        return new static(
            false,
        );
    }
}
