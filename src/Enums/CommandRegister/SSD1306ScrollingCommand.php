<?php

namespace ScrapyardIO\Libraries\Displays\Drivers\SSD1306\Enums\CommandRegister;

enum SSD1306ScrollingCommand: int
{
    case TOGGLE_SCROLL_RIGHT = 0x26;
    case TOGGLE_SCROLL_LEFT = 0x27;
    case TOGGLE_SCROLL_UP_RIGHT = 0x29;
    case TOGGLE_SCROLL_UP_LEFT = 0x2A;
    case STOP_SCROLLING = 0x2E;
    case TOGGLE_SCROLLING = 0x2F;
    case SET_VERTICAL_SCROLL_AREA = 0xA3;
}
