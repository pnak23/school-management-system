<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('communes')) {
            Schema::create('communes', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->foreignId('district_id')->constrained('districts')->cascadeOnUpdate()->restrictOnDelete();
                $table->string('name_en', 120);
                $table->string('name_km', 120)->nullable();
                $table->timestamps();

                $table->unique(['district_id', 'name_en']);
                $table->index('district_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('communes');
    }
};


