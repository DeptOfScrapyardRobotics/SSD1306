# dept-of-scrapyard-robotics/ssd1306 Update Log

## 2026-10-04
* **Update**: 0.10 port. [overview](/overview.md): requires 0.10 split components plus `venusian-surface/contracts` and `venusian-voyager/vessel`; catalog slug; every bus write checked; new exceptions.
* **Update**: [connecting](/connecting.md) rewritten for `conjure()` and the `i2c()` / `spi()` factories (SPI mode 0 or 3, ≤ 10 MHz, DC and RST after the bus). [wiring-config](/wiring-config.md): `spi.speed`, `panel`, `protocol`, `boot_now`.
* **Update**: [settings](/settings.md): one name per setting, read and write, equal to its configuration key. [configuration-object](/configuration-object.md): `alternative_com_pins` replaces `sequential_com_pin_config`, defaults from height; `fromArray()`.
* **Update**: [drawing](/drawing.md): packing through a Surface framebuffer; `FormatSpecification` gone from Surface 0.10; live reference from both benches.
* **Removal**: the three 0.8 warning notes (COM pins naming, read/write property name pairs, unchecked writes): the code now names bit 4 for what it does, uses one property name per setting, and throws on a failed write.
* **Creation**: [hardware smoke](/runbooks/hardware-smoke.md) runbook.

## 2026-09-18
* **Update**: [overview](/overview.md) — `SSD1306` also implements `WindowAddressable` (transmit honours the window) and `Switchable` (`setDisplay(bool)`), the new `DisplayPanel` children in gpio/contracts; `DisplayPanel` itself now carries `transmit()` and extends `FormatSpecification`. Pinned by a contract test in `tests/SSD1306Test.php`; suite 26 passed.

## 2026-09-16
* **Update**: [drawing](/drawing.md) transmit() works in horizontal, vertical and page addressing with one FormatSpec; [overview](/overview.md) `invalidAddressingMode` replaces the FormatSpec exception.
* **Removal**: traps/horizontal-addressing-only (no longer true).
* **Creation**: bundle seeded for 0.8.0 — [overview](/overview.md), [connecting](/connecting.md), [configuration-object](/configuration-object.md), [drawing](/drawing.md), [settings](/settings.md), [wiring-config](/wiring-config.md), four [traps](/traps/index.md).
