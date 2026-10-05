<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\Enums;

enum SSD1306SPIClock: int
{
    /** 4-wire SPI clock cycle time, 100 ns minimum. */
    case MAX_HZ = 10_000_000;
}
