<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_author_item', function (Blueprint $table) {

            // =========================
            // Pivot Keys
            // =========================
            $table->unsignedBigInteger('library_item_id');
            $table->unsignedBigInteger('author_id');

            // Optional metadata about relationship
            $table->string('role', 50)->nullable(); // e.g. Author, Editor, Translator

            // =========================
            // Audit Fields (Project Standard)
            // =========================
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // =========================
            // Foreign Keys
            // =========================
            $table->foreign('library_item_id')
                ->references('id')
                ->on('library_items')
                ->onDelete('cascade');

            $table->foreign('author_id')
                ->references('id')
                ->on('library_authors')
                ->onDelete('cascade');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // =========================
            // Constraints / Indexes
            // =========================
            $table->unique(['library_item_id', 'author_id'], 'uniq_library_item_author');
            $table->index('is_active');
            $table->index('author_id');
            $table->index('library_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_author_item');
    }
};
