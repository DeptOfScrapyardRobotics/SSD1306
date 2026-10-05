<?php

use DeptOfScrapyardRobotics\Displays\SSD1306\Breakouts\SSD1306COMPinsHWConfig;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306AddressingMode;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306VoltageCommonHigh;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306Configuration;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306Exception;
use DeptOfScrapyardRobotics\Displays\SSD1306\Tests\Support\FakeI2CTransport;
use DeptOfScrapyardRobotics\Displays\SSD1306\Tests\Support\FakeOutputPin;
use DeptOfScrapyardRobotics\Displays\SSD1306\Tests\Support\FakeSPITransport;
use DeptOfScrapyardRobotics\Displays\SSD1306\Transports\SSD1306I2CTransport;
use DeptOfScrapyardRobotics\Displays\SSD1306\Transports\SSD1306SPITransport;
use GeneralPurposeIO\Contracts\IntegratedCircuits\CircuitException;
use GeneralPurposeIO\Contracts\IntegratedCircuits\DisplayPanel;
use Surface\Contracts\Framebuffers\BitDepth;
use Surface\Contracts\Framebuffers\BitOrder;
use Surface\Contracts\Framebuffers\FormatSpec;
use Surface\Contracts\Framebuffers\PageAxis;
use Surface\Contracts\Framebuffers\PixelFormat;

/** The datasheet init sequence with this package's defaults, one entry per command. */
function defaultBootCommands(): array
{
    return [
        [0xAE],             // display off
        [0xD5, 0x80],       // clock divide / oscillator
        [0xA8, 0x3F],       // multiplex = height - 1
        [0xD3, 0x00],       // display offset
        [0x40],             // start line 0
        [0x8D, 0x14],       // charge pump on
        [0x20, 0x00],       // horizontal addressing
        [0xA0],             // segment remap off
        [0xC0],             // COM scan normal
        [0xDA, 0x12],       // COM pins
        [0x81, 0xBF],       // contrast 191
        [0xD9, 0xF1],       // pre-charge
        [0xDB, 0x40],       // VCOMH
        [0xA4],             // resume from RAM
        [0xA6],             // normal, not inverted
        [0x2E],             // stop scrolling
        [0xAF],             // display on
    ];
}

/** @return array{0: SSD1306, 1: FakeI2CTransport} */
function i2cPanel(?SSD1306Configuration $config = null, bool $boot = true): array
{
    $bus = new FakeI2CTransport;
    $panel = new SSD1306(new SSD1306I2CTransport($bus), $config ?? new SSD1306Configuration, boot_now: $boot);

    return [$panel, $bus];
}

/** I2C writes with the control byte split off: [control, [bytes]] */
function framed(FakeI2CTransport $bus, int $from = 0): array
{
    return array_map(fn (array $w): array => [$w[0], array_slice($w, 1)], array_slice($bus->writes, $from));
}

it('is a bootable display panel with the configured size', function (): void {
    [$panel] = i2cPanel(new SSD1306Configuration(width: 128, height: 32), boot: false);

    expect($panel)->toBeInstanceOf(DisplayPanel::class)
        ->and($panel->hasBooted())->toBeFalse()
        ->and($panel->width())->toBe(128)
        ->and($panel->height())->toBe(32)
        ->and($panel->transport())->toBeInstanceOf(SSD1306I2CTransport::class);
});

it('boots over I2C with the datasheet init sequence, every command behind a 0x00 control byte', function (): void {
    [$panel, $bus] = i2cPanel();

    expect($panel->hasBooted())->toBeTrue()
        ->and(array_column(framed($bus), 0))->each->toBe(0x00)
        ->and(array_column(framed($bus), 1))->toBe(defaultBootCommands());
});

it('derives the multiplex ratio and the COM pin layout from the configured height', function (): void {
    [, $short] = i2cPanel(new SSD1306Configuration(width: 128, height: 32));
    [, $tall] = i2cPanel(new SSD1306Configuration(width: 128, height: 64));

    expect(framed($short)[2][1])->toBe([0xA8, 0x1F])
        ->and(framed($short)[9][1])->toBe([0xDA, 0x02])
        ->and(framed($tall)[9][1])->toBe([0xDA, 0x12]);
});

