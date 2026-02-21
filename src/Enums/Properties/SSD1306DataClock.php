<?php

namespace ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Enums\Properties;

/**
 * Display Clock Divide Ratio (Register 0xD5, bits 3-0)
 * Divide ratio = N + 1, where N is the enum value
 */
enum SSD1306DataClock: int
{
    case DIVIDE_BY_1 = 0;
    case DIVIDE_BY_2 = 1;
    case DIVIDE_BY_3 = 2;
    case DIVIDE_BY_4 = 3;
    case DIVIDE_BY_5 = 4;
    case DIVIDE_BY_6 = 5;
    case DIVIDE_BY_7 = 6;
    case DIVIDE_BY_8 = 7;
    case DIVIDE_BY_9 = 8;
    case DIVIDE_BY_10 = 9;
    case DIVIDE_BY_11 = 10;
    case DIVIDE_BY_12 = 11;
    case DIVIDE_BY_13 = 12;
    case DIVIDE_BY_14 = 13;
    case DIVIDE_BY_15 = 14;
    case DIVIDE_BY_16 = 15;

    public function getFlags(): array
    {
        return match($this) {
            self::DIVIDE_BY_1    => ['DCLK3' => false, 'DCLK2' => false, 'DCLK1' => false, 'DCLK0' => false],
            self::DIVIDE_BY_2    => ['DCLK3' => false, 'DCLK2' => false, 'DCLK1' => false, 'DCLK0' => true],
            self::DIVIDE_BY_3    => ['DCLK3' => false, 'DCLK2' => false, 'DCLK1' => true, 'DCLK0' => false],
            self::DIVIDE_BY_4    => ['DCLK3' => false, 'DCLK2' => false, 'DCLK1' => true, 'DCLK0' => true],
            self::DIVIDE_BY_5    => ['DCLK3' => false, 'DCLK2' => true, 'DCLK1' => false, 'DCLK0' => false],
            self::DIVIDE_BY_6    => ['DCLK3' => false, 'DCLK2' => true, 'DCLK1' => false, 'DCLK0' => true],
            self::DIVIDE_BY_7    => ['DCLK3' => false, 'DCLK2' => true, 'DCLK1' => true, 'DCLK0' => false],
            self::DIVIDE_BY_8    => ['DCLK3' => false, 'DCLK2' => true, 'DCLK1' => true, 'DCLK0' => true],
            self::DIVIDE_BY_9    => ['DCLK3' => true, 'DCLK2' => false, 'DCLK1' => false, 'DCLK0' => false],
            self::DIVIDE_BY_10   => ['DCLK3' => true, 'DCLK2' => false, 'DCLK1' => false, 'DCLK0' => true],
            self::DIVIDE_BY_11   => ['DCLK3' => true, 'DCLK2' => false, 'DCLK1' => true, 'DCLK0' => false],
            self::DIVIDE_BY_12   => ['DCLK3' => true, 'DCLK2' => false, 'DCLK1' => true, 'DCLK0' => true],
            self::DIVIDE_BY_13   => ['DCLK3' => true, 'DCLK2' => true, 'DCLK1' => false, 'DCLK0' => false],
            self::DIVIDE_BY_14   => ['DCLK3' => true, 'DCLK2' => true, 'DCLK1' => false, 'DCLK0' => true],
            self::DIVIDE_BY_15   => ['DCLK3' => true, 'DCLK2' => true, 'DCLK1' => true, 'DCLK0' => false],
            self::DIVIDE_BY_16   => ['DCLK3' => true, 'DCLK2' => true, 'DCLK1' => true, 'DCLK0' => true],
        };
    }
}
