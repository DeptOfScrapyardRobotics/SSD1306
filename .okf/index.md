---
okf_version: "0.2"
---

# dept-of-scrapyard-robotics/ssd1306 Knowledge Bundle

Package knowledge for `dept-of-scrapyard-robotics/ssd1306` (SSD1306 OLED driver, v0.7.x).
Read this index first; open only the concepts needed for the task.

**Trust rule:** Prefer `status: stable`. Treat `deprecated` as historical only. New agent-written concepts stay `status: draft` until a human verifies them.
**Placement:** Package-root `.okf/` only — never under `src/`.
**Links:** Concept cross-links use paths relative to each file.
**Scope:** This package’s IC surface, Circuits catalog registration, profiles, and smoke sketch. Registry semantics live in `scrapyard-io/gpio-framework` — do not duplicate that bundle here.
**Dist note:** `.okf/` and root `AGENTS.md` are `export-ignore` in `.gitattributes`.

# Orientation

* [Package (0.7)](orientation/package.md) - Composer identity, namespace, provider, dependencies.

# Core

* [SSD1306 IC](core/ssd1306.md) - DisplayPanel class, attributes, factories, tubes FormatSpec/transmit.
* [Circuits integration](core/circuits.md) - Catalog slug, make-profile, profiles, smoke sketch.

# Traps

* [Fabricate leftovers](traps/fabricate-leftovers.md) - Use GeneralPurposeIO Circuits + tubes framebuffers; not Fabricate Displays/Circuits.

# Log

* [Directory update log](log.md)
