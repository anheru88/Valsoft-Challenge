<?php

declare(strict_types=1);

namespace App\Library\Shared\Infrastructure;

use App\Library\Shared\Application\TransactionRunner;
use Closure;
use Illuminate\Database\DatabaseManager;

final readonly class DatabaseTransactionRunner implements TransactionRunner
{
    public function __construct(private DatabaseManager $database) {}

    /**
     * @template TReturn
     *
     * @param  Closure(): TReturn  $work
     * @return TReturn
     */
    public function run(Closure $work): mixed
    {
        return $this->database->transaction($work);
    }
}
