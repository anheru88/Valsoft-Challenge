<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('bio')->nullable();
            $table->smallInteger('birth_year')->nullable();
            $table->timestamps();

            // FR-AUTHOR-1: name uniqueness is advisory (homonyms are real), so the
            // index serves prefix search and duplicate lookup, not a constraint.
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
