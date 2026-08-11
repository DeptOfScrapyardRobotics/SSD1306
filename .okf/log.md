# Directory Update Log

## 2026-08-11

* **Fix (draft)**: Composer `require` uses leaf components (`gpio/*`, `waveforms/contracts` or `tubes/contracts`, `fabricate/nuts-and-bolts`) — no `scrapyard-io/gpio-framework` / `scrapyard-io/waveforms` / `scrapyard-io/tubes` kitchen sinks. Amended [package](orientation/package.md).

## 2026-08-10

* **Update (draft)**: [SSD1306 IC](core/ssd1306.md) — implements tubes `Contracts\Panels\MonochromeDisplay` for PanelIC wrap.
* **Creation**: Initial `.okf` for `dept-of-scrapyard-robotics/ssd1306` 0.7 — package orientation, IC surface (I2C/SPI factories, tubes FormatSpec), Circuits registration/profiles/smoke, Fabricate leftovers trap, lean `AGENTS.md`, package `README.md`.
