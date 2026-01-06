<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('villages')) {
            Schema::create('villages', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->foreignId('commune_id')->constrained('communes')->cascadeOnUpdate()->restrictOnDelete();
                $table->string('name_en', 120);
                $table->string('name_km', 120)->nullable();
                $table->timestamps();

                $table->unique(['commune_id', 'name_en']);
                $table->index('commune_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('villages');
    }
};


