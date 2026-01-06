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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            
            // User relationship (optional - if student has a user account)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            // Basic Information
            $table->string('khmer_name', 255);
            $table->string('english_name', 255);
            $table->date('dob'); // Date of Birth
            $table->enum('sex', ['M', 'F']);
            $table->string('code', 100)->nullable(); // Student code/ID
            $table->text('note')->nullable();
            $table->string('photo', 255)->nullable();
            
            // Birthplace Location (using INT as per requirement)
            $table->integer('birthplace_province_id')->nullable();
            $table->integer('birthplace_district_id')->nullable();
            $table->integer('birthplace_commune_id')->nullable();
            $table->integer('birthplace_village_id')->nullable();
            
            // Current Address Location (using INT as per requirement)
            $table->integer('current_province_id')->nullable();
            $table->integer('current_district_id')->nullable();
            $table->integer('current_commune_id')->nullable();
            $table->integer('current_village_id')->nullable();
            
            // Audit Fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->tinyInteger('is_active')->default(1);
            
            // Timestamps
            $table->timestamps();
            
            // Foreign Keys for Audit Fields
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('user_id');
            $table->index('code');
            $table->index('is_active');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
