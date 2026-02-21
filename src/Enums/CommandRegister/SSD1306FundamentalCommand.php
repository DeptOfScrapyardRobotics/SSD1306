<?php

namespace ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Enums\CommandRegister;

enum SSD1306FundamentalCommand: int
{
    case CONTRAST_CONTROL = 0x81;
    case DISPLAY_ON_RESUME = 0xA4;
    case DISPLAY_ON_FLUSH = 0xA5;
    case NORMAL_DISPLAY_CONTROL = 0xA6;
    case DISPLAY_INVERSION_CONTROL = 0xA7;
    case TOGGLE_DISPLAY_OFF = 0xAE;
    case TOGGLE_DISPLAY_ON = 0xAF;
}
