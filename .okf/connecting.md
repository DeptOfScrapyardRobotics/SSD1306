---
type: Guide
title: Connecting an SSD1306
description: Build the panel over I2C or SPI from the framework's protocol managers; control bytes, DC and RST, addresses.
tags: [i2c, spi, transport, dc, rst]
status: draft
generated: { by: claude-opus-5/claude-code, at: "2026-09-16T00:00:00Z" }
sources:
  - id: i2c
    resource: src/Transports/SSD1306I2CTransport.php
    title: SSD1306I2CTransport
  - id: spi
    resource: src/Transports/SSD1306SPITransport.php
    title: SSD1306SPITransport
  - id: base
    resource: src/Transports/SSD1306DataTransport.php
    title: SSD1306DataTransport
  - id: address
    resource: src/Enums/SSD1306I2CAddress.php
    title: SSD1306I2CAddress
---

# Shape

MagicAlias (`I2C::` / `SPI::` / `DigitalIO::`) → `driver()` → `connectTo()` → `register()` → `device()` / `output()` → SSD1306 transport → panel.

# I2C

Address by SA0: `SAO_GROUNDED` 0x3C, `SAO_ENERGIZED` 0x3D.[^address]

```php
$slave = I2C::driver('native')->connectTo(1)->register()->device(1, 0x3C);
$panel = new SSD1306(new SSD1306I2CTransport($slave), new SSD1306Configuration, boot_now: true);
```

Command write = `[0x00, register, ...args]`. Data write = `[0x40, ...chunk]`, chunk ≤ `max_packet_size`; string data unpacked first.[^i2c] `reset()` no-op.

# SPI

```php
$spi = SPI::driver('native')->connectTo(0)->mode(0)->speed(8_000_000)->register()->device(0, 0);
$pins = DigitalIO::driver('native')->connectTo(0)->register();
$panel = new SSD1306(new SSD1306SPITransport($spi, $pins->output(0, 24), $pins->output(0, 25)), new SSD1306Configuration, boot_now: true);
```

Command: DC low, write `[register, ...args]`. Data: DC high per chunk. `reset()`: RST high → low → high, 3 ms each. `close()` closes DC + RST.[^spi]

# Packet size

Base transport holds `max_packet_size` (ctor default 1024); boot overwrites it from configuration.[^base]

# Related

* [overview](/overview.md) · [wiring-config](/wiring-config.md) · [traps/unchecked-writes](/traps/unchecked-writes.md)

[^i2c]: SSD1306I2CTransport
[^spi]: SSD1306SPITransport
[^base]: SSD1306DataTransport
[^address]: SSD1306I2CAddress
