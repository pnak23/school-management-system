<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('khmer_name', 150);
            $table->string('english_name', 150)->nullable();
            $table->date('dob')->nullable();
            $table->enum('sex', ['M', 'F']);
            $table->string('teacher_code', 50)->nullable();
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
            
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->foreignId('employment_type_id')->nullable()->constrained('employment_types')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('teacher_code');
            $table->index('is_active');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};


