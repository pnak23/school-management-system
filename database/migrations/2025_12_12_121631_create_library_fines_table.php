<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('library_fines', function (Blueprint $table) {
            $table->id();

            // Fine belongs to a loan
            $table->unsignedBigInteger('loan_id');

            // Who is fined (borrower user)
            $table->unsignedBigInteger('user_id');

            // Fine details
            $table->enum('fine_type', ['overdue', 'lost', 'damaged', 'other'])->default('overdue');
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);

            $table->enum('status', ['unpaid', 'paid', 'waived'])->default('unpaid');

            // Optional dates
            $table->dateTime('assessed_at')->useCurrent();
            $table->dateTime('paid_at')->nullable();

            $table->text('note')->nullable();

            // Audit Fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // FKs
            $table->foreign('loan_id')->references('id')->on('library_loans')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('fine_type');
            $table->index('status');
            $table->index('assessed_at');
            $table->index('paid_at');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_fines');
    }
};
