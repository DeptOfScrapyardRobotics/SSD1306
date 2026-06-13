<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\DataObjects;

use BareMetal\DataObjects\DataRegister;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Enums\SSD1306OpCode;

readonly class SSD1306COMScanDirection extends DataRegister
{
    public function __construct(
        public bool $enabled = false,
    ) {}

    public function toBits(): string
    {
        $bits7654 = '1100';
        $bit3 = $this->enabled ? '1' : '0';
        $bits210 = '000';

        return "{$bits7654}{$bit3}{$bits210}";
    }

    public function toOpCode(): SSD1306OpCode
    {
        return SSD1306OpCode::from($this->toByte());
    }

    public static function fromByte(int $byte): static
    {
        $bits = byte2bits($byte);

        return new static(
            $bits[3],
        );
    }

    public static function none(): static
    {
        return new static(
            false,
        );
    }
}
