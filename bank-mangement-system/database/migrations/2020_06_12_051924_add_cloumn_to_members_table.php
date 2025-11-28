<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropMembers_occupation_id_foreignCloumnToMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropForeign('members_occupation_id_foreign');
             $table->dropForeign('members_religion_id_foreign');
              $table->dropForeign('members_special_category_id_foreign');
            });

        Schema::table('members', function (Blueprint $table) {        

            $table->date('anniversary_date')->nullable()->change();
            $table->string('annual_income')->nullable()->change();
            $table->string('village')->nullable()->change();
            $table->bigInteger('occupation_id')->default(0)->change();
            $table->bigInteger('religion_id')->default(0)->change();
            $table->string('signature')->nullable()->change();
            $table->string('photo')->nullable()->change();
            $table->bigInteger('special_category_id')->default(0)->change();
            $table->string('member_id',100)->unique()->after('role_id'); 
            $table->string('associate_code',100)->nullable()->after('member_id');
            $table->integer('mi_code')->after('associate_code');
            $table->integer('fa_code')->after('mi_code');
            $table->integer('age')->default(0)->after('dob');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            //
        });
    }
}
