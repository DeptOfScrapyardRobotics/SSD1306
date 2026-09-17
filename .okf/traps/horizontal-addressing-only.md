---
type: Trap
title: transmit() windows need horizontal addressing
description: The column and page window commands transmit() sends apply in horizontal addressing mode; page mode ignores them and vertical mode is refused.
tags: [trap, drawing, addressing]
status: draft
generated: { by: claude-opus-5/claude-code, at: "2026-09-16T00:00:00Z" }
sources:
  - id: api
    resource: src/Concerns/SSD1306API.php
    title: SSD1306API::setAddressWindow()
  - id: panel
    resource: src/SSD1306.php
    title: SSD1306::generateFormatSpec()
---

# Trap

`setAddressWindow()` sends 0x21 / 0x22, which the chip honours in horizontal and vertical modes only.[^api] Page mode is accepted by `setMemoryAddressingMode()` (FormatSpec generated) but ignores the window → bytes land at page mode's own pointer. Vertical → `unsupportedAddressingModeForFormatSpec`, after the mode command already went out.[^panel]

# Use instead

Stay in horizontal mode (boot default) for `transmit()`.

[^api]: SSD1306API::setAddressWindow()
[^panel]: SSD1306::generateFormatSpec()
