<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToMultipleTables extends Migration
{
     protected $tables = [
        // 'account_heads',
		// 'advanced_transaction',
		// 'all_head_transaction',
		// 'associate_commissions_total_monthly',
		// 'associate_kota_business',
		// 'associate_monthly_commission',
		// 'associate_tds_deduct',
		// 'associate_transaction',
		// 'balancesheet_closed',
		// 'balance_sheet_closings',
		// 'banking_advanced_ledger',
		// 'banking_due_bills_ledger',
		// 'banking_ledger',
		// 'bill_expenses',
		// 'branch_daybook',
		// 'commission_leasers',
		// 'commission_leaser_details',
		// 'commission_leaser_detail_monthly',
		// 'commission_leaser_monthly',
		// 'commission_month_end',
		// 'company_bound',
		// 'correction_log',
		// 'correction_requests',
		// 'credit_card',
		// 'credit_card_transaction',
		// 'customer_transaction',
		// 'day_books',
		// 'debit_card',
		// 'designations',
		// 'eli_moneyback_investments',
		// 'emiloan',
		'plans',
		// 'employees',
		// 'employee_application',
		// 'employee_ledgers',
		// 'employee_salary',
		// 'employee_salary_leasers',
		// 'expenses',
		// 'expense_item',
		// 'expense_logs',
		// 'fa_codes',
		// 'funds_transfer',
		// 'group_loans',
		// 'gst_setting',
		// 'head_closing_balances',
		// 'head_setting',
		// 'holiday_settings',
		// 'jv_journals',
		// 'jv_journal_heads',
		// 'loans',
		// 'loan_charges',
		// 'loan_day_books',
		// 'loan_emis',
		// 'loan_from_banks',
		// 'loan_tenures',
		// 'members',
		// 'member_investments',
		// 'member_investment_interest',
		// 'member_loans',
		// 'noticeboards',
		// 'notifications',
		// 'received_cheques',
		// 'received_vouchers',
		// 'rent_ledgers',
		// 'rent_liabilities',
		// 'rent_liability_ledgers',
		// 'rent_payments',
		// 'roles',
		// 'samraddh_bank_accounts',
		// 'samraddh_bank_daybook',
		// 'saving_accounts',
		// 'shareholders',
		// 'tds_payables',
		// 'user_activities',
		// 'vendors',
		// 'vendor_bills',
		// 'vendor_bill_items',
		// 'vendor_bill_payments',
		// 'vendor_categories',
		// 'vendor_transaction',
        // add more tables here
    ];
    public function up()
    {
		foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable();
				$table->foreign('company_id')->references('id')->on('companies');
				$table->index('company_id');
            });
        }
        // Schema::table('multiple_tables', function (Blueprint $table) {
            //
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    { 	foreach ($this->tables as $table) {
			Schema::table($table, function (Blueprint $table) {
				//
			});
		}
    }
}
