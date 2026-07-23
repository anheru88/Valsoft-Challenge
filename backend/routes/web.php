<?php

use Illuminate\Support\Facades\Route;

/*
 * Librarium is an API-only application; the Angular SPA is served separately.
 * Only the health endpoint (/up) and the versioned API in routes/api.php are
 * meant to be consumed — this root route exists as a discovery pointer.
 */
Route::get('/', fn () => response()->json([
    'name' => config('app.name'),
    'api' => url('/api/v1'),
]));
