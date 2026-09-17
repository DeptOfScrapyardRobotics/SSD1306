---
type: Reference
title: SSD1306Configuration
description: The panel's size and settings object — constructor fields and defaults, get/set by key, derived COM pins breakout.
resource: src/SSD1306Configuration.php
tags: [configuration, settings, com-pins]
status: draft
generated: { by: claude-opus-5/claude-code, at: "2026-09-16T00:00:00Z" }
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
| `sequential_com_pin_config` | true |
| `reverse_line_scan_direction` | false |
| `v_com_h` | `LEVEL_077_ALT` (0x40) |
| `addressing_mode` | `HORIZONTAL_ADDRESSING_MODE` |

Internal, not ctor: `display_on` false, `charge_pump` true, `fill_overlay_on` false, `com_pins_config` built from `enable_com_lr_remap` + `sequential_com_pin_config`.

# get / set

`get(key)` / `set(key, value)` on declared fields; unknown key → `invalidProperty(key)`. `set` does not touch chip — panel setters do that.

# COM pins byte

`00 remap seq 0010`.[^com] Defaults → 0x12 (128×64). `sequential_com_pin_config: false` → 0x02 (128×32).

# Related

* [settings](/settings.md) · [traps/com-pins-naming](/traps/com-pins-naming.md)

[^config]: SSD1306Configuration
[^com]: SSD1306COMPinsHWConfig
