<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('library_loans', function (Blueprint $table) {
            $table->id();

            // Borrower (Student / Teacher / Staff)
            $table->string('borrower_type', 20); 
            // student | teacher | staff

            $table->unsignedBigInteger('borrower_id');

            // Book copy (barcode-based)
            $table->unsignedBigInteger('library_copy_id');

            // Dates
            $table->date('borrowed_at');
            $table->date('due_date');
            $table->date('returned_at')->nullable();

            // Staff who processed the borrow
            $table->unsignedBigInteger('processed_by_staff_id')->nullable();

            // Staff who received the return
            $table->unsignedBigInteger('received_by_staff_id')->nullable();

            // Status
            $table->string('status', 20)->default('borrowed');
            // borrowed | returned | overdue | lost

            $table->text('note')->nullable();

            $table->timestamps();

            /* ===============================
               Foreign Keys
               =============================== */

            // Barcode → library_copies
            $table->foreign('library_copy_id')
                ->references('id')
                ->on('library_copies')
                ->cascadeOnDelete();

            // Processed by staff
            $table->foreign('processed_by_staff_id')
                ->references('id')
                ->on('staff')
                ->nullOnDelete();

            // Received by staff
            $table->foreign('received_by_staff_id')
                ->references('id')
                ->on('staff')
                ->nullOnDelete();

            /* ===============================
               Indexes
               =============================== */

            $table->index(['borrower_type', 'borrower_id']);
            $table->index('library_copy_id');
            $table->index('status');
            $table->index('borrowed_at');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_loans');
    }
};
