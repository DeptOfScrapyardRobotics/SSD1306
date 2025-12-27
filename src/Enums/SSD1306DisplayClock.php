<?php

namespace ScrapyardIO\Displays\Monochrome\SSD1306\Enums;

enum SSD1306DisplayClock: int
{
    /**
     * Oscillator freq=0, Divide ratio=1
     * Slowest oscillator, lowest frame rate
     */
    case SLOWEST = 0x00;

    /**
     * Oscillator freq=4, Divide ratio=1
     * Slow oscillator
     */
    case SLOW = 0x40;

    /**
     * Oscillator freq=8, Divide ratio=1 (RESET default)
     * Default oscillator (~370kHz), divide by 1
     * Frame rate ~60-100Hz
     */
    case DEFAULT = 0x80;

    /**
     * Oscillator freq=10, Divide ratio=1
     * Slightly faster than default
     */
    case FAST = 0xA0;

    /**
     * Oscillator freq=15, Divide ratio=1
     * Fastest oscillator, highest frame rate
     */
    case FASTEST = 0xF0;

    /**
     * Oscillator freq=8, Divide ratio=2
     * Default frequency, half frame rate
     */
    case DEFAULT_HALF_RATE = 0x81;

    /**
     * Oscillator freq=8, Divide ratio=4
     * Default frequency, quarter frame rate
     */
    case DEFAULT_QUARTER_RATE = 0x83;

    /**
     * Build custom display clock value
     *
     * @param int $oscFreq Oscillator frequency (0-15, higher = faster)
     * @param int $divideRatio Clock divide ratio (1-16)
     * @return int Display clock byte value
     */
    public static function custom(int $oscFreq, int $divideRatio): int
    {
        $oscFreq = max(0, min(15, $oscFreq));
        $divideRatio = max(1, min(16, $divideRatio));
        return ($oscFreq << 4) | ($divideRatio - 1);
    }
}

