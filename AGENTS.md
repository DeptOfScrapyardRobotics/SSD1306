# Agent guidelines — dept-of-scrapyard-robotics/ssd1306

## Knowledge Bundle (OKF)

This package ships an Open Knowledge Format bundle at [`.okf/`](.okf/) (excluded from the Composer dist via `.gitattributes` `export-ignore`). Before changing code or advising on this package: read [`.okf/index.md`](.okf/index.md) first, open only the concepts the task needs, prefer `status: stable` over `draft`. When you learn something durable, update the affected concept(s) and append [`.okf/log.md`](.okf/log.md); new or changed concepts stay `status: draft` until a human verifies them.

Do **not** create `.okf` folders under `src/*` — knowledge for this package lives at the package root only. Transport, dock and framebuffer semantics belong to `scrapyard-io/framework` and `venusian/surface`; point there, do not restate them here.

## Where this package sits

`ext-posi` / `ext-ftdi` → `microscrap/*` → `scrapyard-io/framework` (protocol managers, transports) → **`dept-of-scrapyard-robotics/ssd1306`** (panel driver) → Surface CPU engines, which pack frames against the panel's `FormatSpec`.

## Package rules (quick) — 0.8.x

- Composer: `dept-of-scrapyard-robotics/ssd1306` **0.8.0**. PHP `^8.4|^8.5|^8.6`. Namespace `DeptOfScrapyardRobotics\Displays\SSD1306\` → `src/`.
- **Requires split components only**: `gpio/contracts`, `gpio/integrated-circuits`, `gpio/nuts-and-bolts`, `surface/contracts`, `venusian-voyager/nuts-and-bolts`. Never `scrapyard-io/framework`, `venusian/framework` or `venusian/surface`. Protocol components and adapters are `suggest`.
- **Panel = `Bootable` + `DisplayPanel`.** Boot runs the datasheet init sequence from `SSD1306Configuration`. `close()` releases DC and RST on SPI only; bus connections belong to their driver.
- **The panel owns no pixels.** It exposes `formatSpec()` (mono, vertical page, LSB first) and `transmit()` takes bytes already packed to it. Packing lives in Surface.
- **Addressing mode is the panel's problem, never the caller's.** One `FormatSpec` for every mode; `transmit()` places and orders the same bytes for horizontal (window), vertical (window, column-major) and page (`setPagePosition()` per page) mode.
- **`SSD1306Configuration` is the state.** Every setter writes the chip and then the configuration; properties read the configuration, never the chip (the SSD1306 has no readable registers over these transports).
- **Transports** wrap a framework `I2CTransport` (control byte `0x00` command / `0x40` data) or `SPITransport` + DC + RST `DigitalOutTransport`s, chunked by `max_packet_size`.
- **Register breakouts** are `readonly` `DataRegister`s from `gpio/integrated-circuits`.
- **Reach the framework through MagicAliases** (`I2C::`, `SPI::`, `DigitalIO::`), never `app('gpio.*')`.
- **Config** merges under `circuits.ssd1306`; publish tag `ssd1306-config` → `config/circuits/ssd1306.php`. The package reads none of it.
- **Exceptions** descend from `GeneralPurposeIO\Contracts\IntegratedCircuits\CircuitException` → `GPIOLevelException`.
- Enums int- or string-backed, FULLY UPPERCASE cases. No class constants. `is_null($x)` over `$x === null`.

## Verification

```bash
vendor/bin/pest            # recording fakes; no hardware
```

Hardware truth is the SSD1306 at `0x3C` on the Pi 5's `i2c-1` (`fnk`). Announce with `say` before any run that lights the panel.
