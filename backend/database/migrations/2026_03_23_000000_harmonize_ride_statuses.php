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
        // MySQL specific: Update enum values
        DB::statement("ALTER TABLE rides MODIFY COLUMN status ENUM('searching', 'requested', 'accepted', 'arrived', 'started', 'completed', 'cancelled') DEFAULT 'searching'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE rides MODIFY COLUMN status ENUM('requested', 'accepted', 'in_progress', 'completed', 'cancelled') DEFAULT 'requested'");
    }
};
