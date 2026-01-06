<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('districts')) {
            Schema::create('districts', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->foreignId('province_id')->constrained('provinces')->cascadeOnUpdate()->restrictOnDelete();
                $table->string('name_en', 120);
                $table->string('name_km', 120)->nullable();
                $table->enum('type', ['district', 'city'])->default('district');
                $table->timestamps();

                $table->unique(['province_id', 'name_en']);
                $table->index('province_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};


