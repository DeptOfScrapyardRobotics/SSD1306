---
type: Configuration
title: Wiring config
description: circuits.ssd1306 config keys, how the provider merges them, and the ssd1306-config publish tag.
resource: config/ssd1306.php
tags: [config, circuits, publish, provider]
status: draft
generated: { by: claude-opus-5/claude-code, at: "2026-09-16T00:00:00Z" }
sources:
  - id: provider
    resource: src/Providers/SSD1306ServiceProvider.php
    title: SSD1306ServiceProvider
  - id: config
    resource: config/ssd1306.php
    title: ssd1306 config
---

# Merge + publish

`register()`: `config/ssd1306.php` → `circuits.ssd1306`; app values win. `boot()`: tag `ssd1306-config` → `config/circuits/ssd1306.php`, loader keys it the same.[^provider]

```bash
php computer vendor:publish --tag=ssd1306-config
```

# Schema

| Key | Type | Default |
|---|---|---|
| `default_config` | string | `'i2c'` |
| `configs.i2c.driver` / `device` | string / string\|int | `'none'` / `''` |
| `configs.i2c.slave` | int | 0x3C |
| `configs.spi.driver` / `device` | string / string\|int | `'none'` / `''` |
| `configs.spi.chip_select` | int | 0 |
| `configs.spi.dc` / `rst` | `{driver, device, pin}` | pins 0 / 1 |

Package reads none of it; app wires panel from it.[^config] Separate from `SSD1306Configuration`, which holds panel settings.

# Related

* [connecting](/connecting.md)

[^provider]: SSD1306ServiceProvider
[^config]: ssd1306 config
