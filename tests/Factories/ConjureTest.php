<?php

use DeptOfScrapyardRobotics\Displays\SSD1306\Providers\SSD1306ServiceProvider;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306Exception;
use DeptOfScrapyardRobotics\Displays\SSD1306\Transports\SSD1306I2CTransport;
use DeptOfScrapyardRobotics\Displays\SSD1306\Transports\SSD1306SPITransport;
use GeneralPurposeIO\Contracts\SPI\SPIMode;

/** The config the provider merges, with the app's wiring over it, and the provider booted onto the bench's catalog. */
function conjureBench(array $wiring): array
{
    $bench = fakeBench(['circuits' => ['ssd1306' => $wiring]]);
    $provider = new SSD1306ServiceProvider($bench['app']);
    $provider->register();
    $provider->boot();

    return $bench;
}

function spiWiring(array $overrides = []): array
{
    return array_replace_recursive([
        'default_config' => 'spi',
        'configs' => ['spi' => [
            'driver' => 'fake',
            'device' => 'ft232h',
            'chip_select' => 0,
            'dc' => ['driver' => 'fake', 'device' => 'ft232h', 'pin' => 1],
            'rst' => ['driver' => 'fake', 'device' => 'ft232h', 'pin' => 2],
        ]],
    ], $overrides);
}

it('conjures a booted panel over I2C from config, connecting the bus', function (): void {
    $bench = conjureBench(['default_config' => 'i2c', 'configs' => ['i2c' => [
        'driver' => 'fake',
        'device' => 1,
        'panel' => ['width' => 128, 'height' => 32],
    ]]]);

    $panel = $bench['app']->make('circuit')->conjure('ssd1306');
    $slave = $bench['i2c']->slaves['1:60'];

    expect($panel)->toBeInstanceOf(SSD1306::class)
        ->and($panel->hasBooted())->toBeTrue()
        ->and($panel->height())->toBe(32)
        ->and($panel->transport())->toBeInstanceOf(SSD1306I2CTransport::class)
        ->and($bench['i2c']->opened)->toBe([1])
        ->and($slave->writes[0])->toBe([0x00, 0xAE])
        ->and($slave->writes[2])->toBe([0x00, 0xA8, 0x1F]);
});

it('shares an I2C bus the app already connected', function (): void {
    $bench = conjureBench(['default_config' => 'i2c', 'configs' => ['i2c' => ['driver' => 'fake', 'device' => 1]]]);
    $bench['app']->make('gpio.i2c')->driver('fake')->connectTo(1)->register();

    $bench['app']->make('circuit')->conjure('ssd1306');

    expect($bench['i2c']->opened)->toBe([1]);
});

it('conjures a panel over SPI: bus in mode 0 at 10 MHz, then DC and RST on the same device', function (): void {
    $bench = conjureBench(spiWiring());

    $panel = $bench['app']->make('circuit')->conjure('ssd1306');
    $slave = $bench['spi']->slaves['ft232h:0'];
    $rst = $bench['digital']->outputs['ft232h:2'];
    $dc = $bench['digital']->outputs['ft232h:1'];

    expect($panel->transport())->toBeInstanceOf(SSD1306SPITransport::class)
        ->and($panel->hasBooted())->toBeTrue()
        ->and($bench['spi']->settingsOf('ft232h')->mode)->toBe(SPIMode::MODE_0)
        ->and($slave->clock())->toBe(10_000_000)
        ->and($bench['spi']->opened)->toBe(['ft232h'])
        ->and($bench['digital']->opened)->toBe(['ft232h'])
        ->and($rst->levels)->toBe([true, false, true])
        ->and($dc->levels[0])->toBeFalse()
        ->and($slave->writes[0])->toBe(['raw', [0xAE]]);
});

it('clocks its chip select at the configured speed', function (): void {
    $bench = conjureBench(spiWiring(['configs' => ['spi' => ['speed' => 4_000_000]]]));

    $bench['app']->make('circuit')->conjure('ssd1306');

    expect($bench['spi']->slaves['ft232h:0']->clock())->toBe(4_000_000);
});

it('refuses an SPI clock past 10 MHz before touching the bus', function (int $speed): void {
    $bench = conjureBench(spiWiring(['configs' => ['spi' => ['speed' => $speed]]]));

    expect(fn () => $bench['app']->make('circuit')->conjure('ssd1306'))->toThrow(SSD1306Exception::class, "SSD1306 SPI clock {$speed} Hz")
        ->and($bench['spi']->opened)->toBe([]);
})->with([0, 10_000_001]);

it('shares a bus the app opened in mode 3 and refuses one in mode 1 or 2', function (SPIMode $mode, bool $shared): void {
    $bench = conjureBench(spiWiring());
    $bench['app']->make('gpio.spi')->driver('fake')->connectTo('ft232h')->mode($mode)->register();

    $conjure = fn () => $bench['app']->make('circuit')->conjure('ssd1306');

    $shared
        ? expect($conjure())->toBeInstanceOf(SSD1306::class)
        : expect($conjure)->toThrow(SSD1306Exception::class, "runs in mode {$mode->value}");
})->with([
    'mode 3' => [SPIMode::MODE_3, true],
    'mode 1' => [SPIMode::MODE_1, false],
    'mode 2' => [SPIMode::MODE_2, false],
]);

it('names a DC or RST pin config missing its driver, device or pin', function (string $line): void {
    $wiring = spiWiring();
    unset($wiring['configs']['spi'][$line]['pin']);
    $bench = conjureBench($wiring);

    expect(fn () => $bench['app']->make('circuit')->conjure('ssd1306'))->toThrow(SSD1306Exception::class, "needs its {$line} pin");
})->with(['dc', 'rst']);

it('builds without booting when boot_now is false', function (): void {
    $bench = conjureBench(['default_config' => 'i2c', 'configs' => ['i2c' => ['driver' => 'fake', 'device' => 1, 'boot_now' => false]]]);

    $panel = $bench['app']->make('circuit')->conjure('ssd1306');

    expect($panel->hasBooted())->toBeFalse()
        ->and($bench['i2c']->slaves['1:60']->writes)->toBe([]);
});

it('names an unknown panel key in the config', function (): void {
    $bench = conjureBench(['default_config' => 'i2c', 'configs' => ['i2c' => ['driver' => 'fake', 'device' => 1, 'panel' => ['hieght' => 32]]]]);

    expect(fn () => $bench['app']->make('circuit')->conjure('ssd1306'))->toThrow(SSD1306Exception::class, "Invalid property 'hieght'");
});
