<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->string('user_type')->nullable()->after('user_id'); // driver, rider, platform
            $table->string('transaction_type')->nullable()->after('type'); // ride_payment, commission, recharge, payout
            $table->decimal('balance_before', 15, 2)->nullable()->after('description');
            $table->decimal('balance_after', 15, 2)->nullable()->after('balance_before');
            $table->unsignedBigInteger('ride_id')->nullable()->after('balance_after');
            
            $table->foreign('ride_id')->references('id')->on('rides')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropForeign(['ride_id']);
            $table->dropColumn(['user_type', 'transaction_type', 'balance_before', 'balance_after', 'ride_id']);
        });
    }
};
