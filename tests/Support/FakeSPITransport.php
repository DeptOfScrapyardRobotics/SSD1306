<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\Tests\Support;

use GeneralPurposeIO\SPI\SPITransport;

/** Records every write, tagged with the DC level at the moment it happened. */
final class FakeSPITransport extends SPITransport
{
    /** @var list<array{0: string, 1: list<int>}> ['cmd'|'data', bytes] */
    public array $writes = [];

    public function __construct(public readonly FakeOutputPin $dc)
    {
        parent::__construct(0);
    }

    public function handle(): string { return 'fake'; }
    public function read(int $len): array|false { return false; }
    public function transfer(array|string $data): array|false { return false; }
    public function close(): void {}

    public function write(array|string $data): int
    {
        $bytes = is_array($data) ? array_values($data) : array_values(unpack('C*', $data));
        $this->writes[] = [$this->dc->state ? 'data' : 'cmd', $bytes];

        return count($bytes);
    }
}
