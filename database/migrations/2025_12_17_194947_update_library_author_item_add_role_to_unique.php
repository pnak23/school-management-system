<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('library_author_item', function (Blueprint $table) {
            // Drop old unique constraint
            $table->dropUnique('uniq_library_item_author');
            
            // Add ID column as primary key if doesn't exist
            if (!Schema::hasColumn('library_author_item', 'id')) {
                $table->id()->first();
            }
            
            // Make role NOT nullable and set enum values
            DB::statement("ALTER TABLE library_author_item MODIFY role ENUM('author', 'editor', 'translator', 'illustrator', 'contributor') NOT NULL DEFAULT 'author'");
            
            // Add new unique constraint with role
            $table->unique(['library_item_id', 'author_id', 'role'], 'unique_item_author_role');
            
            // Update is_active to tinyint if it's boolean
            DB::statement("ALTER TABLE library_author_item MODIFY is_active TINYINT(1) DEFAULT 1");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('library_author_item', function (Blueprint $table) {
            // Drop new constraint
            $table->dropUnique('unique_item_author_role');
            
            // Restore old constraint
            $table->unique(['library_item_id', 'author_id'], 'uniq_library_item_author');
            
            // Revert role to nullable
            DB::statement("ALTER TABLE library_author_item MODIFY role VARCHAR(50) NULL");
        });
    }
};
