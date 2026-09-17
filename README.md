# ssd1306

Drive SSD1306 monochrome OLED panels from PHP over I2C or SPI, using the ScrapyardIO GPIO framework.

`dept-of-scrapyard-robotics/ssd1306` boots the panel, holds its settings, and writes frame bytes into any window of its memory. It tells you exactly how those bytes must be packed through a `FormatSpec`, so a Surface CPU engine, or your own code, can render for it.

## Requirements

- PHP 8.4 or newer
- A Venusian application with `scrapyard-io/framework` 0.8
- An adapter for your hardware:
  - `microscrap/scrapyard-linux` for native `i2c-dev`, `spidev` and `libgpiod` (needs `ext-posi`)
  - `microscrap/scrapyard-usb` for FTDI MPSSE boards such as the FT232H (needs `ext-ftdi`)

## Installation

```bash
composer require dept-of-scrapyard-robotics/ssd1306
```

The service provider is discovered automatically. It merges the package's wiring config under `circuits.ssd1306`. To publish that config into your app, run:

```bash
php computer vendor:publish --tag=ssd1306-config
```

That writes `config/circuits/ssd1306.php`.

## Quick start

A 128×64 panel at `0x3C` on a Raspberry Pi's `i2c-1`:

```php
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306Configuration;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306I2CAddress;
use DeptOfScrapyardRobotics\Displays\SSD1306\Transports\SSD1306I2CTransport;
use GeneralPurposeIO\I2C\I2C;

$slave = I2C::driver('native')
    ->connectTo(1)
    ->register()
    ->device(1, SSD1306I2CAddress::SAO_GROUNDED->value);

$slave->probe();   // true when the panel answers

$panel = new SSD1306(
    new SSD1306I2CTransport($slave),
    new SSD1306Configuration(width: 128, height: 64),
    boot_now: true,
);

$panel->transmit(0, 0, array_fill(0, 128 * 8, 0xFF));   // every pixel lit
```

Booting runs the SSD1306 init sequence with your configuration and turns the display on.

## Connecting

The panel takes an `SSD1306I2CTransport` or an `SSD1306SPITransport`. Each wraps a connection from the framework's protocol managers.

### I2C

The address depends on the SA0 pin:

| `SSD1306I2CAddress` | Address | SA0 |
|---|---|---|
| `SAO_GROUNDED` | `0x3C` | tied low |
| `SAO_ENERGIZED` | `0x3D` | tied high |

```php
use GeneralPurposeIO\I2C\I2C;

// Linux i2c-dev, bus 1
$slave = I2C::driver('native')->connectTo(1)->register()->device(1, 0x3C);

// FTDI MPSSE
$slave = I2C::driver('usb')->connectTo('ft232h')->register()->device('ft232h', 0x3C);

$panel = new SSD1306(new SSD1306I2CTransport($slave), new SSD1306Configuration, boot_now: true);
```

Commands go out behind a `0x00` control byte and pixel data behind `0x40`.

### SPI

4-wire SPI needs two extra output pins: DC selects command or data, and RST resets the panel at boot.

```php
use DeptOfScrapyardRobotics\Displays\SSD1306\Transports\SSD1306SPITransport;
use GeneralPurposeIO\Digital\DigitalIO;
use GeneralPurposeIO\SPI\SPI;

$spi = SPI::driver('native')->connectTo(0)->mode(0)->speed(8_000_000)->register()->device(0, 0);

$pins = DigitalIO::driver('native')->connectTo(0)->register();
$dc = $pins->output(0, 24);
$rst = $pins->output(0, 25);

$panel = new SSD1306(new SSD1306SPITransport($spi, $dc, $rst), new SSD1306Configuration, boot_now: true);
```

At boot, RST is pulsed high, low, high, 3 ms each. Commands go out with DC low and data with DC high.

### From the published config

The config file holds your wiring. The package merges it but does not open connections from it, so read it where you build the panel:

```php
use GeneralPurposeIO\I2C\I2C;

$name = config('circuits.ssd1306.default_config');      // 'i2c'
$wiring = config("circuits.ssd1306.configs.{$name}");

$slave = I2C::driver($wiring['driver'])
    ->connectTo($wiring['device'])
    ->register()
    ->device($wiring['device'], $wiring['slave']);
```

## Configuration object

`SSD1306Configuration` describes the panel and holds its current settings. Every argument is optional.

