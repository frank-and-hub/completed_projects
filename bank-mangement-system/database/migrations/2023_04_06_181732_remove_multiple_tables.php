<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveMultipleTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    protected $tables = [
        'admin_bank',
        'alerts',
        'all_transaction', 
        'assets', 
        'asset_transfer', 
        'associate_transaction', 
        'bank', 
        'bank_transfer', 
        'branch_cash', 
        'branch_closing', 
        'brands', 
        'buyer_log', 
        'chart', 
        // 'commissions_entry_loan', 
        // 'commission_carry_forward', 
        // 'commission_details_nov_old', 
        // 'commission_entry_detail', 
        // 'commission_loan_details', 
        'contact', 
        'currency', 
        'deposit', 
        'deposits', 
        'employee_salary_transfer', 
        'etemplates', 
        'ext_transfer', 
        'faq', 
        'gateways', 
        'group_loans_old', 
        'group_loan_members', 
        'int_transfer', 
        'ip_addresses', 
        'jobs', 
        'loan', 
        'loan_logs', 
        'maturity_calculations', //  table will replace by 'plan_tenure' table 
        'member_investments_payments', 
        // 'member_investment_associates', // this table will be rename by 'investment_associate_transfer'
        'Member_investment_interest_tds', // Tds amount will transfer in member_investment_interest . After transfer data >> table remove from code and delete it 
        'member_transaction', 
        'merchants', 
        'pages', 
        'password_resets', 
        'profits', 
        'reply_support', 
        'review', 
        'samraddh_bank_closing', 
        'savings', 
        'seller_log', 
        'services', 
        'slider', 
        'social_links', 
        'subscriber', 
        'sub_account_heads', 
        'support', 
        'tds_deposits', 
        'transactions', 
        'transaction_logs', 
        'transaction_references', 
        'transfers', 
        'tran_acct', 
        'trending', 
        'trending_cat', 
        'ui_design', 
        'user_log', 
        'wallet_address', 
        'winfo', 
        'withdrawm', 
        'w_history'
        ];

    public function up()
    {
        foreach ($this->tables as $table) {
            Schema::dropIfExists($table);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
