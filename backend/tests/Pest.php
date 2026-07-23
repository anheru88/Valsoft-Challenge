<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
 * Feature tests hit the HTTP surface and need a database; unit tests cover the
 * framework-free domain layer and must stay database-free (RFC §13).
 */
pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');
