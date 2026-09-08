<?php

namespace ThePrimeStudio\Audit\Tests;

use PHPUnit\Framework\TestCase;
use ThePrimeStudio\Audit\AuditServiceProvider;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

class AuditServiceProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $app = new Container();
        Container::setInstance($app);
        Facade::setApplication($app);
    }

    public function testServiceProviderIsRegistered()
    {
        $app = Container::getInstance();
        $provider = new AuditServiceProvider($app);
        $provider->register();

        $this->assertTrue($app->bound(\ThePrimeStudio\Audit\Managers\CheckManager::class));
    }
}
