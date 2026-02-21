<?php

namespace ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Enums\Properties;

/**
 * Oscillator Frequency (Register 0xD5, bits 7-4)
 * Higher value = higher base oscillator frequency
 */
enum SSD1306OscillatorFrequency: int
{
    case FREQ_0 = 0;
    case FREQ_1 = 1;
    case FREQ_2 = 2;
    case FREQ_3 = 3;
    case FREQ_4 = 4;
    case FREQ_5 = 5;
    case FREQ_6 = 6;
    case FREQ_7 = 7;
    case FREQ_8 = 8;
    case FREQ_9 = 9;
    case FREQ_10 = 10;
    case FREQ_11 = 11;
    case FREQ_12 = 12;
    case FREQ_13 = 13;
    case FREQ_14 = 14;
    case FREQ_15 = 15;

    public function getFlags(): array
    {
        return match($this) {
            self::FREQ_0    => ['OSC3' => false, 'OSC2' => false, 'OSC1' => false, 'OSC0' => false],
            self::FREQ_1    => ['OSC3' => false, 'OSC2' => false, 'OSC1' => false, 'OSC0' => true],
            self::FREQ_2    => ['OSC3' => false, 'OSC2' => false, 'OSC1' => true, 'OSC0' => false],
            self::FREQ_3    => ['OSC3' => false, 'OSC2' => false, 'OSC1' => true, 'OSC0' => true],
            self::FREQ_4    => ['OSC3' => false, 'OSC2' => true, 'OSC1' => false, 'OSC0' => false],
            self::FREQ_5    => ['OSC3' => false, 'OSC2' => true, 'OSC1' => false, 'OSC0' => true],
            self::FREQ_6    => ['OSC3' => false, 'OSC2' => true, 'OSC1' => true, 'OSC0' => false],
            self::FREQ_7    => ['OSC3' => false, 'OSC2' => true, 'OSC1' => true, 'OSC0' => true],
            self::FREQ_8    => ['OSC3' => true, 'OSC2' => false, 'OSC1' => false, 'OSC0' => false],
            self::FREQ_9    => ['OSC3' => true, 'OSC2' => false, 'OSC1' => false, 'OSC0' => true],
            self::FREQ_10   => ['OSC3' => true, 'OSC2' => false, 'OSC1' => true, 'OSC0' => false],
            self::FREQ_11   => ['OSC3' => true, 'OSC2' => false, 'OSC1' => true, 'OSC0' => true],
            self::FREQ_12   => ['OSC3' => true, 'OSC2' => true, 'OSC1' => false, 'OSC0' => false],
            self::FREQ_13   => ['OSC3' => true, 'OSC2' => true, 'OSC1' => false, 'OSC0' => true],   // 13 = 1101
            self::FREQ_14   => ['OSC3' => true, 'OSC2' => true, 'OSC1' => true, 'OSC0' => false],   // 14 = 1110
            self::FREQ_15   => ['OSC3' => true, 'OSC2' => true, 'OSC1' => true, 'OSC0' => true],
        };
    }
}
