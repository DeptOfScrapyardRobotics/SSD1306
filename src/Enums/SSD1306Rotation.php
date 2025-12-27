<?php

namespace ScrapyardIO\Displays\Monochrome\SSD1306\Enums;

use ScrapyardIO\Displays\Monochrome\SSD1306\Enums\SSD1306Command;

enum SSD1306Rotation: int
{
    case LANDSCAPE = 0;
    case INVERTED_LANDSCAPE = 2;
    case PORTRAIT = 3;
    case INVERTED_PORTRAIT = 4;

    public function toRemap(): array
    {
        return match ($this) {
            self::LANDSCAPE => [
                SSD1306Command::NO_HORIZONTAL_INVERSION->value,
                SSD1306Command::NO_VERTICAL_INVERSION->value,
            ],
            self::INVERTED_LANDSCAPE => [
                SSD1306Command::INVERT_DISPLAY_HORIZONTALLY->value,
                SSD1306Command::NO_VERTICAL_INVERSION->value,
            ],
            self::PORTRAIT => [
                SSD1306Command::NO_HORIZONTAL_INVERSION->value,
                SSD1306Command::INVERT_DISPLAY_VERTICALLY->value,
            ],
            self::INVERTED_PORTRAIT => [
                SSD1306Command::INVERT_DISPLAY_HORIZONTALLY->value,
                SSD1306Command::INVERT_DISPLAY_VERTICALLY->value,
            ],
        };
    }
}

