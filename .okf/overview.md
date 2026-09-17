---
type: Package
title: dept-of-scrapyard-robotics/ssd1306
description: SSD1306 OLED panel driver for scrapyard-io/framework 0.8 — identity, requires, classes, boot sequence, errors.
resource: composer.json
tags: [ssd1306, oled, display, i2c, spi, package]
status: draft
generated: { by: claude-opus-5/claude-code, at: "2026-09-16T00:00:00Z" }
sources:
  - id: composer
    resource: composer.json
    title: Package manifest
  - id: panel
    resource: src/SSD1306.php
    title: SSD1306
  - id: bootstrap
    resource: src/Concerns/SSD1306Bootstrap.php
    title: SSD1306Bootstrap
  - id: exception
    resource: src/SSD1306Exception.php
    title: SSD1306Exception
  - id: tests
    resource: tests/SSD1306Test.php
    title: panel tests
---

# Identity

| Field | Value |
|---|---|
| Composer | `dept-of-scrapyard-robotics/ssd1306` **0.8.0** |
| PHP | `^8.4\|^8.5\|^8.6` |
| Namespace | `DeptOfScrapyardRobotics\Displays\SSD1306\` → `src/` |
| Provider | `Providers\SSD1306ServiceProvider` (`extra.venusian.providers`) |

# Requires

Split components only.[^composer]

| Package | Why |
|---|---|
| `gpio/contracts` | transport, `DisplayPanel`, `DataCommander` contracts; exception root |
| `gpio/integrated-circuits` | `Bootable`, `DataRegister` |
| `gpio/nuts-and-bolts` | `byte2bits()` in breakouts |
| `surface/contracts` | `FormatSpec` + framebuffer enums |
| `venusian-voyager/nuts-and-bolts` | `ServiceProvider` |

Suggests: `gpio/i2c`, `gpio/spi`, `gpio/digital`, `microscrap/scrapyard-linux` (ext-posi), `microscrap/scrapyard-usb` (ext-ftdi).

# Classes

| Class | Role |
|---|---|
| `SSD1306` | panel; `Bootable` + `DisplayPanel`; class carries `#[FormatSpec]` attribute[^panel] |
| `SSD1306Configuration` | size + every setting; the panel's state |
| `Transports\SSD1306I2CTransport` | wraps `I2CTransport` |
| `Transports\SSD1306SPITransport` | wraps `SPITransport` + DC + RST `DigitalOutTransport` |
| `Breakouts\{DataClock, ChargePump, COMPinsHWConfig, SegmentRemap, COMScanDirection}` | readonly register breakouts |
| `Enums\{OpCode, AddressingMode, VoltageCommonHigh, Precharge, StartLineCommand, I2CAddress}` | typed register values |

# Construct + boot

`new SSD1306(SSD1306DataTransport $transport, SSD1306Configuration $props, bool $boot_now = false)`[^panel]

`boot()` once:[^bootstrap] packet size → transport; reset (SPI RST pulse, I2C no-op); `AE`; `D5 80`; `A8 height-1`; `D3 offset`; `40+start_line`; `8D 14`; `20 mode` (+ FormatSpec); `A0|A1`; `C0|C8`; `DA com_pins`; `81 contrast`; `D9 F1|22`; `DB vcomh`; `A4`; `A6|A7`; `2E`; `AF`.[^tests]

`close()` → transport close → SPI releases DC + RST; I2C nothing.

# Errors

`SSD1306Exception` → `CircuitException` → `GPIOLevelException`.[^exception]

| Factory | When |
|---|---|
| `invalidMux` | multiplex outside 16–63 (height outside 17–64) |
| `invalidOffset` | offset outside 0–63 |
| `invalidStartLine` | start line outside 0–63 |
| `invalidContrast` | contrast outside 0–255 |
| `unsupportedAddressingModeForFormatSpec` | vertical / invalid addressing mode |
| `invalidProperty` | unknown magic property or configuration key |

# Live reference

Pi 5, `i2c-1`, panel 0x3C, native: boot, 1024-byte frame 28.4 ms, 4-page window 14.5 ms, fill overlay, invert, contrast sweep, clear.

# Related

* [connecting](/connecting.md) · [drawing](/drawing.md) · [settings](/settings.md)

[^composer]: Package manifest
[^panel]: SSD1306
[^bootstrap]: SSD1306Bootstrap
[^exception]: SSD1306Exception
[^tests]: panel tests
