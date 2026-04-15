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
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if (!Schema::hasColumn('settings', 'search_radius_km')) {
                    $table->decimal('search_radius_km', 8, 2)->default(5.00)->after('price_per_km_syp');
                }
            });

            // Set initial value for existing settings
            DB::table('settings')->update(['search_radius_km' => 5.00]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if (Schema::hasColumn('settings', 'search_radius_km')) {
                    $table->dropColumn('search_radius_km');
                }
            });
        }
    }
};
