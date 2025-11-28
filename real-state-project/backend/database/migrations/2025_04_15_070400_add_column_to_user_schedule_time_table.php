<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_schedule_time', function (Blueprint $table) {
            if (!Schema::hasColumn('user_schedule_time', 'user_subscription_id')) {
                $table->char('user_subscription_id', 36)->nullable()->after('schedule_type');
            } else if (Schema::hasColumn('user_schedule_time', 'user_subscription_id')) {
                Schema::table('user_schedule_time', function (Blueprint $table) {
                    $table->char('user_subscription_id', 36)->nullable()->change();
                });
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_schedule_time', function (Blueprint $table) {
            // Drop foreign key if it exists
            // $table->dropForeign('user_schedule_time_user_id_foreign');
            if (Schema::hasColumn('user_schedule_time', 'user_subscription_id')) {
                $table->dropColumn('user_subscription_id');
            }
        });
    }
};
