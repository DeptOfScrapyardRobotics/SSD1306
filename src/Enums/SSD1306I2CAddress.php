<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\Enums;

enum SSD1306I2CAddress: int
{
    case SAO_GROUNDED = 0x3C;
    case SAO_ENERGIZED = 0x3D;
}
