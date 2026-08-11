# dept-of-scrapyard-robotics/ssd1306 (0.7)

I2C / SPI driver for SSD1306 monochrome OLEDs. Extends `GeneralPurposeIO\Circuits\DisplayPanel`.

## Register

Provider registers catalog slug `ssd1306` and wires `ssd1306:make-profile` into `circuit:make-profile`.

## Profiles

```bash
workshop vendor:publish --tag=gpio-circuits-config
workshop circuit:make-profile          # picks any installed IC; SSD1306 delegates here
workshop ssd1306:make-profile          # SSD1306 only
```

The command asks I2C (`driver` / `device` / `slave`) or SPI+DigitalIO (`chip_select`, `dc` / `rst` pins) from `#[Pinout]`, and always sets `boot_now => true`.

```php
Circuit::profile('oled_front');
```

## Smoke sketch

Requires at least one SSD1306 profile in `config/circuits.php`:

```bash
php workshop runner ssd1306-smoke
php workshop runner ssd1306-smoke --profile=oled_front
```

Provisions only via `Circuit::profile()` — no tubes/window driving. Toggles invert until you Ctrl-C. On USB SPI, DC/RST come from the same MPSSE bus when `canServeDigitalPins()` is true.
