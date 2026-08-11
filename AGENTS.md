# Agent guidelines — dept-of-scrapyard-robotics/ssd1306

## Knowledge Bundle (OKF)

This package ships an Open Knowledge Format bundle at [`.okf/`](.okf/) (excluded from Composer dist via `.gitattributes` `export-ignore`).

Before changing this package or advising on SSD1306 architecture:

1. Read [`.okf/index.md`](.okf/index.md) first (progressive disclosure).
2. Open only the linked concepts needed for the task.
3. Prefer `status: stable` concepts; treat `deprecated` as historical only. New/changed concepts stay `status: draft` until a human verifies them.
4. When you learn something durable about **this package**, update the affected `.okf` concept(s) and append `.okf/log.md`.
5. Keep the `.okf` bundle at the **package root** only — do not nest extra `.okf` folders under `src/`.
6. Circuits registry semantics belong in `scrapyard-io/gpio-framework`’s `.okf`; tubes window/rendering knowledge belongs in `scrapyard-io/tubes`.

## Package rules (quick) — 0.7.x

- Composer: `dept-of-scrapyard-robotics/ssd1306` **0.7.0**. Namespace `DeptOfScrapyardRobotics\Displays\SSD1306\`.
- Provider: `SSD1306ServiceProvider` at package root. Catalog slug `ssd1306`. Command `ssd1306:make-profile` (delegated from `circuit:make-profile`). Sketch `ssd1306-smoke`.
- IC extends `GeneralPurposeIO\Circuits\DisplayPanel`, implements `BootSequence`; factories `i2c(...)` / `spi(...)`.
- Frame contract: tubes `FormatSpec` / `DumpedBuffer` — not Fabricate Displays/Framebuffers.
- Breakouts use `GeneralPurposeIO\Circuits\DataRegister`; boot uses `BootScaffolding`.
- Requires leaf components (not kitchen-sink frameworks): `fabricate/nuts-and-bolts`, `gpio/circuits`, `gpio/contracts`, `gpio/digital`, `gpio/i2c`, `gpio/spi`, `tubes/contracts`.
