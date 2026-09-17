---
type: Trap
title: COM pins flag is named opposite the datasheet
description: sequential_com_pin_config true sets bit 4 of the COM pins byte, which the datasheet calls the alternative pin configuration.
tags: [trap, com-pins, configuration]
status: draft
generated: { by: claude-opus-5/claude-code, at: "2026-09-16T00:00:00Z" }
sources:
  - id: com
    resource: src/Breakouts/SSD1306COMPinsHWConfig.php
    title: SSD1306COMPinsHWConfig
  - id: datasheet
    resource: https://cdn-shop.adafruit.com/datasheets/SSD1306.pdf
    title: SSD1306 datasheet, 0xDA
---

# Trap

Datasheet 0xDA bit 4: 0 = sequential, 1 = alternative.[^datasheet] Breakout writes bit 4 = `sequential_com_pin_config`.[^com] Default `true` → 0x12 = alternative = correct for 128×64. 128×32 wants sequential (0x02) → pass `false`.

Wrong value → every other row blank or doubled.

[^com]: SSD1306COMPinsHWConfig
[^datasheet]: SSD1306 datasheet, 0xDA
