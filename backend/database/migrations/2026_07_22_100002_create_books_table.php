<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            // Stored normalized (digits only, ISBN-10 or ISBN-13) — BR-BOOK-1.
            $table->string('isbn', 13)->unique();
            $table->text('description')->nullable();
            $table->string('publisher')->nullable();
            $table->smallInteger('publication_year')->nullable();
            $table->string('cover_url')->nullable();
            $table->unsignedSmallInteger('total_copies');
            // ADR-4: deliberate denormalization — a maintained counter so catalogue
            // listings never aggregate the loans table.
            $table->unsignedSmallInteger('available_copies');
            // FR-BOOK-4: soft deleted books keep their historical loans valid.
            $table->softDeletes();
            $table->timestamps();

            $table->index('title');
            $table->index('publication_year');
            $table->index('available_copies');
        });

        if ($this->supportsNativeConstraints()) {
            // The counter is system-managed (BR-BOOK-2); the database refuses to hold
            // a value the domain considers impossible even if a bug tries to write one.
            DB::statement('ALTER TABLE books ADD CONSTRAINT chk_books_available_copies CHECK (available_copies BETWEEN 0 AND total_copies)');

            // RFC 11: relevance search over catalogue text.
            DB::statement('ALTER TABLE books ADD FULLTEXT ft_books_text (title, description, publisher)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }

    private function supportsNativeConstraints(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
};
