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
        Schema::create('library_copies', function (Blueprint $table) {
            $table->id();

            // =========================
            // FK to Book Title (library_items)
            // =========================
            $table->unsignedBigInteger('library_item_id');

            // =========================
            // Physical Copy Identifier
            // =========================
            $table->string('barcode', 60)->unique(); // scan borrow/return
            $table->string('call_number', 60)->nullable(); // library classification
            $table->unsignedBigInteger('shelf_id')->nullable(); // FK to library_shelves

            // =========================
            // Inventory Info
            // =========================
            $table->date('acquired_date')->nullable();
            $table->enum('condition', ['new', 'good', 'damaged', 'lost'])->default('good');
            $table->enum('status', ['available', 'on_loan', 'reserved', 'lost', 'damaged'])->default('available');

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
                ->onDelete('cascade'); // delete copies if item deleted

            $table->foreign('shelf_id')
                ->references('id')
                ->on('library_shelves')
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
            $table->index('library_item_id');
            $table->index('status');
            $table->index('condition');
            $table->index('is_active');
            $table->index('acquired_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_copies');
    }
};
