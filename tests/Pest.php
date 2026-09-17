<?php

/*
| Proven against recording fakes: every command and data byte the panel would
| see over I2C or SPI, every DC and RST level. Nothing here touches a bus. The
| live check is the SSD1306 at 0x3C on the Pi 5's i2c-1.
*/