| Argument | Default | Meaning |
|---|---|---|
| `width` | `128` | columns |
| `height` | `64` | rows; the multiplex ratio is `height − 1` |
| `contrast` | `191` | 0 to 255 |
| `start_line` | `0` | first RAM row shown, 0 to 63 |
| `display_offset` | `0` | vertical shift, 0 to 63 |
| `max_packet_size` | `1024` | largest data write, in bytes |
| `invert_display` | `false` | lit pixels become dark |
| `enable_com_lr_remap` | `false` | COM pins left/right remap bit |
| `sequential_com_pin_config` | `true` | COM pins bit 4, see [COM pins](#com-pins) |
| `powered_by_host_device` | `true` | `true` sends pre-charge `0xF1`, `false` sends `0x22` |
| `map_line_0_to_line_127` | `false` | segment remap, mirrors horizontally |
| `reverse_line_scan_direction` | `false` | COM scan direction, mirrors vertically |
| `v_com_h` | `LEVEL_077_ALT` | VCOMH deselect level (`0x40`) |
| `addressing_mode` | `HORIZONTAL_ADDRESSING_MODE` | how RAM writes advance |

Read or change a value with `$panel->config()->get('contrast')` and `->set('contrast', 32)`. Use the panel's setters to change the chip itself; they update the configuration as they go.

### COM pins

The COM pins byte is `0x02` with bit 4 set by `sequential_com_pin_config` and bit 5 by `enable_com_lr_remap`. The defaults give `0x12`, which is what 128×64 panels need. 128×32 panels need `0x02`:

```php
new SSD1306Configuration(width: 128, height: 32, sequential_com_pin_config: false);
```

## Drawing

The panel does not keep pixels. You send it bytes already packed the way `formatSpec()` describes:

```php
$spec = $panel->formatSpec();
// pixel_format MONO_VERTICAL_PAGE, bit_depth B1, bit_order LSB_FIRST, page_axis VERTICAL
```

Each byte is one column of one 8-row page. Bit 0 is the top row of that page. Pages run top to bottom, and within a page columns run left to right. A full 128×64 frame is therefore 128 × 8 = 1024 bytes:

```php
$bytes = [];
for ($page = 0; $page < intdiv($panel->height() + 7, 8); $page++) {
    for ($x = 0; $x < $panel->width(); $x++) {
        $byte = 0;
        for ($bit = 0; $bit < 8; $bit++) {
            if (lit($x, $page * 8 + $bit)) {
                $byte |= 1 << $bit;
            }
        }
        $bytes[] = $byte;
    }
}

$panel->transmit(0, 0, $bytes);
```

`transmit($x, $y, $bytes, $width, $height)` points the panel's write window at the rectangle, then streams the bytes into it. Width and height default to the whole panel. The window is page-aligned, so `$y` and `$height` round to whole 8-row pages. To redraw part of the screen, send only that window:

```php
// columns 0–127, rows 16–47 (pages 2–5)
$panel->transmit(0, 16, $four_pages_of_bytes, 128, 32);
```

On a Raspberry Pi 5 over native I2C, a full frame takes about 28 ms and four pages about 15 ms.

`transmit()` takes the same bytes in every addressing mode. In horizontal and vertical mode it opens the window and streams them, reordering column by column for vertical. In page mode it places each page and sends that page's row. On a Raspberry Pi 5, a full frame takes about 28 ms in horizontal and vertical mode and 31 ms in page mode.

## Settings

```php
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306AddressingMode;

$panel->contrast = 0x40;
$panel->invert_display = true;
$panel->toggle_fill_overlay = true;     // light every pixel, RAM untouched
$panel->toggle_fill_overlay = false;    // back to RAM contents
$panel->offset = 4;
$panel->display_on = false;             // sleep
$panel->display_on = true;
$panel->addressing_mode = SSD1306AddressingMode::PAGE_ADDRESSING_MODE;
```

The driver never reads the chip, so every read returns the value the configuration holds.

| Read | Write | Type |
|---|---|---|
| `display_on` | `display_on` | `bool` |
| `contrast` | `contrast` | `int` 0–255 |
| `offset` | `offset` | `int` 0–63 |
| `start_line` | | `int` |
| `addressing_mode` | `addressing_mode` | `SSD1306AddressingMode` |
| `fill_overlay_on` | `toggle_fill_overlay` | `bool` |
| | `invert_display` | `bool` |
| `charge_pump` | `charge_pump_regulator` | `bool` |
| `flip_line_0_and_127` | `segment_remap` | `bool` |
| `flip_line_scan_dir` | `reverse_com_scan_dir` | `bool` |
| `com_pins_config` | `com_pins_hw_config` | `SSD1306COMPinsHWConfig` |
| `powered_by_host_device` | `powered_by_host_device` | `bool` |
| `v_com_h` | `v_com_h` | `SSD1306VoltageCommonHigh` |

The same settings are available as methods: `displayOn()`, `displayOff()`, `setDisplay()`, `setContrast()`, `setDisplayOffset()`, `setDisplayStartLine()`, `setMultiplexRatio()`, `setChargePumpRegulator()`, `setMemoryAddressingMode()`, `setSegmentRemap()`, `setCOMOutputScanDirection()`, `setCOMPinsHardwareConfiguration()`, `setPrechargePeriod()`, `setVoltageCommonHigh()`, `setFillOverlay()`, `setInvertDisplay()`, `setDataClockOscillationFrequency()`, `setAddressWindow()`, `setPagePosition()` and `unsetScroll()`.

## Errors

Failures throw `DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306Exception`, which extends the framework's `GPIOLevelException`:

- A contrast, offset, start line or multiplex value is out of range.
- Your code sets `SSD1306AddressingMode::INVALID`.
- Your code reads or writes a property or configuration key that doesn't exist.

## Closing

```php
$panel->close();
```

On SPI, `close()` releases the DC and RST pins. The bus connection belongs to the protocol driver and stays open for other devices on it.

## Configuration file

`config/circuits/ssd1306.php`:

| Key | Default | Meaning |
|---|---|---|
| `default_config` | `'i2c'` | which entry under `configs` to use |
| `configs.i2c.driver` | `'none'` | I2C adapter: `native` or `usb` |
| `configs.i2c.device` | `''` | a bus number, or `ft232h` |
| `configs.i2c.slave` | `0x3C` | panel address |
| `configs.spi.driver` | `'none'` | SPI adapter |
| `configs.spi.device` | `''` | SPI master |
| `configs.spi.chip_select` | `0` | chip select |
| `configs.spi.dc` / `rst` | pins 0 / 1 | `driver`, `device`, `pin` for each line |

## Testing

```bash
composer install
vendor/bin/pest
```

The suite runs against recording fakes of the I2C bus, SPI bus and pins, so it needs no hardware.

## License

MIT. See [LICENSE](LICENSE).