it('takes an explicit COM pin layout over the height default', function (): void {
    [, $bus] = i2cPanel(new SSD1306Configuration(width: 128, height: 32, alternative_com_pins: true));

    expect(framed($bus)[9][1])->toBe([0xDA, 0x12]);
});

it('applies every boot setting from the configuration', function (): void {
    [, $bus] = i2cPanel(new SSD1306Configuration(
        contrast: 0x10,
        start_line: 5,
        display_offset: 3,
        invert_display: true,
        enable_com_lr_remap: true,
        powered_by_host_device: false,
        map_line_0_to_line_127: true,
        alternative_com_pins: false,
        reverse_line_scan_direction: true,
        v_com_h: SSD1306VoltageCommonHigh::LEVEL_083,
    ));

    $commands = array_column(framed($bus), 1);

    expect($commands)->toContain([0xD3, 0x03], [0x45], [0xA1], [0xC8], [0xDA, 0x22], [0x81, 0x10], [0xD9, 0x22], [0xDB, 0x30], [0xA7]);
});

it('exposes one FormatSpec, the same in every addressing mode', function (): void {
    [$panel] = i2cPanel();

    $spec = $panel->formatSpec();

    expect($spec)->toBeInstanceOf(FormatSpec::class)
        ->and($spec->pixel_format)->toBe(PixelFormat::MONO_VERTICAL_PAGE)
        ->and($spec->bit_depth)->toBe(BitDepth::B1)
        ->and($spec->bit_order)->toBe(BitOrder::LSB_FIRST)
        ->and($spec->page_axis)->toBe(PageAxis::VERTICAL);

    foreach ([SSD1306AddressingMode::VERTICAL_ADDRESSING_MODE, SSD1306AddressingMode::PAGE_ADDRESSING_MODE, SSD1306AddressingMode::HORIZONTAL_ADDRESSING_MODE] as $mode) {
        $panel->addressing_mode = $mode;

        expect($panel->addressing_mode)->toBe($mode)
            ->and($panel->formatSpec())->toEqual($spec);
    }
});

it('boots in any addressing mode', function (SSD1306AddressingMode $mode): void {
    [$panel, $bus] = i2cPanel(new SSD1306Configuration(addressing_mode: $mode));

    expect($panel->hasBooted())->toBeTrue()
        ->and(framed($bus))->toContain([0x00, [0x20, $mode->value]]);
})->with([SSD1306AddressingMode::HORIZONTAL_ADDRESSING_MODE, SSD1306AddressingMode::VERTICAL_ADDRESSING_MODE, SSD1306AddressingMode::PAGE_ADDRESSING_MODE]);

it('refuses the invalid addressing mode without writing it', function (): void {
    [$panel, $bus] = i2cPanel();
    $before = count($bus->writes);

    expect(fn () => $panel->addressing_mode = SSD1306AddressingMode::INVALID)
        ->toThrow(SSD1306Exception::class, 'invalid Addressing Mode - INVALID')
        ->and(count($bus->writes))->toBe($before)
        ->and($panel->addressing_mode)->toBe(SSD1306AddressingMode::HORIZONTAL_ADDRESSING_MODE);
});

it('takes the same bytes in every mode: vertical mode reorders them column by column', function (): void {
    [$panel, $bus] = i2cPanel();
    $panel->addressing_mode = SSD1306AddressingMode::VERTICAL_ADDRESSING_MODE;
    $before = count($bus->writes);

    // 3 columns × 2 pages, page-major: page 2 = [1, 2, 3], page 3 = [4, 5, 6]
    $panel->transmit(8, 16, [1, 2, 3, 4, 5, 6], 3, 16);

    expect(framed($bus, $before))->toBe([
        [0x00, [0x21, 8, 10]],
        [0x00, [0x22, 2, 3]],
        [0x40, [1, 4, 2, 5, 3, 6]],
    ]);
});

