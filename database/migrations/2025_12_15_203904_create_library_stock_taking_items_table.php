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
        // Only create if not exists
        if (!Schema::hasTable('library_stock_taking_items')) {
            Schema::create('library_stock_taking_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('stock_taking_id');
                $table->unsignedBigInteger('copy_id')->comment('Library copy being scanned');
                $table->enum('scan_result', ['found', 'lost', 'damaged', 'not_checked'])->default('found');
                $table->string('condition_note')->nullable()->comment('Condition description if damaged');
                $table->unsignedBigInteger('scanned_by_staff_id')->nullable();
                $table->timestamp('scanned_at')->nullable()->comment('When this item was scanned');
                $table->text('note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                // Indexes
                $table->index('stock_taking_id');
                $table->index('copy_id');
                $table->index('scan_result');
                $table->index('is_active');

                // Unique constraint: one copy per stock taking
                $table->unique(['stock_taking_id', 'copy_id'], 'unique_copy_per_stock_taking');

                // Foreign keys
                $table->foreign('stock_taking_id')->references('id')->on('library_stock_takings')->onDelete('cascade');
                $table->foreign('copy_id')->references('id')->on('library_copies')->onDelete('cascade');
                $table->foreign('scanned_by_staff_id')->references('id')->on('staff')->onDelete('set null');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_stock_taking_items');
    }
};
