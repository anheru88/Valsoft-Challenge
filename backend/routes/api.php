<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AuthorController;
use App\Http\Controllers\Api\V1\BookController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\LoanController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/*
 * Everything is mounted under /api/v1 (ADR-8). Only login and register are
 * public; the rest is deny-by-default behind auth:sanctum (RFC 12).
 */

Route::middleware('throttle:auth')->group(function (): void {
    Route::post('auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');
});

Route::middleware(['auth:sanctum', 'active'])->group(function (): void {
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');
    Route::put('auth/password', [AuthController::class, 'changePassword'])->name('auth.password');

    Route::get('books', [BookController::class, 'index'])->name('books.index');
    Route::post('books', [BookController::class, 'store'])->name('books.store');
    // ETag validators are the only caching the MVP enables (RFC 10): a client
    // holding a current copy gets 304 instead of the payload.
    Route::get('books/{book}', [BookController::class, 'show'])
        ->middleware('cache.headers:private;etag')
        ->name('books.show');
    Route::put('books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('books/{book}', [BookController::class, 'destroy'])->name('books.destroy');

    Route::get('authors', [AuthorController::class, 'index'])->name('authors.index');
    Route::post('authors', [AuthorController::class, 'store'])->name('authors.store');
    Route::get('authors/{author}', [AuthorController::class, 'show'])->name('authors.show');
    Route::put('authors/{author}', [AuthorController::class, 'update'])->name('authors.update');
    Route::delete('authors/{author}', [AuthorController::class, 'destroy'])->name('authors.destroy');

    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('search/books', [SearchController::class, 'books'])->name('search.books');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('dashboard/recent-activity', [DashboardController::class, 'recentActivity'])->name('dashboard.recent-activity');
    Route::get('dashboard/popular-authors', [DashboardController::class, 'popularAuthors'])->name('dashboard.popular-authors');
    Route::get('dashboard/recent-books', [DashboardController::class, 'recentBooks'])->name('dashboard.recent-books');
    Route::get('dashboard/books-by-category', [DashboardController::class, 'booksByCategory'])->name('dashboard.books-by-category');
    Route::get('dashboard/monthly-stats', [DashboardController::class, 'monthlyStats'])->name('dashboard.monthly-stats');

    Route::get('reports/overdue', [ReportController::class, 'overdue'])->name('reports.overdue');
    Route::get('reports/most-borrowed', [ReportController::class, 'mostBorrowed'])->name('reports.most-borrowed');

    Route::get('loans', [LoanController::class, 'index'])->name('loans.index');
    Route::post('loans', [LoanController::class, 'store'])->name('loans.store');
    Route::get('loans/{loan}', [LoanController::class, 'show'])->name('loans.show');
    Route::post('loans/{loan}/return', [LoanController::class, 'return'])->name('loans.return');
    Route::get('users/{user}/loans', [LoanController::class, 'forUser'])->name('users.loans');

    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::patch('users/{user}/status', [UserController::class, 'changeStatus'])->name('users.status');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});