it('takes the same bytes in every mode: page mode places and sends each page itself', function (): void {
    [$panel, $bus] = i2cPanel();
    $panel->addressing_mode = SSD1306AddressingMode::PAGE_ADDRESSING_MODE;
    $before = count($bus->writes);

    $panel->transmit(0x2A, 16, [1, 2, 3, 4, 5, 6], 3, 16);

    expect(framed($bus, $before))->toBe([
        [0x00, [0x0A]], [0x00, [0x12]], [0x00, [0xB2]],
        [0x40, [1, 2, 3]],
        [0x00, [0x0A]], [0x00, [0x12]], [0x00, [0xB3]],
        [0x40, [4, 5, 6]],
    ]);
});

it('sends a full frame in page mode as eight placed pages', function (): void {
    [$panel, $bus] = i2cPanel();
    $panel->addressing_mode = SSD1306AddressingMode::PAGE_ADDRESSING_MODE;
    $before = count($bus->writes);

    $panel->transmit(0, 0, array_fill(0, 128 * 8, 0xFF));

    $frames = framed($bus, $before);
    $pages = array_values(array_filter($frames, fn (array $f): bool => $f[0] === 0x00 && ($f[1][0] & 0xF0) === 0xB0));

    expect($pages)->toBe(array_map(fn (int $p): array => [0x00, [0xB0 | $p]], range(0, 7)))
        ->and(array_sum(array_map(fn (array $f): int => $f[0] === 0x40 ? count($f[1]) : 0, $frames)))->toBe(128 * 8);
});

it('transmits a frame into the column and page window it covers', function (): void {
    [$panel, $bus] = i2cPanel();
    $before = count($bus->writes);

    $panel->transmit(8, 16, array_fill(0, 32, 0xFF), 16, 16);

    expect(framed($bus, $before))->toBe([
        [0x00, [0x21, 8, 23]],
        [0x00, [0x22, 2, 3]],
        [0x40, array_fill(0, 32, 0xFF)],
    ]);
});

it('transmits a full frame by default', function (): void {
    [$panel, $bus] = i2cPanel();
    $before = count($bus->writes);

    $panel->transmit(0, 0, array_fill(0, 128 * 8, 0x00));

    expect(array_slice(framed($bus, $before), 0, 2))->toBe([[0x00, [0x21, 0, 127]], [0x00, [0x22, 0, 7]]])
        ->and(count($bus->writes) - $before)->toBe(3);
});

