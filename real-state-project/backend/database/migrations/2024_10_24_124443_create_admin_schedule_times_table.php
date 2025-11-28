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
        Schema::table('admins', function (Blueprint $table) {
            $table->boolean('is_whatsapp_notification')->default(true);
        });

        Schema::create('admin_schedule_times', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('admin_id')->constrained('admins')->onDelete('cascade');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('is_whatsapp_notification');
        });
        Schema::dropIfExists('admin_schedule_times');
    }
};
