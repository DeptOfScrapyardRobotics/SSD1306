<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Exceptions;

use RuntimeException;

class SSD1306Exception extends RuntimeException
{
    public static function invalidProperty(string $name): static
    {
        return new static("Invalid property $name");
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
