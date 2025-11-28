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
        Schema::table('admin_subscription', function (Blueprint $table) {
            $table->string('pf_payment_id')->nullable();
            $table->string('amount_fee')->nullable()->after('amount');
            $table->string('amount_net')->nullable()->after('amount');
            $table->string('amount_gross')->nullable()->after('amount');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_subscription', function (Blueprint $table) {
            $table->dropColumn('pf_payment_id');
            $table->dropColumn('amount_fee');
            $table->dropColumn('amount_net');
            $table->dropColumn('amount_gross');

        });
    }
};
