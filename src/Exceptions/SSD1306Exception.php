<?php

namespace ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Exceptions;

use Exception;

class SSD1306Exception extends Exception
{
    public static function invalidMuxRatio(int $value): static
    {
        return new static("Mux Ratio Must be between 16 and 63. Value set - {$value}");
    }

    public static function invalidOffset(int $value): static
    {
        return new static("Vertical Offset Must be between 0 and 63. Value set - {$value}");
    }

    public static function invalidStartLine(int $value): static
    {
        return new static("Display start line Must be between 0 and 63. Value set - {$value}");
    }

    public static function invalidSegmentRemapAddress(int $value): static
    {
        return new static("Segment Remap Registers are 160 and 161. Value set - {$value}");
    }
    public static function invalidCOMOutputScanDirection(int $value): static
    {
        return new static("Segment Remap Registers are 192 and 200. Value set - {$value}");
    }

    public static function invalidContrast(int $value): static
    {
        return new static("Mux Ratio Must be between 0 and 255. Value set - {$value}");
    }
}