it('splits data into packets of the configured size, behind a 0x40 control byte', function (): void {
    [$panel, $bus] = i2cPanel(new SSD1306Configuration(max_packet_size: 4));
    $before = count($bus->writes);

    $panel->transport()->data([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
    $panel->transport()->data("\x0B\x0C\x0D\x0E\x0F");

    expect(framed($bus, $before))->toBe([
        [0x40, [1, 2, 3, 4]], [0x40, [5, 6, 7, 8]], [0x40, [9, 10]],
        [0x40, [11, 12, 13, 14]], [0x40, [15]],
    ]);
});

it('reads and writes settings through properties and keeps the configuration current', function (): void {
    [$panel, $bus] = i2cPanel();
    $before = count($bus->writes);

    $panel->contrast = 0x20;
    $panel->invert_display = true;
    $panel->display_offset = 7;
    $panel->display_on = false;
    $panel->fill_overlay_on = true;

    expect(array_column(framed($bus, $before), 1))->toBe([[0x81, 0x20], [0xA7], [0xD3, 0x07], [0xAE], [0xA5]])
        ->and($panel->contrast)->toBe(0x20)
        ->and($panel->display_offset)->toBe(7)
        ->and($panel->display_on)->toBeFalse()
        ->and($panel->fill_overlay_on)->toBeTrue()
        ->and($panel->addressing_mode)->toBe(SSD1306AddressingMode::HORIZONTAL_ADDRESSING_MODE)
        ->and($panel->com_pins_config)->toBeInstanceOf(SSD1306COMPinsHWConfig::class)
        ->and($panel->config()->get('invert_display'))->toBeTrue();
});

it('refuses out-of-range settings and unknown properties', function (): void {
    [$panel] = i2cPanel();

    expect(fn () => $panel->contrast = 256)->toThrow(SSD1306Exception::class, 'Contrast')
        ->and(fn () => $panel->display_offset = 64)->toThrow(SSD1306Exception::class, 'Offset')
        ->and(fn () => $panel->nope)->toThrow(SSD1306Exception::class, "Invalid property 'nope'")
        ->and(fn () => $panel->nope = 1)->toThrow(SSD1306Exception::class, "Invalid property 'nope'");
});

it('the configuration reads and writes its own keys and names an unknown one', function (): void {
    $config = new SSD1306Configuration(width: 96);

    $config->set('contrast', 12);

    expect($config->get('width'))->toBe(96)
        ->and($config->get('contrast'))->toBe(12)
        ->and(fn () => $config->get('nope'))->toThrow(SSD1306Exception::class, "'nope'")
        ->and(fn () => $config->set('nope', 1))->toThrow(SSD1306Exception::class, "'nope'");
});

it('roots its exception at the framework circuit exception', function (): void {
    expect(SSD1306Exception::invalidContrast(300))->toBeInstanceOf(CircuitException::class);
});

// --- SPI ---------------------------------------------------------------------------

/** @return array{0: SSD1306, 1: FakeSPITransport, 2: FakeOutputPin, 3: FakeOutputPin} */
function spiPanel(): array
{
    $dc = new FakeOutputPin(24);
    $rst = new FakeOutputPin(25);
    $spi = new FakeSPITransport(0);
    $spi->dc = $dc;
    $panel = new SSD1306(new SSD1306SPITransport($spi, $dc, $rst), new SSD1306Configuration, boot_now: true);

    return [$panel, $spi, $dc, $rst];
}

it('boots over SPI: pulses RST, then sends every command with DC low', function (): void {
    [, $spi, , $rst] = spiPanel();

    expect($rst->levels)->toBe([true, false, true])
        ->and(array_column($spi->writes, 0))->each->toBe('cmd')
        ->and(array_column($spi->writes, 1))->toBe(defaultBootCommands());
});

it('sends data over SPI with DC high, in packets', function (): void {
    [$panel, $spi] = spiPanel();
    $before = count($spi->writes);

    $panel->transmit(0, 0, [1, 2, 3], 3, 8);

    expect(array_slice($spi->writes, $before))->toBe([
        ['cmd', [0x21, 0, 2]],
        ['cmd', [0x22, 0, 0]],
        ['data', [1, 2, 3]],
    ]);
});

it('releases DC and RST on close', function (): void {
    [$panel, $spi, $dc, $rst] = spiPanel();

    $panel->close();

    expect($dc->closed())->toBeTrue()
        ->and($rst->closed())->toBeTrue()
        ->and($spi->closed())->toBeFalse();
});

it('leaves the I2C connection to its driver on close', function (): void {
    [$panel, $bus] = i2cPanel();

    $panel->close();

    expect($bus->closed())->toBeFalse();
});

it('is a window-addressable, switchable display panel that does not refresh on command', function (): void {
    expect(is_subclass_of(SSD1306::class, DisplayPanel::class))->toBeTrue()
        ->and(is_subclass_of(SSD1306::class, \GeneralPurposeIO\Contracts\IntegratedCircuits\WindowAddressable::class))->toBeTrue()
        ->and(is_subclass_of(SSD1306::class, \GeneralPurposeIO\Contracts\IntegratedCircuits\Switchable::class))->toBeTrue()
        ->and(is_subclass_of(SSD1306::class, \GeneralPurposeIO\Contracts\IntegratedCircuits\RefreshesOnCommand::class))->toBeFalse();
});

it('reads back every setting under the name it was written', function (string $name, mixed $value, array $command): void {
    [$panel, $bus] = i2cPanel();
    $before = count($bus->writes);

    $panel->{$name} = $value;

    expect($panel->{$name})->toEqual($value)
        ->and($panel->config()->get($name))->toEqual($value)
        ->and(array_column(framed($bus, $before), 1))->toBe([$command]);
})->with([
    'display_on' => ['display_on', false, [0xAE]],
    'display_offset' => ['display_offset', 9, [0xD3, 9]],
    'contrast' => ['contrast', 0x7F, [0x81, 0x7F]],
    'start_line' => ['start_line', 12, [0x4C]],
    'charge_pump' => ['charge_pump', false, [0x8D, 0x10]],
    'addressing_mode' => ['addressing_mode', SSD1306AddressingMode::PAGE_ADDRESSING_MODE, [0x20, 0x02]],
    'map_line_0_to_line_127' => ['map_line_0_to_line_127', true, [0xA1]],
    'reverse_line_scan_direction' => ['reverse_line_scan_direction', true, [0xC8]],
    'com_pins_config' => ['com_pins_config', new SSD1306COMPinsHWConfig(true, false), [0xDA, 0x22]],
    'powered_by_host_device' => ['powered_by_host_device', false, [0xD9, 0x22]],
    'v_com_h' => ['v_com_h', SSD1306VoltageCommonHigh::LEVEL_065, [0xDB, 0x00]],
    'fill_overlay_on' => ['fill_overlay_on', true, [0xA5]],
    'invert_display' => ['invert_display', true, [0xA7]],
]);

it('keeps the COM left/right remap key in step with a written COM pin config', function (): void {
    [$panel] = i2cPanel();

    $panel->com_pins_config = new SSD1306COMPinsHWConfig(enable_com_lr_remap: true, alternative_com_pins: true);

    expect($panel->config()->get('enable_com_lr_remap'))->toBeTrue();
});

it('fails boot when the I2C panel does not acknowledge', function (): void {
    $bus = new FakeI2CTransport;
    $bus->nack_from = 0;

    expect(fn () => new SSD1306(new SSD1306I2CTransport($bus), new SSD1306Configuration, boot_now: true))
        ->toThrow(SSD1306Exception::class, 'SSD1306 command 0xAE write failed: -1 of 2 bytes');
});

it('fails a transmit whose data packet is not acknowledged', function (): void {
    [$panel, $bus] = i2cPanel();
    $bus->nack_from = count($bus->writes) + 2;

    expect(fn () => $panel->transmit(0, 0, [1, 2, 3], 3, 8))
        ->toThrow(SSD1306Exception::class, 'SSD1306 data write failed: -1 of 4 bytes');
});

it('fails an SPI write the bus could not send', function (): void {
    [$panel, $spi] = spiPanel();
    $spi->answer = -1;

    expect(fn () => $panel->contrast = 1)->toThrow(SSD1306Exception::class, 'SSD1306 command 0x81 write failed: -1 of 2 bytes')
        ->and(fn () => $panel->transport()->data("\x01\x02"))->toThrow(SSD1306Exception::class, 'SSD1306 data write failed: -1 of 2 bytes');
});

it('bounds the packet size by what the bus takes in one write', function (): void {
    $spi = new FakeSPITransport(0);
    $pin = new FakeOutputPin(1);

    expect(fn () => new SSD1306I2CTransport(new FakeI2CTransport, 8192))->toThrow(SSD1306Exception::class, 'max_packet_size 8192 must be at least 1 and at most 8191')
        ->and(fn () => new SSD1306I2CTransport(new FakeI2CTransport, 0))->toThrow(SSD1306Exception::class, 'max_packet_size 0')
        ->and(fn () => (new SSD1306I2CTransport(new FakeI2CTransport))->maxPacketSize(9000))->toThrow(SSD1306Exception::class, 'max_packet_size 9000')
        ->and((new SSD1306I2CTransport(new FakeI2CTransport, 8191))->maxPacketSize(16))->toBeInstanceOf(SSD1306I2CTransport::class)
        ->and(new SSD1306SPITransport($spi, $pin, $pin, 65536))->toBeInstanceOf(SSD1306SPITransport::class);
});

it('builds a configuration from a config entry\'s panel array and names an unknown key', function (): void {
    $config = SSD1306Configuration::fromArray(['width' => 96, 'height' => 16, 'contrast' => 0x20]);

    expect($config->get('width'))->toBe(96)
        ->and($config->get('height'))->toBe(16)
        ->and($config->get('contrast'))->toBe(0x20)
        ->and($config->get('com_pins_config')->alternative_com_pins)->toBeFalse()
        ->and(fn () => SSD1306Configuration::fromArray(['widht' => 96]))->toThrow(SSD1306Exception::class, "Invalid property 'widht'");
});
