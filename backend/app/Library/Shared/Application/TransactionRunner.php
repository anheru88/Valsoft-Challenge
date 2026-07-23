<?php

declare(strict_types=1);

namespace App\Library\Shared\Application;

use Closure;

/**
 * Atomicity as a contract (BR-LOAN-7). Actions declare that a unit of work is
 * all-or-nothing without importing the DB facade into the domain.
 */
interface TransactionRunner
{
    /**
     * @template TReturn
     *
     * @param  Closure(): TReturn  $work
     * @return TReturn
     */
    public function run(Closure $work): mixed;
}
