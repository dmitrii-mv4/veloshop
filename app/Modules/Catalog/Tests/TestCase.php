<?php

namespace App\Modules\Catalog\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use \Illuminate\Foundation\Testing\LazilyRefreshDatabase;
}
