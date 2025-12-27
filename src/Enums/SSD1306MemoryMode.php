<?php

namespace ScrapyardIO\Displays\Monochrome\SSD1306\Enums;

enum SSD1306MemoryMode: int
{
    /**
     * Horizontal Addressing Mode
     * Auto-increment: column by column, then move to next page
     * Best for full-screen framebuffer updates
     */
    case HORIZONTAL = 0x00;

    /**
     * Vertical Addressing Mode
     * Auto-increment: page by page, then move to next column
     * Useful for vertical scrolling text
     */
    case VERTICAL = 0x01;

    /**
     * Page Addressing Mode (RESET default)
     * Manual page addressing required
     * Only column auto-increments within current page
     */
    case PAGE = 0x02;
}

