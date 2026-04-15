<?php
// c:\xampp\htdocs\taxiApp_backend\backend\database\migrations\2026_04_02_110000_add_localized_fields_to_notifications_table.php

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
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'title_en')) {
                $table->string('title_en')->nullable()->after('type');
            }
            if (!Schema::hasColumn('notifications', 'title_ar')) {
                $table->string('title_ar')->nullable()->after('title_en');
            }
            if (!Schema::hasColumn('notifications', 'message_en')) {
                $table->text('message_en')->nullable()->after('title_ar');
            }
            if (!Schema::hasColumn('notifications', 'message_ar')) {
                $table->text('message_ar')->nullable()->after('message_en');
            }
            if (!Schema::hasColumn('notifications', 'image')) {
                $table->string('image')->nullable()->after('message_ar');
            }
            if (!Schema::hasColumn('notifications', 'link')) {
                $table->string('link')->nullable()->after('image');
            }
            if (!Schema::hasColumn('notifications', 'target')) {
                $table->string('target')->nullable()->default('all')->after('link');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'title_ar', 'message_en', 'message_ar', 'image', 'link', 'target']);
        });
    }
};
