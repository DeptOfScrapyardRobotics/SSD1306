<?php

use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306I2CAddress;

/*
| conjure('ssd1306') builds the panel from default_config, or the config named.
| A config is named after its protocol unless it carries 'protocol'. 'panel'
| holds SSD1306Configuration's constructor arguments by name: width, height,
| contrast, invert_display, alternative_com_pins, and the rest.
*/
return [
    'default_config' => 'i2c',
    'configs' => [
        'i2c' => [
            'driver' => 'none',
            'device' => '',
            'slave' => SSD1306I2CAddress::SAO_GROUNDED->value,
            'panel' => [
                'width' => 128,
                'height' => 64,
            ],
        ],
        'spi' => [
            'driver' => 'none',
            'device' => '',
            'chip_select' => 0,
            'speed' => 10_000_000,
            'dc' => [
                'driver' => 'none',
                'device' => '',
                'pin' => 0,
            ],
            'rst' => [
                'driver' => 'none',
                'device' => '',
                'pin' => 1,
            ],
            'panel' => [
                'width' => 128,
                'height' => 64,
            ],
        ],
    ],
];
