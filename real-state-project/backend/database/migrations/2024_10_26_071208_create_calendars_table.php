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
        Schema::create('calendars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('internal_propertie_id')->references('id')->on('internal_properties')->onDelete('cascade');
            $table->foreignUuid('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignUuid('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->foreignUuid('sent_internal_property_user_id')->references('id')->on('sent_internal_property_users')->onDelete('cascade');

            $table->timestamp('event_datetime');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('link')->nullable();
            $table->enum('status', ['pending', 'accepted', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendars');
    }
};
