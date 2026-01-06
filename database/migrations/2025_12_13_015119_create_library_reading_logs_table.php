<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('library_reading_logs', function (Blueprint $table) {
            $table->id();

            // Parent visit
            $table->unsignedBigInteger('visit_id');

            // Book read (title-level) + optional copy-level
            $table->unsignedBigInteger('library_item_id');
            $table->unsignedBigInteger('copy_id')->nullable();

            // Reading time (optional)
            $table->dateTime('start_time')->nullable()->index();
            $table->dateTime('end_time')->nullable()->index();

            // Type (for future extension)
            $table->enum('reading_type', ['in_library'])->default('in_library')->index();

            $table->text('note')->nullable();

            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            /* ===============================
               Foreign Keys
               =============================== */

            $table->foreign('visit_id')
                ->references('id')->on('library_visits')
                ->cascadeOnDelete();

            $table->foreign('library_item_id')
                ->references('id')->on('library_items')
                ->cascadeOnDelete();

            // Optional FK: if you scan barcode copy when reading
            $table->foreign('copy_id')
                ->references('id')->on('library_copies')
                ->nullOnDelete();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            /* ===============================
               Constraints & Indexes
               =============================== */

            // Prevent duplicate same item in same visit if you want (optional but helpful)
            $table->unique(['visit_id', 'library_item_id', 'copy_id'], 'uniq_visit_item_copy');

            $table->index('library_item_id');
            $table->index('copy_id');
            $table->index('is_active');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_reading_logs');
    }
};
