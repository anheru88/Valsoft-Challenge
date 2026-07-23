<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Authenticates the next request with a bearer token.
     *
     * The guard caches the user it resolved for the lifetime of the test case,
     * so a follow-up request would reuse the first one's identity — including a
     * token that has since been revoked. Forgetting the guards makes every call
     * resolve its token again, the way separate HTTP requests do.
     */
    protected function withBearerToken(string $token): static
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($token);
    }
}
