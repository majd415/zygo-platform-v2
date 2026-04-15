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
            $table->string('registration_front')->nullable()->after('license_back');
            $table->string('registration_back')->nullable()->after('registration_front');
            $table->string('insurance_photo')->nullable()->after('registration_back');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_documents', function (Blueprint $table) {
            $table->dropColumn(['registration_front', 'registration_back', 'insurance_photo']);
        });
    }
};
