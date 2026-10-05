<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306;

use GeneralPurposeIO\Contracts\IntegratedCircuits\CircuitException;

class SSD1306Exception extends CircuitException
{
    public static function transportMissingProtocol(): static
    {
        return new static("SSD1306 requires an SPI or an I2C capable connection.");
    }

    public static function missingDigitalPins(): static
    {
        return new static("SSD1306 requires SPI connections to enable DC and RST DigitalOutput pins.");
    }

    public static function incompletePin(string $name): static
    {
        return new static("SSD1306 SPI needs its {$name} pin as driver, device and pin.");
    }

    public static function notConnected(string $protocol, string $driver, string|int $device): static
    {
        return new static("SSD1306 could not get a {$protocol} connection from driver [{$driver}] on device [{$device}].");
    }

    public static function spiClockOutOfRange(int $hz): static
    {
        return new static("SSD1306 SPI clock {$hz} Hz is outside 1 Hz – 10 MHz.");
    }

    public static function wrongSpiMode(string|int $device, int $mode): static
    {
        return new static("SPI bus [{$device}] runs in mode {$mode}; the SSD1306 samples on the rising edge and needs mode 0 or 3.");
    }

    public static function writeFailed(string $what, int $expected, int $written): static
    {
        return new static("SSD1306 {$what} write failed: {$written} of {$expected} bytes. Check the panel's power, wiring and address.");
    }

    public static function invalidPacketSize(int $size, int $max): static
    {
        return new static("SSD1306 max_packet_size {$size} must be at least 1 and at most {$max} on this bus.");
    }

    public static function invalidMux(int $ratio): static
    {
        return new static("invalid Multiplex Ratio - $ratio");
    }

    public static function invalidOffset(int $offset): static
    {
        return new static("invalid Display Offset - $offset");
    }

    public static function invalidStartLine(int $pos): static
    {
        return new static("invalid StartLine - {$pos}");
    }

    public static function invalidContrast(int $pos): static
    {
        return new static("invalid Contrast value - {$pos}");
    }

    public static function invalidAddressingMode(string $mode): static
    {
        return new static("invalid Addressing Mode - {$mode}");
    }

    public static function invalidProperty(string $name, string $class): static
    {
        return new static("Invalid property '{$name}' on {$class}.");
    }
}