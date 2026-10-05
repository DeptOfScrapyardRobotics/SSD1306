---
type: Configuration
title: Wiring config
description: circuits.ssd1306 config keys, how conjure() reads them, how the provider merges them, and the ssd1306-config publish tag.
resource: config/ssd1306.php
tags: [config, circuits, publish, provider, conjure]
status: draft
generated: { by: claude-opus/5.5, at: 2026-10-04T21:10:00Z }
sources:
  - id: provider
    resource: src/Providers/SSD1306ServiceProvider.php
    title: SSD1306ServiceProvider
  - id: config
    resource: config/ssd1306.php
    title: ssd1306 config
---

# Merge + publish

`register()`: `config/ssd1306.php` → `circuits.ssd1306`; app values win. `boot()`: tag `ssd1306-config` → `config/circuits/ssd1306.php`; `circuit` bound → `addCircuit('ssd1306', SSD1306::class)`.[^provider]

```bash
php computer vendor:publish --tag=ssd1306-config
```

# Schema

| Key | Type | Default |
|---|---|---|
| `default_config` | string | `'i2c'` |
| `configs.<name>.protocol` | `'i2c'` \| `'spi'` | the config's name |
| `configs.i2c.driver` / `device` | string / string\|int | `'none'` / `''` |
| `configs.i2c.slave` | int | 0x3C |
| `configs.spi.driver` / `device` | string / string\|int | `'none'` / `''` |
| `configs.spi.chip_select` | int | 0 |
| `configs.spi.speed` | int Hz | 10 000 000 |
| `configs.spi.dc` / `rst` | `{driver, device, pin}` | pins 0 / 1 |
| `configs.*.panel` | `SSD1306Configuration` ctor args by name | `{width: 128, height: 64}` |
| `configs.*.boot_now` | bool | true (factory default) |

`panel` key the constructor does not take → `invalidProperty`.[^config] `protocol` lets an app keep two panels: `left`, `right`, each `'protocol' => 'spi'`.

# Related

* [connecting](/connecting.md) · [configuration-object](/configuration-object.md)

[^provider]: SSD1306ServiceProvider
[^config]: ssd1306 config
