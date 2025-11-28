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
        Schema::table('notifications', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            if (Schema::hasColumn('notifications', 'notifiable_type')) {
                $table->dropColumn('notifiable_type');
                $table->dropColumn('notifiable_id');
            }
            if (Schema::hasColumn('notifications', 'data')) {
                $table->dropColumn('data');
            }
            $table->unsignedBigInteger('user_id')->after('id');
            $table->string('title', 255)->nullable()->after('type');
            $table->longText('meta')->nullable()->after('type');
            $table->longText('message')->nullable()->after('type');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->dropColumn('title');
            $table->dropColumn('meta');
            $table->dropColumn('message');
            $table->morphs('notifiable');
            $table->text('data');
        });
    }
};
