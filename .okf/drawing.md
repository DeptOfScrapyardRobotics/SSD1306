---
type: Guide
title: Drawing frames
description: How to pack bytes for the panel from its FormatSpec and write them into a column/page window with transmit().
tags: [drawing, formatspec, framebuffer, transmit]
status: draft
generated: { by: claude-opus-5/claude-code, at: "2026-09-16T00:00:00Z" }
sources:
  - id: panel
    resource: src/SSD1306.php
    title: SSD1306::transmit() / formatSpec()
  - id: api
    resource: src/Concerns/SSD1306API.php
    title: SSD1306API::setAddressWindow() / setPagePosition()
  - id: formatspec
    resource: venusian/surface:src/Surface/Contracts/Framebuffers/FormatSpec.php
    title: Surface FormatSpec
---

# FormatSpec

`formatSpec()` → `MONO_VERTICAL_PAGE`, `B1`, `TOP_TO_BOTTOM`, `LSB_FIRST`, `PageAxis::VERTICAL`. One spec for every addressing mode; caller packs the same bytes whatever the mode.[^panel] Type from `surface/contracts`.[^formatspec]

# Packing

1 byte = 1 column × 1 page (8 rows). Bit 0 = page's top row. Order: page 0..N, within page column 0..W. 128×64 = 1024 bytes.

```php
for ($page = 0; $page < intdiv($h + 7, 8); $page++) {
    for ($x = 0; $x < $w; $x++) {
        $byte = 0;
        for ($bit = 0; $bit < 8; $bit++) {
            if (lit($x, $page * 8 + $bit)) {
                $byte |= 1 << $bit;
            }
        }
        $bytes[] = $byte;
    }
}
```

# transmit()

`transmit(int $origin_x, int $origin_y, array $raw_data, ?int $frame_width = null, ?int $frame_height = null)`, per addressing mode:[^panel]

| Mode | Traffic |
|---|---|
| horizontal | `setAddressWindow()` → `data(bytes)` |
| vertical | `setAddressWindow()` → `data(bytes reordered column-major)` |
| page | per page: `setPagePosition(x, page)` (`0x0L`, `0x1H`, `0xB0 \| page`) → `data(row)` |

Window:[^api] `21 x, x+w-1` · `22 y>>3, (y+h-1)>>3`. Rows page-aligned. Byte count = w × pages. `INVALID` mode refused by `setMemoryAddressingMode()`.

# Live reference

Pi 5 native I2C, 1024-byte packets: full frame 28 ms horizontal / 28 ms vertical / 31 ms page; 40×24 window 4–5 ms.

# Related

* [connecting](/connecting.md) · [settings](/settings.md)

[^panel]: SSD1306::transmit() / formatSpec()
[^api]: SSD1306API::setAddressWindow() / setPagePosition()
[^formatspec]: Surface FormatSpec
