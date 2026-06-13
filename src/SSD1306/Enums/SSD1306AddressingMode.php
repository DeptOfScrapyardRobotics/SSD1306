<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Enums;

enum SSD1306AddressingMode: int
{
    case HORIZONTAL_ADDRESSING_MODE = 0x00;
    case VERTICAL_ADDRESSING_MODE = 0x01;
    case PAGE_ADDRESSING_MODE = 0x02;
    case INVALID = 0x03;

}
