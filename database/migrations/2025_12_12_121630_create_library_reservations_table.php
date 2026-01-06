<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('library_reservations', function (Blueprint $table) {
            $table->id();

            // Who reserves
            $table->unsignedBigInteger('user_id');

            // What title is reserved
            $table->unsignedBigInteger('library_item_id');

            // When a copy becomes available, we can assign it (optional)
            $table->unsignedBigInteger('assigned_copy_id')->nullable();

            // Queue / Priority
            $table->unsignedInteger('queue_no')->nullable(); // optional (can be computed)
            $table->enum('status', ['pending', 'ready', 'fulfilled', 'cancelled', 'expired'])
                  ->default('pending');

            // Dates
            $table->dateTime('reserved_at')->useCurrent();
            $table->dateTime('expires_at')->nullable(); // e.g., hold for 2 days after ready

            $table->text('note')->nullable();

            // Audit Fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // FKs
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('library_item_id')->references('id')->on('library_items')->onDelete('cascade');
            $table->foreign('assigned_copy_id')->references('id')->on('library_copies')->onDelete('set null');

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            // Avoid duplicate active pending reservation for same user+title
            $table->unique(['user_id', 'library_item_id'], 'uniq_reservation_user_item');

            // Indexes
            $table->index('status');
            $table->index('reserved_at');
            $table->index('expires_at');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_reservations');
    }
};
