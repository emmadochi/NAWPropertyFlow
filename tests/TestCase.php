<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Define custom migration parameters for refresh database.
     */
    protected function migrateFreshUsing()
    {
        return [
            '--database' => 'sqlite',
            '--path' => 'database/migrations/tenant',
            '--realpath' => false,
        ];
    }
}
