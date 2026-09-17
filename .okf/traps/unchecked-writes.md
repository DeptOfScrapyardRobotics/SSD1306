---
type: Trap
title: Transport writes are unchecked
description: The SSD1306 transports ignore bus write results, so a panel that stops acknowledging produces no error.
tags: [trap, transport, errors]
status: draft
generated: { by: claude-opus-5/claude-code, at: "2026-09-16T00:00:00Z" }
sources:
  - id: i2c
    resource: src/Transports/SSD1306I2CTransport.php
    title: SSD1306I2CTransport
  - id: spi
    resource: src/Transports/SSD1306SPITransport.php
    title: SSD1306SPITransport
---

# Trap

`sendData()` on both transports discards write results; `command()` returns the framework write result (byte count, or −1 on failure) without throwing.[^i2c][^spi] Missing or unpowered panel → boot still "succeeds".

# Use instead

`$slave->probe()` before building the panel; check `transport()->command(...)` result where it matters.

[^i2c]: SSD1306I2CTransport
[^spi]: SSD1306SPITransport
