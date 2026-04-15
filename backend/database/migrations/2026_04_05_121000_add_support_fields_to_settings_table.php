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
        Schema::table('settings', function (Blueprint $box) {
            $box->string('whatsapp_phone')->nullable()->after('support_phone');
            $box->string('email_support')->nullable()->after('whatsapp_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $box) {
            $box->dropColumn(['whatsapp_phone', 'email_support']);
        });
    }
};
