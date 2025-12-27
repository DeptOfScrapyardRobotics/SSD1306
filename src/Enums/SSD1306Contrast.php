<?php

namespace ScrapyardIO\Displays\Monochrome\SSD1306\Enums;

enum SSD1306Contrast: int
{
    /**
     * Minimum brightness (very dim, barely visible)
     */
    case MINIMUM = 0x00;

    /**
     * Low brightness (power saving)
     */
    case LOW = 0x3F;

    /**
     * Medium brightness (default, good balance)
     */
    case MEDIUM = 0x7F;

    /**
     * High brightness
     */
    case HIGH = 0xBF;

    /**
     * Maximum brightness (harsh, high power consumption)
     */
    case MAXIMUM = 0xFF;
}

