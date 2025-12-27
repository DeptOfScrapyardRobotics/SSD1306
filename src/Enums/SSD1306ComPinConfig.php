<?php

namespace ScrapyardIO\Displays\Monochrome\SSD1306\Enums;

enum SSD1306ComPinConfig: int
{
    /**
     * Sequential COM pin configuration
     * Disable COM Left/Right remap
     * Used for 128x32 and smaller displays
     */
    case SEQUENTIAL = 0x02;

    /**
     * Alternative COM pin configuration (RESET default)
     * Disable COM Left/Right remap
     * Used for 128x64 displays
     */
    case ALTERNATIVE = 0x12;

    /**
     * Sequential COM pin configuration
     * Enable COM Left/Right remap
     */
    case SEQUENTIAL_REMAP = 0x22;

    /**
     * Alternative COM pin configuration
     * Enable COM Left/Right remap
     */
    case ALTERNATIVE_REMAP = 0x32;

    /**
     * Get appropriate config for display height
     */
    public static function forHeight(int $height): self
    {
        return match($height) {
            64 => self::ALTERNATIVE,
            //32 => self::SEQUENTIAL,
            default => self::SEQUENTIAL,
        };
    }
}

