<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Library\Domains\Auth\Exceptions\UserInactiveException;
use App\Library\Domains\Users\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * FR-USER-6: deactivation revokes tokens, but a token issued microseconds
 * earlier must not outlive the decision — so the state is checked on every
 * authenticated request too.
 */
final class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && ! $user->is_active) {
            throw new UserInactiveException;
        }

        return $next($request);
    }
}
