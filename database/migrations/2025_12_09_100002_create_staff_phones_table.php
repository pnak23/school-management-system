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
        Schema::create('staff_phones', function (Blueprint $table) {
            // Primary Key
            $table->id();

            // Staff Relationship (NOT NULL, cascade on delete)
            $table->foreignId('staff_id')
                ->constrained('staff')
                ->cascadeOnDelete();

            // Phone Information
            $table->string('phone', 30);
            $table->boolean('is_primary')->default(false);
            $table->string('note', 255)->nullable();

            // Audit Fields
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->boolean('is_active')->default(true);

            // Timestamps
            $table->timestamps();

            // Indexes for Performance
            $table->index('staff_id');
            $table->index('phone');
            $table->index('is_primary');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_phones');
    }
};


