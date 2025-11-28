<?php
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AssociateRegistration\AssociateInvestmentController;
use App\Http\Controllers\Api\Epassbook\EpassBookLoanController;
use App\Http\Controllers\Api\InvestmentManagement\InvestmentDueReportFilter;
use App\Http\Controllers\Api\LoanManagement\LoanController;
use App\Http\Controllers\Api\AssociateRegistration\AssociateReportController;
use App\Http\Controllers\Api\AssociatePlanCategoryController;
use App\Http\Controllers\Api\LoanPlanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::middleware('auth:api')->get('/user', function (Request $request) {
	return $request->user();
});
Route::group(['namespace' => 'Api'], function () {
	Route::post('/sendotp', 'LoginController@sendOtp');
	Route::post('/otpvarified', 'LoginController@otpVarified');
	Route::post('/setupicode', 'LoginController@setUpiCode');
	Route::post('/loginwithupi', 'LoginController@loginWithUpi');
	Route::post('/memberinvestments', 'MemberinvestmentController@memberInvestments');
	Route::post('/investmenttransactions', 'MemberinvestmentController@investmentTransactions');
	Route::post('/viewtransactiondetails', 'MemberinvestmentController@viewTransactionDetails');
	Route::post('/memberdetails', 'MemberinvestmentController@memberDetails');
	//----------- Associate Api  Start -----------------
	// -------------    Login  MANAGEMENT --------------
	Route::post('/associate_sendotp', 'AssociateLoginController@sendOtp');
	Route::post('/send_payment_otp', 'AssociateLoginController@send_payment_otp');
	// New Api for renewal otp added by Sourab Biswas on 21-12-2023
	Route::post('/send_renewal_otp', 'AssociateLoginController@sendRenewalOtp');
	//
	Route::post('/associate_otpvarified', 'AssociateLoginController@otpVarified');
	Route::post('/associate_setupicode', 'AssociateLoginController@setUpiCode');
	Route::post('/associate_loginwithupi', 'AssociateLoginController@loginWithUpi');
	Route::post('/associate_user_profile', 'AssociateLoginController@user_profile');
	// -------------    MEMBER  MANAGEMENT --------------
	Route::post('/associate_memberlist', 'AssociateMemberController@member_list');
	Route::post('/associate_membermatch', 'AssociateMemberController@member_match');
	Route::post('/associate_activelist', 'AssociateMemberController@active_associate_list');
	Route::post('/associate_memberdetail', 'AssociateMemberController@member_detail');
	Route::post('/associate_memberbalance', 'AssociateMemberController@member_balance');
	// -------------    ASSOCIATES MANAGEMENT --------------
	Route::post('/associate_list', 'AssociateDetailController@associateList');
	Route::post('/associate_commission_list', 'AssociateDetailController@associateCommissionList');
	Route::post('/commission_ledger_list', 'AssociateDetailController@commissionLedgerList');
	Route::post('/associate_commission_detail_list', 'AssociateDetailController@commissionDetailList');
	Route::post('/associate_ssb_account_balance', 'AssociateDetailController@associate_Ssb_Current_Balance');
	Route::post('/associate_ssb_details', 'AssociateDetailController@associateSsbDetails');
	Route::post('/renewal', 'AssociateDetailController@renewstore');
	Route::post('/associate_detail', 'AssociateDetailController@associate_detail');
	Route::post('/associate_detail_list', 'AssociateDetailController@associate_detail_list');
	Route::post('/associate_quota_list', 'AssociateDetailController@associateQuotaList');
	Route::post('/associate_investment_commission', 'AssociateDetailController@associateInvestmentCommission');
	Route::post('/associate_loan_commission', 'AssociateDetailController@associateLoanCommission');
	Route::post('/associate_tree_list', 'AssociateDetailController@associateTreeList');
	Route::post('/associate_tree', 'AssociateDetailController@associateTreeView');
	// -------------    Account  MANAGEMENT --------------
	Route::post('/account_ledger', 'AcoountDetailController@accountTranscation');
	Route::post('/transaction_detail', 'AcoountDetailController@transcationDetail');
	Route::post('/loan_ledger', 'AcoountDetailController@loan_ledger');
	Route::post('/grouploan_ledger', 'AcoountDetailController@grouploan_ledger');
	// -------------    Investment   MANAGEMENT --------------
	Route::post('/branch_list', 'AssociateInvestmentController@branchList');
	Route::post('/plan_register_paymentmode', 'AssociateInvestmentController@paymentModeRegister');
	Route::post('/plan_list', 'AssociateInvestmentController@planList');
	Route::post('/renew_paymentmode', 'AssociateInvestmentController@paymentModeRenew');
	Route::post('/associate_investment_list', 'AssociateInvestmentController@investmentList');
	Route::post('/common_investment', 'AssociateInvestmentController@commonListingInvestment');
	Route::post('/common_renew', 'AssociateInvestmentController@commonListingRenew');
	Route::post('/invest_member_detail', 'AssociateInvestmentController@investmentMemberDetail');
	Route::post('/associate_investment_detail', 'AssociateInvestmentController@investment_detail');
	Route::post('/collector_agent', 'AssociateInvestmentController@collectorDetail');
	Route::post('/renew_account_detail', 'AssociateInvestmentController@renewAccountDetail');
	Route::post('/plan_tenure', 'AssociateInvestmentController@tenurePlan');
	Route::post('/plan_maturity', 'AssociateInvestmentController@planMaturity');
	Route::post('/renew_submit', 'AssociateInvestmentController@renewSubmit');
	Route::post('/mb_maturity', 'AssociateInvestmentController@mbMaturity');
	Route::post('/samraddh_jeevan_maturity', 'AssociateInvestmentController@samraddhJeevanMaturity');
	Route::post('/samraddh_bhavhishya_maturity', 'AssociateInvestmentController@samraddhBhavhishyaMaturity');
	Route::post('/samraddh_kanyadhan_yojana', 'AssociateInvestmentController@samraddhKanyadhanYojana');
	Route::post('/associate_renew_report', 'AssociateInvestmentController@associate_renew_report');
	Route::post('/associate_renewSSB_report', 'AssociateInvestmentController@associate_renewSSB_report');
	Route::post('/ssb_investment_register', 'AssociateInvestmentController@registerSSBInvestment');
	Route::post('/kanyadhan_investment_register', 'AssociateInvestmentController@registerKanyadhanInvestment');
	Route::post('/mb_investment_register', 'AssociateInvestmentController@registerMbInvestment');
	Route::post('/ffd_investment_register', 'AssociateInvestmentController@registerFFDInvestment');
	Route::post('/frd_investment_register', 'AssociateInvestmentController@registerFRDInvestment');
	Route::post('/jeevan_investment_register', 'AssociateInvestmentController@registerJeevanInvestment');
	Route::post('/dd_investment_register', 'AssociateInvestmentController@registerSddInvestment');
	Route::post('/mis_investment_register', 'AssociateInvestmentController@registerMISInvestment');
	Route::post('/fd_investment_register', 'AssociateInvestmentController@registerFDInvestment');
	Route::post('/rd_investment_register', 'AssociateInvestmentController@registerRDInvestment');
	Route::post('/sb_investment_register', 'AssociateInvestmentController@registerSBInvestment');
	// -------------    Loan   MANAGEMENT --------------
	Route::post('/loan_list', 'AssociateLoanController@loanList');
	Route::post('/group_loan_list', 'AssociateLoanController@groupLoanList');
	Route::post('/loan-amount-details', 'AssociateLoanController@loanAmountDetails');
	Route::post('/group-loan-amount-details', 'AssociateLoanController@groupLoanAmountDetails');
	Route::post('/associate-details', 'AssociateLoanController@associateDetails');
	Route::post('/deposit-emi', 'AssociateLoanController@depositEmi');
	Route::post('/deposit-group-loan-emi', 'AssociateLoanController@depositGroupLoanEmi');
	Route::post('/loan_recovery', 'AssociateLoanController@loanRecovery');
	Route::post('/group_loan_recovery', 'AssociateLoanController@groupLoanRecovery');
	Route::post('/loan_recovery_list', 'AssociateLoanController@loanRecoveryList');
	Route::post('/group_loan_recovery_list', 'AssociateLoanController@groupLoanRecoveryList');
	Route::post('/pl_loan_detail', 'AssociateLoanController@plLoanDetail');
	Route::post('/staff_loan_detail', 'AssociateLoanController@staffLoanDetail');
	Route::post('/investment_loan_detail', 'AssociateLoanController@investmentLoanDetail');
	Route::post('/group_loan_detail', 'AssociateLoanController@groupLoanDetail');
	// -------------    Loan   MANAGEMENT --------------
	Route::post('/associate_business_report', 'AssociateReportController@associate_business_report');
	Route::post('/associate_business_summary_report', 'AssociateReportController@associate_business_summary_report');
	Route::post('/associate_business_compare_report', 'AssociateReportController@associate_business_compare_report');
	Route::post('/associate_maturity_report', 'AssociateReportController@associate_maturity_report');
	Route::post('/associate_collection_report', 'AssociateReportController@associateCollectionReport');
	Route::post('/associate_collection_compare_report', 'AssociateReportController@associateCollectionCompareReport');
	Route::post('/get_company_name', 'AssociateReportController@companyname');
	//----------- Associate Api  End -----------------
	// Route::post('/renew/store', '/RenewalnewController@store');
	// -------------  Submit Investment Payments --------------
	// Route::post('/submit_group_loan_payment', 'AssociateInvestmentOtpController@submit_group_loan_payment');
	//Route::post('/submit_renewal_payment', 'AssociateInvestmentOtpController@submit_renewal_payment');
	// -------------   Renewal Account  MANAGEMENT --------------
	Route::post('/collactor_account', 'AssociateRenewalController@collactor_account');
	Route::post('/submit_renewal_payment', 'AssociateRenewalController@renewal_payment');
	//----------- Loan controller api-----------------
	Route::post('/submit_loan_payment', 'AssociateLoanApiController@deposite_loan_emi');
	Route::post('/submit_group_loan_payment', 'AssociateLoanApiController@group_loan_payment');
	Route::post('/gst_amount_penalty', 'AssociateRenewalController@gst_amount_penalty');
	/* --------------- E-passbook  start --------------------------------------*/
	// Notification api --- E-passbook
	Route::post('/send_notification', 'Epassbook\NotificationApiController@send_notification');
	Route::post('/get_notification', 'Epassbook\NotificationApiController@get_notification');
	Route::post('/submit_associate_loan_payment', 'Epassbook\NotificationApiController@submitLoanPayment');
	// Route::post('/submit_associate_group_loan_payment', 'Epassbook\NotificationApiController@submitGroupLoanPayment');
	Route::post('/submit_associate_group_loan_payment', 'Epassbook\NotificationApiController@submitGroupLoanPayment');
	// Route::post('/get_notification_details', 'Epassbook\NotificationApiController@getNotificationDetails');
	Route::post('/get_notification_details', 'Epassbook\NotificationApiController@getNotificationDetails');
	// Debit card --------------- E-passbook
	Route::post('/get_ssb_account_balance', 'Epassbook\DebitCardController@checkSSBAccountBalance');
	Route::post('/send_debit_card_amount', 'Epassbook\DebitCardController@sendDebitCardAmount');
	Route::post('/get_account_details', 'Epassbook\AccountListingApiController@getAccountDetails');
	Route::post('/send_loan_otp', 'Epassbook\OtpApiController@sendLoanOtp');
	// Route::post('/submit_investment_payment', 'Epassbook\RenewalController@renewal_payment');
	Route::post('/submit_investment_payment', 'Epassbook\RenewalController@renewal_payment');
	Route::post('/notification_read_update', 'Epassbook\NotificationApiController@notificationReadUpdate');
	Route::post('/get_account_details_new', 'Epassbook\AccountListingApiController@getAccountDetailsNew');
	/* --------------- E-passbook  End  --------------------------------------*/

	/*-------------Associate App Registration api Start----------------------*/
	Route::post('getRegistrationdetails', 'AssociateRegistration\RegistrationDetailController@getOccupation');
	Route::post('getStateDetail', 'AssociateRegistration\RegistrationDetailController@getStateDetail');
	Route::post('getNomineesDetails', 'AssociateRegistration\RegistrationDetailController@getNomineesDetails');
	/*-------------Associate App Registration api End----------------------*/

	/**--------------Associate App multiple Renewal Start*/
	Route::post('get_investment_details', 'RenewalAssociateController@getInvetmentDetails');
	Route::post('associate_renewal_submit', 'RenewalAssociateController@submitRenewals');
	Route::post('associate_collection_report', 'RenewalAssociateController@collectionReport');
	/**--------------Associate App multiple Renewal End*/

	// Associate Buisness Report Create by shahid khan on 11/10/2023
	Route::post('BuisnessReport', 'AssociateRegistration\AssociateBusinessController@report');
	/**--------------Company  Start*/
	// Route::get('company_name', 'CompanyController@companyname');
	/**--------------Company End*/
	// E-passbook and notification routes
	Route::group(['middleware' => 'epassbook.login'], function () {
		Route::post('/get_account_details', 'Epassbook\AccountListingApiController@getAccountDetails');
		Route::post('/epassbook_user_logout', 'Epassbook\AccountListingApiController@epassbookLogout');
		// Route::post('/e_pass/loan_plan', 'Epassbook\EpassBookLoanController@fetchLoans');
		Route::post('/e_pass/loan_listing', 'Epassbook\EpassBookLoanController@getLoanListing');
		Route::post('/e_pass/depositeLoanEmi', 'Epassbook\EpassBookLoanController@depositeLoanEmi');
		// Route::post('/e_pass/loan_details','Epassbook\EpassBookLoanController@fetchLoanDetails');
		// Route::post('/e_pass/loan_deposite_emi','Epassbook\EpassBookLoanController@depositeLoanEmi');
		// Route::post('/e_pass/companyDetails','Epassbook\EpassBookLoanController@companyDetails');
	});

	// ---------------------------- Associate App new update on 21-12-2023 by Sourab Biswas start ----------------------------
	// Loan Listing
	Route::post('plan_lists', 'AssociateRegistration\AssociateLoanController@fetchLoans');
	Route::post('loan_listings', 'AssociateRegistration\AssociateLoanController@getLoanListing');
	Route::post('loan_details', 'AssociateRegistration\AssociateLoanController@fetchLoanDetails');
	Route::post('deposite_loan_emi', 'AssociateRegistration\AssociateLoanController@depositeLoanEmi');
	Route::post('get_company_details', 'AssociateRegistration\AssociateLoanController@companyDetails');
	Route::post('send_loan_emi_otp', 'AssociateRegistration\AssociateLoanController@sendOtp');
	Route::post('get_loan_by_account_number', 'AssociateRegistration\AssociateLoanController@fetchLoanDetailsByAccountNumber');
	// Associate Buisness Report Create by shahid khan on 11/10/2023
	Route::post('BuisnessReport', 'AssociateRegistration\AssociateBusinessController@report');
	// Associate Investment Routes
	Route::group(['middleware' => 'associate.login'], function () {
		// Investment Register Api
		Route::post('customer_details', [AssociateInvestmentController::class, 'customerDetails']);
		Route::post('calculate_maturity_date', [AssociateInvestmentController::class, 'maturityDate']);
		Route::post('nominee_details', [AssociateInvestmentController::class, 'getNomineeDetails']);
		Route::post('relation', [AssociateInvestmentController::class, 'getRelation']);
		Route::post('investment_register', [AssociateInvestmentController::class, 'registerInvestment']);
		Route::post('plan_tenures', [AssociateInvestmentController::class, 'planTenure']);
		Route::post('calculate_kanyadhan_amount', [AssociateInvestmentController::class, 'calculateAgeTenure']);
		// Renewal APis
		// created by Durgesh (24-10-2013)for renewal Investment management
		Route::post('renewal_listing', [AssociateReportController::class, 'renewalTransaction']);
		Route::post('branch', [AssociateReportController::class, 'branch']);
		Route::post('report_plans', [AssociateReportController::class, 'reportPlans']);
		Route::post('transaction_method', [AssociateReportController::class, 'transactions']);
		// Start the Routes for the Loan Controller (By Gaurav)
		Route::post('loan_transaction_listing', [LoanController::class, 'loanListing']);
		Route::post('loan_plans_listing', [LoanPlanController::class, 'planListing']);
		//  Report Create by Gaurav  on 25/10/2023
		Route::post('investmentDueReport', 'AssociateRegistration\InvestmentDueReportController@report');
		Route::post('investment_due_report_pagination', 'AssociateRegistration\InvestmentDueReportController@pagination');
		Route::post('investment_plan_category', [AssociatePlanCategoryController::class, 'getPlanCategory']);
		// Associate Buisness Report Create by shahid khan on 11/10/2023
		Route::post('BuisnessReport', 'AssociateRegistration\AssociateBusinessController@report');
		// Associate Buisness compare Report Create by shahid khan on 24/10/2023
		Route::post('compareBuisnessReport', 'AssociateRegistration\AssociateBusinessCompareReportController@report');
		// Associate comission Report Create by shahid khan on 25/10/2023
		Route::post('associateComissionReport', 'AssociateRegistration\AssociateComissionController@report');
		// Associate collection Report Create by shahid khan on 26/10/2023
		Route::post('associateCollectionReport', 'AssociateRegistration\AssociateCollectionReportController@report');
		Route::post('year', 'AssociateRegistration\AssociateCollectionReportController@getYear');
		Route::post('month', 'AssociateRegistration\AssociateCollectionReportController@getMonth');
		// Active branches of company Create by shahid khan on 11/08/2023
		Route::post('company_branches', 'AssociateRegistration\AssociateCollectionReportController@companyBranch');
		// Create by Mahesh (20-11-2023) for SSB Account Checking
		Route::post('ssb_chk', [AssociateInvestmentController::class, 'ssb_chk']);
		/** ssab transaction listing api created by sourab on 27-11-2023 */
		Route::post('associate_ssb_transaction', 'AssociateDetailController@associate_ssb_transaction');
		Route::post('view_trasaction_deatils', 'AssociateDetailController@view_trasaction_deatils');
	});
	// Create by Gaurav (24-10-2023) for Associate Investment Management
	Route::post('investment_due_report_filter', [InvestmentDueReportFilter::class, 'investmentFilter']);
	// ---------------------------- Associate App new update on 21-12-2023 by Sourab Biswas End ----------------------------
});
