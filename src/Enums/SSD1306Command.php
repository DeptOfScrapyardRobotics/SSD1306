<?php

namespace ScrapyardIO\Displays\Monochrome\SSD1306\Enums;

enum SSD1306Command: int {
    case SET_MEMORY_MODE = 0x20;

    case SET_COLUMN_RANGE = 0x21;
    case SET_PAGE_RANGE = 0x22;

    case SET_CONTRAST = 0x81;
    case CHARGE_PUMP_SETTING = 0x8D;

    case SET_MULTIPLEX_RATIO = 0xA8;

    case SET_DISPLAY_OFFSET = 0xD3;
    case SET_DISPLAY_CLOCK = 0xD5;
    case SET_PRECHARGE_PERIOD = 0xD9;
    case SET_COM_PINS = 0xDA;
    case SET_VOLTAGE_COMMON_HIGH = 0xDB;

    case DISPLAY_OFF = 0xAE;
    case DISPLAY_ON = 0xAF;
    case DISPLAY_START_LINE = 0x40;

    case NO_HORIZONTAL_INVERSION = 0xA0; // 0 starts on the left
    case INVERT_DISPLAY_HORIZONTALLY = 0xA1; // 0 starts on the right

    case NO_VERTICAL_INVERSION = 0xC0; // Scanning goes from top to bottom
    case INVERT_DISPLAY_VERTICALLY = 0xC8; // Scanning goes from bottom to top

    case ENTIRE_DISPLAY_ON_RESUME = 0xA4;
    case ENTIRE_DISPLAY_ON = 0xA5;
    case NORMAL_DISPLAY = 0xA6;
    case INVERSE_DISPLAY = 0xA7;
    case DEACTIVATE_SCROLL = 0x2E;
}
