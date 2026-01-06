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
        Schema::table('library_publishers', function (Blueprint $table) {
            // Add missing contact fields
            $table->string('address', 255)->nullable()->after('name');
            $table->string('phone', 30)->nullable()->after('address');
            $table->string('email', 100)->nullable()->after('phone');
            
            // Remove old fields if they exist (optional)
            // $table->dropColumn(['country', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('library_publishers', function (Blueprint $table) {
            $table->dropColumn(['address', 'phone', 'email']);
        });
    }
};
