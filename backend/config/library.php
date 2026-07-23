<?php

declare(strict_types=1);

/*
 * Business constants the domain layer reads through injected configuration
 * rather than hard-coded literals, so the rules stay tunable per deployment
 * without touching the rules themselves.
 */
return [

    'loans' => [
        // BR-LOAN-2: maximum simultaneous active loans per member.
        'max_active_per_member' => (int) env('LIBRARY_MAX_ACTIVE_LOANS', 5),

        // BR-LOAN-5: default and maximum loan window, in days from check-out.
        'default_period_days' => (int) env('LIBRARY_LOAN_PERIOD_DAYS', 14),
        'max_period_days' => (int) env('LIBRARY_LOAN_MAX_PERIOD_DAYS', 60),
    ],

    'catalog' => [
        // FR-BOOK-1: publication year lower bound (first printed books).
        'min_publication_year' => 1450,
    ],

    'pagination' => [
        // FR-LIST-1.
        'default_per_page' => 15,
        'max_per_page' => 100,
    ],

    'search' => [
        // FR-SRCH-3: shorter non-ISBN queries are rejected as too broad.
        'min_query_length' => 2,
    ],

];
