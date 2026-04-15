<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add service category multipliers to settings and service_category to users (drivers).
     * Economy = base price (multiplier 1.0, implicit).
     * Comfort = base price × comfort_multiplier.
     * Premium = base price × premium_multiplier.
     */
    public function up(): void
    {
        // 1. Add multipliers to settings
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'comfort_multiplier')) {
                $table->decimal('comfort_multiplier', 5, 2)->default(1.10)->after('commission_rate');
            }
            if (!Schema::hasColumn('settings', 'premium_multiplier')) {
                $table->decimal('premium_multiplier', 5, 2)->default(1.25)->after('comfort_multiplier');
            }
        });

        // 2. Add service_category to users (for driver classification)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'service_category')) {
                $table->string('service_category')->default('economy')->after('role');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('settings', 'comfort_multiplier')) $columns[] = 'comfort_multiplier';
            if (Schema::hasColumn('settings', 'premium_multiplier')) $columns[] = 'premium_multiplier';
            if (!empty($columns)) $table->dropColumn($columns);
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'service_category')) {
                $table->dropColumn('service_category');
            }
        });
    }
};
