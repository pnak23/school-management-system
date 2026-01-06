<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('library_stock_taking_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('stock_taking_id');
            $table->unsignedBigInteger('copy_id');

            // Result of scan during stock take
            $table->enum('scan_result', ['found', 'lost', 'damaged', 'not_checked'])
                  ->default('found');

            // Optional condition snapshot
            $table->string('condition_note', 255)->nullable();

            // Who scanned (staff)
            $table->unsignedBigInteger('scanned_by_staff_id')->nullable();
            $table->dateTime('scanned_at')->useCurrent();

            $table->text('note')->nullable();

            // Audit Fields (project standard)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            /* ===============================
               Foreign Keys
               =============================== */

            $table->foreign('stock_taking_id')
                ->references('id')->on('library_stock_takings')
                ->cascadeOnDelete();

            $table->foreign('copy_id')
                ->references('id')->on('library_copies')
                ->cascadeOnDelete();

            $table->foreign('scanned_by_staff_id')
                ->references('id')->on('staff')
                ->nullOnDelete();

            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by')
                ->references('id')->on('users')
                ->nullOnDelete();

            /* ===============================
               Constraints & Indexes
               =============================== */

            // Prevent duplicate scanning same copy in same stock session
            $table->unique(['stock_taking_id', 'copy_id'], 'uniq_stocktaking_copy');

            $table->index('scan_result');
            $table->index('scanned_at');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_stock_taking_items');
    }
};
