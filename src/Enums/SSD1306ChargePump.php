<?php

namespace ScrapyardIO\Displays\Monochrome\SSD1306\Enums;

enum SSD1306ChargePump: int
{
    /**
     * Disable internal charge pump (RESET default)
     * Requires external VCC supply (7-15V)
     */
    case DISABLED = 0x10;

    /**
     * Enable internal charge pump
     * Generates VCC from VDD (no external VCC needed)
     * Required for most OLED modules
     */
    case ENABLED = 0x14;

    public static function enabled(bool $flag): int
    {
        return $flag ? self::ENABLED->value : self::DISABLED->value;
    }
}

