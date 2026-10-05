<?php

use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306I2CAddress;
use DeptOfScrapyardRobotics\Displays\SSD1306\Providers\SSD1306ServiceProvider;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306;
use DeptOfScrapyardRobotics\Displays\SSD1306\Tests\Support\ConfigPathVessel;
use GeneralPurposeIO\IntegratedCircuits\CircuitRegistry;
use Voyager\Config\Repository;
use Voyager\NutsAndBolts\ServiceProvider;

it('registers the wiring config under circuits.ssd1306, keeping anything the app already set', function (): void {
    $vessel = new ConfigPathVessel;
    $vessel->registerInstance('config', new Repository(['circuits' => [
        'front_panel' => ['ic' => 'st7789'],
        'ssd1306' => ['default_config' => 'spi'],
    ]]));

    (new SSD1306ServiceProvider($vessel))->register();

    $config = $vessel->make('config');

    expect($config->get('circuits.ssd1306.default_config'))->toBe('spi')
        ->and($config->get('circuits.ssd1306.configs.i2c.slave'))->toBe(SSD1306I2CAddress::SAO_GROUNDED->value)
        ->and($config->get('circuits.ssd1306.configs.spi.dc.pin'))->toBe(0)
        ->and($config->get('circuits.ssd1306.configs.spi.speed'))->toBe(10_000_000)
        ->and($config->get('circuits.ssd1306.configs.i2c.panel'))->toBe(['width' => 128, 'height' => 64])
        ->and($config->get('circuits.front_panel'))->toBe(['ic' => 'st7789'])
        ->and($config->has('ssd1306'))->toBeFalse();
});

it('publishes the config into config/circuits under the ssd1306-config tag', function (): void {
    $app = new ConfigPathVessel('/app/config');
    $app->registerInstance('config', new Repository);

    $provider = new SSD1306ServiceProvider($app);
    $provider->register();
    $provider->boot();

    expect(ServiceProvider::pathsToPublish(SSD1306ServiceProvider::class, 'ssd1306-config'))->toBe([
        dirname(__DIR__, 2).'/config/ssd1306.php' => '/app/config/circuits/ssd1306.php',
    ]);
});

it('adds the panel to the circuit catalog when one is bound', function (): void {
    $app = new ConfigPathVessel;
    $app->registerInstance('config', new Repository);
    $app->registerInstance('circuit', $catalog = new CircuitRegistry);

    $provider = new SSD1306ServiceProvider($app);
    $provider->register();
    $provider->boot();

    expect($catalog->listCircuits())->toBe(['ssd1306' => SSD1306::class]);
});

it('boots without a circuit catalog', function (): void {
    $app = new ConfigPathVessel;
    $app->registerInstance('config', new Repository);

    $provider = new SSD1306ServiceProvider($app);
    $provider->register();
    $provider->boot();

    expect($app->isBound('circuit'))->toBeFalse();
});
