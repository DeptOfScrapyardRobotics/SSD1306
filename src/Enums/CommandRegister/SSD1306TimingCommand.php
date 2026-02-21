<?php

namespace ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Enums\CommandRegister;

enum SSD1306TimingCommand: int
{
    case SET_DISPLAY_CLOCK_DIVIDE_RATIO_AND_OSC_FREQ = 0xD5;
    case SET_PRECHARGE_PERIOD = 0xD9;
    case SET_V_COM_H_DESELECT_LEVEL = 0xDB;
    case NO_OP = 0xE3;
}
