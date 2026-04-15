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
        Schema::table('driver_documents', function (Blueprint $table) {
            $table->string('car_photo_front')->nullable()->after('car_photo');
            $table->string('car_photo_back')->nullable()->after('car_photo_front');
            $table->string('car_photo_left')->nullable()->after('car_photo_back');
            $table->string('car_photo_right')->nullable()->after('car_photo_left');
            $table->string('license_back')->nullable()->after('driving_license');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_documents', function (Blueprint $table) {
            $table->dropColumn(['car_photo_front', 'car_photo_back', 'car_photo_left', 'car_photo_right', 'license_back']);
        });
    }
};
