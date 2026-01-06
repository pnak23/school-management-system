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
        if (!Schema::hasTable('library_stock_takings')) {
            Schema::create('library_stock_takings', function (Blueprint $table) {
                $table->id();
                $table->string('reference_no')->unique()->comment('Auto-generated: STK-YYYYMMDD-0001');
                $table->timestamp('started_at')->nullable()->comment('When stock taking started');
                $table->timestamp('ended_at')->nullable()->comment('When stock taking completed/cancelled');
                $table->enum('status', ['in_progress', 'completed', 'cancelled'])->default('in_progress');
                $table->unsignedBigInteger('conducted_by_staff_id')->nullable()->comment('Staff who conducts audit');
                $table->text('note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                // Indexes
                $table->index('status');
                $table->index('is_active');
                $table->index('created_at');

                // Foreign keys
                $table->foreign('conducted_by_staff_id')->references('id')->on('staff')->onDelete('set null');
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
        Schema::dropIfExists('library_stock_takings');
    }
};
