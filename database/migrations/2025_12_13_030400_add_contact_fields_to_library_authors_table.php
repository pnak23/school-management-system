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
        Schema::table('library_authors', function (Blueprint $table) {
            // Add contact fields after biography
            $table->string('phone', 30)->nullable()->after('biography');
            $table->string('email', 100)->nullable()->after('phone');
            $table->string('website', 255)->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('library_authors', function (Blueprint $table) {
            $table->dropColumn(['phone', 'email', 'website']);
        });
    }
};


