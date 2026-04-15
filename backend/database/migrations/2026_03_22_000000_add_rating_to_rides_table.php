<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->integer('rating')->nullable();
            $table->string('rating_comment')->nullable();
        });
        
        // Also ensure user table has average rating
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'rating')) {
                $table->decimal('rating', 3, 2)->default(5.00);
                $table->integer('rating_count')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->dropColumn(['rating', 'rating_comment']);
        });
    }
};
