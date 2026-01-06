<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('library_items', function (Blueprint $table) {
            $table->id();

            // =========================
            // Book Identity (ISBN + Edition)
            // =========================
            $table->string('title', 255);
            $table->string('isbn', 20)->nullable()->unique();
            $table->string('edition', 50)->nullable(); // e.g. 1st, 2nd, Revised
            $table->year('published_year')->nullable();
            $table->string('language', 50)->nullable(); // Khmer, English, etc.

            // =========================
            // Description / Media
            // =========================
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();

            // =========================
            // Relations (Parents)
            // =========================
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('publisher_id')->nullable();

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
            $table->foreign('category_id')
                ->references('id')
                ->on('library_categories')
                ->onDelete('set null');

            $table->foreign('publisher_id')
                ->references('id')
                ->on('library_publishers')
                ->onDelete('set null');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // =========================
            // Indexes
            // =========================
            $table->index('title');
            $table->index('isbn');
            $table->index('edition');
            $table->index('category_id');
            $table->index('publisher_id');
            $table->index('is_active');
            $table->index('published_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_items');
    }
};
