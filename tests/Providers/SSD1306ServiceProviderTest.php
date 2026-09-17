<?php

use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306I2CAddress;
use DeptOfScrapyardRobotics\Displays\SSD1306\Providers\SSD1306ServiceProvider;
use DeptOfScrapyardRobotics\Displays\SSD1306\Tests\Support\ConfigPathVessel;
use Voyager\Config\Repository;
use Voyager\NutsAndBolts\ServiceProvider;
use Voyager\Vessel\Vessel;

it('registers the wiring config under circuits.ssd1306, keeping anything the app already set', function (): void {
    $vessel = new Vessel;
    $vessel->instance('config', new Repository(['circuits' => [
        'front_panel' => ['ic' => 'st7789'],
        'ssd1306' => ['default_config' => 'spi'],
    ]]));

    (new SSD1306ServiceProvider($vessel))->register();

    $config = $vessel->make('config');

    expect($config->get('circuits.ssd1306.default_config'))->toBe('spi')
        ->and($config->get('circuits.ssd1306.configs.i2c.slave'))->toBe(SSD1306I2CAddress::SAO_GROUNDED->value)
        ->and($config->get('circuits.ssd1306.configs.spi.dc.pin'))->toBe(0)
        ->and($config->get('circuits.front_panel'))->toBe(['ic' => 'st7789'])
        ->and($config->has('ssd1306'))->toBeFalse();
});

it('publishes the config into config/circuits under the ssd1306-config tag', function (): void {
    $app = new ConfigPathVessel('/app/config');
    $app->instance('config', new Repository);

    $provider = new SSD1306ServiceProvider($app);
    $provider->register();
    $provider->boot();

    expect(ServiceProvider::pathsToPublish(SSD1306ServiceProvider::class, 'ssd1306-config'))->toBe([
        dirname(__DIR__, 2).'/config/ssd1306.php' => '/app/config/circuits/ssd1306.php',
    ]);
});
