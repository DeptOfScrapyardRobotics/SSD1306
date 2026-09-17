<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\Tests\Support;

use GeneralPurposeIO\I2C\I2CTransport;

/** Records every write as a byte list. */
final class FakeI2CTransport extends I2CTransport
{
    /** @var list<list<int>> */
    public array $writes = [];

    public bool $closed = false;

    public function __construct(int $address = 0x3C)
    {
        parent::__construct($address);
    }

    public function handle(): string { return 'fake'; }
    public function probe(): bool { return true; }
    public function read(int $len): array|false { return false; }
    public function writeRead(array|string $bytes_to_write, int $bytes_to_read): array|false { return false; }
    public function bulkWrite(array|string $messages): array|false { return false; }
    public function close(): void { $this->closed = true; }

    public function write(array|string $data): int
    {
        $bytes = is_array($data) ? array_values($data) : array_values(unpack('C*', $data));
        $this->writes[] = $bytes;

        return count($bytes);
    }
}
