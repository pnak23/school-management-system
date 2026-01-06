<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('library_visits', function (Blueprint $table) {
            $table->id();

            // Visitor identity (either member user OR guest)
            $table->unsignedBigInteger('user_id')->nullable();   // member (student/teacher/staff)
            $table->unsignedBigInteger('guest_id')->nullable();  // guest

            // Visit timing
            $table->date('visit_date')->index();
            $table->dateTime('check_in_time')->useCurrent()->index();
            $table->dateTime('check_out_time')->nullable()->index();

            // Session + purpose
            $table->enum('session', ['morning', 'afternoon', 'evening'])->default('morning')->index();
            $table->enum('purpose', ['read', 'study', 'borrow', 'return', 'other'])->default('read')->index();

            // Staff handling (librarian)
            $table->unsignedBigInteger('checked_in_by_staff_id')->nullable();
            $table->unsignedBigInteger('checked_out_by_staff_id')->nullable();

            $table->text('note')->nullable();

            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            /* ===============================
               Foreign Keys
               =============================== */

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('guest_id')->references('id')->on('library_guests')->nullOnDelete();

            $table->foreign('checked_in_by_staff_id')->references('id')->on('staff')->nullOnDelete();
            $table->foreign('checked_out_by_staff_id')->references('id')->on('staff')->nullOnDelete();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            /* ===============================
               Indexes
               =============================== */
            $table->index(['visit_date', 'session']);
            $table->index(['user_id', 'visit_date']);
            $table->index(['guest_id', 'visit_date']);
            $table->index('is_active');
            $table->index('created_at');

            // NOTE (Business rule): a visit must have either user_id OR guest_id (not both).
            // Enforce via validation in controller (recommended).
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_visits');
    }
};
