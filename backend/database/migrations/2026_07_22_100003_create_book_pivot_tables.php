<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // BR-BOOK-5: every book carries at least one author and one category, so both
        // pivots are indexed in each join direction (RFC 9.2).
        Schema::create('author_book', function (Blueprint $table) {
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained()->cascadeOnDelete();

            $table->primary(['book_id', 'author_id']);
            $table->index(['author_id', 'book_id']);
        });

        Schema::create('book_category', function (Blueprint $table) {
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            $table->primary(['book_id', 'category_id']);
            $table->index(['category_id', 'book_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_category');
        Schema::dropIfExists('author_book');
    }
};
