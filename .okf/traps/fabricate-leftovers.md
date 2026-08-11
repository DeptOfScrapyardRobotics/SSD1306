---
type: Trap
title: Fabricate leftovers
description: SSD1306 0.7 uses GeneralPurposeIO Circuits and tubes framebuffers — not Fabricate Displays/Circuits leftovers.
tags: [traps, fabricate, circuits, tubes, displays]
generated: { by: cursor-agent/grok-4.5, at: "2026-08-11T00:32:00Z" }
verified: { by: null, at: null }
status: draft
sources:
  - id: ic
    resource: src/SSD1306.php
    title: SSD1306 imports
  - id: breakout
    resource: src/Breakouts/SSD1306ChargePump.php
    title: DataRegister breakout
  - id: internal-api
    resource: src/Concerns/SSD1306InternalAPI.php
    title: BootScaffolding usage
---

# Trap

Do **not** import or revive:

- `Fabricate\Contracts\Circuits\*`
- `Fabricate\Circuits\DataRegister` / Fabricate boot scaffolding
- `Fabricate\Displays\*` or Fabricate framebuffer types for this driver’s pixel contract

# Use instead

| Concern | Correct FQCN |
|---------|----------------|
| Taxonomy base | `GeneralPurposeIO\Circuits\DisplayPanel` |
| Attributes / BootSequence | `GeneralPurposeIO\Contracts\Circuits\Attributes\*`, `BootSequence`, `BootScaffolding` |
| Bit helpers | `GeneralPurposeIO\Circuits\DataRegister` |
| Circuit alias | `GeneralPurposeIO\Core\MagicAliases\Circuit` |
| Frame format / dump | `ScrapyardIO\Tubes\Contracts\Framebuffers\{FormatSpec,DumpedBuffer,…}` |

`SSD1306` already wires tubes `FormatSpec` / `DumpedBuffer` and GeneralPurposeIO Circuits types.[^ic][^breakout][^internal-api]

# Related

* [SSD1306 IC](../core/ssd1306.md)
* [Circuits integration](../core/circuits.md)

[^ic]: SSD1306 imports
[^breakout]: DataRegister breakout
[^internal-api]: BootScaffolding usage
