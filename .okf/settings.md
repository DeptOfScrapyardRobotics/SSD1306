---
type: Reference
title: Panel settings
description: Magic property names for reading and writing SSD1306 settings, the setter methods behind them, and where state lives.
tags: [settings, properties, contrast, invert, addressing]
status: draft
generated: { by: claude-opus-5/claude-code, at: "2026-09-16T00:00:00Z" }
sources:
  - id: bootstrap
    resource: src/Concerns/SSD1306Bootstrap.php
    title: SSD1306Bootstrap __get / __set
  - id: api
    resource: src/Concerns/SSD1306API.php
    title: SSD1306API
---

# State

Setters write chip, then configuration. Reads come from configuration only; driver never reads chip.[^api]

# Properties

| Read | Write | Setter |
|---|---|---|
| `display_on` | `display_on` | `setDisplay` / `displayOn` / `displayOff` |
| `contrast` | `contrast` | `setContrast` 0–255 |
| `offset` | `offset` | `setDisplayOffset` 0–63 |
| `start_line` | — | `setDisplayStartLine` 0–63 |
| `addressing_mode` | `addressing_mode` | `setMemoryAddressingMode` |
| `fill_overlay_on` | `toggle_fill_overlay` | `setFillOverlay` (A5 / A4) |
| — | `invert_display` | `setInvertDisplay` (A7 / A6) |
| `charge_pump` | `charge_pump_regulator` | `setChargePumpRegulator` |
| `flip_line_0_and_127` | `segment_remap` | `setSegmentRemap` (A1 / A0) |
| `flip_line_scan_dir` | `reverse_com_scan_dir` | `setCOMOutputScanDirection` (C8 / C0) |
| `com_pins_config` | `com_pins_hw_config` | `setCOMPinsHardwareConfiguration` |
| `powered_by_host_device` | `powered_by_host_device` | `setPrechargePeriod` (F1 / 22) |
| `v_com_h` | `v_com_h` | `setVoltageCommonHigh` |

Unknown name → `invalidProperty`.[^bootstrap] Also: `setMultiplexRatio` 16–63, `setDataClockOscillationFrequency`, `setAddressWindow`, `unsetScroll`, `getAddressingMode`.

# Related

* [traps/property-name-pairs](/traps/property-name-pairs.md) · [configuration-object](/configuration-object.md)

[^bootstrap]: SSD1306Bootstrap __get / __set
[^api]: SSD1306API
