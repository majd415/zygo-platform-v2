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
        Schema::table('rides', function (Blueprint $table) {
            if (!Schema::hasColumn('rides', 'car_type')) {
                $table->string('car_type')->nullable()->after('dropoff_lng');
            }
            if (!Schema::hasColumn('rides', 'payment_method')) {
                $table->string('payment_method')->default('cash')->after('car_type');
            }
            if (!Schema::hasColumn('rides', 'coupon_id')) {
                $table->foreignId('coupon_id')->nullable()->constrained('coupons')->after('payment_method');
            }
            if (!Schema::hasColumn('rides', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0)->after('coupon_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('rides', 'car_type')) $columns[] = 'car_type';
            if (Schema::hasColumn('rides', 'payment_method')) $columns[] = 'payment_method';
            if (Schema::hasColumn('rides', 'coupon_id')) $columns[] = 'coupon_id';
            if (Schema::hasColumn('rides', 'discount_amount')) $columns[] = 'discount_amount';
            
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
