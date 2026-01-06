<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('library_copy_status_history', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('copy_id');

            // Status/Condition change snapshots
            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30);

            $table->string('old_condition', 30)->nullable();
            $table->string('new_condition', 30)->nullable();

            // Why changed (optional)
            $table->string('action', 30)->nullable(); // borrow, return, mark_lost, mark_damaged, repair, etc.
            $table->text('note')->nullable();

            // Who changed it (usually user who did action)
            $table->unsignedBigInteger('changed_by')->nullable(); // users.id
            $table->dateTime('changed_at')->useCurrent();

            // Audit Fields (still keep standard for consistency)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // FKs
            $table->foreign('copy_id')->references('id')->on('library_copies')->onDelete('cascade');

            $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('copy_id');
            $table->index('changed_at');
            $table->index('new_status');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_copy_status_history');
    }
};
