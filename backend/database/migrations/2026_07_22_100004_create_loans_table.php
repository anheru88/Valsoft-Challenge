<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('book_id')->constrained()->restrictOnDelete();
            $table->date('loaned_at');
            $table->date('due_date');
            // FR-LOAN-3: status is derived from returned_at and due_date, never stored,
            // so it cannot drift from the facts.
            $table->dateTime('returned_at')->nullable();
            $table->timestamps();

            // BR-LOAN-2/3/4: "this member's open loans" backs every check-out rule.
            $table->index(['user_id', 'returned_at']);
            // BR-BOOK-4: "this book's open loans" backs the delete guard.
            $table->index(['book_id', 'returned_at']);
            // FR-RPT-1: overdue scans.
            $table->index(['due_date', 'returned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
