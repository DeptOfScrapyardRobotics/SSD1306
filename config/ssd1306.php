<?php

use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306I2CAddress;

return [
    'default_config' => 'i2c',
    'configs' => [
        'i2c' => [
            'driver' => 'none',
            'device' => '',
            'slave' => SSD1306I2CAddress::SAO_GROUNDED->value,
        ],
        'spi' => [
            'driver' => 'none',
            'device' => '',
            'chip_select' => 0,
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
        ],
    ],
];