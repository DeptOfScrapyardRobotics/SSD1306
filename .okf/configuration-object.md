---
type: Reference
title: SSD1306Configuration
description: The panel's geometry and settings object — constructor fields and defaults, fromArray(), get/set by key, COM pins byte.
resource: src/SSD1306Configuration.php
tags: [configuration, settings, com-pins]
status: draft
generated: { by: claude-opus/5.5, at: 2026-10-04T21:10:00Z }
sources:
  - id: config
    resource: src/SSD1306Configuration.php
    title: SSD1306Configuration
  - id: com
    resource: src/Breakouts/SSD1306COMPinsHWConfig.php
    title: SSD1306COMPinsHWConfig
---

# Fields

Ctor, all optional:[^config]

| Field | Default |
|---|---|
| `width` | 128 |
| `height` | 64 |
| `contrast` | 191 |
| `start_line` | 0 |
| `display_offset` | 0 |
| `max_packet_size` | 1024 |
| `invert_display` | false |
| `enable_com_lr_remap` | false |
| `powered_by_host_device` | true |
| `map_line_0_to_line_127` | false |
| `alternative_com_pins` | null → by height |
| `reverse_line_scan_direction` | false |
| `v_com_h` | `LEVEL_077_ALT` (0x40) |
| `addressing_mode` | `HORIZONTAL_ADDRESSING_MODE` |

Internal, not ctor: `display_on` false, `charge_pump` true, `fill_overlay_on` false, `com_pins_config` built from `enable_com_lr_remap` + `alternative_com_pins`.

# fromArray

`SSD1306Configuration::fromArray(array $panel)`: ctor args by name, as a config entry's `panel`. Unknown key → `invalidProperty`.

# get / set

`get(key)` / `set(key, value)` on declared fields; unknown key → `invalidProperty`. `set` does not touch chip — panel setters do that, then `set`.

# COM pins byte

0xDA = `00 remap alt 0010`.[^com] Bit 4: 1 alternative, 0 sequential. `alternative_com_pins` null → alternative above 32 rows (128×64 → 0x12), sequential at 32 or fewer (128×32, 96×16 → 0x02). Wrong layout blanks or doubles every other row; pass a bool for a panel that differs.

# Related

* [settings](/settings.md) · [wiring-config](/wiring-config.md)

[^config]: SSD1306Configuration
[^com]: SSD1306COMPinsHWConfig
