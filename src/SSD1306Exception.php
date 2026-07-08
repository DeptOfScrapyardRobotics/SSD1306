<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306;

use BareMetal\Contracts\Displays\DisplayException;

class SSD1306Exception extends DisplayException
{
    public static function transportMissingProtocol(): static
    {
        return new static("SSD1306 requires an SPI or an I2C capable connection.");
    }

    public static function missingDigitalPins(): static
    {
        return new static("SSD1306 requires SPI connections to enable DC and RST DigitalOutput pins.");
    }

    public static function invalidMux(int $ratio): static
    {
        return new static("invalid Multiplex Ratio - $ratio");
    }

    public static function invalidOffset(int $offset): static
    {
        return new static("invalid Display Offset - $offset");
    }

    public static function invalidStartLine(int $pos): static
    {
        return new static("invalid StartLine - {$pos}");
    }

    public static function invalidContrast(int $pos): static
    {
        return new static("invalid Contrast value - {$pos}");
    }

    public static function unsupportedAddressingModeForFormatSpec(string $mode): static
    {
        return new static("No FormatSpec mapping for addressing mode {$mode}; only page-major (HORIZONTAL/PAGE) packing is currently expressible.");
    }
}
