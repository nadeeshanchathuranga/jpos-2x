<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected static ?string $testAppKey = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (empty(config('app.key'))) {
            self::$testAppKey ??= 'base64:'.base64_encode(random_bytes(32));
            config(['app.key' => self::$testAppKey]);
        }

        $this->withoutVite();
    }
}
