<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Service Areas
        Schema::create('service_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('radius_km', 8, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Driver Documents
        Schema::create('driver_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('national_id_front')->nullable();
            $table->string('national_id_back')->nullable();
            $table->string('car_photo')->nullable();
            $table->string('car_type')->nullable();
            $table->string('car_model')->nullable();
            $table->string('driving_license')->nullable();
            $table->timestamps();
        });

        // Coupons
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->decimal('fixed_discount', 10, 2)->nullable();
            $table->date('expiration_date')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Rides
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rider_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('pickup_address');
            $table->decimal('pickup_lat', 10, 8);
            $table->decimal('pickup_lng', 11, 8);
            $table->string('dropoff_address');
            $table->decimal('dropoff_lat', 10, 8);
            $table->decimal('dropoff_lng', 11, 8);
            
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->decimal('ride_price', 10, 2)->nullable();
            $table->enum('currency', ['usd', 'syp'])->default('usd');
            $table->enum('status', ['requested', 'accepted', 'in_progress', 'completed', 'cancelled'])->default('requested');
            
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->onDelete('set null');
            $table->enum('payment_method', ['cash', 'wallet'])->default('cash');

            $table->timestamps();
        });

        // Wallets transactions (balance is in users table)
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['credit', 'debit']);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Settings
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('app_name')->default('TaxiApp');
            $table->string('logo')->nullable();
            $table->decimal('price_per_km_usd', 8, 2)->default(1.50);
            $table->decimal('price_per_km_syp', 8, 2)->default(20000);
            $table->string('support_phone')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('rides');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('driver_documents');
        Schema::dropIfExists('service_areas');
    }
};
