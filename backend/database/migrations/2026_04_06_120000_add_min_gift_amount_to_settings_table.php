<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'min_gift_amount')) {
                $table->decimal('min_gift_amount', 12, 2)->default(1000)->after('commission_rate');
            }
        });

        // Set default value for existing rows
        DB::table('settings')->whereNull('min_gift_amount')->update(['min_gift_amount' => 1000]);
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'min_gift_amount')) {
                $table->dropColumn('min_gift_amount');
            }
        });
    }
};
