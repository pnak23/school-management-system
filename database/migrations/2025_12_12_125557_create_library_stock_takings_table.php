<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('library_stock_takings', function (Blueprint $table) {
            $table->id();

            // Human readable reference (e.g. STK-2025-0001)
            $table->string('reference_no', 50)->unique();

            // Session timing
            $table->dateTime('started_at')->useCurrent();
            $table->dateTime('ended_at')->nullable();

            // Status of session
            $table->enum('status', ['in_progress', 'completed', 'cancelled'])
                  ->default('in_progress');

            // Person in charge (staff)
            $table->unsignedBigInteger('conducted_by_staff_id')->nullable();

            $table->text('note')->nullable();

            // Audit Fields (project standard)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Foreign Keys
            $table->foreign('conducted_by_staff_id')
                ->references('id')->on('staff')
                ->nullOnDelete();

            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by')
                ->references('id')->on('users')
                ->nullOnDelete();

            // Indexes
            $table->index('status');
            $table->index('started_at');
            $table->index('ended_at');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_stock_takings');
    }
};
