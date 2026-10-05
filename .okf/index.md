---
okf_version: "0.2"
---

# dept-of-scrapyard-robotics/ssd1306

SSD1306 monochrome OLED driver for `scrapyard-io/framework` 0.10. I2C or SPI, conjured from config, datasheet boot from a configuration object, checked writes, page-windowed frame writes, `FormatSpec` for Surface framebuffers.

Read this index first, open only concepts task needs. Every concept `status: draft` until human verifies.

# Concepts

* [Package](overview.md) - SSD1306 OLED panel driver for scrapyard-io/framework 0.10 — identity, requires, classes, boot sequence, errors.
* [Connecting](connecting.md) - conjure() and the i2c() / spi() factories, sharing a bus, SPI mode and clock, DC and RST, building the transport by hand.
* [Configuration object](configuration-object.md) - SSD1306Configuration fields and defaults, fromArray(), get/set by key, COM pins byte.
* [Drawing](drawing.md) - FormatSpec, packing frames with a Surface framebuffer, transmit() windows per addressing mode, live timings.
* [Settings](settings.md) - Magic properties under their configuration key names, the setters behind them, where state lives.
* [Wiring config](wiring-config.md) - circuits.ssd1306 keys, how conjure() reads them, provider merge, publish tag.

# Runbooks

* [Hardware smoke](runbooks/hardware-smoke.md) - Pi 5 over I2C, FT232H over SPI, scratch script booted through the real providers.

# Log

* [log.md](log.md)
