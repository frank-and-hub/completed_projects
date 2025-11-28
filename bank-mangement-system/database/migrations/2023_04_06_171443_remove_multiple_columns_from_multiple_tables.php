<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveMultipleColumnsFromMultipleTables extends Migration
{

    
    /**
     * Run the migrations.
     *
      * @return void
      */
  








    
    public function up()
    {
      
            Schema::table('advanced_transaction', function (Blueprint $table) {
                $table->dropColumn([ 'Amount_to_id',
                'Amount_to_name',
                'Amount_from_id',
                'Amount_from_name',
                'V_date',
                'Cheque_date',
                'Cheque_bank_from',
                'Cheque_bank_ac_from',
                'Cheque_bank_ifsc_from',
                'Cheque_bank_branch_from',
                'Cheque_bank_from_id',
                'Cheque_bank_ac_from_id',
                'Cheque_bank_to',
                'Cheque_bank_ac_to',
                'Cheque_bank_to_name',
                'Cheque_bank_to_branch',
                'Cheque_bank_to_ac_no',
                'Cheque_bank_to_ifsc',
                'Transction_bank_from',
                'Transction_bank_ac_from',
                'Transction_bank_ifsc_from',
                'Transction_bank_branch_from',
                'Transction_bank_from_id',
                'Transction_bank_from_ac_id',
                'Transction_bank_to',
                'Transction_bank_ac_to',
                'Transction_bank_to_name',
                'Transction_bank_to_ac_no',
                'Transction_bank_to_branch',
                'transction_bank_to_ifsc']);
            });

            Schema::table('all_head_transaction', function (Blueprint $table) {
                $table->dropColumn(['Opening_balance',
                'closing_balance',
                'Amount_to_id',
                'Amount_to_name',
                'Amount_from_id',
                'Amount_from_name',
                'V_date',
                'Cheque_date',
                'Cheque_bank_from',
                'Cheque_bank_ac_from',
                'Cheque_bank_ifsc_from',
                'Cheque_bank_branch_from',
                'Cheque_bank_from_id',
                'Cheque_bank_ac_from_id',
                'Cheque_bank_to',
                'Cheque_bank_ac_to',
                'Cheque_bank_to_name',
                'Cheque_bank_to_branch',
                'Cheque_bank_to_ac_no',
                'Cheque_bank_to_ifsc',
                'Transction_bank_from',
                'Transction_bank_ac_from',
                'Transction_bank_ifsc_from',
                'Transction_bank_branch_from',
                'Transction_bank_from_id',
                'Transction_bank_from_ac_id',
                'Transction_bank_to',
                'Transction_bank_ac_to',
                'Transction_bank_to_name',
                'Transction_bank_to_ac_no',
                'Transction_bank_to_branch',
                'Transction_bank_to_ifsc',
                'transction_date']);
            });

            Schema::table('branch_daybook', function (Blueprint $table) {
                $table->dropColumn([   'Opening_balance',
                'closing_balance',
                'Amount_to_id',
                'Amount_to_name',
                'Amount_from_id',
                'Amount_from_name',
                'V_date',
                'Cheque_date',
                'Cheque_bank_from',
                'Cheque_bank_ac_from',
                'Cheque_bank_ifsc_from',
                'Cheque_bank_branch_from',
                'Cheque_bank_from_id',
                'Cheque_bank_ac_from_id',
                'Cheque_bank_to',
                'Cheque_bank_ac_to',
                'Cheque_bank_to_name',
                'Cheque_bank_to_branch',
                'Cheque_bank_to_ac_no',
                'Cheque_bank_to_ifsc',
                'Transction_bank_from',
                'Transction_bank_ac_from',
                'Transction_bank_ifsc_from',
                'Transction_bank_branch_from',
                'Transction_bank_from_id',
                'Transction_bank_from_ac_id',
                'Transction_bank_to',
                'Transction_bank_ac_to',
                'Transction_bank_to_name',
                'Transction_bank_to_ac_no',
                'Transction_bank_to_branch',
                'Transction_bank_to_ifsc',
                'Transction_date',
                'Is_contra',
                'Contra_id',
                'Amount_type']);
            });

            Schema::table('customer_transaction', function (Blueprint $table) {
                $table->dropColumn([ 'Amount_to_id',
                'Amount_to_name',
                'Amount_from_id',
                'Amount_from_name',
                'V_date',
                'Cheque_date',
                'Cheque_bank_from',
                'Cheque_bank_ac_from',
                'Cheque_bank_ifsc_from',
                'Cheque_bank_branch_from',
                'Cheque_bank_from_id',
                'Cheque_bank_ac_from_id',
                'Cheque_bank_to',
                'Cheque_bank_ac_to',
                'Cheque_bank_to_name',
                'Cheque_bank_to_branch',
                'Cheque_bank_to_ac_no',
                'Cheque_bank_to_ifsc',
                'Transction_bank_from',
                'Transction_bank_ac_from',
                'Transction_bank_ifsc_from',
                'Transction_bank_branch_from',
                'Transction_bank_from_id',
                'Transction_bank_from_ac_id',
                'Transction_bank_to',
                'Transction_bank_ac_to',
                'Transction_bank_to_name',
                'Transction_bank_to_ac_no',
                'Transction_bank_to_branch',
                'transction_bank_to_ifsc']);
            });

            Schema::table('vendor_transaction', function (Blueprint $table) {
                $table->dropColumn(['Amount_to_id',
                'Amount_to_name',
                'Amount_from_id',
                'Amount_from_name',
                'V_date',
                'Cheque_date',
                'Cheque_bank_from',
                'Cheque_bank_ac_from',
                'Cheque_bank_ifsc_from',
                'Cheque_bank_branch_from',
                'Cheque_bank_from_id',
                'Cheque_bank_ac_from_id',
                'Cheque_bank_to',
                'Cheque_bank_ac_to',
                'Cheque_bank_to_name',
                'Cheque_bank_to_branch',
                'Cheque_bank_to_ac_no',
                'Cheque_bank_to_ifsc',
                'Transction_bank_from',
                'Transction_bank_ac_from',
                'Transction_bank_ifsc_from',
                'Transction_bank_branch_from',
                'Transction_bank_from_id',
                'Transction_bank_from_ac_id',
                'Transction_bank_to',
                'Transction_bank_ac_to',
                'Transction_bank_to_name',
                'Transction_bank_to_ac_no',
                'Transction_bank_to_branch',
                'transction_bank_to_ifsc']);
            });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('multiple_tables', function (Blueprint $table) {
            //
        });
    }
}