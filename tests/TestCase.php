<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Seed the website content whenever RefreshDatabase rebuilds the schema
     * (it only migrates once per run, so every test class must agree on this).
     */
    protected bool $seed = true;
}
