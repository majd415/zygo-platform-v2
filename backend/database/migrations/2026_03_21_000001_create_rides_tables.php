<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update existing rides table
        Schema::table('rides', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('rides', 'accepted_at')) $table->timestamp('accepted_at')->nullable();
            if (!Schema::hasColumn('rides', 'arrived_at')) $table->timestamp('arrived_at')->nullable();
            if (!Schema::hasColumn('rides', 'started_at')) $table->timestamp('started_at')->nullable();
            if (!Schema::hasColumn('rides', 'completed_at')) $table->timestamp('completed_at')->nullable();
            if (!Schema::hasColumn('rides', 'cancelled_at')) $table->timestamp('cancelled_at')->nullable();
            
            if (!Schema::hasColumn('rides', 'distance_meters')) $table->integer('distance_meters')->nullable();
            if (!Schema::hasColumn('rides', 'duration_seconds')) $table->integer('duration_seconds')->nullable();
        });

        // 2. Create ride_requests table
        if (!Schema::hasTable('ride_requests')) {
            Schema::create('ride_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ride_id')->constrained()->onDelete('cascade');
                $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
                $table->string('status')->default('sent'); // sent, accepted, rejected, ignored, expired
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ride_requests');
        Schema::table('rides', function (Blueprint $table) {
            $table->dropColumn(['accepted_at', 'arrived_at', 'started_at', 'completed_at', 'cancelled_at', 'distance_meters', 'duration_seconds']);
        });
    }
};
