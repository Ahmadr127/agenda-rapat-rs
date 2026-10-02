<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PreventRequestForgery::class);

        // Data dasar RBAC agar setiap feature test punya role bawaan.
        // Dilewati untuk test yang tidak memakai database.
        if (Schema::hasTable('roles')) {
            $this->seed(\Database\Seeders\RbacSeeder::class);
        }
    }
}
