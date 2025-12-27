<?php

namespace ScrapyardIO\Displays\Monochrome\SSD1306\Enums;

enum SSD1306I2CAddress: int {
    case SA0_GROUNDED = 0x3C;
    case SA0_ENERGIZED = 0x3D;
}
