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
        Schema::create('sent_internal_property_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignUuid('internal_property_id')->references('id')->on('internal_properties')->onDelete('cascade');
            $table->foreignUuid('search_id')->references('id')->on('user_search_property')->onDelete('cascade');
            $table->foreignUuid('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->enum('status', ['pending', 'sent'])->default('pending');
            $table->string('message_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('sent_internal_property_users', function (Blueprint $table) {
        //     $table->dropForeign(['user_id']);
        //     $table->dropForeign(['internal_property_id']);
        //     $table->dropForeign(['search_id']);
        //     $table->dropForeign(['admin_id']);
        // });
        Schema::dropIfExists('sent_internal_property_users');
    }
};
