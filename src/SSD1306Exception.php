<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306;

use GeneralPurposeIO\Contracts\IntegratedCircuits\CircuitException;

class SSD1306Exception extends CircuitException
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

    public static function invalidAddressingMode(string $mode): static
    {
        return new static("invalid Addressing Mode - {$mode}");
    }

    public static function invalidProperty(string $name, string $class): static
    {
        return new static("Invalid property '{$name}' on {$class}.");
    }
}