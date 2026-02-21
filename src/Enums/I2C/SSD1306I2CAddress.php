<?php

namespace ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Enums\I2C;

enum SSD1306I2CAddress: int {
    case SA0_GROUNDED = 0x3C;
    case SA0_ENERGIZED = 0x3D;
}
