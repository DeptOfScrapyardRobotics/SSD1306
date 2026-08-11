<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\Enums;

enum SSD1306CatalogIc: string
{
    case SSD1306 = 'ssd1306';

    /**
     * @return list<string>
     */
    public static function slugs(): array
    {
        return array_map(
            static fn (self $case): string => $case->value,
            self::cases(),
        );
    }
}
