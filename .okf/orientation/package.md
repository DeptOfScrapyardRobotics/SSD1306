---
type: Module
title: Package (0.7)
description: dept-of-scrapyard-robotics/ssd1306 Composer identity, namespace, and discovery.
resource: composer.json
tags: [orientation, package, 0.7, ssd1306]
generated: { by: cursor-agent/grok-4.5, at: "2026-08-11T00:32:00Z" }
verified: { by: null, at: null }
status: draft
sources:
  - id: composer
    resource: composer.json
    title: Package composer.json
  - id: provider
    resource: src/SSD1306ServiceProvider.php
    title: SSD1306ServiceProvider
  - id: gitattributes
    resource: .gitattributes
    title: Dist export-ignore
---

# Identity

| Field | Value |
|-------|-------|
| Composer | `dept-of-scrapyard-robotics/ssd1306` **0.7.0** |
| PHP | `^8.4\|^8.5\|^8.6` |
| Namespace | `DeptOfScrapyardRobotics\Displays\SSD1306\` → `src/` |
| Provider | `DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306ServiceProvider` (package root, not `Providers/`) |
| Catalog slug | `ssd1306` |

# Requires

| Package | Constraint |
|---------|------------|
| `fabricate/nuts-and-bolts` | `^0.7.0` |
| `gpio/circuits` | `^0.7.0` |
| `gpio/contracts` | `^0.7.0` |
| `gpio/digital` | `^0.7.0` |
| `gpio/i2c` | `^0.7.0` |
| `gpio/spi` | `^0.7.0` |
| `tubes/contracts` | `^0.7.0` |

Suggested (optional): `ext-posi`, `ext-ftdi`, `microscrap/posix`, `microscrap/mpsse` at `^0.7.0`.[^composer]

# Discovery

`extra.scrapyard-io.providers` lists `SSD1306ServiceProvider`. That provider registers the catalog IC, profile command, and `ssd1306-smoke` sketch.[^provider]

# Dist

`.okf/` and `AGENTS.md` are `export-ignore` — Composer dist tarballs omit them.[^gitattributes]

# Related

* [SSD1306 IC](../core/ssd1306.md)
* [Circuits integration](../core/circuits.md)

[^composer]: Package composer.json
[^provider]: SSD1306ServiceProvider
[^gitattributes]: Dist export-ignore
