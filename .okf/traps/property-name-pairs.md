---
type: Trap
title: Settings read and write under different names
description: Several SSD1306 settings use one magic property name to read and another to write.
tags: [trap, properties]
status: draft
generated: { by: claude-opus-5/claude-code, at: "2026-09-16T00:00:00Z" }
sources:
  - id: bootstrap
    resource: src/Concerns/SSD1306Bootstrap.php
    title: SSD1306Bootstrap
---

# Trap

Pairs (read → write): `fill_overlay_on` → `toggle_fill_overlay`; `charge_pump` → `charge_pump_regulator`; `flip_line_0_and_127` → `segment_remap`; `flip_line_scan_dir` → `reverse_com_scan_dir`; `com_pins_config` → `com_pins_hw_config`. `invert_display` write-only; `start_line` read-only.[^bootstrap] Wrong half → `invalidProperty`.

# Use instead

Table in [settings](/settings.md), or setter methods; `config()->get()` for any current value.

[^bootstrap]: SSD1306Bootstrap
