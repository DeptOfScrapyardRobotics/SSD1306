---
type: Module
title: Circuits integration
description: Catalog registration, ssd1306:make-profile, profiles, and ssd1306-smoke sketch.
resource: src/SSD1306ServiceProvider.php
tags: [circuits, catalog, profile, smoke, workshop]
generated: { by: cursor-agent/grok-4.5, at: "2026-08-11T00:32:00Z" }
verified: { by: null, at: null }
status: draft
sources:
  - id: provider
    resource: src/SSD1306ServiceProvider.php
    title: SSD1306ServiceProvider
  - id: catalog
    resource: src/Enums/SSD1306CatalogIc.php
    title: SSD1306CatalogIc
  - id: console-enum
    resource: src/Enums/SSD1306ConsoleCommand.php
    title: SSD1306ConsoleCommand
  - id: make-profile
    resource: src/Console/SSD1306MakeProfileCommand.php
    title: ssd1306:make-profile
  - id: smoke
    resource: src/Sketches/SSD1306Smoke.php
    title: ssd1306-smoke
---

# Role

This package **owns the SSD1306 chip driver** and registers it with gpio-framework Circuits. Registry / fluent / profile **semantics** live in `scrapyard-io/gpio-framework` — open that package’s `.okf` for `CircuitRegistry`, `PendingCircuit`, and `circuit:make-profile` behavior.

# Catalog

On `boot()`:[^provider]

```php
Circuit::addCircuit(SSD1306CatalogIc::SSD1306->value, SSD1306::class); // 'ssd1306'
Circuit::registerProfileCommand('ssd1306', 'ssd1306:make-profile');
```

Slug enum: `SSD1306CatalogIc::SSD1306 = 'ssd1306'`.[^catalog]

# Profiles

Publish gpio Circuits config first (from gpio-framework), then scaffold:

```bash
workshop vendor:publish --tag=gpio-circuits-config
workshop circuit:make-profile          # picks any installed IC; SSD1306 delegates here
workshop ssd1306:make-profile          # SSD1306 only
```

`ssd1306:make-profile` uses `ScaffoldsCircuitProfiles` + `CircuitAttributeInspector` — prompts from `#[IntegratedCircuit]` / `#[Pinout]`, writes `config/circuits.php` with `boot_now => true`.[^make-profile]

```php
Circuit::profile('oled_front'); // recipe ic => ssd1306
```

# Smoke sketch

Sketch slug: `ssd1306-smoke` (`#[SketchAttribute('ssd1306-smoke')]`), registered when `SketchRegistry` is bound.[^provider][^smoke]

```bash
php workshop runner ssd1306-smoke
php workshop runner ssd1306-smoke --profile=oled_front
```

Requires at least one profile whose `ic` is `ssd1306`. Provisions **only** via `Circuit::profile()` — toggles invert (~750 ms) until Ctrl-C; closes the panel on shutdown.[^smoke]

# Related

* [SSD1306 IC](ssd1306.md)
* [Package (0.7)](../orientation/package.md)

[^provider]: SSD1306ServiceProvider
[^catalog]: SSD1306CatalogIc
[^make-profile]: ssd1306:make-profile
[^smoke]: ssd1306-smoke
