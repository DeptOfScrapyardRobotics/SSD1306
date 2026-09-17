---
okf_version: "0.2"
---

# dept-of-scrapyard-robotics/ssd1306 — knowledge bundle

SSD1306 monochrome OLED driver for `scrapyard-io/framework` 0.8. I2C or SPI, datasheet boot from a configuration object, page-windowed frame writes, `FormatSpec` for Surface CPU engines.

Read this index first, open only concepts task needs. Every concept `status: draft` until human verifies.

# Concepts

* [overview.md](/overview.md) - package identity, requires, classes, boot sequence, errors
* [connecting.md](/connecting.md) - I2C and SPI transports, control bytes, DC/RST, addresses
* [configuration-object.md](/configuration-object.md) - SSD1306Configuration fields, get/set, COM pins byte
* [drawing.md](/drawing.md) - FormatSpec, packing, transmit() windows, timings
* [settings.md](/settings.md) - property names, setter methods, state kept in configuration
* [wiring-config.md](/wiring-config.md) - circuits.ssd1306 keys, publish tag

# Traps

* [traps/](/traps/index.md) - COM pins naming, get/set name pairs, unchecked writes

# Log

* [log.md](/log.md)
