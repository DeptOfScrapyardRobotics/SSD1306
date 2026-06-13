Introduction
============

PHP Package for the SSD1306 Monochrome OLED display.

Compatible I2C Interfaces
===============
The SSD1306 display communicates with your device over I2C, the InterIntegrated Circuit Protocol.

You can interface with displays such as the SSD1306 with this package the following ways:
* A Linux Single-Board Computer's exposed GPIO pins using the dedicated I2C SDA/SCL pins
* An MPSSE-enabled USB-to-Serial device such as an FT232H generally using D0 and SCL and D1 for SDA connected to nearly any Linux or MacOS USB port.

Compatible SPI Interfaces
===============
The SSD1306 display also supports SPI, the Serial Peripheral Interface.

You can interface with displays such as the SSD1306 over SPI with this package the following ways:
* A Linux Single-Board Computer's exposed GPIO pins using the dedicated SPI MOSI/SCK and CS pins as well as 2 GPIO pins for DC and RST.
* An MPSSE-enabled USB-to-Serial device such as an FT232H generally using D0 and SCK, D1 for MOSI, D2 for MISO and D3 for CS, D4 and D5 for RST/DC and connected to nearly any Linux or MacOS USB port.

Dependencies
=============
This package makes use of modules within:
* [The ScrapyardIO Framework](https://github.com/ScrapyardIO/framework)

This package also requires one of the following extensions in order to interface with I2C/SPI
* [POSI Extension v^0.4.0 or newer](https://github.com/php-io-extensions/posi)
* [FTDI Extension v^0.4.0 or newer](https://github.com/php-io-extensions/ftdi)

In addition, an extension wrapper package is needed

For ext-posi
* [Microscrap POSIX Package v0.4.0 or newer](https://github.com/microscrap/posix)
* [Microscrap Native I2C Package v0.4.0 or newer](https://github.com/microscrap/i2c)
* [Microscrap Native SPI Package v0.4.0 or newer](https://github.com/microscrap/spi)
* [Microscrap Native GPIO Package v0.4.0 or newer](https://github.com/microscrap/gpio)

For ext-ftdi
* [Microscrap FTDI Package v0.4.0 or newer](https://github.com/microscrap/ftdi)
* [Microscrap MPSSE Package v0.4.0 or newer](https://github.com/microscrap/mpsse)

Installing from Composer
====================
Inside the root of your PHP Project, simply require the BMP package from composer
```shell
composer require dept-of-scrapyard-robotics/ssd1306
```
Framework Configuration
====================
If you would like to use the ScrapyardIO Framework to bootstrap your display without
wasting lines configuring your display right in the script you can add your desired
configuration to scrapyard-io.php, such as in this example:

### I2C
```php

use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\SSD1306;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Enums\SSD1306I2CAddress

return [
    'displays' => [
        // For Native Configurations 
        'ssd1306-native' => [
            'class_name' => SSD1306::class,
            'connection' => ['driver' => 'native'],
            'startup' => [
                'i2c' => [
                    'chip_device' => 1,
                    'slave_address' => SSD1306I2CAddress::SAO_GROUNDED->value,
                ],
            ],
        ],
        // For USB Configurations
        'ssd1306-usb' => [
            'class_name' => SSD1306::class,
            'connection' => ['driver' => 'usb'],
            'startup' => [
                'i2c' => [
                    'chip_device' => 'ft232h',
                    'slave_address' => SSD1306I2CAddress::SAO_GROUNDED->value,
                ],
            ],
        ],        
    ]
];

```

### SPI
```php

use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\SSD1306;

return [
    'displays' => [
        // For Native Configurations 
        'ssd1306-native' => [
            'class_name' => SSD1306::class,
            'connection' => ['driver' => 'native'],
            'startup' => [
                'spi' => [
                    'master' => 0,
                    'chip_select' => 0,
                ],
            ],
        ],
        // For USB Configurations
        'ssd1306-usb' => [
            'class_name' => SSD1306::class,
            'connection' => ['driver' => 'usb'],
            'startup' => [
                'spi' => [
                    'master' => 'ft232h',
                    'chip_select' => 0,
                ],
            ],
        ],        
    ]
];
```

Basic Usage
============

### Native (POSIX) I2C driver. (Single Board Computers)
```php

use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\SSD1306;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Enums\SSD1306I2CAddress;

$native_i2c_display = SSD1306::connection('native')
    ->i2c(1, SSD1306I2CAddress::SAO_GROUNDED->value)
    ->create()

```

### Native (POSIX) SPI driver. (Single Board Computers)
```php

use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\SSD1306;

$native_spi_display = SSD1306::connection('native')
    ->spi(0,0)
    ->gpiochip(0)
    ->dc(22)
    ->rst(24)
    ->create()
```

### USB (MPSSE) driver using I2C. (Linux and MacOS)
```php

use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\SSD1306;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Enums\SSD1306I2CAddress;

$usb_i2c_display = SSD1306::connection('usb')
    ->i2c('ft232h', SSD1306I2CAddress::SAO_GROUNDED->value)
    ->create()

```

### USB (MPSSE) driver using SPI. (Linux and MacOS)
```php

use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\SSD1306;

$usb_spi_display = SSD1306::connection('usb')
    ->spi('ft232h',0)
    ->gpiochip('ft232h')
    ->rst(0)
    ->dc(1)
    ->create()
```

## Alternative Usage

### Using Through the Display Library (as a MonochromeDisplay)
```php
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\SSD1306;
use RealityInterface\Displays\Applied\Monochrome\MonochromeDisplay;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Enums\SSD1306I2CAddress;

$ssd1306 = SSD1306::connection('native')
    ->i2c(1, SSD1306I2CAddress::SAO_GROUNDED->value)
    ->create()
    
$display = MonochromeDisplay::as($ssd1306);

```

### Using Through the Display Framework (with an autoloaded config) (as a MonochromeDisplay)
```php

use RealityInterface\Displays\Applied\Monochrome\MonochromeDisplay;

$sensor = MonochromeDisplay::using('ssd1306');



```
