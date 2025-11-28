<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/clear-cache', function () {
    $exitCode = Artisan::call('cache:clear');
    return "Cache is cleared";
});
Route::get('/ipnbtc', 'PaymentController@ipnBchain')->name('ipn.bchain');
Route::post('/ipnpaypal', 'PaymentController@ipnpaypal')->name('ipn.paypal');
Route::post('/ipnperfect', 'PaymentController@ipnperfect')->name('ipn.perfect');
Route::post('/ipnstripe', 'PaymentController@ipnstripe')->name('ipn.stripe');
Route::post('/ipnskrill', 'PaymentController@skrillIPN')->name('ipn.skrill');
Route::post('/ipnflutter', 'PaymentController@flutterIPN')->name('ipn.flutter');
Route::post('/ipnvogue', 'PaymentController@vogueIPN')->name('ipn.vogue');
Route::post('/ipnpaystack', 'PaymentController@paystackIPN')->name('ipn.paystack');
Route::post('/ipncoinpaybtc', 'PaymentController@ipnCoinPayBtc')->name('ipn.coinPay.btc');
Route::post('/ext_transfer', 'UserController@submitpay')->name('submit.pay');
Route::get('/member-inactive', 'Admin\AdminController@member');
Route::get('/mack-address', function () {
    if (isset ($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if (isset ($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if (isset ($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if (isset ($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if (isset ($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if (isset ($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    $macCommandString = "arp " . $ipaddress . " | awk 'BEGIN{ i=1; } { i++; if(i==3) print $3 }'";
    $mac = exec($macCommandString);
    echo "hello" . $mac;
});
// Front end routes
Route::get('/', function () {
    return redirect('/login');
})->name('home');
Route::get('/faq', 'FrontendController@faq')->name('faq');
Route::get('/about', 'FrontendController@about')->name('about');
Route::get('/blog', 'FrontendController@blog')->name('blog');
Route::get('/terms', 'FrontendController@terms')->name('terms');
Route::get('/privacy', 'FrontendController@privacy')->name('privacy');
Route::get('/page/{id}', 'FrontendController@page');
Route::get('/single/{id}/{slug}', 'FrontendController@article');
Route::get('/cat/{id}/{slug}', 'FrontendController@category');
Route::get('/contact', 'FrontendController@contact')->name('contact');
Route::post('/contact', ['uses' => 'FrontendController@contactSubmit', 'as' => 'contact-submit']);
Route::post('/about', 'FrontendController@subscribe')->name('subscribe');
Route::get('session', function () {
    return p(Session()->all());
});
Route::post('/py_scheme', 'FrontendController@py_scheme')->name('py_scheme');
Route::get('/registerCIB', 'ScriptController@registerCIB');
Route::get('/sendAmountSher', 'ScriptController@sendAmountSher');
//-------------------- SSB Debit CArd --------------
Route::post('ssbDebitCard/response', 'DebitCardController@index')->name('debit_card.response');
// User routes
Auth::routes();
/***************** Branch panel routes start *****************/
Route::post('/login', 'Branch\LoginController@submitlogin')->name('submitlogin');
Route::post('/varified', 'Branch\LoginController@otpvarified')->name('otpvarified');
Route::get('/login', 'Branch\LoginController@login')->name('login');
Route::post('/resendotp', 'Branch\LoginController@resendOtp')->name('resendotp');
Route::group(['prefix' => 'branch',], function () {
    Route::get('logout', 'Branch\DashboardController@logout')->name('branch.logout');
    Route::group(['middleware' => 'isActive'], function () {
        Route::middleware(['CheckStatus'])->group(function () {
            Route::get('updateBalance', 'Branch\TestController@updateBalance')->name('branch.updateBalance');
            Route::get('updateDescription', 'Branch\TestController@updateDescription')->name('branch.updateDescription');
            Route::get('updatedateform', 'Branch\TestController@updatedateform')->name('updatedateform');
            Route::post('updatedate', 'Branch\TestController@updatedate')->name('updatedate');
            Route::get('dashboard', 'Branch\DashboardController@index')->name('branch.dashboard');
            /***************** Member Management start *****************/
            Route::get('member', 'Branch\MemberController@index')->name('branch.member_list');
            Route::get('customer', 'Branch\MemberController@customerindex')->name('branch.customer_list');
            Route::post('member_list', 'Branch\MemberController@membersListing')->name('branch.member_listing');
            Route::post('customers_listing', 'Branch\MemberController@customerListing')->name('branch.customer_listing');
            Route::get('member/registration', 'Branch\MemberController@register')->name('branch.member_register');
            Route::post('member/registration', 'Branch\MemberController@save')->name('branch.member_save');
            Route::get('member/detail/{id}', 'Branch\MemberController@memberDetail')->name('branch.memberDetail');
            Route::get('member/investment/detail/{id}/{member_id}', 'Branch\InvestmentController@investmentDetail')->name('branch.member_investmentdetail');
            Route::get('form_g/{id}', 'Branch\FormGController@index')->name('branch.form_g');
            Route::post('form_g', 'Branch\FormGController@getData')->name('branch.form_g.getData');
            Route::post('form_g/create', 'Branch\FormGController@save')->name('branch.update_15g.save');
            Route::post('export_update_15g', 'Branch\ExportController@export_update_15g')->name('branch.update_15g.report.export');
            Route::post('delete_update_15g_record', 'Branch\FormGController@delete')->name('branch.update_15g.record.delete');
            Route::post('get_district', 'Branch\MemberController@getDistrict')->name('branch.districtlist');
            Route::post('get_city', 'Branch\MemberController@getCity')->name('branch.citylist');
            Route::post('get_associate_member', 'Branch\MemberController@getAssociateMember')->name('branch.getassociatemember');
            Route::post('member_email_check', 'Branch\MemberController@memberEmailCheck')->name('branch.memberemailcheck');
            Route::post('member_formno_check', 'Branch\MemberController@memberFormNoCheck')->name('branch.memberformnocheck');
            Route::get('member/recipt/{id}', 'Branch\MemberController@memberRecipt')->name('branch.recipt');
            Route::post('member/image', 'Branch\MemberController@imageUpload')->name('member.image');
            Route::post('correction_request', 'Branch\CorrectionController@saveCoreectionRequest')->name('correction.request');
            Route::get('member/corrections', 'Branch\CorrectionController@correctionRequestView')->name('branch.member.correctionrequest');
            Route::get('associate/corrections', 'Branch\CorrectionController@correctionRequestView')->name('branch.associate.correctionrequest');
            Route::get('investment/corrections', 'Branch\CorrectionController@correctionRequestView')->name('branch.investment.correctionrequest');
            Route::get('renewal/corrections', 'Branch\CorrectionController@correctionRequestView')->name('branch.renewal.correctionrequest');
            Route::get('printpassbook/corrections', 'Branch\CorrectionController@correctionRequestView')->name('branch.printpassbook.correctionrequest');
            Route::get('printcertificate/corrections', 'Branch\CorrectionController@correctionRequestView')->name('branch.printcertificate.correctionrequest');
            Route::post('correctionrequestlist', 'Branch\CorrectionController@correctionRequestList')->name('branch.correctionrequestlist');
            /***************** Member account detail start *****************/
            Route::get('member/account/{id}', 'Branch\SavingController@index')->name('branch.savingDetail');
            //by Durgesh
            Route::get('member/saving/{id}', 'Branch\SavingController@savingIndex')->name('branch.savingIndex');
            Route::post('member/saving', 'Branch\SavingController@savingListing')->name('branch.savingListing');
            Route::get('member/account/printpassbook/{id}/{member}', 'Branch\SavingController@passbook')->name('branch.printpassbook');
            Route::post('member/account/printpassbook/{id}/{member}', 'Branch\SavingController@passbook_filter')->name('branch.printpassbookfilter');
            Route::get('member/account/statement/{id}/{member}', 'Branch\SavingController@statement')->name('branch.accountstatement');
            Route::post('member/account/statement/{id}/{member}', 'Branch\SavingController@statement_filter')->name('branch.statementfilter');
            /***************** Member account detail end  *****************/
            Route::get('member/investment/{id}', 'Branch\InvestmentController@index')->name('branch.member_investmentlist');
            Route::post('export/member-investment', 'Branch\ExportController@exportmemberInvestment')->name('branch.member_investment.report.export');
            Route::post('export/member-loan', 'Branch\ExportController@exportmemberLoan')->name('branch.member_loan.report.export');
            Route::post('export/member-group-loan', 'Branch\ExportController@exportmemberGroupLoan')->name('branch.member_group_loan.report.export');
            Route::post('export/member_transaction', 'Branch\ExportController@exportmemberTransaction')->name('branch.member_transaction.report.export');
            Route::get('print-member-investment/{id}', 'Branch\MemberController@print_member_investment')->name('branch.print_member_investment');
            Route::get('print-member-loan/{id}', 'Branch\MemberController@print_member_loan')->name('branch.print_member_loan');
            Route::get('print-member-groupLoan/{id}', 'Branch\MemberController@print_member_groupLoan')->name('branch.print_member_groupLoan');
            Route::get('print-member-transaction/{id}', 'Branch\MemberController@print_member_transaction')->name('branch.print_member_transaction');
            Route::post('/gst_chrg', 'Branch\InvestmentController@checkgstCharge')
                ->name('branch.gst.gst_charge');
            Route::post('member_investment_list', 'Branch\InvestmentController@investmentListing')->name('branch.member_investmentlisting');
            Route::get('member/loan/{id}', 'Branch\MemberLoanController@index')->name('branch.member_loanlist');
            Route::post('member-loan', 'Branch\MemberLoanController@membersLoanListing')->name('branch.member_loan_listing');
            Route::post('loan-list-export', 'Branch\ExportController@loan_list_export')->name('branch.loan_kist_export');
            Route::post('group-loan-list-export', 'Branch\ExportController@group_loan_list_export')->name('branch.group_loan_list_export');
            Route::post('update-pdf-generate-status', 'Branch\LoanController@update_pdf_generate_status')->name('branch.memberLoans.updatePdfGenerate');
            Route::post('get_approved_cheque_branchwise', 'Branch\LoanController@getBranchApprovedCheque')->name('branch.approve_cheque_branchwise');
            Route::post('update-print-nodues-status', 'Branch\LoanController@update_nodues_print_status')->name('branch.memberLoans.update_no_dues_print_status');
            Route::post('gst_amount_penalty', 'Branch\LoanController@gst_amount_penalty')
                ->name('branch.loan.getgstLatePenalty');
            Route::post('member-grouploan', 'Branch\MemberLoanController@membersGroupLoanListing')->name('branch.member_grouploan_listing');
            Route::post('loan-transaction-ajax', 'Branch\LoanController@loanTransactionAjax')
                ->name('branch.loan.transactionlist');
            Route::get('loan-transactions', 'Branch\LoanController@loanTransaction')
                ->name('branch.loan.transaction');
            Route::post('loan-transaction-export', 'Branch\ExportController@loanTransactionExportList')
                ->name('branch.loantransaction.export');
            Route::get('loan/emi-transactions/{id}/{type}', 'Branch\MemberLoanController@emiTransactionsView')->name('branch.lona.emitransactions');
            Route::post('loan/emi-transactions-list', 'Branch\MemberLoanController@emiTransactionsList')->name('branch.loan.emi_list');
            Route::post('member_loan_list', 'Branch\MemberLoanController@loanListing')->name('branch.member_loanlisting');
            Route::get('member/loan/detail/{id}/{member_id}', 'Branch\MemberLoanController@loanDetail')->name('branch.member_loandetail');
            Route::get('member/transactions/{id}', 'Branch\TransactionController@index')->name('branch.transactionsDetail');
            Route::post('transactions_list', 'Branch\TransactionController@transactionsListing')->name('branch.transactions_lists');
            Route::post('checkId', 'Branch\MemberController@getMemberFromIdProof')->name('branch.checkId');
            /***************** Member Management End  *****************/
            /***************** Passbook start *****************/
            Route::get('member/passbook', 'Branch\PassbookController@index')->name('branch.passbook');
            Route::group(['middleware' => ['permission:Passbook view']], function () {
                Route::get('investment/renew/receipt/{id}', 'Branch\PassbookController@renewal_receipt')->name('branch.investment.renew.receipt');
                Route::get('investment/renew/ssbtransaction/receipt/{id}', 'Branch\PassbookController@viewssbTransactionreceipt')->name('branch.investment.renew.receipt.ssbtransaction');
                Route::post('passbook_list', 'Branch\PassbookController@accountListing')->name('branch.passbook_listing');
            });
            Route::group(['middleware' => ['permission:Passbook Cover View']], function () {
                Route::get('member/passbook/cover/{id}', 'Branch\PassbookController@passbookCover')->name('branch.passbook_cover');
                Route::post('covePrint', 'Branch\PassbookController@coverPrint')->name('branch.cover_print');
            });
            Route::post('certificate_print', 'Branch\PassbookController@certificatePrint')->name('branch.certificate_print');
            //-------------new passbook ---
            Route::group(['middleware' => ['permission:New Passbook Cover View']], function () {
                Route::get('member/passbook/cover_new/{id}', 'Branch\PassbookController@passbookCoverNew')->name('branch.passbook_cover_new');
            });
            Route::group(['middleware' => ['permission:Maturity View']], function () {
                Route::get('member/passbook/maturity/{id}', 'Branch\PassbookController@passbookMaturity')->name('branch.passbook_maturity');
            });
            //Route::group(['middleware' => ['permission:Member Investment Transaction']], function () {
            Route::get('member/passbook/transaction/{id}/{code}', 'Branch\PassbookController@passbookTransaction')->name('branch.passbook_transaction');
            Route::group(['middleware' => ['permission:New Passbook Transactions View']], function () {
                Route::match (['get', 'post'], 'member/passbook/new_tran_start', 'Branch\PassbookController@transactionStartNew')->name('branch.transaction_start_new');
                //-------- new passbook tran -----
                Route::get('member/passbook/transaction_new/{id}/{code}', 'Branch\PassbookController@passbookTransactionNew')->name('branch.passbook_transaction_new');
            });
            //});
            Route::get('member/passbook/transaction/{id}', 'Branch\PassbookController@viewTransaction')->name('branch.view_passbook_transaction');
            Route::get('member/passbook/ssbtransaction/{id}', 'Branch\PassbookController@viewssbTransaction')->name('branch.view_passbook_transaction');
            Route::post('tran_list', 'Branch\PassbookController@transactionList')->name('branch.transaction_listing');
            Route::match (['get', 'post'], 'member/passbook/tran_start', 'Branch\PassbookController@transactionStart')->name('branch.transaction_start');
            // Route::match(['get', 'post'], 'member/passbook/new_tran_start', 'Branch\PassbookController@transactionStartNew')->name('branch.transaction_start_new');
            Route::group(['middleware' => ['permission:Certificate View']], function () {
                Route::get('member/passbook/certificate/{id}/{code}', 'Branch\PassbookController@certificate')->name('branch.certificate');
            });
            Route::post('payPrint', 'Branch\PassbookController@payPrint')->name('branch.pay_print');
            Route::post('CertificatepayPrint', 'Branch\PassbookController@CertificatepayPrint')->name('branch.Certificatepay_print');
            /***************** Passbook end *****************/
            /***************** Associate Management start  *****************/
            Route::group(['middleware' => ['permission:Associate Create']], function () {
                Route::get('associate/registration', 'Branch\MemberAssociateController@register')->name('branch.associate_register');
                Route::post('associate/registration', 'Branch\MemberAssociateController@save')->name('branch.associate_save');
            });
            //============= customer associate registration start
            Route::get('associate/registration/company', 'Branch\AssociateRegistrationController@index')->name('branch.associateregistercompany.index');
            Route::group(['middleware' => ['permission:Associate Create']], function () {
                // Route::get('associate/registration2', 'Branch\MemberAssociateController2@register2')->name('branch.associate_register2');
                // Route::post('associate/registration2', 'Branch\MemberAssociateController2@save2')->name('branch.associate_save2');
                Route::post('associate/registration/save', 'Branch\AssociateRegistrationController@store')->name('branch.customer.associate.save');
            });
            Route::post('get_customer', 'Branch\AssociateRegistrationController@getCustomerData')->name('branch.customerDataGet');
            Route::post('associateCustomerSsbAccountCustomerGet', 'Branch\AssociateRegistrationController@associateSsbAccountGet')->name('branch.associateSsbAccountGet.customer');
            // Route::post('associateSsbAccountCustomerGet', 'Branch\MemberAssociateController2@associateSsbAccountGet')->name('branch.associateSsbAccountGet.customer');
            Route::post('associate/registration/customer/store', 'Branch\AssociateRegistrationController@store')->name('branch.associate.store.customer');
            Route::post('associate/registration/customer/dependents', 'Branch\AssociateRegistrationController@create')->name('branch.associate.dependents.customer');
            Route::post('get_senior/customer', 'Branch\AssociateRegistrationController@getSeniorDetail')->name('branch.seniorDetail.customer');
            Route::post('getCarderAssociate/customer', 'Branch\AssociateRegistrationController@getCarderAssociate')->name('branch.getCarderAssociate.customer');
            //============= customer associate registration end
            Route::get('associate', 'Branch\MemberAssociateController@index')->name('branch.associate_list');
            Route::post('associate_list', 'Branch\MemberAssociateController@associateListing')->name('branch.associater_listing');
            Route::group(['middleware' => ['permission:Associate Profile View']], function () {
                Route::get('associate/detail/{id}', 'Branch\MemberAssociateController@associateDetail')->name('branch.associateDetail');
            });
            Route::post('get_member', 'Branch\MemberAssociateController@getMemberData')->name('branch.memberDataGet');
            Route::post('get_senior', 'Branch\MemberAssociateController@getSeniorDetail')->name('branch.seniorDetail');
            Route::post('associate_formno_check', 'Branch\MemberAssociateController@associateFormNoCheck')->name('branch.associateformnocheck');
            Route::get('associate/receipt/{id}', 'Branch\MemberAssociateController@reciept')->name('branch.associate_receiept');
            Route::post('associate_ssb_check', 'Branch\MemberAssociateController@checkSsbAcount')->name('branch.associatessbaccountcheck');
            Route::post('ssb_check_balance', 'Branch\MemberAssociateController@checkSsbAcountBalance')->name('branch.checkssbblance');
            Route::post('associateSsbAccountGet', 'Branch\MemberAssociateController@associateSsbAccountGet')->name('branch.associateSsbAccountGet');
            Route::post('associateRdAccountGet', 'Branch\MemberAssociateController@associateRdbAccountGet')->name('branch.associateRdAccountGet');
            Route::post('associateRdAccounts', 'Branch\MemberAssociateController@associateRdbAccounts')->name('branch.associateRdAccounts');
            Route::post('getCarderAssociate', 'Branch\MemberAssociateController@getCarderAssociate')->name('branch.getCarderAssociate');
            Route::get('associate/commission', 'Branch\MemberAssociateController@associateCommission')->name('branch.associate.commission');
            Route::post('associatecommissionlist', 'Branch\MemberAssociateController@associateCommissionList')->name('branch.associate.commissionlist');
            Route::post('exportassociatecommissionlist', 'Branch\ExportController@exportAssociateCommission')->name('branch.associate.exportcommission');
            /* Branch Associate Collection Report Start */
            Route::get('associate-collection-report', 'Branch\MemberAssociateController@AssociateCollectionReport')->name('branch.associate.associatecollectionreport');
            Route::post('associate-collection-report-list', 'Branch\MemberAssociateController@AssociateCollectionReportList')->name('branch.associate.associatecollectionreportlist');
            Route::post('associatecollectionreportexport', 'Branch\ExportController@AssociateCollectionReportExport')->name('branch.associate.associatecollectionreportexport');
            /* Branch Associate Collection Report Start */
            Route::post('export-investments-branch-list', 'Branch\ExportController@exportInvestmentPlanBranch')->name('branch.investment.export');
            Route::get('associate/commission-detail/{id}', 'Branch\MemberAssociateController@associateCommissionDetail')->name('branch.associate.commission.detail');
            Route::post('associatecommissionDetaillist', 'Branch\MemberAssociateController@associateCommissionDetailList')->name('branch.associate.commissionDetaillist');
            Route::post('exportassociatecommissionDetaillist', 'Branch\ExportController@exportAssociateCommissionDetail')->name('branch.associate.exportcommissionDetail');
            /************* Withdraw system ******************/
            Route::get('withdrawal', 'Branch\PaymentManagement\WithdrawalController@index')->name('branch.withdraw.ssb');
            Route::post('getAccountDetails', 'Branch\PaymentManagement\WithdrawalController@accountDetails')->name('branch.withdraw.accountdetails');
            Route::post('save-withdrawal', 'Branch\PaymentManagement\WithdrawalController@saveWithdrawal')->name('branch.withdrawal.save');
            Route::get('update-ssb-transaction', 'Branch\PaymentManagement\WithdrawalController@updateSsbBalance')->name('branch.updatessbtransaction');
            Route::post('send-ssb-otp', 'Branch\PaymentManagement\WithdrawalController@sendOtpToSSB')->name('branch.send.ssb.otp');
            Route::post('verify-ssb-otp', 'Branch\PaymentManagement\WithdrawalController@verifySSbOtp')->name('branch.verify.ssb_otp');
            Route::post('update-ssb-otp', 'Branch\PaymentManagement\WithdrawalController@updateSSbOtp')->name('branch.update.ssb.otp');
            /************* Withdraw system ******************/
            /************* Fund Transfer start ******************/
            Route::get('fund-transfer/branch-to-ho', 'Branch\FundTransferManagement\FundTransferController@branchToHo')->name('branch.fundtransfer.branchtoho');
            Route::get('fund-transfer/bank-to-bank', 'Branch\FundTransferManagement\FundTransferController@bankToBank')->name('branch.fundtransfer.banktobank');
            Route::get('fund-transfer/branch-to-ho/create', 'Branch\FundTransferManagement\FundTransferController@createBranchToHo')->name('branch.fundtransfer.createbranchtoho');
            Route::get('fund-transfer/bank-to-bank/create', 'Branch\FundTransferManagement\FundTransferController@createBankToBank')->name('branch.fundtransfer.createbanktobank');
            Route::post('fund-transfer/branch-to-ho-listing', 'Branch\FundTransferManagement\FundTransferController@branchToHoListing')->name('branch.fundtransfer.branchtoholisting');
            Route::post('fund-transfer/bank-to-bank-listing', 'Branch\FundTransferManagement\FundTransferController@bankTobankListing')->name('branch.fundtransfer.banktobranchlisting');
            Route::post('fund-transfer/get-loan-micro-amount', 'Branch\FundTransferManagement\FundTransferController@getLoanMicroAmount')->name('branch.fundTransfer.getloanmicroamount');
            Route::post('fund-transfer-ho', 'Branch\FundTransferManagement\FundTransferController@fundTransferHeadOffice')->name('branch.fund.transfer.head.office');
            Route::post('fund-transfer-bank', 'Branch\FundTransferManagement\FundTransferController@fundTransferBankToBank')->name('branch.fund.transfer.bank');
            Route::get('fund-transfer/report', 'Branch\FundTransferManagement\FundTransferController@fundtransfer_report')->name('branch.fundtransfer.report');
            Route::post('fund-transfer/reportListing', 'Branch\FundTransferManagement\FundTransferController@fundTransferReportListing')->name('branch.fund-transfer.report_lisiting');
            Route::post('getBankAccountNo', 'Branch\FundTransferManagement\FundTransferController@getbankaccountno')->name('branch.getBankAccountNo');
            Route::post('branchBalanceGet', 'Branch\FundTransferManagement\FundTransferController@getbranchbankbalanceamount')->name('branch.getbranchbankbalanceamount');
            Route::post('fund-transfer/exportreport', 'Branch\ExportController@exportFundTransfer')->name('branch.fundTransfer.export');
            Route::post('getBankListByCompanyId', 'Branch\FundTransferManagement\FundTransferController@getBankListByCompanyId')->name('branch.getBankListByCompanyId');
            /*get Bank Name*/
            Route::post('getBankList', 'Branch\InvestmentplanController@getBankList')->name('branch.getBankList');
            /************* Fund Transfer end ******************/
            Route::post('checkmemberExist', 'Branch\InvestmentplanController@checkMemberExist')->name('investment.checkmemberExist');
            // Route::get('investment/commission/{id}', 'Branch\InvestmentplanController@investmentCommission')->name('branch.investment.commission');
            // Route::post('investmentcommissionlisting', 'Branch\InvestmentplanController@investmentCommissionListing')->name('branch.investment.commissionlisting');
            // Route::post('investmentcommissionexport', 'Branch\ExportController@exportInvestmentCommission')->name('branch.investmentcommission.export');
            Route::get('associate-upgrade', 'Branch\MemberAssociateController@upgrade')->name('branch.associate.upgrade');
            Route::get('associate-status', 'Branch\MemberAssociateController@active_deactivate')->name('branch.associate.status');
            Route::get('associate-downgrade', 'Branch\MemberAssociateController@downgrade')->name('branch.associate.downgrade');
            Route::post('associate-upgradesave', 'Branch\MemberAssociateController@upgrade_save')->name('branch.associate.upgrade_save');
            Route::post('getAssociateDetail', 'Branch\MemberAssociateController@getAssociateData')->name('branch.associter_dataGet');
            Route::post('associate-statussave', 'Branch\MemberAssociateController@status_save')->name('branch.associate.status_save');
            Route::post('getAssociateDetailAll', 'Branch\MemberAssociateController@getAssociateDataAll')->name('branch.associate_dataGetAll');
            Route::post('admin_getCarderForUpgrade', 'Branch\MemberAssociateController@getCarderUpgrade')->name('branch.getCarderForUpgrade');
            Route::post('associate-downgrade', 'Branch\MemberAssociateController@downgrade_save')->name('branch.associate.downgrade_save');
            /***************** Associate Management End  *****************/
            // Investment Report management
            Route::get('daily/report', 'Branch\InvestmentReportController@dailyReport')->name('branch.investment.daily.report');
            Route::get('monthly/report', 'Branch\InvestmentReportController@monthlyReport')->name('branch.investment.monthly.report');
            Route::post('daily/report/listing', 'Branch\InvestmentReportController@dailyReportListing')->name('branch.investement.dailyReportListing');
            Route::post('report/listing/export', 'Branch\InvestmentReportController@export')->name('branch.investement_report.export');
            // End
            Route::resource('new_investment', 'Branch\InvestmentControllerV2');
            //Investment Management
            Route::get('registerplan', 'Branch\InvestmentplanController@registerPlans')->name('register.plan');
            Route::post('investmentgetmember', 'Branch\InvestmentplanController@getmember')->name('investment.member');
            Route::post('investmentgetassociate', 'Branch\InvestmentplanController@getAccociateMember')->name('investment.associate');
            Route::post('serachmember', 'Branch\InvestmentplanController@searchmember')->name('investment.searchmember');
            Route::post('investmentkanyadhanamount', 'Branch\InvestmentplanController@kanyadhanAmount')->name('investment.kanyadhanamount');
            Route::post('getForm', 'Branch\InvestmentplanController@planForm')->name('investment.planform');
            Route::post('getEditForm', 'Branch\InvestmentplanController@editPlanForm')->name('investment.editplanform');
            Route::get('investments', 'Branch\InvestmentplanController@investments')->name('investment.plans');
            Route::post('investments/getCompanyIdPlans', 'Branch\InvestmentplanController@getCompanyIdPlans')->name('branch.getCompanyIdPlans');
            Route::post('investmentslisting', 'Branch\InvestmentplanController@investmentListing')->name('investment.listing');
            Route::get('renewaldetails', 'Branch\RenewaldetailsController@renewaldetails')->name('investment.renewaldetails');
            Route::post('renewaldetailslisting/getCompanyIdPlans', 'Branch\RenewaldetailsController@getCompanyIdPlans')->name('branch.renewal.getCompanyIdPlans');
            Route::post('renewaldetailslisting', 'Branch\RenewaldetailsController@renewaldetailsListing')->name('renewaldetails.listing');
            Route::get('savingaccountreport', 'Branch\SavingaccountreportController@savingaccountreport')->name('investment.savingaccountreport');
            Route::post('savingaccountreportlisting', 'Branch\SavingaccountreportController@savingaccountreportListing')->name('savingaccountreport.listing');
            Route::post('storeplan', 'Branch\InvestmentplanController@Store')->name('branch.investment.store');
            Route::post('storeReinvestPlan', 'Branch\InvestmentplanController@reinvestStore')->name('reinvestment.store');
            Route::post('renewal_list_export', 'Branch\ExportController@exportRenewalList')->name('branch.renewal_list.report.export');
            Route::post('opensavingaccount', 'Branch\InvestmentplanController@openSavingAccount')->name('investment.opensavingaccount');
            Route::group(['middleware' => ['permission:Investment Plan Detail View']], function () {
                Route::get('investment/{id}', 'Branch\InvestmentplanController@edit')->name('investment.edit');
            });
            Route::post('branch/updateplan', 'Branch\InvestmentplanController@Update')->name('branch.investment.update');
            //Route::group(['middleware' => ['permission:Print Investment Receipt']], function () {
            Route::get('investment/recipt/{id}', 'Branch\InvestmentplanController@planRecipt')->name('investment.recipt');
            //});
            /*********** update day book **************/
            Route::get('updatedaybook', 'Branch\InvestmentplanController@updateDayBook')->name('branch.updatedaybook');
            Route::get('updatetranactions', 'Branch\InvestmentplanController@updateTranactions')->name('branch.updatetranactions');
            Route::get('updatesavingtranactions', 'Branch\InvestmentplanController@updateSavingTranactions')->name('branch.updatesavingtranactions');
            Route::get('adjust-stationary-charges', 'Branch\InvestmentplanController@adjustStationaryCharges')->name('branch.adjustStationaryCharges');
            /*********** update day book **************/
            //Branch Investment Renewal
            Route::get('renewplan/new', 'Branch\NewRenewalController@renew')->name('branch.renew.new');
            Route::get('renewplan/new/{allContactNumbers}/{allAccountNumbers}/{rAmounts}/{encodeRequests}/{encodebranchCode}/{encodebranchName}/{ssb}/{totalAmount}/{amount}/{ren_dates}', 'Branch\NewRenewalController@send_message')->name('branch.renew.new.sendMessage');
            Route::post('renew/new/store', 'Branch\NewRenewalController@storeAjax')
                ->name('branch.renew.new.storeajax');
            Route::get('renew/recipt/new/{url}/{branchCode}/{branchName}/{ssb}/{totalAmount}', 'Branch\NewRenewalController@renewalDetails')->name('branch.renew.new.receipt');
            //Renewal
            Route::group(['middleware' => ['permission:Renewal Investment']], function () {
                Route::get('renewplan', 'Branch\RenewalController@renew')->name('investment.renew');
            });
            Route::post('investment/getInvestmentDetails', 'Branch\RenewalController@getInvestmentDetails')->name('investment.renewplan');
            Route::post('getCollectorAssociate', 'Branch\RenewalController@getCollectorAssociate')->name('investment.getcollectorassociate');
            /*commented by amar*/
            /*    Route::post('renew/recipt', 'Branch\RenewalController@store')->name('renew.store');*/
            /*Code added by Amar*/
            Route::post('renew/recipt', 'Branch\RenewalController@store')
                ->name('branch.renew.store');
            Route::post('renew/store', 'Branch\RenewalController@storeAjax')
                ->name('branch.renew.storeajax');
            /*End of code*/
            Route::get('renew/recipt/{url}/{branchCode}/{branchName}/{ssb}', 'Branch\RenewalController@renewalDetails')->name('renew.receipt');
            Route::get('renew/recipt/{url}/{branchCode}/{branchName}', 'Branch\RenewalController@renewalDetails')->name('renew.receipt');
            Route::get('update-renewal', 'Branch\RenewalController@updateRenewal')->name('branch.renew.updaterenewal');
            Route::post('update-renewal-transaction', 'Branch\RenewalController@updateRenewalTransaction')->name('branch.renew.updaterenewaltransaction');
            /************* Demand Advice ******************/
            Route::get('demand-advices', 'Branch\DemandAdvice\DemandAdviceController@index')->name('branch.demand.advices');
            Route::post('demand-advice-listing', 'Branch\DemandAdvice\DemandAdviceController@demandAdviceListing')->name('branch.demandadvice.list');
            Route::post('ta-advanced-listing', 'Branch\DemandAdvice\DemandAdviceController@taAdvancedListing')->name('branch.taadvanced.list');
            Route::get('demand-advice/addadvice', 'Branch\DemandAdvice\DemandAdviceController@addAdvice')->name('branch.demand.addadvice');
            Route::post('get-sub-account', 'Branch\DemandAdvice\DemandAdviceController@getSubAccount')->name('branch.demand.getsubaccountbycategory');
            Route::post('save-demand-advice', 'Branch\DemandAdvice\DemandAdviceController@saveAdvice')->name('branch.demand.saveadvice');
            Route::get('demand-advice/view/{id}', 'Branch\DemandAdvice\DemandAdviceController@viewAdvice')->name('branch.demand.edit');
            Route::get('demand-advice/edit-demand-advice/{id}', 'Branch\DemandAdvice\DemandAdviceController@editAdvice')->name('branch.demand.edit');
            Route::post('update-advice', 'Branch\DemandAdvice\DemandAdviceController@updateAdvice')->name('branch.demand.update');
            Route::get('delete-demand-advice/{id}', 'Branch\DemandAdvice\DemandAdviceController@delete')->name('branch.demand.delete');
            Route::post('ssb-details', 'Branch\DemandAdvice\DemandAdviceController@getSsbDetails')->name('branch.demand.getssb');
            Route::post('employee-details', 'Branch\DemandAdvice\DemandAdviceController@getEmployeeDetails')->name('branch.demand.getemployee');
            Route::post('getHeadLedgerData', 'Branch\DemandAdvice\DemandAdviceController@getHeadLedgerData')->name('branch.getHeadLedgerData');
            Route::post('getSSBAccountNumber', 'Branch\DemandAdvice\DemandAdviceController@getSSBAccountNumber')->name('branch.getSSBAccountNumber');
            Route::post('owner-details', 'Branch\DemandAdvice\DemandAdviceController@getOwnerDetails')->name('branch.demand.getowner');
            Route::post('investment-details', 'Branch\DemandAdvice\DemandAdviceController@getInvestmentDetails')->name('branch.demand.getinvestment');
            Route::get('demand-advice/report', 'Branch\DemandAdvice\DemandAdviceController@report')->name('branch.demand.report');
            Route::post('demand-advice-report-listing', 'Branch\DemandAdvice\DemandAdviceController@reportListing')->name('branch.demandadvice.reportlist');
            Route::post('export-demand-advice-report', 'Branch\DemandAdvice\DemandAdviceController@exportDemandAdviceReport')->name('branch.demandadvice.export');
            Route::get('demand-advice/application', 'Branch\DemandAdvice\DemandAdviceController@application')->name('branch.demand.application');
            Route::post('demand-advice-application-listing', 'Branch\DemandAdvice\DemandAdviceController@applicationListing')->name('branch.demandadvice.applicationlist');
            Route::post('update-print-status', 'Branch\DemandAdvice\DemandAdviceController@printDemandAdvice')->name('branch.demand.updateprint');
            /*death help maturitu ui*/
            Route::get('demand-advice/demand-advice-maturity', 'Branch\DemandAdvice\DemandAdviceController@demandAdvicematurity')->name('branch.demand.advices.demand_advice_maturity');
            Route::post('demand-advice-maturity-list', 'Branch\DemandAdvice\DemandAdviceController@demandAdvicematurityList')->name('branch.demandadvices.demand_advice_maturity_list');
            Route::post('get-investment-data', 'Branch\DemandAdvice\DemandAdviceController@getInvestmentData')->name('branch.demand.getinvestmentdata');
            Route::post('demand-advice/investment-maturity-amount', 'Branch\DemandAdvice\DemandAdviceController@saveInvestmentMaturityAmount')->name('branch.demand.saveInvestmentMaturityAmount');
            Route::post('get-bank-daybook-amount', 'Branch\DemandAdvice\DemandAdviceController@getBankDayBookAmount')->name('branch.demadadvice.getbankdaybookamount');
            Route::post('get-branch-daybook-amount', 'Branch\DemandAdvice\DemandAdviceController@getBranchDayBookAmount')->name('branch.demadadvice.getbranchdaybookamount');
            Route::get('demand-advice/view-ta-advanced', 'Branch\DemandAdvice\DemandAdviceController@viewTaAdvanced')->name('branch.damandadvice.viewtaadvanced');
            Route::get('demand-advice/adjust-ta-advanced/{id}', 'Branch\DemandAdvice\DemandAdviceController@adjustTaAdvanced')->name('branch.demand.adjusttaadvanced');
            Route::post('update-ta-advanced', 'Branch\DemandAdvice\DemandAdviceController@updateTaAdvanced')->name('branch.demand.updatetaadvanced');
            Route::post('demand-advice/member-details', 'Branch\DemandAdvice\DemandAdviceController@getMemberDetails')->name('branch.demand.getmemberdata');
            /************* Demand Advice ******************/
            //Loan Management V2
            Route::resource('loan', 'Branch\LoanRegisterController')->only(['create', 'store']);
            Route::post('loan/store', 'Branch\LoanRegisterController@store')->name('loan.register.store');
            Route::post('get_customer_details', 'Branch\LoanRegisterController@getCustomer')->name('get_customer_details');
            Route::post('check_loan_against_investment_percentage', 'Branch\LoanRegisterController@checkLoanAgainstInvestmentPercentage')->name('branch.check_loan_against_investment_percentage');
            Route::post('get_member_id_proof', 'Branch\LoanRegisterController@getMemberIdProof')->name('branch.get_member_id_proof');
            Route::get('loan/receipt/{id}', 'Branch\LoanRegisterController@receipt')->name('branch.loan.receipt');
            //Loan Management

            Route::post('get_member_employee_details', 'Branch\LoanRegisterController@getCustomerEmployeeDetails')->name('branch.getEmployeeData');
            // Route::group(['middleware' => ['permission:Register Loan']], function () {
            Route::get('registerloan', 'Branch\LoanController@registerLoan')->name('register.loan');
            // });
            Route::post('loangetmember', 'Branch\LoanController@getmember')->name('loan.member');
            Route::post('getplanname', 'Branch\LoanController@getPlanName')->name('loan.getplanname');
            Route::post('getMemberInvestmentList', 'Branch\LoanController@getMemberInvestmentList')->name('loan.memberinvestmentlist');
            Route::post('loangetgroupmember', 'Branch\LoanController@getGroupMember')->name('loan.groupmember');
            Route::post('loangetassociatemember', 'Branch\LoanController@getAccociateMember')->name('loan.associatemember');
            Route::get('loans', 'Branch\LoanController@loans')->name('loan.loans');
            Route::group(['middleware' => ['permission:Loan View']], function () {
                Route::post('loans/getLoanPlanByType', 'Branch\LoanController@getLoanPlanByType')->name('loan.getLoanPlanByType');
            });
            // Route::group(['middleware' => ['permission:Group Loans Details']], function () {
            Route::get('loan/group', 'Branch\LoanController@groupLoan')->name('loan.grouploan');
            // });
            Route::post('loanlisting', 'Branch\LoanController@loanListing')->name('loan.listing');
            Route::post('getActiveLoans', 'Branch\LoanController@getActiveLoans')->name('branch.getActiveLoans');
            Route::post('getInvestmentLoan', 'Branch\LoanController@getInvestmentLoan')->name('branch.getInvestmentLoan.exist');
            Route::post('planform_saving_account', 'Branch\InvestmentplanController@planform_saving_account')->name('investment.planform_saving_account');
            Route::post('getFileCharge', 'Branch\LoanController@getFileCharge')->name('branch.getFileCharge');
            Route::post('groupLoanlisting', 'Branch\LoanController@groupLoanListing')->name('loan.group.listing');
            // Route::post('storeloan', 'Branch\LoanController@Store')->name('loan.store');
            Route::get('loan/{id}', 'Branch\LoanController@Edit')->name('loan.edit');
            Route::get('loan/view/{id}', 'Branch\LoanController@View')->name('loan.view');
            Route::post('updateloan', 'Branch\LoanController@Update')->name('loan.update');
            Route::get('loan/sendapprovalrequest/{id}', 'Branch\LoanController@SendApprovalRequest')->name('loan.sendapprovalrequest');
            // Route::post('deposite-loan-emi', 'Branch\LoanController@depositeLoanEmi')->name('loan.depositeloanemi');
            Route::post('deposite-group-loan-emi', 'Branch\LoanController@depositeGroupLoanEmi')->name('grouploan.depositeloanemi');
            Route::get('loan/print/{id}/{type}', 'Branch\LoanController@printView')->name('loan.print');
            //Route::get('loan/form/print/{id}/{type}', 'Admin\ExportController@downloadLoanForm')->name('loan.download.pdf');
            Route::get('loan/form/print/{id}/{type}', 'Branch\LoanController@printLoanForm')->name('loan.download.pdf');
            Route::get('loan/download-recovery-clear/{id}/{type}', 'Branch\ExportController@DownloadRecoveryNoDueLoan')->name('loan.downloadrecoveryclear.pdf');
            Route::get('loan/print-recovery-clear/{id}/{type}', 'Branch\ExportController@PrintRecoveryNoDueLoan')->name('loan.printrecoveryclear.pdf');
            Route::get('loan/form/termcondition/{id}/{type}', 'Admin\ExportController@downloadLoanTermCondition')->name('loan.download.termconditionpdf');
            Route::post('branch-getCollectorAssociate', 'Branch\LoanController@getCollectorAssociate')->name('loan.getcollectorassociate');
            /*************************** Loan Start **************/
            // Route::get('loan/commission/{id}', 'Branch\LoanController@loanCommission')->name('branch.loan_commission');
            // Route::post('loan-commission', 'Branch\LoanController@loanCommissionList')->name('branch.loan_commission_list');
            // Route::post('loanCommissionExport', 'Branch\ExportController@loanCommissionExport')->name('branch.loan.loanCommissionExport');
            Route::get('loan/commission-group/{id}', 'Branch\LoanController@loanGroupCommission')->name('branch.loan_commission_group');
            Route::post('loan-group_commission', 'Branch\LoanController@loanGroupCommissionList')->name('branch.loan_group_commission_list');
            Route::post('loanGroupCommissionExport', 'Branch\ExportController@loanGroupCommissionExport')->name('branch.loan.loanGroupCommissionExport');
            /*************************** Loan Changes End **************/
            /******************** START reinvestment ******************************/
            Route::get('reinvest', 'Branch\Reinvest\ReinvestController@index')->name('branch.index');
            Route::post('getInvestment', 'Branch\Reinvest\ReinvestController@getInvestment')->name('branch.getInvestment');
            Route::post('reinvest-save', 'Branch\Reinvest\ReinvestController@save')->name('branch.reinvestSave');
            Route::post('reinvest-save-plane', 'Branch\Reinvest\ReinvestController@createPlane')->name('branch.reinvestSavePlane');
            Route::post('member-save', 'Branch\MemberController@save')->name('branch.member.save');
            Route::post('reGetForm', 'Branch\Reinvest\ReinvestController@planForm')->name('reinvestment.planform');
            Route::post('saveFormData', 'Branch\Reinvest\ReinvestController@saveForm')->name('reinvestment.saveForm');
            Route::get('add-plan', 'Branch\Reinvest\ReinvestController@addPlaneCode')->name('addPlan');
            /******************** END reinvestment ******************************/
            Route::get('events', 'Branch\EventController@index')->name('branch.events');
            Route::post('nextmonth', 'Branch\EventController@nextMonth')->name('branch.events.nextmonth');
            /********* Report Export ***********/
            Route::post('exportmemberlist', 'Branch\ExportController@exportMember')->name('branch.member.export');
            Route::post('exportcustomerlist', 'Branch\ExportController@exportCustomer')->name('branch.customer.export');
            Route::post('exportassociatelist', 'Branch\ExportController@exportAssociate')->name('branch.associate.export');
            /*********************/
            /***********************Investment update functionality *********************/
            Route::post('registerplan/approve_cheque', 'Branch\CommanTransactionsController@approveReceivedCheque')->name('branch.approve_recived_cheque_list');
            Route::post('registerplan/approve_cheque_detail', 'Branch\CommanTransactionsController@approveReceivedChequeDetail')->name('branch.approve_cheque_detail');
            /***********************Investment update functionality *********************/
            /********************** Received Cheque Management start **************************/
            Route::get('received/cheque', 'Branch\ChequeController@index')->name('branch.received.cheque_list');
            Route::get('received/cheque/add', 'Branch\ChequeController@receivedChequeAdd')->name('branch.received.cheque_add');
            Route::post('getBankAccount', 'Branch\ChequeController@getBankAccount')->name('branch.bank_account_list');
            Route::post('received/cheque_listing', 'Branch\ChequeController@receivedChequeListing')->name('branch.received.cheque_listing');
            Route::post('received/cheque-save', 'Branch\ChequeController@receivedChequeSave')->name('branch.received.cheque_save');
            Route::post('received/receivedChequeExport', 'Branch\ExportController@receivedChequeExport')->name('branch.received.cheque.export');
            Route::get('received/cheque/view/{id}', 'Branch\ChequeController@receivedChequeView')->name('branch.received.cheque_view');
            Route::post('bank_cheque_list', 'Branch\ChequeController@bankChequeList')->name('branch.bank_cheque_list');
            /********************** Received Cheque Management End **************************/
            /********************** HR Management start **************************/
            /********************** Employee start **************************/
            Route::get('hr/employee', 'Branch\HrManagement\EmployeeController@index')->name('branch.hr.employee_list');
            Route::get('hr/employ/ledger/{id}', 'Branch\HrManagement\EmployeeController@ledgerEmploy')->name('branch.hr.employee.ledger_report');
            Route::post('hr/employ/ledger/', 'Branch\HrManagement\EmployeeController@ledgerEmployListing')->name('branch.hr.employee.ledger_listing');
            Route::post('hr/employee_listing', 'Branch\HrManagement\EmployeeController@employeeListing')->name('branch.hr.employee_listing');
            Route::get('hr/employee/application', 'Branch\HrManagement\EmployeeController@applicationList')->name('branch.hr.employee_application_list');
            Route::post('hr/employee_application', 'Branch\HrManagement\EmployeeController@employeeApplicationListing')->name('branch.hr.employee_application');
            Route::get('hr/employee/register', 'Branch\HrManagement\EmployeeController@add')->name('branch.hr.employee_add');
            Route::get('hr/employee/edit/{id}', 'Branch\HrManagement\EmployeeController@edit')->name('branch.hr.employee_edit');
            Route::get('hr/employee/detail/{id}', 'Branch\HrManagement\EmployeeController@detail')->name('branch.hr.employee_detail');
            Route::post('hr/employee-save', 'Branch\HrManagement\EmployeeController@employeeSave')->name('branch.hr.employee_save');
            Route::post('hr/employee-update', 'Branch\HrManagement\EmployeeController@employeeUpdate')->name('branch.hr.employee_update');
            Route::post('hr/employeeExport', 'Branch\ExportController@employeeExport')->name('branch.hr.employee_export');
            Route::post('hr/employeeApplicationExport', 'Branch\ExportController@employeeApplicationExport')->name('branch.hr.employee_application_export');
            Route::get('hr/employee/resign_request', 'Branch\HrManagement\EmployeeController@resignRequest')->name('branch.hr.employee_resign_request');
            Route::post('hr/resign_save', 'Branch\HrManagement\EmployeeController@resignRequestSave')->name('branch.hr.resign_save');
            Route::get('hr/employee/transfer_letter/{id}', 'Branch\HrManagement\EmployeeController@transferLetter')->name('branch.hr.employee_transfer_letter');
            Route::get('hr/employee/transfer', 'Branch\HrManagement\EmployeeController@transferList')->name('branch.hr.employee_transfer_list');
            Route::post('hr/employee_transfer', 'Branch\HrManagement\EmployeeController@employeeTransferListing')->name('branch.hr.employee_transfer');
            Route::post('hr/employeeTransferExport', 'Branch\ExportController@employeeTransferExport')->name('branch.hr.employee_transfer_export');
            Route::get('hr/employee/transfer/detail/{id}', 'Branch\HrManagement\EmployeeController@transferDetail')->name('branch.hr.employee_transfer_detail');
            Route::post('employeeDataGet', 'Branch\HrManagement\EmployeeController@employeeDataGet')->name('branch.employeeDataGet');
            Route::post('designationDataGet', 'Branch\HrManagement\EmployeeController@designationDataGet')->name('branch.designationDataGet');
            Route::post('designationByCategory', 'Branch\HrManagement\EmployeeController@designationByCategory')->name('branch.designationByCategory');
            Route::post('trasnsferCount', 'Branch\HrManagement\EmployeeController@trasnsferCount')->name('branch.trasnsferCount');
            Route::get('hr/employee/application_print/{id}', 'Branch\HrManagement\EmployeeController@application_print')->name('branch.hr.application_print');
            Route::post('hr/employeeApplicationExportpdf', 'Branch\ExportController@employeeApplicationExportpdf')->name('branch.hr.employee_application_export_pdf');
            Route::post('employ-check-ssb-account', 'Branch\HrManagement\EmployeeController@checkSsbAccount')->name('branch.employ.check.ssb.account');
            /********************** Employee End **************************/
            /********************** HR Management End **************************/
            /********************** Notice Board Start **************************/
            Route::get('noticeboard', 'Branch\NoticeboardController@index')->name('branch.noticeboard');
            Route::post('get-noticeboard', 'Branch\NoticeboardController@getDocument')->name('branch.get-noticeboard');
            /********************** Notice Board End **************************/
            /*Expense Booking Start*/
            Route::get('expense', 'Branch\Expense\ExpenseController@index')->name('branch.expense');
            Route::post('get_indirect_expense', 'Branch\Expense\ExpenseController@get_indirect_expense')->name('branch.get_indirect_expense');
            Route::post('get_indirect_expense_sub_head', 'Branch\Expense\ExpenseController@get_indirect_expense_sub_head')->name('branch.get_indirect_expense_sub_head');
            Route::post('save', 'Branch\Expense\ExpenseController@save')->name('branch.expense.save');
            Route::get('report/expense/{id}', 'Branch\Expense\ExpenseController@report_expense')->name('branch.report');
            Route::post('report/expense/liting', 'Branch\Expense\ExpenseController@expense_report_listing')->name('branch.expense_listing');
            Route::post('export/expense', 'Branch\ExportController@export_expense_report')->name('branch.expense.export');
            Route::get('expense/edit/{id}', 'Branch\Expense\ExpenseController@edit')->name('branch.expense.edit');
            Route::post('expense/delete-expense', 'Branch\Expense\ExpenseController@delete_expense')->name('branch.expense.delete-expense');
            Route::post('expense/approve_expense', 'Branch\Expense\ExpenseController@approve_expense')->name('branch.expense.approve_expense');
            Route::get('report/bill_expense', 'Branch\Expense\ExpenseController@expense_bill')->name('branch.expense.expense_bill');
            Route::post('report/bill_expense/liting', 'Branch\Expense\ExpenseController@bill_expense_report_listing')->name('branch.bill_expense_listing');
            Route::post('expense/update', 'Branch\Expense\ExpenseController@update')->name('branch.expense.update');
            Route::post('expense/bill_delete', 'Branch\Expense\ExpenseController@deleteBill')->name('branch.expense.deleteBill');
            Route::post('expense/bill_export', 'Branch\Expense\ExpenseController@export_bill')->name('branch.bill.export');
            Route::get('report/expense_pr/{id}', 'Branch\Expense\ExpenseController@report_expense_print')
                ->name('branch.report.print');
            /*Expense Booking End */
            /********************** Branch Report Manangement start **************************/
            Route::get('report/associate_business', 'Branch\Report\ReportController@associateBusinessReport')->name('branch.report.associate_business_report');
            Route::post('associate_business', 'Branch\Report\ReportController@associateBusinessList')->name('branch.report.associate_business');
            Route::get('report/associate_business_summary', 'Branch\Report\ReportController@associateBusinessSummaryReport')->name('branch.report.associate_business_summary_report');
            Route::post('associate_business_summary', 'Branch\Report\ReportController@associateBusinessSummaryList')->name('branch.report.associate_business_summary');
            // Route::get('report/associate_business_compare', 'Branch\Report\ReportController@associateBusinessCompareReport')->name('branch.report.associate_business_compare_report');
            Route::post('associate_business_compare', 'Branch\Report\ReportController@associateBusinessCompareList')->name('branch.report.associate_business_compare');
            Route::post('associateBusinessListExport', 'Branch\ExportController@associateBusinessListExport')->name('branch.report.associateBusinessListExport');
            Route::post('associateBusinessSummaryExport', 'Branch\ExportController@associateBusinessSummaryExport')->name('branch.report.associateBusinessSummaryExport');
            Route::post('associateBusinessCompareExport', 'Branch\ExportController@associateBusinessCompareExport')->name('branch.report.associateBusinessCompareExport');
            Route::post('branchRegionByZone', 'Branch\Report\ReportController@branchRegionByZone')->name('branch.report.branchRegionByZone');
            Route::post('branchSectorByRegion', 'Branch\Report\ReportController@branchSectorByRegion')->name('branch.report.branchSectorByRegion');
            Route::post('branchBySector', 'Branch\Report\ReportController@branchBySector')->name('branch.report.branchBySector');
            Route::get('report/maturity', 'Branch\Report\ReportController@maturity')->name('branch.report.maturity');
            Route::post('report/maturity', 'Branch\Report\ReportController@planmaturity')->name('branch.report.maturityListing.plans');
            Route::post('report/maturityListing', 'Branch\Report\ReportController@maturityReportListing')->name('branch.report.maturityListing');
            Route::post('maturityDetailExport', 'Branch\ExportController@maturityListExport')->name('branch.maturity.report.export');
            Route::get('report/day_book', 'Branch\Report\DayBookController@day_bookReport')->name('branch.report.day_book');
            Route::post('daybookReportExport', 'Branch\ExportController@daybookReportExport')->name('branch.daybook.report.export');
            // Route::post('report/day_business', 'Admin\Report\ReportController@filtered_day_business_report')->name('admin.report.filtered_day_business');
            Route::get('print/report/day_book', 'Branch\Report\DayBookController@print_day_bookReport')->name('branch.print.report.day_book');
            Route::post('report/day_book_list', 'Branch\Report\DayBookController@day_filterbookReport')->name('branch.report.day_booklisting');
            Route::post('daybook/transaction_list', 'Branch\Report\DayBookController@transaction_list')->name('branch.daybook.transaction_listing');
            Route::get('report/loan', 'Branch\Report\ReportController@loan')->name('branch.report.loan');
            Route::post('report/companyIdToLoan', 'Branch\Report\ReportController@companyIdToLoan')->name('branch.report.companyIdToLoan');
            Route::post('report/loan-list', 'Branch\Report\ReportController@loanListing')->name('branch.report.loanlist');
            Route::get('report/group-loan', 'Branch\Report\ReportController@groupLoan')->name('branch.report.grouploan');
            Route::post('report/group-loan-list', 'Branch\Report\ReportController@groupLoanListing')->name('branch.report.grouploanlist');
            Route::post('loanDetailExport', 'Branch\ExportController@loanListExport')->name('branch.loan.report.export');
            Route::post('grouploanDetailExport', 'Branch\ExportController@groupLoanListExport')->name('branch.grouploan.report.export');
            Route::get('report/day_business', 'Branch\Report\BranchBusinessController@branch_business')->name('branch.report.day_business');
            Route::post('branch_business_listing', 'Branch\Report\BranchBusinessController@branch_business_listing')->name('branch.report.branch_business_listing');
            Route::post('branchBusinessReportExport', 'Branch\ExportController@branchBusinessReportExport')->name('branch.branch_business.report.export');
            /********************** Branch Report Manangement End **************************/
            /**************************** Associate Changes Start ***************/
            Route::get('associate/loan-commission-detail/{id}', 'Branch\MemberAssociateController@associateCommissionDetailLoan')->name('branch.associate.commission.detail_loan');
            Route::post('associatecommissionDetaillistLoan', 'Branch\MemberAssociateController@associateCommissionDetailListLoan')->name('branch.associate.commissionDetaillistLoan');
            Route::post('exportassociatecommissionDetaillistLoan', 'Branch\ExportController@exportAssociateCommissionDetailLoan')->name('branch.associate.exportcommissionDetailLoan');
            /**************************** Associate Changes End ***************/
            Route::post('bankChkbalanceBranch', 'Branch\CommanTransactionsController@bankChkbalance')->name('branch.bankChkbalanceBranch');
            Route::post('branchChkbalanceBranch', 'Branch\CommanTransactionsController@branchChkbalance')->name('branch.branchChkbalanceBranch');
            Route::post('getsubheadBranch', 'Branch\CommanTransactionsController@getSubHead')->name('branch.account_head_get_branch');
            Route::post('empCheck', 'Branch\CommanTransactionsController@empCheck')->name('branch.empCheck');
            /**************Voucher start  *************************/
            Route::get('voucher', 'Branch\Voucher\VoucherController@index')->name('branch.voucher');
            Route::get('/voucher/create', 'Branch\Voucher\VoucherController@create')->name('branch.voucher.create');
            Route::post('/voucher/save', 'Branch\Voucher\VoucherController@save')->name('branch.voucher.save');
            Route::get('voucher/print/{id}', 'Branch\Voucher\VoucherController@print')->name('branch.voucher.print');
            Route::post('/voucher/listing', 'Branch\Voucher\VoucherController@voucherList')->name('branch.voucher.list');
            Route::post('voucher/export', 'Branch\ExportController@voucherExport')->name('branch.voucher.exportList');
            /* BranchBankBalanceAmount */
            Route::post('BranchBankBalanceAmount', 'Branch\CommanController@getbranchbankbalanceamount')->name('branch.branchBankBalanceAmount');
            /* BranchBankBalanceAmount */
            /*********************** Voucher End  *********************************/
            Route::get('report/day_book_duplicate', 'Branch\Report\DayBookDublicateController@day_bookReport')->name('branch.report.day_book_dublicate');
            Route::post('daybookReportExportDublicate', 'Branch\ExportController@daybookReportExportDublicate')->name('branch.daybook.report.exportDublicate');
            // Route::post('report/day_business', 'Admin\Report\ReportController@filtered_day_business_report')->name('admin.report.filtered_day_business');
            Route::get('print/report/day_book_duplicate', 'Branch\Report\DayBookDublicateController@print_day_bookReport')->name('branch.print.report.day_book_dublicate');
            Route::post('report/day_book_list_dublicate', 'Branch\Report\DayBookDublicateController@day_filterbookReport')->name('branch.report.day_booklisting_dublicate');
            Route::post('dublicate_daybook/transaction_list', 'Branch\Report\DayBookDublicateController@transaction_list')->name('branch.dublicate_daybook.transaction_listing');
            //  get bank through company by Durgesh
            Route::post('branch/getBankByCompany', 'Branch\ChequeController@getBankByCompany')->name('branch.bank_list_by_company');
            /*---------------Loan Management Branch Start (GAURAV) ---------------------*/
            Route::post('loans/fetch', 'Branch\LoanController@fetch')->name('branch.loan.fetch');
            Route::post('loans/getLoanPlanlist', 'Branch\CommanController@getLoanPlanByType')->name('branch.loan.getplanlist');
            Route::post('loan/group/fetch', 'Branch\LoanController@groupLoanFetch')->name('group.loan.fetch');
            Route::post('loan-transactions/fetch', 'Branch\LoanController@transactionFetch')->name('transaction.loan.fetch');
            /*---------------Loan Management Branch End (GAURAV) ---------------------*/
            Route::get('daily/report', 'CommonController\Investment\InvestmentReportController@dailyReport')->name('branch.investment.daily.report');
            Route::get('monthly/report', 'CommonController\Investment\InvestmentReportController@monthlyReport')->name('branch.investment.monthly.report');
            Route::post('daily/report/listing', 'CommonController\Investment\InvestmentReportController@dailyReportListing')->name('branch.investement.dailyReportListing');
            Route::post('report/listing/export', 'CommonController\Investment\InvestmentReportController@export')->name('branch.investement_report.export');
            // Form_g table changes Start //
            Route::post('form_g/datacheck', 'Branch\FormGController@datacheck')->name('branch.form15g.datacheck');
            // Form_g table changes End //
            // voucher uodates by tansukh changes Start //
            Route::post('/voucher/checkGstData', 'Branch\Voucher\VoucherController@checkGstData')->name('branch.voucher.checkGstData');
            /****Member Gst get by membercompany  */
            Route::post('/gst_chrg_member', 'Branch\InvestmentController@checkgstChargeMember')->name('branch.gst.gst_charge_member');
            Route::post('get-member-details', 'Branch\Voucher\VoucherController@getMemberDetails')->name('branch.voucher.memberdetails');
            // voucher uodates by tansukh changes End //
            //check renewal Ammount limit Start
            Route::post('investment/renewlimit', 'Branch\NewRenewalController@renewlimit')->name('branch.investment.renewlimit');
            //check renewal Ammount limit End
            //investment Ssb listing amount limit Start
            Route::post('savingaccountreportlisting/export', 'Branch\SavingaccountreportController@export')->name('savingaccountreport.export');
            //investment Ssb listing amount limit End
            /*---------------Mother Branch Business Report Start(Mahesh) ---------------------*/
            Route::get('report/mother_branch_business', 'Branch\Report\MotherBranchBusinessController@index')->name('branch.report.mother_branch_business');
            Route::post('mother_branch_business_listing', 'Branch\Report\MotherBranchBusinessController@mother_branch_business_listing')->name('branch.report.mother_branch_business_listing');
            Route::post('motherBranchBusinessReportExport', 'Branch\Report\MotherBranchBusinessController@motherBranchBusinessReportExport')->name('branch.motherbranch_business.report.export');
            /*---------------Mother Branch Business Report End (mahesh) ---------------------*/
            // Advance Payment Section by mahesh Start
            Route::get('/advancePayment/{id}/{paymenttype}', 'Branch\AdvancePayment\AdvancePaymentController@add')->name('branch.advancePayment.add');
            Route::get('/addRequest', 'Branch\AdvancePayment\AdvancePaymentController@add_request')->name('branch.advancePayment.add_request');
            Route::get('/requestList', 'Branch\AdvancePayment\AdvancePaymentController@requestList')->name('branch.advancePayment.requestList');
            Route::get('/paymentList', 'Branch\AdvancePayment\AdvancePaymentController@paymentList')->name('branch.advancePayment.paymentList');
            Route::post('/advancePayment/getemployee', 'Branch\AdvancePayment\AdvancePaymentController@getemployee')->name('branch.advancePayment.getemployee');
            Route::post('/advancePayment/saveadvancepayment', 'Branch\AdvancePayment\AdvancePaymentController@saveTAadvancepayment')->name('branch.advancePayment.saveadvancepayment');
            Route::post('/advancePayment/advancerequest', 'Branch\AdvancePayment\AdvancePaymentController@advancerequest')->name('branch.advancePayment.advancerequest');
            Route::post('/AdvancedRequestListing', 'Branch\AdvancePayment\AdvancePaymentController@AdvancedRequestListing')->name('branch.advancePayment.AdvancedRequestListing');
            Route::post('/PaymentListing', 'Branch\AdvancePayment\AdvancePaymentController@PaymentListing')->name('branch.advancePayment.PaymentListing');
            Route::post('/getOwnerName', 'Branch\AdvancePayment\AdvancePaymentController@getOwnerNames')->name('branch.advancePayment.getOwnerNames');
            Route::post('/getemployeee', 'Branch\AdvancePayment\AdvancePaymentController@getemployeee')->name('branch.advancePayment.getemployeee');
            Route::get('/Adjestmentview/{id}', 'Branch\AdvancePayment\AdvancePaymentController@Adjestmentview')->name('branch.advancePayment.Adjestmentview');
            Route::post('/AdjListingtable', 'Branch\AdvancePayment\AdvancePaymentController@AdjListingtable')->name('branch.advancePayment.AdjListingtable');
            Route::post('/exportAdvanceRequestList', 'Branch\AdvancePayment\AdvancePaymentController@exportAdvanceRequestList')->name('branch.exportAdvanceRequestList');
            Route::post('companydate', 'Branch\AdvancePayment\AdvancePaymentController@companydate')->name('branch.companydate');
            // Advance Payment Section by mahesh end
            // --------------------------------Associate busnisses --------------------------------
            Route::get('report/associate_business_report', 'CommonController\associate\AssociateBusinessController@index')->name('branch.common.associate_busniss_report');
            Route::post('associate_busniss_report/list', 'CommonController\associate\AssociateBusinessController@listing')->name('branch.common.associate_busniss_report_list');
            Route::get('report/associate_business_compare', 'CommonController\associate\AssociateBusinessController@compare')->name('branch.report.associate_business_compare_report');
            Route::post('associate_business_compare/list', 'CommonController\associate\AssociateBusinessController@comparelisting')->name('branch.common.associate_busniss_compare_list');
            Route::post('associate_business_report/exportcompare', 'CommonController\associate\AssociateBusinessController@exportcompare')->name('branch.common.associate_busniss_report_exportcompare');
            Route::post('associate_business_report/export', 'CommonController\associate\AssociateBusinessController@export')->name('branch.common.associate_busniss_report_export');
            /** Member blaklist for loan start on 16 nov-2023 by Mahesh */
            Route::get('blacklist-members-on-loan', 'CommonController\MemberBlacklist\MemberBlacklistController@index')->name('branch.blacklist-members-on-loan');
            Route::post('action_blacklist_member_for_loan', 'CommonController\MemberBlacklist\MemberBlacklistController@actionBlacklistMemberForLoan')->name('branch.common_controller.action_blacklist_member_for_loan');
            Route::get('add-blacklist-member-on-loan', 'CommonController\MemberBlacklist\MemberBlacklistController@addBlacklist')->name('branch.add-blacklist-member-on-loan');
            Route::get('save-blacklist-member-on-loan', 'CommonController\MemberBlacklist\MemberBlacklistController@save')->name('branch.save-blacklist-member-on-loan');
            Route::post('member_blacklist_member_data', 'CommonController\MemberBlacklist\MemberBlacklistController@getBlacklistMemberData')->name('branch.member_blacklist_member_data');
            Route::post('member_blacklist_on_loan_listing', 'CommonController\MemberBlacklist\MemberBlacklistController@member_blacklist_on_loan_listing')->name('branch.member_blacklist_on_loan_listing');
            Route::post('exportblacklist_memberlist_export', 'CommonController\MemberBlacklist\MemberBlacklistController@exportMemberBlacklistOnLoan')->name('branch.member_blacklist_on_loan_listing_export');
            Route::post('block_details', 'CommonController\MemberBlacklist\MemberBlacklistController@blockDetails')->name('branch.block_details');
            /** Member blaklist for loan end */





            /**
             * loan emi payment new module by shahid 21/11/2023
             */
            Route::get('branch_loan_emi_payment', 'Branch\LoanEmiPaymentBranch\BranchLoanEmiPaymentController@index')->name('branch.LoanEmiPayment');
            Route::post('branch_loan_account_details', 'Branch\LoanEmiPaymentBranch\BranchLoanEmiPaymentController@getAccountDetails')->name('branch.LoanAccountDetails');
            Route::post('fetchBranchByCompanyId', 'Admin\PaymentManagement\FundTransferController@fetchbranchbycompanyid')->name('branch.fetchbranchbycompanyid');
            Route::post('getLoanCollectorAssociate', 'Admin\LoanController@getCollectorAssociate')->name('branch.loan.getcollectorassociate');
            Route::post('getBankAccountNos', 'Admin\PaymentManagement\FundTransferController@getBankAccountNos')->name('branch.getBankAccountNos');
            Route::post('registerplan/approve_cheque_details', 'Admin\CommanController@approveReceivedChequeDetail')->name('branch.approve_cheque_details');
            Route::post('deposite-loan-emi', 'Branch\LoanController@depositeLoanEmi')->name('branch.loan.depositeloanemi');
            Route::get('branch_loan_emi_payment_common', 'CommonController\LoanEmiPayment\LoanEmiPaymentController@index')->name('branch.common.LoanEmiPayment');
            Route::post('branch_loan_account_details_common', 'CommonController\LoanEmiPayment\LoanEmiPaymentController@getAccountDetails')->name('branch.common.LoanAccountDetails');
            /**end loan ei payment module */

            /** created by sourab on 17-01-24 */
            Route::get('correctionmanagement/renewal', 'Branch\CorrectionController@renewal')->name('branch.correctionmanagement.renewal');
            Route::post('correctionmanagement/renewal/list', 'Branch\CorrectionController@renewalList')->name('branch.correctionrequest.renewal.list');
            Route::post('correctionmanagement/renewal/export', 'Branch\CorrectionController@renewalListExport')->name('correction.export.branch.renewal');
            /*-------------- Correction Management Start--------------*/
            Route::get('correctionmanagement/add', 'Branch\CorrectionManagement\CorrectionManagementController@index')->name('branch.correctionmanagement.index');
            Route::post('correction/fields', 'Branch\CorrectionManagement\CorrectionManagementController@fields')->name('branch.correctionmanagement.fields');
            Route::post('correction/details', 'Branch\CorrectionManagement\CorrectionManagementController@details')->name('branch.correctionmanagement.details');
            Route::post('correction/save', 'Branch\CorrectionManagement\CorrectionManagementController@save')->name('branch.correctionmanagement.save');
            Route::get('correction/view', 'Branch\CorrectionManagement\CorrectionManagementController@correctionRequestviewnew')->name('branch.correctionmanagement.request');
            Route::post('correction/requestlist', 'Branch\CorrectionManagement\CorrectionManagementController@correctionRequestlists')->name('branch.correctionrequest.list');
            Route::post('export/correctionrequest', 'Branch\CorrectionManagement\CorrectionManagementController@exportcorrection')->name('correction.export.branch');
            /*-------------- Correction Management End--------------*/
            /*-------------- investment Management commission start created by durgesh 10-01-2024--------------*/

            Route::get('investment/commission/{id}', 'Branch\CommissionDetailReportController@investmentCommission')->name('branch.investment.commission');
            Route::post('investmentcommissionlisting', 'Branch\CommissionDetailReportController@investmentCommissionListing')->name('branch.investment.commissionlisting');
            Route::post('investmentcommissionexport', 'Branch\CommissionDetailReportController@exportInvestmentCommission')->name('branch.investmentcommission.export');

            Route::get('loan/commission/{id}', 'Branch\CommissionDetailReportController@loanCommission')->name('branch.loan_commission');
            Route::post('loan-commission', 'Branch\CommissionDetailReportController@loanCommissionList')->name('branch.loan_commission_list');
            Route::post('loanCommissionExport', 'Branch\CommissionDetailReportController@loanCommissionExport')->name('branch.loan.loanCommissionExport');

            /*-------------- investment Management commission end created by durgesh 10-01-2024--------------*/


            /**Created by Gaurav 19-12-2023 */
            Route::get('member-registration', 'CommonController\Member\MemberController@register')->name('branch.member_registration');
            Route::group(['middleware' => ['permission:Member Create']], function () {
                Route::post('member-registration-save', 'CommonController\Member\MemberController@save')->name('branch.member_registration.save');
            });
            Route::post('get_associateMember', 'CommonController\Member\MemberController@getAssociateMember')->name('branch.associate_member');

            Route::post('member-registration/emp_detail', 'CommonController\Member\MemberController@empDetail')->name('branch.member.empDetail');
            Route::post('check_idProof', 'CommonController\Member\MemberController@getMemberFromIdProof')->name('branch.check_idProof');
            Route::post('member/update', 'CommonController\Member\MemberController@save')->name('branch.memberUpdate');
            Route::get('members', 'CommonController\Member\MemberController@index')->name('branch.member.index');
            /**End */

            /** Created by Durgesh 20-12-2023 */
            Route::get('employee/register', 'CommonController\Employee\EmployeeController@add')->name('branch.employee_add');
            Route::post('employee/companydate', 'CommonController\Employee\EmployeeController@companydate')->name('branch.employee.companydate');
            Route::post('employee-save', 'CommonController\Employee\EmployeeController@employeeSave')->name('branch.employee_save');
            /********************** Employee Comman Register  end **************************/
            //created by Durgesh 19-12-2023--------
            Route::post('employeeDetail', 'CommonController\Employee\EmployeeController@employeeDetail')->name('branch.employeeDetail');
            Route::post('designationByCategory', 'CommonController\Employee\EmployeeController@designationByCategory')->name('branch.designationByCategory');
            Route::post('check-ssb-account', 'CommonController\Employee\EmployeeController@checkSsbAccount')->name('branch.check.ssb.account');
            Route::post('designationDataGet', 'CommonController\Employee\EmployeeController@designationDataGet')->name('branch.designationDataGet');
            Route::post('ssbDataGet', 'CommonController\Employee\EmployeeController@ssbDataGet')->name('branch.ssbDataGet');

            Route::post('get_m_District', 'CommonController\Member\MemberController@getDistrict')->name('branch.district_lists');
            Route::post('get_m_City', 'CommonController\Member\MemberController@getCity')->name('branch.city_lists');

            Route::post('refNoExistbranch', 'Branch\LoanController@refNoExist')->name('branch.ecs.refNo.exist');
            Route::post('ref-no-store-branch', 'Admin\LoanController@refNoStore')->name('branch.loan.refNoStore');


            /** Created by Durgesh 29-02-2024 */
            Route::get('loan/ecs/ecs_transactions', 'CommonController\Ecs\EcsController@index')->name('branch.ecs.ecs.transactions_list');
            Route::post('loan/ecs/ecs_transactions_list', 'CommonController\Ecs\EcsController@getData')->name('branch.ecs.ecs.transactions_listing');
            Route::post('loan/ecs/ecs_transactions_list_export', 'CommonController\Ecs\EcsController@ecsExport')->name('branch.ecs.ecs.transactions_export');

            // Created by Durgesh End here ------------------------------------>
            //Day Bussiness Report (created by Durgesh 28-09-2023)
            Route::get('report/day_business_report', 'CommonController\DayBussinessReportController@index')->name('branch.bussiness.report');
            //Day Bussiness Report created by Durgesh 04-10-2023
            Route::post('day_book_report', 'CommonController\DayBussinessReportController@reportDetail')->name('branch.dayBook.report');
            Route::post('day_book_report_export', 'CommonController\DayBussinessReportController@reportExport')->name('branch.dayBook.report.Export');

            Route::post('loan/investment/data/exist', 'Branch\LoanRegisterController@getLoanInvestmentRecord')->name('branch.loan.investment.data.exist');
             
            //fund trasfer log on branch by sourab on 12-04-24
            Route::post('fund-transfer/delete','Branch\FundTransferManagement\FundTransferController@deleteFundTransfer')->name('branch.fund_transfer.delete');
            Route::get('fund-transfer/logs/{id}','Branch\FundTransferManagement\FundTransferController@fundtransferlogs')->name('branch.fund_transfer.logs');

            Route::post('loan/request/reject', 'Branch\LoanController@loanRequestReject')->name('loan.reject');


        });
    });
    /*-------------- Branch End--------------*/
    Route::get('sms', 'JobController@sms');
    Route::get('email', 'JobController@processQueue');
    Route::get('branch-id-update-for-reinvest', 'Branch\Reinvest\ReinvestController@updateBranchId');
    Route::get('c-id-update-for-reinvest', 'Branch\Reinvest\ReinvestController@updateCId');
    Route::get('get-member-id-for-reinvest', 'Branch\Reinvest\ReinvestController@getMemberId');
    Route::get('get-member-investment-for-branch-update', 'Branch\Reinvest\ReinvestController@updateBranchIdInTransaction');
    Route::get('get-member-investment-renewal-transaction', 'Branch\ExportController@memberInvestmentTransaction');
});
/***************** Branch panel routes end *****************/
Route::post('/2fa', 'LoginController@submitfa')->name('submitfa');
Route::get('/2fa', 'LoginController@faverify')->name('2fa');
Route::post('/register', 'RegisterController@submitregister')->name('submitregister');
Route::get('/register', 'RegisterController@register')->name('register');
Route::get('/forget', 'UserController@forget')->name('forget');
Route::get('/r_pass', 'UserController@r_pass')->name('r_pass');
Route::group(['prefix' => 'user',], function () {
    Route::get('authorization', 'UserController@authCheck')->name('user.authorization');
    Route::post('verification', 'UserController@sendVcode')->name('user.send-vcode');
    Route::post('smsVerify', 'UserController@smsVerify')->name('user.sms-verify');
    Route::post('verify-email', 'UserController@sendEmailVcode')->name('user.send-emailVcode');
    Route::post('postEmailVerify', 'UserController@postEmailVerify')->name('user.email-verify');
    Route::group(['middleware' => 'isActive'], function () {
        Route::middleware(['CheckStatus'])->group(function () {
            Route::get('dashboard', 'UserController@dashboard')->name('user.dashboard');
            Route::get('plans', 'UserController@plans')->name('user.plans');
            Route::post('calculate', 'UserController@calculate');
            Route::post('buy', 'UserController@buy');
            Route::post('withdraw-update', 'UserController@withdrawupdate');
            Route::get('profile', 'UserController@profile')->name('user.profile');
            Route::post('kyc', 'UserController@kyc');
            Route::post('account', 'UserController@account');
            Route::post('avatar', 'UserController@avatar');
            Route::get('statement', 'UserController@statement')->name('user.statement');
            Route::get('merchant', 'UserController@merchant')->name('user.merchant');
            Route::get('sender_log', 'UserController@senderlog')->name('user.senderlog');
            Route::get('add-merchant', 'UserController@addmerchant')->name('user.add-merchant');
            Route::get('merchant-documentation', 'UserController@merchant_documentation')->name('user.merchant-documentation');
            Route::post('add-merchant', 'UserController@submitmerchant')->name('submit.merchant');
            Route::get('transfer_process/{id}/{token}', 'UserController@transferprocess')->name('transfer.process');
            Route::get('edit-merchant/{id}', 'UserController@Editmerchant')->name('edit.merchant');
            Route::get('log-merchant/{id}', 'UserController@Logmerchant')->name('log.merchant');
            Route::get('cancel_merchant/{id}', 'UserController@Cancelmerchant')->name('cancel.merchant');
            Route::get('submit_merchant/{id}', 'UserController@Paymerchant')->name('pay.merchant');
            Route::post('editmerchant', 'UserController@updatemerchant')->name('update.merchant');
            Route::get('ticket', 'UserController@ticket')->name('user.ticket');
            Route::post('submit-ticket', 'UserController@submitticket')->name('submit-ticket');
            Route::get('ticket/delete/{id}', 'UserController@Destroyticket')->name('ticket.delete');
            Route::get('reply-ticket/{id}', 'UserController@Replyticket')->name('ticket.reply');
            Route::post('reply-ticket', 'UserController@submitreply');
            Route::get('own_bank', 'UserController@ownbank')->name('user.ownbank');
            Route::post('own_bank', 'UserController@submitownbank')->name('submit.ownbank');
            Route::post('other_bank', 'UserController@submitotherbank')->name('submit.otherbank');
            Route::get('other_bank', 'UserController@otherbank')->name('user.otherbank');
            Route::post('local_preview', 'UserController@submitlocalpreview')->name('submit.localpreview');
            Route::get('local_preview', 'UserController@localpreview')->name('user.localpreview');
            Route::get('fund', 'UserController@fund')->name('user.fund');
            Route::get('preview', 'UserController@depositpreview')->name('user.preview');
            Route::post('fund', 'UserController@fundsubmit')->name('fund.submit');
            Route::get('bank_transfer', 'UserController@bank_transfer')->name('user.bank_transfer');
            Route::post('bank_transfer', 'UserController@bank_transfersubmit')->name('bank_transfersubmit');
            Route::get('withdraw', 'UserController@withdraw')->name('user.withdraw');
            Route::post('withdraw', 'UserController@withdrawsubmit')->name('withdraw.submit');
            Route::get('save', 'UserController@save')->name('user.save');
            Route::post('save', 'UserController@submitsave')->name('submitsave');
            Route::get('branch', 'UserController@branch')->name('user.branch');
            Route::get('password', 'UserController@changePassword')->name('user.password');
            Route::post('password', 'UserController@submitPassword')->name('change.password');
            Route::get('pin', 'UserController@changePin')->name('user.pin');
            Route::post('pin', 'UserController@submitPin')->name('change.pin');
            Route::get('loan', 'UserController@loan')->name('user.loan');
            Route::post('loansubmit', 'UserController@loansubmit');
            Route::post('bankupdate', 'UserController@bankupdate');
            Route::get('payloan/{id}', 'UserController@payloan')->name('user.payloan');
            Route::get('upgrade', 'UserController@upgrade')->name('user.upgrade');
            Route::get('read', 'UserController@read')->name('user.read');
            Route::post('deposit-confirm', 'PaymentController@depositConfirm')->name('deposit.confirm');
            Route::get('buy_asset', 'UserController@buyasset')->name('user.buyasset');
            Route::post('buy_asset', 'UserController@submitbuyasset')->name('submit.buyasset');
            Route::get('sell_asset', 'UserController@sellasset')->name('user.sellasset');
            Route::post('sell_asset', 'UserController@submitsellasset')->name('submit.sellasset');
            Route::get('exchange_asset', 'UserController@exchangeasset')->name('user.exchangeasset');
            Route::post('exchange_asset', 'UserController@submitexchangeasset')->name('submit.exchangeasset');
            Route::get('transfer_asset', 'UserController@transferasset')->name('user.transferasset');
            Route::post('transfer_asset', 'UserController@submittransferasset')->name('submit.transferasset');
            Route::get('check_asset', 'UserController@checkasset')->name('user.checkasset');
            Route::post('check_asset', 'UserController@submitcheckasset')->name('submit.checkasset');
            Route::post('2fa', 'UserController@submit2fa')->name('change.2fa');
        });
    });
    Route::get('logout', 'UserController@logout')->name('user.logout');
});
Route::get('user-password/reset', 'User\ForgotPasswordController@showLinkRequestForm')->name('user.password.request');
Route::post('user-password/email', 'User\ForgotPasswordController@sendResetLinkEmail')->name('user.password.email');
Route::get('user-password/reset/{token}', 'User\ResetPasswordController@showResetForm')->name('user.password.reset');
Route::post('user-password/reset', 'User\ResetPasswordController@reset');
Route::group(['prefix' => 'admin'], function () {
    Route::get('/', 'Admin\AdminLoginController@index')->name('admin.loginForm');
    Route::post('/', 'Admin\AdminLoginController@authenticate')->name('admin.login');
});
Route::post('/loginAdmin', 'Admin\AdminLoginController@submitlogin')->name('submitAdminlogin');
Route::post('/adminvarified', 'Admin\AdminLoginController@otpAdminvarified')->name('otpAdminvarified');
Route::post('/resendAdminotp', 'Admin\AdminLoginController@resendAdminotp')->name('resendAdminotp');
/*-------------- Admin Start--------------*/
Route::group(['prefix' => 'admin', 'middleware' => 'auth:admin'], function () {
    Route::get('/logout', 'AdminController@logout')->name('admin.logout');
    Route::get('/dashboard', 'Admin\AdminController@dashboard')->name('admin.dashboard');
    //Blog controller
    Route::post('/createcategory', 'PostController@CreateCategory');
    Route::post('/updatecategory', 'PostController@UpdateCategory');
    Route::get('/post-category', 'PostController@category')->name('admin.cat');
    Route::get('/unblog/{id}', 'PostController@unblog')->name('blog.unpublish');
    Route::get('/pblog/{id}', 'PostController@pblog')->name('blog.publish');
    Route::get('blog', 'PostController@index')->name('admin.blog');
    Route::get('blog/create', 'PostController@create')->name('blog.create');
    Route::post('blog/create', 'PostController@store')->name('blog.store');
    Route::get('blog/delete/{id}', 'PostController@destroy')->name('blog.delete');
    Route::get('category/delete/{id}', 'PostController@delcategory')->name('blog.delcategory');
    Route::get('blog/edit/{id}', 'PostController@edit')->name('blog.edit');
    Route::post('blog-update', 'PostController@updatePost')->name('blog.update');
    //Web controller
    Route::post('social-links/update', 'WebController@UpdateSocial')->name('social-links.update');
    Route::get('social-links', 'WebController@sociallinks')->name('social-links');
    Route::post('about-us/update', 'WebController@UpdateAbout')->name('about-us.update');
    Route::get('about-us', 'WebController@aboutus')->name('about-us');
    Route::post('privacy-policy/update', 'WebController@UpdatePrivacy')->name('privacy-policy.update');
    Route::get('privacy-policy', 'WebController@privacypolicy')->name('privacy-policy');
    Route::post('terms/update', 'WebController@UpdateTerms')->name('terms.update');
    Route::get('terms', 'WebController@terms')->name('admin.terms');
    Route::post('/createfaq', 'WebController@CreateFaq');
    Route::post('faq/update', 'WebController@UpdateFaq')->name('faq.update');
    Route::get('faq/delete/{id}', 'WebController@DestroyFaq')->name('faq.delete');
    Route::get('faq', 'WebController@faq')->name('admin.faq');
    Route::post('/createservice', 'WebController@CreateService');
    Route::post('service/update', 'WebController@UpdateService')->name('service.update');
    Route::get('service/edit/{id}', 'WebController@EditService')->name('brand.edit');
    Route::get('service/delete/{id}', 'WebController@DestroyService')->name('service.delete');
    Route::get('service', 'WebController@services')->name('admin.service');
    Route::post('/createpage', 'WebController@CreatePage');
    Route::post('page/update', 'WebController@UpdatePage')->name('page.update');
    Route::get('page/delete/{id}', 'WebController@DestroyPage')->name('page.delete');
    Route::get('page', 'WebController@page')->name('admin.page');
    Route::get('/unpage/{id}', 'WebController@unpage')->name('page.unpublish');
    Route::get('/ppage/{id}', 'WebController@ppage')->name('page.publish');
    Route::post('/createreview', 'WebController@CreateReview');
    Route::post('review/update', 'WebController@UpdateReview')->name('review.update');
    Route::get('review/edit/{id}', 'WebController@EditReview')->name('review.edit');
    Route::get('review/delete/{id}', 'WebController@DestroyReview')->name('review.delete');
    Route::get('review', 'WebController@review')->name('admin.review');
    Route::get('/unreview/{id}', 'WebController@unreview')->name('review.unpublish');
    Route::get('/preview/{id}', 'WebController@preview')->name('review.publish');
    Route::post('/createbrand', 'WebController@CreateBrand');
    Route::post('brand/update', 'WebController@UpdateBrand')->name('brand.update');
    Route::get('brand/edit/{id}', 'WebController@EditBrand')->name('brand.edit');
    Route::get('brand/delete/{id}', 'WebController@DestroyBrand')->name('brand.delete');
    Route::get('brand', 'WebController@brand')->name('admin.brand');
    Route::get('/unbrand/{id}', 'WebController@unbrand')->name('brand.unpublish');
    Route::get('/pbrand/{id}', 'WebController@pbrand')->name('brand.publish');
    Route::post('createbranch', 'WebController@CreateBranch');
    Route::get('branch-create', 'Admin\BranchController@create')->name('branch.create');
    Route::get('branch-edit/{id}', 'Admin\BranchController@edit')->name('branch.edit');
    Route::post('branch-create', 'Admin\BranchController@CreateBranch');
    Route::post('branch/update', 'Admin\BranchController@UpdateBranch')->name('branch.update');
    Route::post('check-branch', 'Admin\BranchController@checkBranch')->name('check.branch');
    Route::post('check-email', 'Admin\BranchController@checkEmail')->name('check.email');
    Route::post('check-phone', 'Admin\BranchController@checkPhone')->name('check.phone');
    Route::post('branch-listing', 'Admin\BranchController@branchListing')->name('branch.listing');
    Route::get('/get-ip/{id}', 'Admin\IpAddressController@getIp');
    Route::get('/branch-status/{id}/{status}', 'Admin\BranchController@branchStatusUpdate');
    Route::post('/add-ip', 'Admin\IpAddressController@addIp');
    Route::post('cities', 'CityController@getCity')->name('cities');
    Route::post('getPermission', 'Admin\PermissionController@getPermission')->name('getPermission');
    Route::post('ip/update', 'Admin\IpAddressController@UpdateIp')->name('ip.update');
    // Route::get('branch/delete/{id}', 'WebController@DestroyBranch')->name('branch.delete');
    Route::get('ip/delete/{id}', 'Admin\IpAddressController@DestroyIp')->name('ip.delete');
    Route::get('branch', 'Admin\BranchController@branch')->name('admin.branch');
    Route::get('branch/change-password/{id}', 'Admin\BranchController@password')->name('admin.changedPassword');
    Route::post('branch/change-password', 'Admin\BranchController@changePassword')->name('admin.branchChangedPassword');
    Route::post('branch/active-deactive', 'Admin\BranchController@updateAllBranches')->name('admin.branch.updateall');
    Route::get('permission', 'Admin\PermissionController@index')->name('admin.permission');
    Route::post('permission', 'Admin\PermissionController@saveUserAccess')->name('admin.permission');
    Route::post('permission-create', 'Admin\PermissionController@create')->name('permission.create');
    Route::post('role-create', 'Admin\PermissionController@createRole')->name('role.create');
    Route::get('currency', 'WebController@currency')->name('admin.currency');
    Route::get('pcurrency/{id}', 'WebController@pcurrency')->name('blog.publish');
    Route::get('logo', 'WebController@logo')->name('admin.logo');
    Route::post('updatelogo', 'WebController@UpdateLogo');
    Route::post('updatefavicon', 'WebController@UpdateFavicon');
    Route::get('home-page', 'WebController@homepage')->name('homepage');
    Route::post('home-page/update', 'WebController@Updatehomepage')->name('homepage.update');
    Route::post('section1/update', 'WebController@section1');
    Route::post('section2/update', 'WebController@section2');
    Route::post('section3/update', 'WebController@section3');
    Route::post('section4/update', 'WebController@section4');
    Route::post('section8/update', 'WebController@section8');
    Route::post('section9/update', 'WebController@section9');
    //Withdrawal controller
    Route::get('withdraw-log', 'WithdrawController@withdrawlog')->name('admin.withdraw.log');
    Route::get('withdraw-method', 'WithdrawController@withdrawmethod')->name('admin.withdraw.method');
    Route::post('withdraw-method', 'WithdrawController@store')->name('admin.withdraw.store');
    Route::get('withdraw-method/delete/{id}', 'WithdrawController@DestroyMethod')->name('withdrawmethod.delete');
    Route::get('withdraw-approved', 'WithdrawController@withdrawapproved')->name('admin.withdraw.approved');
    Route::get('withdraw-declined', 'WithdrawController@withdrawdeclined')->name('admin.withdraw.declined');
    Route::get('withdraw-unpaid', 'WithdrawController@withdrawunpaid')->name('admin.withdraw.unpaid');
    Route::get('withdraw/delete/{id}', 'WithdrawController@DestroyWithdrawal')->name('withdraw.delete');
    Route::get('approvewithdraw/{id}', 'WithdrawController@approve')->name('withdraw.approve');
    Route::get('declinewithdraw/{id}', 'WithdrawController@decline')->name('withdraw.declined');
    Route::get('approvewithdrawm/{id}', 'WithdrawController@approvem')->name('withdraw.approvem');
    Route::get('/declinewithdrawm/{id}', 'WithdrawController@declinem')->name('withdraw.declinedm');
    //Deposit controller
    Route::get('bank-transfer', 'DepositController@banktransfer')->name('admin.banktransfer');
    Route::get('bank_transfer/delete/{id}', 'DepositController@DestroyTransfer')->name('transfer.delete');
    Route::post('bankdetails', 'DepositController@bankdetails');
    Route::get('deposit-log', 'DepositController@depositlog')->name('admin.deposit.log');
    Route::get('deposit-method', 'DepositController@depositmethod')->name('admin.deposit.method');
    Route::post('storegateway', 'DepositController@store');
    Route::get('approvebk/{id}', 'DepositController@approvebk')->name('deposit.approvebk');
    Route::get('declinebk/{id}', 'DepositController@declinebk')->name('deposit.declinebk');
    Route::get('deposit-approved', 'DepositController@depositapproved')->name('admin.deposit.approved');
    Route::get('deposit-pending', 'DepositController@depositpending')->name('admin.deposit.pending');
    Route::get('deposit-declined', 'DepositController@depositdeclined')->name('admin.deposit.declined');
    Route::get('deposit/delete/{id}', 'DepositController@DestroyDeposit')->name('deposit.delete');
    Route::get('approvedeposit/{id}', 'DepositController@approve')->name('deposit.approve');
    Route::get('declinedeposit/{id}', 'DepositController@decline')->name('deposit.decline');
    //Save 4 me controller
    Route::get('save-completed', 'SaveController@Completed')->name('admin.save.completed');
    Route::get('save-pending', 'SaveController@Pending')->name('admin.save.pending');
    Route::get('save/delete/{id}', 'SaveController@Destroy')->name('save.delete');
    Route::get('save-release/{id}', 'SaveController@Release')->name('save.release');
    /* *************** SSb Account Setting Start  ************** */
    Route::get('/ssbaccount-register', 'Admin\SsbController@register')->name('admin.ssbaccount.register');
    Route::post('/ssbaccount-register', 'Admin\SsbController@save')->name('admin.ssbaccount.save');
    Route::get('/ssbaccountdetails', 'Admin\SsbController@ssbaccountdetails')->name('admin.ssbaccount.ssbaccountdetails');
    Route::post('/ssbaccountdetailssearch', 'Admin\SsbController@ssbaccountdetailssearch')->name('admin.ssbaccount.ssbaccountdetailssearch');
    Route::post('/ssbaccountdetailssave', 'Admin\SsbController@ssbaccountdetails')->name('admin.ssbaccount.ssbaccountdetailssave');
    Route::post('/ssbactivatesetting', 'Admin\SsbController@activatesetting')->name('admin.ssbaccount.activatesetting');
    /* *************** SSb Account Setting End  ************** */
    /* *************** Transcation Start  ************** */
    Route::get('/transcationdetails', 'Admin\TranscationController@transcationdetails')->name('admin.transcation.transcationdetails');
    Route::post('/transcationdetailssearch', 'Admin\TranscationController@transcationdetailssearch')->name('admin.transcation.transcationdetailssearch');
    /* *************** Transcation end  ************** */
    //Gst Module Start *******
    Route::get('/gst_setting', 'Admin\GstController@gst_setting_form')
        ->name('admin.gst.gst_setting_form');
    Route::post('/gst_setting_save', 'Admin\GstController@gst_setting_save')
        ->name('admin.gst_setting.save');
    Route::get('/gst_setting_list', 'Admin\GstController@index')
        ->name('admin.gst.gst_setting_listing');
    Route::post('/gst_setting_listing', 'Admin\GstController@setting_listing')
        ->name('admin.gst.gst_setting_listing_detail');
    Route::get('/head_gst_setting', 'Admin\GstController@HeadSettingform')->name('admin.gst.head.setting');
    Route::post('/head_gst_setting_save', 'Admin\GstController@headSettingSave')->name('admin.head_setting.save');
    Route::get('/head_gst_setting_listing', 'Admin\GstController@headSetting')->name('admin.gst.head.setting_list');
    Route::post('/head_setting_listing', 'Admin\GstController@head_setting_listing')
        ->name('admin.gst.head_setting_listing_detail');
    Route::get('edit/gst_setting/{id}', 'Admin\GstController@edit_gst_setting_form')
        ->name('admin.gst.editgst_setting_form');
    Route::post('/gst_setting_update', 'Admin\GstController@gst_setting_update')
        ->name('admin.gst_setting.update');
    Route::get('edit/head_setting/{id}', 'Admin\GstController@edit_head_setting_form')
        ->name('admin.gst.editgst_setting_form');
    Route::post('/head_setting_update', 'Admin\GstController@head_setting_update')
        ->name('admin.head_setting.update');
    Route::post('/gst_chrg', 'Admin\GstController@checkgstCharge')
        ->name('admin.gst.gst_charge');
    //Gst Report Management
    Route::get('gst_report', 'Admin\GstReportController@gst_report')->name('admin.gstReport.report');
    Route::get('crdr_report', 'Admin\GstReportController@crdr_report')->name('admin.gstReport.crdr_report');
    Route::get('gst_summary_report', 'Admin\GstReportController@gst_summary_report')->name('admin.gst_summary_report');
    Route::post('gst_summary_report_listing', 'Admin\GstReportController@gst_summary_report_listing')->name('admin.gstsummary.listing');
    Route::post('export_gst_summary_report', 'Admin\GstReportController@exportgstSummaryReport')
        ->name('admin.gst_summary_report.export');
    Route::post('gst_cr_dr_note_report', 'Admin\GstReportController@gst_cr_dr_note_report')
        ->name('admin.gst_cr_dr_note.listing');
    Route::post('export_gst_outward_report', 'Admin\GstReportController@exportgstOutwardReport')
        ->name('admin.gst_outward_report.export');
    Route::post('export_gst_crdr_report', 'Admin\GstReportController@exportgstcrdrReport')
        ->name('admin.gst_crdr_report.export');
    //Gst Report management End
    //Gst Module End *****************
    //Loan controller
    Route::get('loan-completed', 'AdminController@Loancompleted')->name('admin.loan.completed');
    Route::get('loan-pending', 'AdminController@Loanpending')->name('admin.loan.pending');
    Route::get('loan-hold', 'AdminController@Loanhold')->name('admin.loan.hold');
    Route::post('loans/status', 'Admin\LoanController@tenureStatusChange')->name('admin.loan.plan_status_change');
    Route::get('loan/delete/{id}', 'AdminController@LoanDestroy')->name('loan.delete');
    Route::get('loan-approve/{id}', 'AdminController@Loanapprove')->name('loan.approve');
    //--------------------loan url changes status Alpana ------
    Route::get('loan/tenure', 'Admin\LoanController@LoansTenure')->name('admin.loan.loans');
    Route::get('loan/delete/{id}', 'Admin\LoanController@Destroy')->name('loan.delete');
    Route::get('loan/tenure/create', 'Admin\LoanController@Create')->name('admin.loan.create');
    Route::post('get-loan-category', 'Admin\LoanController@getloanCategory')->name('admin.get.loanCategory');
    Route::get('loan/plan/details/{id}', 'Admin\LoanController@Edit')->name('admin.plan.loans');
    Route::post('loan-store', 'Admin\LoanController@Store')->name('admin.loan.store');
    Route::get('loan/tenure/edit/{id}', 'Admin\LoanController@Edit')->name('admin.loan.edit');
    Route::post('loan_tenure_update', 'Admin\LoanController@updateTenure')->name('admin.loan.updates');
    Route::post('loan-ajax-listing', 'Admin\LoanController@loanListing')->name('admin.loan.list');
    Route::post('loan/status_change', 'Admin\LoanController@tenureStatusChange')->name('admin.loan.plan_status_change');
    Route::get('loan/plan/listing', 'Admin\LoanController@index')->name('admin.loan.plan_listing');
    Route::post('loan/plan/ajax-listing', 'Admin\LoanController@planListing')->name('admin.loan.planlist');
    Route::get('loan/plan/create', 'Admin\LoanController@planCreate')->name('admin.loan.planCreate');
    Route::post('loan/plan/store', 'Admin\LoanController@planStore')->name('admin.loan.plan.store');
    Route::post('loan/plan/status_change', 'Admin\LoanController@planstatusChange')->name('admin.loan.plan.statusChange');
    Route::post('getInsuranceCharge', 'Admin\LoanController@getInsuranceCharge')->name('admin.loan.getInsuranceCharge');
    Route::post('loan/getplanlist', 'Admin\CommanController@getLoanPlanByType')->name('admin.loan.getplanlist');
    Route::get('loan/loan-requests', 'Admin\LoanController@loanRequest')->name('admin.loan.request');
    Route::post('loan/getactiveplanlist', 'Admin\CommanController@getActivePlanByType')->name('admin.loan.getactiveplanlist');
    Route::post('loan/getDublicateTenure', 'Admin\LoanController@getDublicateTenure')->name('admin.loan.getDublicateTenure');
    //--------------------loan url changes end  Alpana ------
    Route::get('loan-emi-delete-form', 'Admin\DeleteEmiLoanController@emideleteForm')->name('admin.loan.emi_deleteForm');
    Route::post('loan-emi-list', 'Admin\DeleteEmiLoanController@emilist')->name('admin.loan.emi_record');
    Route::get('delete/emi/{id}/{type}/{id2}', 'Admin\DeleteEmiLoanController@delete_emi_transaction')->name('admin.loan.edit');
    Route::post('delete/emi/{id}/{type}/{id2}/', 'Admin\DeleteEmiLoanController@delete_emi_transaction_with_reason')->name('admin.loan.delete');

    Route::post('get_approved_cheque_branchwise', 'Admin\LoanController@getBranchApprovedCheque')->name('admin.approve_cheque_branchwise');
    // Route::post('loan-transaction-ajax', 'Admin\LoanController@loanTransactionAjax')
    //     ->name('admin.loan.transactionlist');
    // Route::get('loan-transactions', 'Admin\LoanController@loanTransaction')
    //     ->name('admin.loan.transaction');
    Route::post('loan-transaction-export', 'Admin\LoanController@loanTransactionExportList')
        ->name('admin.loantransaction.export');
    Route::get('group-loan-requests', 'Admin\LoanController@groupLoanRequest')->name('admin.grouploan.request');
    Route::post('loan-requests-ajax', 'Admin\LoanController@loanRequestAjax')->name('admin.loan.requestlist');
    Route::post('group-loan-requests-ajax', 'Admin\LoanController@groupLoanRequestAjax')->name('admin.grouploan.requestlist');
    Route::get('loan/approve/{id}', 'Admin\LoanController@loanRequestApproval')->name('admin.loan.approve');
    Route::get('loan/approve-group-loan/{id}', 'Admin\LoanController@groupLoanRequestApproval')->name('admin.grouploan.approve');
    Route::get('loan/loan-request-reject/{id}/{type}', 'Admin\LoanController@loanRequestRejection')->name('admin.loan.reject');
    Route::get('loan/view/{id}/{type}', 'Admin\LoanController@View')->name('admin.loan.view');
    Route::get('loan/edit/{id}', 'Admin\LoanController@editLoan')->name('admin.memberloan.edit');
    Route::post('loan/update', 'Admin\LoanController@updateLoan')->name('admin.memberloan.update');
    Route::post('check/penalty', 'Admin\LoanController@checkPenalty')->name('admin.check.penalty');
    Route::get('loan/transfer/{id}', 'Admin\LoanController@transferAmountView')->name('admin.loan.amounttranfer');
    Route::get('loan/print/{id}/{type}', 'Admin\LoanController@printView')->name('loan.print');
    Route::post('loan/close', 'Admin\LoanController@loanClosing')->name('admin.loan.close');
    Route::post('grouploan/close', 'Admin\LoanController@groupLoanClosing')->name('admin.grouploan.close');
    //Route::get('loan/form/print/{id}/{type}', 'Admin\ExportController@downloadLoanForm')->name('loan.download.pdf');
    Route::get('loan/form/print/{id}/{type}', 'Admin\LoanController@printLoanForm')->name('loan.printloan.form');
    Route::get('loan/download-recovery-clear/{id}/{type}', 'Admin\ExportController@DownloadRecoveryNoDueLoan')->name('loan.downloadrecoveryclear.pdf');
    Route::get('loan/print-recovery-clear/{id}/{type}', 'Admin\ExportController@PrintRecoveryNoDueLoan')->name('loan.printrecoveryclear.pdf');
    Route::get('loan/form/termcondition/{id}/{type}', 'Admin\ExportController@downloadLoanTermCondition')->name('loan.download.termconditionpdf');
    Route::post('loan/loan-amount-transfer', 'Admin\LoanController@transferAmount')->name('admin.loan.transferamount');
    Route::get('loan/transfer-group-loan-amount/{id}', 'Admin\LoanController@transferGroupLoanAmountView')->name('admin.loan.grouploanamounttranfer');
    Route::post('loan/group-loan-amount-transfer', 'Admin\LoanController@transferGroupLoanAmount')->name('admin.grouploan.transferamount');
    // Route::get('loans/recovery', 'Admin\LoanController@recovery')->name('admin.loan.recovery');
    // Recovery loan controller made by Mahesh
    Route::get('loans/recovery', 'Admin\RecoveryLoanController@recovery')->name('admin.loan.recovery');
    Route::post('loans/recovery/type', 'Admin\RecoveryLoanController@loantype')->name('admin.loan.loantype');
    Route::get('loans/group-loans-recovery', 'Admin\RecoveryLoanController@groupLoanRecovery')->name('admin.grouploan.recovery');
    Route::post('loan-recovery-list', 'Admin\RecoveryLoanController@recoveryListAjax')->name('admin.loan.recovery_list');
    Route::post('group-loan-recovery-list', 'Admin\RecoveryLoanController@groupLoanRecoveryListAjax')->name('admin.grouploan.recovery_list');
    Route::post('loan-transaction-ajax', 'Admin\RecoveryLoanController@loanTransactionAjax')
        ->name('admin.loan.transactionlist');
    Route::get('loan-transactions', 'Admin\RecoveryLoanController@loanTransaction')
        ->name('admin.loan.transaction');
    Route::post('loan/loantype', 'Admin\RecoveryLoanController@nongrouploan')->name('admin.loan.nongrouploan');
    Route::post('loan/loantype', 'Admin\RecoveryLoanController@grouploan')->name('admin.loan.grouploan');
    Route::post('loan-transaction-export', 'Admin\RecoveryLoanController@loanTransactionExportList')
        ->name('admin.loantransaction.export');
    Route::get('loan/emi-transactions/{id}/{type}', 'Admin\RecoveryLoanController@emiTransactionsView')->name('admin.lona.emitransactions');
    Route::post('loan/deposit/emi-transactions-list', 'Admin\RecoveryLoanController@depositLoanTransaction')->name('admin.loan.deposit.emi_list');
    Route::post('loan/emi-transactions-list', 'Admin\RecoveryLoanController@emiTransactionsList')->name('admin.loan.emi_list');
    // Recovery loan controller made by Mahesh
    // Route::get('loans/group-loans-recovery', 'Admin\LoanController@groupLoanRecovery')->name('admin.grouploan.recovery');
    // Route::post('loan-recovery-list', 'Admin\LoanController@recoveryListAjax')->name('admin.loan.recovery_list');
    // Route::post('group-loan-recovery-list', 'Admin\LoanController@groupLoanRecoveryListAjax')->name('admin.grouploan.recovery_list');
    Route::post('loan-recovery-export', 'Admin\ExportController@loanRecoveryExport')->name('admin.loanrecovery.export');
    Route::post('loan-details-export', 'Admin\ExportController@loanDetailsExport')->name('admin.loandetails.export');
    Route::post('group-loan-recovery-export', 'Admin\ExportController@groupLoanRecoveryExport')->name('admin.grouploanrecovery.export');
    Route::post('group-loan-details-export', 'Admin\ExportController@groupLoanDetailsExport')->name('admin.grouploandetails.export');
    Route::post('deposite-loan-emi', 'Admin\LoanController@depositeLoanEmi')->name('admin.loan.depositeloanemi');
    Route::post('deposite-group-loan-emi', 'Admin\LoanController@depositeGroupLoanEmi')->name('admin.grouploan.depositeloanemi');
    // Route::get('loan/emi-transactions/{id}/{type}', 'Admin\LoanController@emiTransactionsView')->name('admin.lona.emitransactions');
    // Route::post('loan/emi-transactions-list', 'Admin\LoanController@emiTransactionsList')->name('admin.loan.emi_list');
    Route::post('loan/get-bank-amount', 'Admin\LoanController@getBankDayBookAmount')->name('admin.loan.getbankdaybookamount');
    Route::get('loan/deposit/emi-transactions/{id}/{type}', 'Admin\LoanController@depositeloanEmiView')->name('admin.loan.deposit.emitransactions');
    // Route::post('loan/deposit/emi-transactions-list', 'Admin\LoanController@depositLoanTransaction')->name('admin.loan.deposit.emi_list');
    Route::post('gst_amount_penalty', 'Admin\LoanController@gst_amount_penalty')
        ->name('admin.loan.getgstLatePenalty');
    /*Delete Laon tenure Start */
    Route::post('delete_loan_tenure_charge', 'Admin\LoanController@delete_loan_tenure_charge')
        ->name('admin.loan.delete_loan_tenure_charge');
    Route::post('edit_loan_plan', 'Admin\LoanController@editPlan')
        ->name('admin.loan.plan.edit');
    /*End*/
    //* Loan >> Settings >> Loan Charges Start*//
    Route::get('loan/loansettings/loancharges', 'Admin\LoanSettings\LoanChargeController@LoanCharges')->name('admin.loan.loansettings.loancharges');
    Route::get('loan/loansettings/loancharges-create', 'Admin\LoanSettings\LoanChargeController@LoanChargesCreate')->name('admin.loan.loansettings.loanchargescreate');
    Route::post('loan/loansettings/loancharge-store', 'Admin\LoanSettings\LoanChargeController@LoanChargesStore')->name('admin.loan.loansettings.loancharges.store');
    Route::get('loan/loansettings/loancharges-edit/{id}', 'Admin\LoanSettings\LoanChargeController@LoanChargesEdit')->name('admin.loan.loansettings.loanchargesedit');
    Route::post('loan/loansettings/loancharges-edit', 'Admin\LoanSettings\LoanChargeController@LoanChargesUpdate')->name('admin.loan.loansettings.loancharges.update');
    Route::post('loan/loansettings/loancharges-list', 'Admin\LoanSettings\LoanChargeController@LoanChargesList')->name('admin.loan.loansettings.loanchargelist');
    Route::post('loan/loansettings/loanchargelistexport', 'Admin\LoanSettings\LoanChargeController@LoanChargelistExport')->name('admin.loan.loansettings.loanchargelistexport');
    Route::post('planByLoanType', 'Admin\LoanSettings\LoanChargeController@planByLoanType')->name('admin.planByLoanType');
    Route::post('tenureByPlanName', 'Admin\LoanSettings\LoanChargeController@tenureByPlanName')->name('admin.tenureByPlanName');
    Route::post('loanChargeCheckExistingTenure', 'Admin\LoanSettings\LoanChargeController@loanChargeCheckExistingTenure')->name('admin.loanChargeCheckExistingTenure');
    //* Loan >> Settings >> Loan Charges End*//
    // Reject Hold//
    Route::post('loan/rejectHold', 'Admin\LoanController@loanRequestRejectHold')->name('admin.loan.reject_hold');
    Route::get('loan/logs/{loanId}/{loanType}', 'Admin\LoanController@loanLogs')->name('admin.loan.loanLogs');
    Route::get('loan/pending/{id}/{loanType}/{status}/{date}', 'Admin\LoanController@loanStatusChange')->name('admin.loan.status.change');
    //Reject Hold End //
    /************************** Loan Start **************/
    // Route::get('loan/commission/{id}', 'Admin\LoanController@loanCommission')->name('admin.loan_commission');
    // Route::post('loan-commission', 'Admin\LoanController@loanCommissionList')->name('admin.loan_commission_list');
    // Route::post('loanCommissionExport', 'Admin\ExportController@loanCommissionExport')->name('admin.loan.loanCommissionExport');
    Route::get('loan/commission-group/{id}', 'Admin\LoanController@loanGroupCommission')->name('admin.loan_commission_group');
    Route::post('loan-group_commission', 'Admin\LoanController@loanGroupCommissionList')->name('admin.loan_group_commission_list');
    Route::post('loanGroupCommissionExport', 'Admin\ExportController@loanGroupCommissionExport')->name('admin.loan.loanGroupCommissionExport');
    Route::post('loan/checkAccountNumber', 'Admin\LoanController@checkAccountNumber')->name('admin.demand.loan.checkAccountNumber');
    Route::get('loans/outstanding_report', 'Admin\LoanController@outstanding_report')
        ->name('admin.memberLoans.outStanding');
    Route::post('loans/report_list', 'Admin\LoanController@LoanoutstandingDuereport')
        ->name('admin.loan.reportList');
    Route::get('outstanding_report/group_loans', 'Admin\LoanController@outstanding_report')
        ->name('admin.memberGroupLoans.outStanding');
    Route::get('loans/loan_due_report', 'Admin\LoanController@outstanding_report')
        ->name('admin.loan.loan_due_report');
    Route::post('export/Loanoutstandingreport', 'Admin\LoanController@export_Loanoutstandingreport')
        ->name('admin.loan.outStanding.export');
    Route::post('loan/repayment_chart', 'Admin\LoanController@repayment_chart')
        ->name('admin.loan.repayment_chart');
    Route::post('export/repayment', 'Admin\LoanController@export_repayment')
        ->name('admin.loan.repayment.export');
    /*************************** Loan Changes End **************/
    Route::get('personal-employ-detail', 'Admin\LoanController@personalAndEmployDetail')->name('admin.loan.personal.detail');
    Route::post('update-pdf-generate-status', 'Admin\LoanController@update_pdf_generate_status')->name('admin.memberLoans.updatePdfGenerate');
    Route::post('update-print-nodues-status', 'Admin\LoanController@update_nodues_print_status')->name('admin.memberLoans.update_no_dues_print_status');
    //Py scheme plan controller
    /*Route::get('py-completed', 'admin/PyschemeController@Completed')->name('admin.py.completed');
    Route::get('py-pending', 'admin/PyschemeController@Pending')->name('admin.py.pending');*/
    Route::get('py-plans', 'Admin\PyschemeController@Plans')->name('admin.py.plans');
    Route::get('py/delete/{id}', 'Admin\PyschemeController@Destroy')->name('py.delete');
    Route::get('py-plan/delete/{id}', 'Admin\PyschemeController@PlanDestroy')->name('py.plan.delete');
    Route::get('py-plan/create', 'Admin\PyschemeController@Create')->name('admin.plan.create');
    Route::post('py-plan-create', 'Admin\PyschemeController@Store')->name('admin.plan.store');
    Route::get('py-plan/{id}', 'Admin\PyschemeController@Edit')->name('admin.plan.edit');
    Route::get('py-plan/show/{slug?}', 'Admin\PyschemeController@show')->name('admin.plan.show');
    Route::post('py-plan-edit', 'Admin\PyschemeController@Update')->name('admin.plan.update');
    Route::post('py-plan/fetch/slug', 'Admin\PyschemeController@fetchSlug')->name('admin.plan.fetch.slug');
    Route::post('py-plan-status', 'Admin\PyschemeController@Status')->name('admin.plan.status');
    Route::get('py-plan-tenure-status/{id}', 'Admin\PyschemeController@tenureStatus')->name('admin.tenure.status');
    Route::post('py-plan-ajax-listing', 'Admin\PyschemeController@planListing')->name('investment.plan_list');
    //samradh money back setting start
    Route::get('py-plans/money-back/{slug}', 'Admin\MoneyBackController@index')->name('moneyBack.list');
    Route::post('py-plans/money-back', 'Admin\MoneyBackController@store')->name('moneyBack.store');
    Route::get('py-plans/money-back/status/{id}', 'Admin\MoneyBackController@status')->name('MoneyBack.status');
    Route::post('py-plans/money-back/update', 'Admin\MoneyBackController@update')->name('moneyBack.update');
    Route::post('py-plans/money-back/trash', 'Admin\MoneyBackController@destroy')->name('moneyBack.destroy');
    Route::post('py-plans/money-back/checkAvailablity', 'Admin\MoneyBackController@checkAvailablity')->name('MoneyBack.check');
    //samradh money back setting end
    //samradh Death help setting start
    Route::get('py-plans/death-help/{slug?}', 'Admin\DeathHelpController@index')->name('deathHelp.list');
    Route::post('py-plans/death-help', 'Admin\DeathHelpController@store')->name('deathHelp.store');
    Route::post('py-plans/death-help/update', 'Admin\DeathHelpController@update')->name('deathHelp.update');
    Route::get('py-plans/death-help/status/{id}', 'Admin\DeathHelpController@status')->name('deathHelp.status');
    Route::post('py-plans/death-help/trash', 'Admin\DeathHelpController@destroy')->name('deathHelp.destroy');
    Route::post('py-plans/death-help/checkAvailablity', 'Admin\DeathHelpController@checkAvailablity')->name('deathHelp.check');
    //samradh Death help setting end
    //samradh loan against deposit start
    Route::get('py-plans/loan-against/{slug?}', 'Admin\LoanAgainstDepositController@index')->name('loanAgainst.list');
    Route::post('py-plans/loan-against', 'Admin\LoanAgainstDepositController@store')->name('loanAgainst.store');
    Route::post('py-plans/loan-against/update', 'Admin\LoanAgainstDepositController@update')->name('loanAgainst.update');
    Route::get('py-plans/loan-against/status/{id}', 'Admin\LoanAgainstDepositController@status')->name('loanAgainst.status');
    Route::post('py-plans/loan-against/trash', 'Admin\LoanAgainstDepositController@destroy')->name('loanAgainst.destroy');
    Route::post('py-plans/loan-against/checkAvailablity', 'Admin\LoanAgainstDepositController@checkAvailablity')->name('loanAgainst.check');
    /***** py-plans tenure Start */
    Route::get('py-plans/tenure/{id}', 'Admin\PyschemeController@tenure')->name('admin.py-plans.tenure');
    Route::post('py-plans/tenure/tenure_save', 'Admin\PyschemeController@tenure_save')->name('admin.py-plans.tenure.tenure_save');
    /*****py-plans tenure End */
    //Samraddh FD Bank Start
    Route::get('create/samraddh/bank', 'Admin\CompanyFDController@create')->name('admin.create.fd');
    Route::get('samraddh/fd/list', 'Admin\CompanyFDController@index')->name('admin.company.fd.list');
    Route::post('samraddh/store', 'Admin\CompanyFDController@store')->name('admin.save.company_fd');
    Route::post('samraddh/listing', 'Admin\CompanyFDController@listing_company_bound')->name('admin.company_bound_listing');
    Route::get('company_bound/interest/{id}', 'Admin\CompanyFDController@generate_interest')->name('admin.company.fd.generate_interest');
    Route::post('samraddh/save/interest', 'Admin\CompanyFDController@saveInterest')->name('admin.save.interest');
    Route::get('samraddh/interest/transaction/{id}', 'Admin\CompanyFDController@transactions')->name('admin.interest.transaction');
    Route::post('samraddh/interest/transaction_list', 'Admin\CompanyFDController@transactionList')->name('admin.interest.transaction_listing');
    Route::get('samraddh/fd/close/{id}', 'Admin\CompanyFDController@FDClose')->name('admin.fd.close');
    Route::post('samraddh/fd/close', 'Admin\CompanyFDController@FDClosePermanent')->name('admin.fd.close.permanent');
    Route::post('samraddh/fd/delete', 'Admin\CompanyFDController@destroy')->name('admin.fd.delete');
    Route::post('samraddh/companyBond/export', 'Admin\ExportController@ExportCompanyBond')->name('admin.comapnyBond.export');
    Route::post('samraddh/companyBond/transaction/export', 'Admin\ExportController@ExportCompanyBondInterestTransaction')->name('admin.comapnyBond.interest_transaction.export');
    //Samraddh FD Bank End
    // Investment Report management
    Route::get('daily/report', 'Admin\InvestmentReportController@dailyReport')->name('admin.investment.daily.report');
    Route::get('monthly/report', 'Admin\InvestmentReportController@monthlyReport')->name('admin.investment.monthly.report');
    Route::post('daily/report/listing', 'Admin\InvestmentReportController@dailyReportListing')->name('admin.investement.dailyReportListing');
    Route::post('report/listing/export', 'Admin\InvestmentReportController@export')->name('admin.investement_report.export');
    // End
    //Investment Management V2
    Route::get('registerplan', 'Admin\InvestmentplanController@registerPlans')->name('admin.register.plan');
    Route::resource('investment', 'Admin\InvestmentControllerV2');
    Route::post('getForm', 'Admin\InvestmentControllerV2@planForm')->name('admin.investment.planform');
    Route::post('investmentgetmember', 'Admin\InvestmentControllerV2@getmember')->name('admin.investment.member');
    //  Route::post('storeplan', 'Admin\InvestmentControllerV2@Store')->name('admin.investment.store');
    Route::get('investment/recipt/{id}', 'Admin\InvestmentControllerV2@planRecipt')->name('admin.investment.recipt');
    //Investment Management
    //Investment Management
    Route::get('registerplan', 'Admin\InvestmentplanController@registerPlans')->name('admin.register.plan');
    // Route::resource('investment', 'Admin\InvestmentController');
    Route::post('getReinvestDetail', 'Admin\EInvestmentController@getReinvestAccountDetail')->name('admin.getReinvestDetail');
    Route::post('save-reinvest-mbdata', 'Admin\EInvestmentController@saveReinvestMbData')->name('admin.savereinvestmbdata');
    Route::post('get-eli-deposit-amount', 'Admin\EInvestmentController@getEliDepositeAmount')->name('eli.getdepositamount');
    Route::post('export_e_invest_transaction', 'Admin\ExportController@export_einvest_transaction')->name('admin.export_e_invest_transaction');
    Route::post('e_invest/transaction_list', 'Admin\EInvestmentController@transaction_list')->name('admin.transaction.list');
    // Route::post('investmentgetmember', 'Admin\InvestmentplanController@getmember')->name('admin.investment.member');
    Route::post('investmentgetassociate', 'Admin\InvestmentplanController@getAccociateMember')->name('admin.investment.associate');
    Route::post('serachmember', 'Admin\InvestmentplanController@searchmember')->name('admin.investment.searchmember');
    Route::post('investmentkanyadhanamount', 'Admin\InvestmentplanController@kanyadhanAmount')->name('admin.investment.kanyadhanamount');
    // Route::post('getForm', 'Admin\InvestmentplanController@planForm')->name('admin.investment.planform');
    Route::post('getPlanInvestment', 'Admin\InvestmentplanController@getCompanyToPlan')->name('admin.investment.getCompanyToBranch');
    Route::post('getEditInvestmentForm', 'Admin\InvestmentplanController@editPlanForm')->name('admin.investment.editplanform');
    Route::get('getEditInvestmentForm', 'Admin\InvestmentplanController@editPlanForm')->name('admin.investment.editplanform');
    Route::get('investments', 'Admin\InvestmentplanController@investments')->name('admin.investment.plans');
    Route::post('investmentslisting', 'Admin\InvestmentplanController@investmentsListing')->name('admin.investment.listing');
    Route::get('investment/details/{id}', 'Admin\InvestmentplanController@edit')->name('admin.investment.edit');
    Route::get('renewaldetails', 'Admin\RenewaldetailsController@renewaldetails')->name('admin.investment.renewaldetails');
    Route::post('renewaldetailslisting', 'Admin\RenewaldetailsController@renewaldetailsListing')->name('admin.renewaldetails.listing');
    Route::post('renewaldetailslisting\getCompany', 'Admin\RenewaldetailsController@getCompanyIdPlans')->name('admin.investment.getCompanyIdPlans');
    Route::get('savingaccountreport', 'Admin\SavingaccountreportController@savingaccountreport')->name('admin.investment.savingaccountreport');
    Route::post('savingaccountreportlisting', 'Admin\SavingaccountreportController@savingaccountreportListing')->name('admin.savingaccountreport.listing');
    Route::get('usermanagement', 'Admin\AdminController@usermanagementdetails')->name('admin.usermanagement.usermanagementdetails');
    Route::post('usermanagementlisting', 'Admin\AdminController@usermanagementdetailsListing')->name('admin.usermanagementdetails.listing');
    Route::get('usermanagement-register', 'Admin\AdminController@register')->name('admin.usermanagement.register');
    Route::get('usermanagement-register/{id}', 'Admin\AdminController@edit_register')->name('admin.usermanagement.edit_register');
    Route::get('usermanagement-detail/{id}', 'Admin\AdminController@detail')->name('admin.usermanagement.detail');
    Route::post('usermanagement-save', 'Admin\AdminController@save')->name('admin.usermanagement.save');
    Route::post('active_deactive_admin_user', 'Admin\AdminController@active_deactive_admin_user')->name('active_deactive_admin_user');
    Route::get('usermanagement-permission/{id}', 'Admin\AdminController@userPermission')->name('admin.usermanagement.usermanagement-permission');
    Route::post('save_user_permission_data', 'Admin\AdminController@save_user_permission_data')->name('save_user_permission_data');
    Route::post('get_employee_name_to_code', 'Admin\AdminController@get_employee_name_to_code')->name('get_employee_name_to_code');
    // Route::post('storeplan', 'Admin\InvestmentplanController@Store')->name('admin.investment.store');
    Route::post('opensavingaccount', 'Admin\InvestmentplanController@openSavingAccount')->name('admin.investment.opensavingaccount');
    Route::post('export-investments-list', 'Admin\ExportController@exportInvestmentPlan')->name('admin.investment.export');
    Route::get('investment/passbook/transaction/{id}/{code}', 'Admin\PassbookController@passbookTransaction')->name('admin.investment.passbook_transaction');
    Route::get('investment/passbook/cover/{id}', 'Admin\PassbookController@passbookCover')->name('admin.passbook_cover');
    Route::get('investment/passbook/cover_new/{id}', 'Admin\PassbookController@passbookCoverNew')->name('branch.passbook_cover_new');
    Route::get('investment/passbook/maturity/{id}', 'Admin\PassbookController@passbookMaturity')->name('admin.passbook_maturity');
    Route::post('tran_list_old', 'Admin\PassbookController@transactionList')->name('admin.investment.transaction_listing');
    Route::get('tran_list_old', 'Admin\PassbookController@transactionList')->name('admin.investment.transaction_listing');
    Route::post('investment/passbook/tran_start', 'Admin\PassbookController@transactionStart')->name('admin.transaction_start');
    // -------- admin new passbook transaction
    Route::get('investment/passbook/transaction_new/{id}/{code}', 'Admin\PassbookController@passbookTransactionNew')->name('admin.investment.passbook_transaction_new');
    Route::post('tran_list', 'Admin\PassbookController@transactionListNew')->name('admin.investment.transaction_listing_new');
    Route::post('investment/passbook/tran_start_new', 'Admin\PassbookController@transactionStartNew')->name('admin.transaction_start_new');
    Route::get('investment/passbook/transaction/{id}', 'Admin\PassbookController@viewTransaction')->name('admin.view_passbook_transaction');
    Route::get('investment/passbook/ssbtransaction/{id}', 'Admin\PassbookController@viewssbTransaction')->name('admin.view_passbook_transaction');
    Route::post('get_brs_report_closing_balance', 'Admin\Brs\BrsController@get_brs_report_closing_balance')->name('admin.get_brs_report_closing_balance');
    Route::post('brs_reporting_listing', 'Admin\Brs\BrsController@brs_reporting_listing')->name('admin.brs_reporting_listing');
    Route::post('get_brs_report_data', 'Admin\Brs\BrsController@getBRSDATA')->name('admin.get_brs_report_data');
    Route::post('save_brs_report_data', 'Admin\Brs\BrsController@save_brs_report_data')->name('admin.save_brs_report_data');
    Route::post('clear_brs_report_data', 'Admin\Brs\BrsController@clear_brs_report_data')->name('admin.clear_brs_report_data');
    Route::post('print_brs_report_data', 'Admin\Brs\BrsController@print_brs_report_data')->name('admin.print_brs_report_data');
    Route::get('investment/updateprintstatus/{id}/{correctionid}', 'Admin\InvestmentplanController@updatePrintStatus')->name('admin.investment.updateprintstatus');
    Route::get('investment/updatecertificateprintstatus/{id}/{correctionid}', 'Admin\InvestmentplanController@updateCertificatePrintStatus')->name('admin.investment.updatecertificateprintstatus');
    Route::post('updateplan', 'Admin\InvestmentplanController@Update')->name('admin.investment.update');
    //Route::group(['middleware' => ['permission:Investment Receipt']], function () {
    // Route::get('investment/recipt/{id}', 'Admin\InvestmentplanControllerV2@planRecipt')->name('admin.investment.recipt');
    //});
    // Route::get('investment/commission/{id}', 'Admin\InvestmentplanController@investmentCommission')->name('admin.investment.commission');
    // Route::post('investmentcommissionlisting', 'Admin\InvestmentplanController@investmentCommissionListing')->name('admin.investment.commissionlisting');
    // Route::post('investmentcommissionexport', 'Admin\ExportController@exportInvestmentCommission')->name('admin.investmentcommission.export');
    Route::post('renewal_list_export', 'Admin\ExportController@exportRenewalList')->name('admin.renewal_list.report.export');

    Route::get('memberinvestment/corrections', 'Admin\CorrectionController@correctionRequestView')->name('admin.investment.correctionrequest');
    Route::post('correctionrequestlist', 'Admin\CorrectionController@correctionRequestList')->name('admin.correctionrequestlist');
    Route::get('renew/corrections', 'Admin\CorrectionController@correctionRequestView')->name('admin.renew.correctionrequest');
    Route::get('printpassbook/corrections', 'Admin\CorrectionController@correctionRequestView')->name('admin.printpassbook.correctionrequest');
    Route::get('printcertificate/corrections', 'Admin\CorrectionController@correctionRequestView')->name('admin.printcertificate.correctionrequest');

    Route::get('renew/delete/{id}/{correctionid}/{code}', 'Admin\RenewalController@delete')->name('admin.renew.delete');
    Route::post('get-ssb-amount', 'Admin\RenewalController@getSavingBalance')->name('admin.renewal.ssbamount');
    Route::get('investment/renew/receipt/{id}', 'Admin\RenewalController@renewal_receipt')->name('admin.investment.renew.receipt');
    Route::get('investment/renew/ssbtransaction/receipt/{id}', 'Admin\RenewalController@viewssbTransactionreceipt')->name('admin.investment.renew.receipt.ssbtransaction');
    Route::get('update-renewal', 'Admin\RenewalController@updateRenewal')->name('admin.renew.updaterenewal');
    Route::get('update-ssb', 'Admin\RenewalController@updateSsb')->name('admin.renew.updatessb');
    Route::post('update-renewal-transaction', 'Admin\RenewalController@updateRenewalTransaction')->name('admin.renew.updaterenewaltransaction');
    Route::post('update-ssb-transaction', 'Admin\RenewalController@updateSsbTransaction')->name('admin.renew.updatessbtransaction');
    Route::get('update-all-renewal-transaction', 'Admin\RenewalController@updateAllRenewalTransaction')->name('admin.allrenew.updaterenewaltransaction');
    Route::get('update-all-ssb-transaction', 'Admin\RenewalController@updateAllSsbTransaction')->name('admin.allrenew.updatessbtransaction');
    Route::post('correctionexport', 'Admin\ExportController@exportCorrectionRequest')->name('admin.correction.export');
    Route::post('associatecorrectionexport', 'Admin\ExportController@exportassociateCorrectionRequest')->name('admin.associatecorrection.export');
    Route::post('investmentcorrectionexport', 'Admin\ExportController@exportinvestmentCorrectionRequest')->name('admin.investmentcorrection.export');
    Route::post('renewcorrectionexport', 'Admin\ExportController@exportrenewCorrectionRequest')->name('admin.renewcorrectionexportcorrection.export');
    Route::post('printpassbookcorrectionexport', 'Admin\ExportController@exportprintpassbookCorrectionRequest')->name('admin.printpasscorrectionexportcorrection.export');
    Route::post('printcertificatecorrectionexport', 'Admin\ExportController@exportprintcertificateCorrectionRequest')->name('admin.printcertificatecorrectionexportcorrection.export');
    Route::post('rejectcorrectionrequest', 'Admin\CorrectionController@rejectCoreectionRequest')->name('correction.request.reject');
    //Admin Investment Renewal
    Route::get('renewplan', 'Admin\RenewalController@renew')->name('admin.renew');
    Route::get('renewplan/new', 'Admin\NewRenewalController@renew')->name('admin.renew.new');
    Route::post('renew/new/store', 'Admin\NewRenewalController@storeAjax')->name('admin.renew.new.storeajax');
    Route::get('renew/new/redirect/{allContactNumbers?}/{allAccountNumbers?}/{rAmounts}/{encodeRequests}/{encodebranchCode}/{encodebranchName}/{ssb}/{totalAmount}/{amount}/{ren_dates}', 'Admin\NewRenewalController@send_message');
    Route::get('renew/recipt/new/{url}/{branchCode}/{branchName}/{ssb}/{totalAmount}', 'Admin\NewRenewalController@renewalDetails')->name('admin.renew.new.receipt');
    Route::post('investment/getInvestmentDetails', 'Admin\RenewalController@getInvestmentDetails')->name('admin.investment.renewplan');
    Route::post('getCollectorAssociate', 'Admin\RenewalController@getCollectorAssociate')->name('admin.investment.getcollectorassociate');
    Route::post('renew/recipt', 'Admin\RenewalController@store')->name('admin.renew.store');
    /*Code added by amar*/
    Route::post('renew/store', 'Admin\RenewalController@storeAjax')->name('admin.renew.storeajax');
    //End of code
    Route::get('renew/recipt/{url}/{branchCode}/{branchName}/{ssb}', 'Admin\RenewalController@renewalDetails')->name('admin.renew.receipt');
    Route::get('renew/recipt/{url}/{branchCode}/{branchName}', 'Admin\RenewalController@renewalDetails')->name('admin.renew.receipt');
    Route::post('getLoanCollectorAssociate', 'Admin\LoanController@getCollectorAssociate')->name('admin.loan.getcollectorassociate');
    //Setting controller
    Route::get('settings', 'SettingController@Settings')->name('admin.setting');
    Route::post('settings', 'SettingController@SettingsUpdate')->name('admin.settings.update');
    Route::get('email', 'SettingController@Email')->name('admin.email');
    Route::post('email', 'SettingController@EmailUpdate')->name('admin.email.update');
    Route::get('sms', 'SettingController@Sms')->name('admin.sms');
    Route::post('sms', 'SettingController@SmsUpdate')->name('admin.sms.update');
    Route::get('account', 'Admin\SettingController@Account')->name('admin.account');
    Route::post('account', 'Admin\SettingController@AccountUpdate')->name('admin.account.update');
    Route::post('viewotp', 'Admin\SettingController@viewotp')->name('admin.account.viewotp');
    Route::post('otp_account_varified', 'Admin\SettingController@AccountUpdate')->name('otp_account_varified');
    //Transfer controller
    Route::get('own-bank', 'TransferController@Ownbank')->name('admin.ownbank');
    Route::get('own-bank/delete/{id}', 'TransferController@Destroyownbank')->name('ownbank.delete');
    Route::get('other-bank', 'TransferController@Otherbank')->name('admin.otherbank');
    Route::get('other-bank/delete/{id}', 'TransferController@Destroyotherbank')->name('otherbank.delete');
    Route::get('app-otherbank/{id}', 'TransferController@Approve')->name('otherbank.approve');
    //User controller
    Route::get('users', 'AdminController@Users')->name('admin.users');
    Route::get('messages', 'AdminController@Messages')->name('admin.message');
    Route::get('unblock-user/{id}', 'AdminController@Unblockuser')->name('user.unblock');
    Route::get('block-user/{id}', 'AdminController@Blockuser')->name('user.block');
    Route::get('manage-user/{id}', 'AdminController@Manageuser')->name('user.manage');
    Route::get('user/delete/{id}', 'AdminController@Destroyuser')->name('user.delete');
    Route::get('email/{id}/{name}', 'AdminController@Email')->name('user.email');
    Route::post('email_send', 'AdminController@Sendemail')->name('user.email.send');
    Route::get('promo', 'AdminController@Promo')->name('user.promo');
    Route::post('promo', 'AdminController@Sendpromo')->name('user.promo.send');
    Route::get('message/delete/{id}', 'AdminController@Destroymessage')->name('message.delete');
    Route::get('ticket', 'AdminController@Ticket')->name('admin.ticket');
    Route::get('ticket/delete/{id}', 'AdminController@Destroyticket')->name('ticket.delete');
    Route::get('close-ticket/{id}', 'AdminController@Closeticket')->name('ticket.close');
    Route::get('manage-ticket/{id}', 'AdminController@Manageticket')->name('ticket.manage');
    Route::post('reply-ticket', 'AdminController@Replyticket')->name('ticket.reply');
    Route::post('profile-update', 'AdminController@Profileupdate');
    Route::post('credit-account', 'AdminController@Credit');
    Route::post('debit-account', 'AdminController@Debit');
    Route::get('approve-kyc/{id}', 'AdminController@Approvekyc')->name('admin.approve.kyc');
    Route::get('reject-kyc/{id}', 'AdminController@Rejectkyc')->name('admin.reject.kyc');
    //Asset controller
    Route::get('asset-buy', 'AssetController@Buy')->name('admin.asset.buy');
    Route::get('asset-sell', 'AssetController@Sell')->name('admin.asset.sell');
    Route::get('asset-exchange', 'AssetController@Exchange')->name('admin.asset.exchange');
    Route::get('asset-plans', 'AssetController@Plans')->name('admin.asset.plans');
    Route::get('asset/delete/{id}', 'AssetController@Destroy')->name('asset.delete');
    Route::get('asset-plan/delete/{id}', 'AssetController@PlanDestroy')->name('asset.plan.delete');
    Route::get('asset-plan-create', 'AssetController@Create')->name('admin.asset.create');
    Route::post('asset-plan-create', 'AssetController@Store')->name('admin.asset.store');
    Route::get('asset-plan/{id}', 'AssetController@Edit')->name('admin.asset.edit');
    Route::post('asset-plan-edit', 'AssetController@Update')->name('admin.asset.update');
    //Merchant controller
    Route::get('approved-merchant', 'MerchantController@Approvedmerchant')->name('approved.merchant');
    Route::get('pending-merchant', 'MerchantController@Pendingmerchant')->name('pending.merchant');
    Route::get('declined-merchant', 'MerchantController@Declinedmerchant')->name('declined.merchant');
    Route::get('merchant-log', 'MerchantController@merchantlog')->name('merchant.log');
    Route::get('transfer-log', 'MerchantController@transferlog')->name('transfer.log');
    Route::get('merchant/delete/{id}', 'MerchantController@Destroymerchant')->name('merchant.delete');
    Route::get('log/delete/{id}', 'MerchantController@Destroylog')->name('log.delete');
    Route::get('approvemerchant/{id}', 'MerchantController@approve')->name('merchant.approve');
    Route::get('declinemerchant/{id}', 'MerchantController@decline')->name('merchant.decline');
    /*******************  Member Management Start   ***********************/
    Route::get('member', 'Admin\MemberController@index')->name('admin.member');
    Route::get('customer', 'Admin\MemberController@customerindex')->name('admin.customer_list');
    Route::post('customers_listing', 'Admin\MemberController@customerListing')->name('admin.customer_listing');
    Route::post('member_list', 'Admin\MemberController@membersListing')->name('admin.member_listing');
    Route::get('member-register', 'Admin\MemberController@register')->name('admin.member.register');
    // Route::get('blacklist-members-on-loan', 'Admin\MemberBlacklistController@index')->name('admin.blacklist-members-on-loan');
    Route::post('member_blacklist_on_loan_listing', 'Admin\MemberBlacklistController@member_blacklist_on_loan_listing')->name('admin.member_blacklist_on_loan_listing');
    Route::get('add-blacklist-member-on-loan', 'Admin\MemberBlacklistController@add_blacklist')->name('admin.add-blacklist-member-on-loan');
    Route::post('member_blacklist_member_data', 'Admin\MemberBlacklistController@getBlacklistMemberData')->name('admin.member_blacklist_member_data');
    Route::post('exportblacklist_memberlist', 'Admin\ExportController@exportMemberBlacklistOnLoan')->name('admin.member.blacklist_on_loan_export');
    Route::post('action_blacklist_member_for_loan', 'Admin\MemberBlacklistController@action_blacklist_member_for_loan')->name('admin.action_blacklist_member_for_loan');
    Route::get('print-blacklist-member-on-loan', 'Admin\MemberBlacklistController@print_blacklist_member_on_loan')->name('admin.print-blacklist-member-on-loan');
    Route::get('member-detail/{id}', 'Admin\MemberController@detail')->name('admin.member.detail');
    Route::get('member-receipt/{id}', 'Admin\MemberController@receipt')->name('admin.member.receipt');
    Route::get('form_g/{id}', 'Admin\FormGController@index')->name('admin.form_g');
    Route::post('form_g', 'Admin\FormGController@getData')->name('admin.form_g.getData');
    Route::post('form_g/create', 'Admin\FormGController@save')->name('admin.update_15g.save');
    Route::post('export_update_15g', 'Admin\ExportController@export_update_15g')->name('admin.update_15g.report.export');
    Route::post('delete_update_15g_record', 'Admin\FormGController@delete')->name('admin.update_15g.record.delete');
    Route::post('member-save', 'Admin\MemberController@save')->name('admin.member.save');
    Route::get('tds-payable', 'Admin\TdspayableController@index')->name('admin.tds-payable');
    Route::post('tds_payable_listing', 'Admin\TdspayableController@tds_payable_listing')->name('admin.tds_payable_listing');
    Route::post('export_tds_payable', 'Admin\TdspayableController@export_tds_payable')->name('admin.tds_payable.export_tds_payable');
    Route::get('add-tds-payable', 'Admin\TdspayableController@add_tds_payable')->name('admin.add-tds-payable');
    Route::post('get-tds-payable-amount', 'Admin\TdspayableController@getTdsPayableAmount')->name('admin.tdspayableamount');
    Route::post('print-tds-payable', 'Admin\TdspayableController@print_tds_payable')->name('admin.print-tds-payable');
    Route::post('pay-tds-payable-amount', 'Admin\TdspayableController@payTdsPayableAmount')->name('admin.paytdspayableamount');
    Route::get('bill-expense', 'Admin\BillExpenseController@index')->name('admin.bill-expense');
    Route::post('get_item_details', 'Admin\BillExpenseController@get_item_details')->name('admin.get_item_details');
    Route::post('get_items', 'Admin\BillExpenseController@get_items')->name('admin.get_items');
    Route::get('bill-payment', 'Admin\BillPaymentController@index')->name('admin.bill-payment');
    Route::post('export_bill_payment', 'Admin\ExportController@export_bill_payment')->name('admin.bill_payment.export_bill_payment');

    Route::get('head-grouping', 'Admin\HeadGroupingController@index')->name('admin.head-grouping');
    Route::post('get_change_sub_head', 'Admin\HeadGroupingController@get_change_sub_head')->name('admin.get_change_sub_head');
    //Route::post('get_sub_head_using_head', 'Admin\HeadGroupingController@get_sub_head_using_head')->name('admin.get_sub_head_using_head');
    Route::post('change_account_head_position', 'Admin\HeadGroupingController@change_account_head_position')->name('admin.change_account_head_position');
    Route::post('get_sub_head_using_head', 'Admin\HeadGroupingController@get_sub_head_using_head')->name('admin.get_sub_head_using_head');
    Route::post('get_bank_balance', 'Admin\CommanController@getBankBalance')->name('admin.get_bank_balance');
    Route::post('get_comapny_details', 'Admin\HeadGroupingController@get_comapny_details')->name('admin.get_comapny_details');

    // head logs
    Route::get('head-logs', 'Admin\AccountHeadReport\HeadController@headlogs')->name('admin.head_logs');
    Route::post('head-logs/listing', 'Admin\AccountHeadReport\HeadController@headlogListing')->name('admin.head_logs.listing');
    Route::get('head/grouping/logs/{id}', 'Admin\AccountHeadReport\HeadController@headGroupingLogsDetail')->name('admin.groupHeadLogs.listing');
    Route::post('/export-to-excel', 'Admin\ExportController@export')->name('admin.logs.listing');

    // Credit Card Start
    Route::get('credit-card', 'Admin\CreditCardController@index')->name('admin.credit-card');
    Route::get('credit-card/create', 'Admin\CreditCardController@create')->name('admin.credit-card.create');
    Route::post('credit-card/credit_card_save', 'Admin\CreditCardController@credit_card_save')->name('admin.credit-card.credit_card_save');
    Route::post('credit-card/credit_card_listing', 'Admin\CreditCardController@credit_card_listing')->name('admin.credit-card.credit_card_listing');
    Route::get('credit-card/edit/{id}', 'Admin\CreditCardController@edit')->name('admin.credit-card.edit');
    Route::post('credit-card/delete-credit-card', 'Admin\CreditCardController@delete_credit_card')->name('admin.credit-card.delete-credit-card');
    Route::get('credit-card/view_transaction/{id}', 'Admin\CreditCardController@view_transaction')->name('admin.credit-card.view_transaction');
    Route::post('credit-card/credit_card_transaction_listing', 'Admin\CreditCardController@credit_card_transaction_listing')->name('admin.credit-card.credit_card_transaction_listing');
    Route::post('balanceSheetReportCreditCard', 'Admin\BalanceSheetController@credit_card_list')->name('admin.balance-sheet.credit_card_listing');
    Route::post('balanceSheetCreditCardExport', 'Admin\ExportController@balanceSheetCreditCardExport')->name('admin.balance_sheet.credit_card.export');
    Route::any('balance-sheet/head', 'Admin\BalanceSheetController@currentDetail')->name('balance-sheet.head');
    Route::get('balance-sheet/{key}', 'Admin\BalanceSheetController@datatable')->name('balance-sheet.page');
    // CreditCard End
    // Debit Card Start
    Route::get('debit-card', 'Admin\DebitCardController@index')
        ->name('admin.debit-card');
    Route::post('debit-card/ssb_detail_show', 'Admin\DebitCardController@ssb_detail_show')
        ->name('admin.debit-card.ssb_detail_show');
    Route::get('debit-card/create', 'Admin\DebitCardController@create')
        ->name('admin.debit-card.create');
    Route::post('debit-card/debit_card_save', 'Admin\DebitCardController@debit_card_save')
        ->name('admin.debit-card.debit_card_save');
    Route::post('debit-card/debit_card_listing', 'Admin\DebitCardController@debit_card_listing')
        ->name('admin.debit-card.debit_card_listing');
    Route::post('debit-card/delete-debit-card', 'Admin\DebitCardController@delete_debit_card')
        ->name('admin.debit-card.delete-debit-card');
    Route::post('debit-card/approve_reject-debit-card', 'Admin\DebitCardController@approve_reject_debit_card')
        ->name('admin.debit-card.approve_reject-debit-card');
    Route::get('debit-card/edit/{id}', 'Admin\DebitCardController@edit')
        ->name('admin.debit-card.edit');
    Route::post('debit-card/debit_card_update', 'Admin\DebitCardController@debit_card_update')
        ->name('admin.debit-card.debit_card_update');
    Route::post('debit-card/emp_detail_show', 'Admin\DebitCardController@emp_detail_show')
        ->name('admin.debit-card.emp_detail_show');
    Route::get('debit-card/card-history', 'Admin\DebitCardController@card_history')
        ->name('admin.debit-card.card_history');
    Route::post('debit-card/card_tr_history', 'Admin\DebitCardController@card_tr_history')
        ->name('admin.debit-card.card_tr_history');
    Route::get('debit-card/card-history/{id}', 'Admin\DebitCardController@card_history1')
        ->name('admin.debit-card.card_history1');
    Route::get('debit-card/ssb-history/{id}', 'Admin\DebitCardController@card_ssb_history')
        ->name('admin.card_ssb_history');
    // Debit Card End
    /*********LOGS Activity Start*******/
    Route::get('activityLogs', 'Admin\ActivityLogsController@index')
        ->name('admin.activityLogs');
    /*********LOGS Activity End*******/
    Route::get('add-banking', 'Admin\BankingController@index')->name('admin.add-banking');
    Route::post('get-banks-data', 'Admin\BankingController@get_banks_data')->name('admin.get-banks-data');
    Route::post('get-cheque-data', 'Admin\BankingController@get_cheque_data')->name('admin.get-cheque-data');
    Route::get('view-ledger-listing', 'Admin\LedgerController@index')->name('admin.view-ledger-listing');
    Route::post('ledger-listing', 'Admin\LedgerController@ledgerListing')->name('admin.ledger-listing');
    Route::post('getHeadLedgerData', 'Admin\LedgerController@getHeadLedgerData')->name('admin.getHeadLedgerData');
    Route::post('getSSBAccountNumber', 'Admin\DemandAdvice\DemandAdviceController@getSSBAccountNumber')->name('admin.getSSBAccountNumber');
    Route::post('getAssociateMember', 'Admin\MemberController@getAssociateMember')->name('admin.associatemember');
    Route::get('member-payment', 'Admin\MemberController@memberChequePaymentView')->name('admin.member.investmentchequepayment');
    Route::post('member-payment-listing', 'Admin\MemberController@memberChequePaymentListing')->name('admin.member.investmentchequepaymentlisting');
    Route::get('member-payment/cheque/{id}', 'Admin\MemberController@memberChequeStatus')->name('admin.member.changestatus');
    Route::post('member-payment-export', 'Admin\ExportController@exportChequeStatusListing')->name('admin.member.exportinvestmentchequelistin');
    Route::post('memberEmailCheck', 'Admin\MemberController@memberEmailCheck')->name('admin.emailcheck');
    Route::get('member-edit/{id}', 'Admin\MemberController@edit')->name('admin.member.edit');
    Route::post('member-update', 'Admin\MemberController@update')->name('admin.member.update');
    Route::post('member-image', 'Admin\MemberController@imageUpload')->name('admin.member.image');
    Route::get('member-loan/{id}', 'Admin\MemberController@loan')->name('admin.member.loan');
    Route::post('member_loan', 'Admin\MemberController@membersLoanListing')->name('admin.member_loan_listing');
    Route::post('member_grouploan', 'Admin\MemberController@membersGroupLoanListing')->name('admin.member_grouploan_listing');
    Route::get('member-investment/{id}', 'Admin\MemberController@investment')->name('admin.member.investment');
    Route::post('member-investment', 'Admin\MemberController@membersInvestment')->name('admin.member_investment');
    Route::post('export/member-investment', 'Admin\ExportController@exportmemberInvestment')->name('admin.member_investment.report.export');
    Route::post('export/member-loan', 'Admin\ExportController@exportmemberLoan')->name('admin.member_loan.report.export');
    Route::post('export/member-group-loan', 'Admin\ExportController@exportmemberGroupLoan')->name('admin.member_group_loan.report.export');
    Route::post('export/member_transaction', 'Admin\ExportController@exportmemberTransaction')->name('admin.member_transaction.report.export');
    Route::get('print-member-investment/{id}', 'Admin\MemberController@print_member_investment')->name('admin.print_member_investment');
    Route::get('print-member-loan/{id}', 'Admin\MemberController@print_member_loan')->name('admin.print_member_loan');
    Route::get('print-member-groupLoan/{id}', 'Admin\MemberController@print_member_groupLoan')->name('admin.print_member_groupLoan');
    Route::get('print-member-transaction/{id}', 'Admin\MemberController@print_member_transaction')->name('admin.print_member_transaction');
    Route::get('member-account/{id}', 'Admin\MemberController@account')->name('admin.member.account');
    Route::post('member_account', 'Admin\MemberController@membersAccount')->name('admin.member_account');
    Route::get('member-account-statement/{id}/{member}', 'Admin\MemberController@statement')->name('admin.accountstatement');
    Route::post('member-account-statement/{id}/{member}', 'Admin\MemberController@statement_filter')->name('admin.statementfilter');
    Route::get('member-transactions/{id}', 'Admin\MemberController@transactions')->name('admin.transactionsDetail');
    Route::post('transactions_list', 'Admin\MemberController@transactionsListing')->name('admin.transactions_lists');
    Route::post('exportmemberlist', 'Admin\ExportController@exportMember')->name('admin.member.export');
    Route::post('exportcustomerlist', 'Admin\ExportController@exportCustomer')->name('admin.customer.export');
    Route::get('member/corrections', 'Admin\CorrectionController@correctionRequestView')->name('admin.member.correctionrequest');
    /*******************  Member Management END   ***********************/
    /*******************  Associate Management Start   ***********************/
    Route::get('associate', 'Admin\AssociateController@index')->name('admin.associate');
    Route::post('associate_list', 'Admin\AssociateController@associateListing')->name('admin.associate_listing');
    Route::get('associate-register', 'Admin\AssociateController@associateRegister')->name('admin.associate.register');
    Route::get('associate-detail/{id}', 'Admin\AssociateController@detail')->name('admin.associate.detail');
    Route::get('associate-receipt/{id}', 'Admin\AssociateController@receipt')->name('admin.associate.receipt');
    Route::post('associate-save', 'Admin\AssociateController@save')->name('admin.associate.save');
    Route::get('associate-edit/{id}', 'Admin\AssociateController@edit')->name('admin.associate-edit');
    Route::get('associatecommission', 'Admin\AssociateController@associateCommission')->name('admin.associate.commission');
    Route::post('associatecommissionlist', 'Admin\AssociateController@associateCommissionList')->name('admin.associate.commissionlist');
    Route::post('exportassociatecommissionlist', 'Admin\ExportController@exportAssociateCommission')->name('admin.associate.exportcommission');
    Route::get('associate/corrections', 'Admin\CorrectionController@correctionRequestView')->name('admin.associate.correctionrequest');
    /**************** Kota Business Report *************************/
    Route::get('associatebusinessreport', 'Admin\AssociateController@kotaBusinessReport')->name('admin.associate.businessreport');
    Route::post('kotabusinessreport', 'Admin\AssociateController@kotaBusinessReportListing')->name('admin.associate.kotabusinesslist');
    Route::post('exportkotabusinessreport', 'Admin\ExportController@exportKotaBusinessReport')->name('admin.associate.exportkotabusiness');
    Route::post('getassociatecarder', 'Admin\AssociateController@getAssociateCarder')->name('admin.associate.getAssociateCarder');
    /**************** Kota Business Report *************************/
    Route::post('getMember', 'Admin\AssociateController@getMemberData')->name('admin.member_dataGet');
    Route::post('admin_get_senior', 'Admin\AssociateController@getSeniorDetail')->name('admin.seniorDetails');
    Route::post('admin_getCarderAssociate', 'Admin\AssociateController@getCarderAssociate')->name('admin.getAssociateCarder');
    Route::post('admin_associateSsbAccountGet', 'Admin\AssociateController@associateSsbAccountGet')->name('admin.associateSsbAccountGets');
    Route::post('admin_associateRdAccounts', 'Admin\AssociateController@associateRdbAccounts')->name('admin.associateRdAccount');
    Route::post('admin_associateRdAccountGet', 'Admin\AssociateController@associateRdbAccountGet')->name('admin.associateRdAccountGets');
    Route::post('admin_associate_ssb_check', 'Admin\AssociateController@checkSsbAcount')->name('admin.associate_ssb_accountcheck');
    Route::post('admin_ssb_check_balance', 'Admin\AssociateController@checkSsbAcountBalance')->name('admin.checkssb_blance');
    Route::post('associate-update', 'Admin\AssociateController@update')->name('admin.associate.update');
    Route::post('associate-dep-delete', 'Admin\AssociateController@deleteDependent')->name('admin.associate.dependent.delete');
    Route::get('associate-idcard/{id}', 'Admin\AssociateController@idCard')->name('admin.associate.idcard');
    Route::post('exportassociatelist', 'Admin\ExportController@exportAssociate')->name('admin.associate.export');
    Route::match (['get', 'post'], 'associate-tree', 'Admin\AssociateController@tree_hierarchy')->name('admin.associate.tree');
    Route::get('associate-upgrade', 'Admin\AssociateController@upgrade')->name('admin.associate.upgrade');
    Route::get('associate-status', 'Admin\AssociateController@active_deactivate')->name('admin.associate.status');
    Route::get('associate-downgrade', 'Admin\AssociateController@downgrade')->name('admin.associate.downgrade');
    Route::post('associate-upgradesave', 'Admin\AssociateController@upgrade_save')->name('admin.associate.upgrade_save');
    Route::post('getAssociateDetail', 'Admin\AssociateController@getAssociateData')->name('admin.associter_dataGet');
    Route::post('associate-statussave', 'Admin\AssociateController@status_save')->name('admin.associate.status_save');
    Route::post('getAssociateDetailAll', 'Admin\AssociateController@getAssociateDataAll')->name('admin.associate_dataGetAll');
    Route::post('admin_getCarderForUpgrade', 'Admin\AssociateController@getCarderUpgrade')->name('admin.getCarderForUpgrade');
    Route::post('associate-downgrade', 'Admin\AssociateController@downgrade_save')->name('admin.associate.downgrade_save');
    Route::get('associate-commission-detail/{id}', 'Admin\AssociateController@associateCommissionDetail')->name('admin.associate.commission.detail');
    Route::post('associatecommissionDetaillist', 'Admin\AssociateController@associateCommissionDetailList')->name('admin.associate.commissionDetaillist');
    Route::post('exportassociatecommissionDetaillist', 'Admin\ExportController@exportAssociateCommissionDetail')->name('admin.associate.exportcommissionDetail');
    Route::get('associate-tree-view/{id}', 'Admin\AssociateController@tree_view')->name('admin.associate.treeview');
    Route::post('exportAssociateTree', 'Admin\ExportController@exportAssociateTree')->name('admin.associate.exportAssociateTree');
    Route::match (['get', 'post'], 'associate-commission-transfer', 'Admin\AssociateController@commissionTransfer')->name('admin.associate.commissionTransfer');
    Route::post('associate-commission-transfer-Save', 'Admin\AssociateController@commissionTransferSave')->name('admin.associate.commissionTransferSave');
    Route::post('laserCheck', 'Admin\AssociateController@laserCheck')->name('admin.laserCheck');
    Route::get('associate-commission-transfer-list', 'Admin\AssociateCommissionController@commissionTransferList')->name('admin.associate.commissionTransferList');
    Route::post('leaserList', 'Admin\AssociateCommissionController@leaserList')->name('admin.associate.leaserList');
    Route::post('leaserExport', 'Admin\ExportController@leaserExport')->name('admin.associate.leaserExport');
    // don't use below first route route witout permission
    Route::get('createAssociatSSB/{associate_no}/{associate_join_date}', 'Admin\AssociateController@createAssociatSSB');
    /* Associate Collection Report Start*/
    Route::get('associate-collection-report', 'Admin\AssociateController@AssociateCollectionReport')->name('admin.associate.associatecollectionreport');
    Route::post('associate-collection-report-list', 'Admin\AssociateController@AssociateCollectionReportList')->name('admin.associate.associatecollectionreportlist');
    Route::post('associate-collection-report-export', 'Admin\ExportController@AssociateCollectionReportExport')->name('admin.associate.associatecollectionreportexport');
    /* Associate Collection Report End*/
    Route::get('associate-commission-transfer-detail/{id}', 'Admin\AssociateCommissionController@commissionTransferDetail')->name('admin.associate.commissionTransferDetail');
    Route::post('leaserDetailList', 'Admin\AssociateCommissionController@leaserDetailList')->name('admin.associate.leaserDetailList');
    Route::post('leaserDetailExport', 'Admin\ExportController@leaserDetailExport')->name('admin.associate.leaserDetailExport');
    Route::post('laserdelete', 'Admin\AssociateController@laserDelete')->name('admin.associate.laserdelete');
    /*******************  Associate Management END   ***********************/
    Route::post('checkIdProof', 'Admin\MemberController@getMemberFromIdProof')->name('admin.checkIdProof');
    Route::match (['get', 'post'], 'member/interest-tds/{id}', 'Admin\MemberController@memberInterestTds')->name('admin.member.interesttds');
    Route::post('member/interest-tds-listing', 'Admin\MemberController@memberInterestTdsListing')->name('admin.member.interesttdslisting');
    Route::post('exportmemberinvestmenttdslist', 'Admin\ExportController@exportMemberInvestmentTds')->name('admin.member.exportinteresttdslisting');
    Route::get('member/tds-certificate/{id}', 'Admin\MemberController@memberTdsCertificate')->name('admin.member.tdscertificate');
    Route::post('print-member-tds', 'Admin\MemberController@printInvestmentTds')->name('admin.member.printinvestmenttds');
    // Script Controller
    Route::get('samraadh_balance', 'Admin\ScriptController@index')->name('admin.script');
    Route::get('update/maturity_date', 'Admin\ScriptController@update_maturity_date')->name('admin.update_maturity_date');
    Route::get('deposite_query', 'Admin\ScriptController@deposite_query')->name('admin.deposite_query');
    /********************* Reinvest Start ************************/
    Route::get('reinvest', 'Admin\Reinvest\ReinvestController@index')->name('admin.reinvest');
    Route::post('reinvest-listing', 'Admin\Reinvest\ReinvestController@reInvestListing')->name('reinvest.listing');
    Route::get('approve-reinvestment/{id}/{anumber}', 'Admin\Reinvest\ReinvestController@approveReinvestment')->name('reinvest.approve');
    Route::get('edit-reinvestment/{id}/{anumber}', 'Admin\Reinvest\ReinvestController@editReinvestment')->name('reinvest.edit');
    Route::post('getEditForm', 'Admin\Reinvest\ReinvestController@editPlanForm')->name('admin.reinvestment.editplanform');
    Route::post('updatereinvestment', 'Admin\Reinvest\ReinvestController@update')->name('admin.reinvestment.update');
    /********************** Reinvest End **************************/
    /********************** Holidays *******************************/
    Route::post('getEvent', 'Admin\EventController@getEvent')->name('admin.getevent');
    Route::get('viewEvent', 'Admin\EventController@index')->name('admin.viewevent');
    Route::post('addEvent', 'Admin\EventController@add')->name('admin.addevent');
    Route::post('removeEvent', 'Admin\EventController@remove')->name('admin.removeevent');
    Route::post('saveHolidaySetting', 'Admin\EventController@saveHolidaySetting')->name('admin.saveholidaysetting');
    Route::post('getAllEvent', 'Admin\EventController@getAllEvent')->name('admin.getallevent');
    Route::post('exportHoliday', 'Admin\ExportController@exportHolidays')->name('admin.holidays.export');
    Route::post('getStateMonths', 'Admin\EventController@getStateMonths')->name('admin.getstatemonths');
    Route::post('getGlobalDate', 'Admin\EventController@getGlobalDate')->name('admin.getglobaldate');
    /********************** Holidays *******************************/
    Route::get('associatecollection', 'Admin\AssociateController@associateCommissionCollection')->name('admin.associate.collection');
    Route::post('associatecollectionlist', 'Admin\AssociateController@associateCollectionList')->name('admin.associate.collectionlist');
    Route::post('exportassociatecommissionCollectionlist', 'Admin\ExportController@exportAssociateCommissionCollection')->name('admin.associate.exportcommissionCollection');
    Route::get('accounthead', 'Admin\CategoryController@accountHead')->name('admin.accountHead');
    Route::get('accounthead/{id}', 'Admin\CategoryController@accountHeadCategory')->name('admin.accountHead.category');
    Route::post('account-head-listing', 'Admin\CategoryController@accountHeadListing')->name('admin.accounthead.list');
    Route::get('addaccounthead', 'Admin\CategoryController@addCategory')->name('admin.addaccounthead');
    Route::post('getaccountnumber', 'Admin\CategoryController@getAccountNumber')->name('admin.accounthead.getaccountnumber');
    Route::post('geteditaccountnumber', 'Admin\CategoryController@getEditAccountNumber')->name('admin.accounthead.geteditaccountnumber');
    Route::post('save-account-head', 'Admin\CategoryController@saveAccountHead')->name('admin.saveaccounthead');
    Route::get('editaccounthead/{id}', 'Admin\CategoryController@editAccountHead')->name('admin.accounthead.edit');
    Route::post('updateaccounthead', 'Admin\CategoryController@updateAccountHead')->name('admin.accounthead.update');
    Route::get('deleteaccounthead/{id}', 'Admin\CategoryController@deleteAccountHead')->name('admin.accounthead.delete');
    Route::get('subaccounthead', 'Admin\CategoryController@subAccountHead')->name('admin.subaccountHead');
    Route::post('sub-account-head-listig', 'Admin\CategoryController@subAccountHeadListing')->name('admin.subaccounthead.list');
    Route::get('addsubaccounthead', 'Admin\CategoryController@addSubCategory')->name('admin.addsubaccounthead');
    Route::post('getsabaccountnumber', 'Admin\CategoryController@getSubAccountNumber')->name('admin.subaccounthead.getaccountnumber');
    Route::post('getsabeditaccountnumber', 'Admin\CategoryController@getSubEditAccountNumber')->name('admin.subaccounthead.geteditaccountnumber');
    Route::post('getaccounthead', 'Admin\CategoryController@getAccountHead')->name('admin.getaccounthead');
    Route::post('save-sub-account-head', 'Admin\CategoryController@saveSubAccountHead')->name('admin.savesubaccounthead');
    Route::get('editsubaccounthead/{id}', 'Admin\CategoryController@editSubAccountHead')->name('admin.subaccounthead.edit');
    Route::post('updatesubaccounthead', 'Admin\CategoryController@updateSubAccountHead')->name('admin.subaccounthead.update');
    Route::get('deletesubaccounthead/{id}', 'Admin\CategoryController@deleteSubAccountHead')->name('admin.subaccounthead.delete');
    Route::get('users', 'Admin\UserController@index')->name('admin.users');
    Route::post('users-listing', 'Admin\UserController@userListing')->name('admin.users.list');
    Route::get('user/adduser', 'Admin\UserController@addUser')->name('admin.adduser');
    Route::post('user/getemployeeocde', 'Admin\UserController@getEmployeeCode')->name('admin.getemployeecode');
    Route::post('save-user', 'Admin\UserController@saveUser')->name('admin.saveuser');
    Route::get('user/edituser/{id}', 'Admin\UserController@editUser')->name('admin.user.edit');
    Route::get('user/view/{id}', 'Admin\UserController@viewUser')->name('admin.user.view');
    Route::post('updateuser', 'Admin\UserController@updateUser')->name('admin.user.update');
    Route::get('user/updatestatus/{id}', 'Admin\UserController@updateStatus')->name('admin.user.updatestatus');
    Route::get('user/deleteuser/{id}', 'Admin\UserController@deleteUSer')->name('admin.user.deleteuser');
    /************* Rent Management  ******************/
    Route::get('rentliabilities', 'Admin\RentManagement\RentController@index')->name('admin.rent.liabilities');
    Route::get('rent/addliability', 'Admin\RentManagement\RentController@addLiability')->name('admin.rent.addliability');
    Route::post('rent/save-liability', 'Admin\RentManagement\RentController@saveLiability')->name('admin.rent.saveliability');
    Route::post('rent-liabilities-listing', 'Admin\RentManagement\RentController@rentLiabilitiesListing')->name('admin.rentliabilities.list');
    Route::post('rent-liability-type', 'Admin\RentManagement\RentController@rentLiabilitiyType')->name('admin.rentliabilities.type');
    Route::get('rent/edit-rent-liability/{id}', 'Admin\RentManagement\RentController@editLiability')->name('admin.rent.edit');
    Route::post('update-liability', 'Admin\RentManagement\RentController@updateLiability')->name('admin.rent.update');
    Route::get('rent/updatestatus/{id}', 'Admin\RentManagement\RentController@updateStatus')->name('admin.rent.updatestatus');
    Route::post('exportrentliabilities', 'Admin\ExportController@exportRentLiabilities')->name('admin.rent.export');
    Route::get('exportrentliabilities', 'Admin\ExportController@exportRentLiabilities')->name('admin.rent.export');
    Route::get('rent/rent-payable', 'Admin\RentManagement\RentController@rentPayableView')->name('admin.rent.payable');
    Route::post('rent-payable-listing', 'Admin\RentManagement\RentController@rentPayableListing')->name('admin.rentpayable.list');
    Route::post('rent/transfer-rent-payable', 'Admin\RentManagement\RentController@transferRentPayableView')->name('admin.rentpayable.transfer');
    Route::post('transfer-rent-payable-amount', 'Admin\RentManagement\RentController@transferRentPayableAmount')->name('admin.rentpayable.transferamount');
    Route::get('rent/rent-report', 'Admin\RentManagement\RentController@rentReportView')->name('admin.rent.report');
    Route::post('rent-report-listing', 'Admin\RentManagement\RentController@rentReportListing')->name('admin.rentreport.list');
    Route::post('rent-report-export', 'Admin\ExportController@exportRentReport')->name('admin.rentreport.export');
    Route::post('rent-report-ids', 'Admin\RentManagement\RentController@rentReportIds')->name('admin.rentreport.ids');
    Route::post('rent_ssb_check', 'Admin\RentManagement\RentController@rentSsbCheck')->name('admin.rent_ssb_check');
    Route::post('rent_employee_check', 'Admin\RentManagement\RentController@rentEmployeeCheck')->name('admin.rent_employee_check');
    Route::get('rent/rent-ledger', 'Admin\RentManagement\RentController@ledger_list')->name('admin.rent.rent-ledger');
    Route::post('rent-ledger-listing', 'Admin\RentManagement\RentController@rentLedgerListing')->name('admin.ledger.list');
    Route::post('rent-ledger-export', 'Admin\ExportController@exportLedger')->name('admin.ledger.export');
    Route::post('rent-payment-ledger-export', 'Admin\ExportController@exportRentPaymentLedger')->name('admin.payment.ledger.export');
    Route::post('rent/ledger-delete', 'Admin\RentManagement\RentController@ledgerDelete')->name('admin.rent.leger-delete');
    Route::get('rent/ledger-report/{id}', 'Admin\RentManagement\RentController@ledgerReport')->name('admin.rent.leger-report');
    Route::get('rent/payment/ledger-report', 'Admin\RentManagement\RentController@rentpaymentledgerReport')->name('admin.rent.payment-ledger-report');
    Route::post('rent/ledger-report', 'Admin\RentManagement\RentController@rentLedgerReportListing')->name('admin.rent.leger-report-list');
    Route::post('rent/payment-ledger-report', 'Admin\RentManagement\RentController@rentPaymentLedgerReportListing')->name('admin.rent.payment_leger-report-list');
    Route::post('rent-ledger-report-export', 'Admin\ExportController@exportLedgerReport')->name('admin.ledger_report.export');
    Route::get('rent/ledger-payable/{id}', 'Admin\RentManagement\RentController@ledger_payable')->name('admin.rent.leger-payable');
    Route::post('rent_transfer_next', 'Admin\RentManagement\RentController@rentTransferNext')->name('admin.rent.rent_transfer_next');
    Route::post('rent_transfer_save', 'Admin\RentManagement\RentController@rentTransferSave')->name('admin.rent.rent_transfer_save');
    Route::get('rent/transfer-advance/{id}/{l}', 'Admin\RentManagement\RentController@advancePayble')->name('admin.rent.rent_transfer_advance');
    Route::post('rent_transfer_advance_save', 'Admin\RentManagement\RentController@rentTransferAdvanceSave')->name('admin.rent.rent_transfer_advance_save');
    Route::match (['get', 'post'], 'rent/ledger-create', 'Admin\RentManagement\RentController@ledgerCreate')->name('admin.rent.ledger-create');
    Route::post('rent/ledger-save', 'Admin\RentManagement\RentController@ledgerSave')->name('admin.rent.ledger-save');
    Route::get('rent/ledger-liability/{id}', 'Admin\RentManagement\RentController@ledgerLiability')->name('admin.rent.leger-lib-report');
    Route::post('ledger-liability', 'Admin\RentManagement\RentController@rentLiabilityLedgerListing')->name('admin.lib_ledger.list');
    Route::post('exportRentTransfer', 'Admin\ExportController@exportRentTransfer')->name('admin.ledger.exportRentTransfer');
    /************* Rent Management ******************/
    /**********************Expense Managemnet Start***************/
    Route::get('expense', 'Admin\Expense\ExpenseController@index')->name('admin.expense');
    Route::post('get_indirect_expense', 'Admin\Expense\ExpenseController@get_indirect_expense')->name('admin.get_indirect_expense');
    Route::post('get_indirect_expense_sub_head', 'Admin\Expense\ExpenseController@get_indirect_expense_sub_head')->name('admin.get_indirect_expense_sub_head');
    Route::post('save', 'Admin\Expense\ExpenseController@save')->name('admin.expense.save');
    Route::get('report/expense/{id}', 'Admin\Expense\ExpenseController@report_expense')->name('admin.report');
    Route::post('report/expense/liting', 'Admin\Expense\ExpenseController@expense_report_listing')->name('admin.expense_listing');
    Route::post('export/expense', 'Admin\ExportController@export_expense_report')->name('admin.expense.export');
    Route::get('expense/edit/{id}', 'Admin\Expense\ExpenseController@edit')->name('branch.expense.edit');
    Route::post('expense/delete-expense', 'Admin\Expense\ExpenseController@delete_expense')->name('admin.expense.delete-expense');
    Route::post('expense/approve_expense', 'Admin\Expense\ExpenseController@approve_expense')->name('admin.expense.approve_expense');
    Route::get('report/bill_expense', 'Admin\Expense\ExpenseController@expense_bill')->name('admin.expense.expense_bill');
    Route::post('report/bill_expense/liting', 'Admin\Expense\ExpenseController@bill_expense_report_listing')->name('admin.bill_expense_listing');
    Route::post('expense/update', 'Admin\Expense\ExpenseController@update')->name('admin.expense.update');
    Route::post('expense/bill_delete', 'Admin\Expense\ExpenseController@deleteBill')->name('admin.expense.deleteBill');
    Route::post('expense/bill_export', 'Admin\Expense\ExpenseController@export_bill')->name('admin.bill.export');
    Route::get('report/expense_pr/{id}', 'Admin\Expense\ExpenseController@report_expense_print')
        ->name('admin.report.print');
    /*********************Expense Management End *****************/
    /************* Demand Advice ******************/
    Route::get('demand-advices', 'Admin\DemandAdvice\DemandAdviceController@index')->name('admin.demand.advices');
    Route::post('demand-advice-listing', 'Admin\DemandAdvice\DemandAdviceController@demandAdviceListing')->name('admin.demandadvice.list');
    Route::post('ta-advanced-listing', 'Admin\DemandAdvice\DemandAdviceController@taAdvancedListing')->name('admin.taadvanced.list');
    Route::get('demand-advice/addadvice', 'Admin\DemandAdvice\DemandAdviceController@addAdvice')->name('admin.demand.addadvice');
    Route::post('get-sub-account', 'Admin\DemandAdvice\DemandAdviceController@getSubAccount')->name('admin.demand.getsubaccountbycategory');
    Route::post('save-demand-advice', 'Admin\DemandAdvice\DemandAdviceController@saveAdvice')->name('admin.demand.saveadvice');
    Route::get('demand-advice/view/{id}', 'Admin\DemandAdvice\DemandAdviceController@viewAdvice')->name('admin.demand.edit');
    Route::get('demand-advice/edit-demand-advice/{id}', 'Admin\DemandAdvice\DemandAdviceController@editAdvice')->name('admin.demand.edit');
    Route::post('update-advice', 'Admin\DemandAdvice\DemandAdviceController@updateAdvice')->name('admin.demand.update');
    Route::get('approve-demand-advice/{id}', 'Admin\DemandAdvice\DemandAdviceController@approveDemandAdvice')->name('admin.demand.approve');
    Route::get('delete-demand-advice/{id}', 'Admin\DemandAdvice\DemandAdviceController@delete')->name('admin.demand.delete');
    Route::post('delete-demand-advice/delete', 'Admin\DemandAdvice\DemandAdviceController@deleteMultiple')->name('admin.demand.deletemultiple');
    Route::post('ssb-details', 'Admin\DemandAdvice\DemandAdviceController@getSsbDetails')->name('admin.demand.getssb');
    Route::post('employee-details', 'Admin\DemandAdvice\DemandAdviceController@getEmployeeDetails')->name('admin.demand.getemployee');
    Route::post('owner-details', 'Admin\DemandAdvice\DemandAdviceController@getOwnerDetails')->name('admin.demand.getowner');
    Route::post('investment-details', 'Admin\DemandAdvice\DemandAdviceController@getInvestmentDetails')->name('admin.demand.getinvestment');
    Route::post('checkAccountNumber', 'Admin\DemandAdvice\DemandAdviceController@checkAccountNumber')->name('admin.demand.checkAccountNumber');
    Route::get('demand-advice/report', 'Admin\DemandAdvice\DemandAdviceController@report')->name('admin.demand.report');
    Route::post('demand-advice-report-listing', 'Admin\DemandAdvice\DemandAdviceController@reportListing')->name('admin.demandadvice.reportlist');
    // Route::post('export-demand-advice-report', 'Admin\ExportController@exportDemandAdviceReport')->name('admin.demandadvice.export');
    Route::post('export-demand-advice-report', 'Admin\DemandAdvice\DemandAdviceController@export')->name('admin.demandadvice.export');
    /******************* Demand Advice alpan 17 july 23 *************** */
    // Route::post('export-demand-advice-report_new', 'Admin\DemandAdvice\DemandAdviceController@export_new')->name('admin.demandadvice.export_new');
    Route::post('export-demand-advice-report_new', 'Admin\DemandAdvice\DemandAdviceController@export')->name('admin.demandadvice.export_new');
    Route::post('demand-advice/member-details', 'Admin\DemandAdvice\DemandAdviceController@getMemberDetails')->name('admin.demand.getmemberdata');
    Route::get('print/demand-advice', 'Admin\DemandAdvice\DemandAdviceController@print_demand_advice')->name('admin.demand.print_demand_advice');
    Route::post('update-print-status', 'Admin\DemandAdvice\DemandAdviceController@printDemandAdvice')->name('admin.demand.updateprint');
    Route::post('get-tds', 'Admin\DemandAdvice\DemandAdviceController@getTds')->name('admin.demand.gettds');
    /*death help maturitu ui*/
    Route::get('demand-advice/demand-advice-maturity', 'Admin\DemandAdvice\DemandAdviceController@demandAdvicematurity')->name('admin.demand.advices.demand_advice_maturity');
    Route::post('demand-advice-maturity-list', 'Admin\DemandAdvice\DemandAdviceController@demandAdvicematurityList')->name('admin.demandadvices.demand_advice_maturity_list');
    Route::post('get-investment-data', 'Admin\DemandAdvice\DemandAdviceController@getInvestmentData')->name('admin.demand.getinvestmentdata');
    Route::post('demand-advice/investment-maturity-amount', 'Admin\DemandAdvice\DemandAdviceController@saveInvestmentMaturityAmount')->name('admin.demand.saveInvestmentMaturityAmount');
    Route::get('demand-advice/application', 'Admin\DemandAdvice\DemandAdviceController@application')->name('admin.demand.application');
    Route::post('demand-advice-application-listing', 'Admin\DemandAdvice\DemandAdviceController@applicationListing')->name('admin.demandadvice.applicationlist');
    Route::post('demand-advice-approve', 'Admin\DemandAdvice\DemandAdviceController@approveDemandAdviceView')->name('admin.demandadvice.approve');
    Route::post('get-bank-daybook-amount', 'Admin\DemandAdvice\DemandAdviceController@getBankDayBookAmount')->name('admin.demadadvice.getbankdaybookamount');
    Route::post('get-branch-daybook-amount', 'Admin\DemandAdvice\DemandAdviceController@getBranchDayBookAmount')->name('admin.demadadvice.getbranchdaybookamount');
    Route::post('approve-payment', 'Admin\DemandAdvice\DemandAdviceController@approvePayment')->name('admin.damandadvice.approvepayments');
    Route::get('demand-advice/view-ta-advanced', 'Admin\DemandAdvice\DemandAdviceController@viewTaAdvanced')->name('admin.damandadvice.viewtaadvanced');
    Route::get('demand-advice/adjust-ta-advanced/{id}', 'Admin\DemandAdvice\DemandAdviceController@adjustTaAdvanced')->name('admin.demand.adjusttaadvanced');
    Route::post('update-ta-advanced', 'Admin\DemandAdvice\DemandAdviceController@updateTaAdvanced')->name('admin.demand.updatetaadvanced');
    //Reject demand route
    Route::post('reject-demand-advice', 'Admin\DemandAdvice\DemandAdviceController@rejectDemand')->name('admin.demandadvice.reject');
    Route::get('demand-advice/report_reject', 'Admin\DemandAdvice\DemandAdviceController@rejectReport')->name('admin.demand.report_reject');
    Route::post('demand-advice/report_reject/fetch/branch', 'Admin\DemandAdvice\DemandAdviceController@fetchBranch')->name('admin.demand-advice.report_reject.fetch.branch');
    Route::post('demand-advice-rejectReport-listing', 'Admin\DemandAdvice\DemandAdviceController@reportRejectListing')->name('admin.demandadvice.rejectReport.reportlist');
    Route::post('demand-advice/redemand', 'Admin\DemandAdvice\DemandAdviceController@reDemand_advice')->name('admin.demand.re_demand');
    Route::post('demand-advice/redemand/reason', 'Admin\DemandAdvice\DemandAdviceController@rejectDemandReason')->name('admin.demand.rejectReason');
    Route::post('export-demand-advice-reject-report', 'Admin\DemandAdvice\DemandAdviceController@exportRejectReport')->name('admin.demandadvice.reject_export');
    /************* Demand Advice ******************/
    /************* Emergancy Maturity *************/
    Route::get('emergancy-maturity/add', 'Admin\EmergancyMaturityController@add')->name('admin.emergancymaturity.add');
    Route::post('emergancy-investment-details', 'Admin\EmergancyMaturityController@getInvestmentDetails')->name('admin.emergancymaturity.getinvestment');
    Route::post('emergancy-maturity/save', 'Admin\EmergancyMaturityController@save')->name('admin.emergancymaturity.save');
    Route::get('emergancy-maturity', 'Admin\EmergancyMaturityController@index')->name('admin.emergancymaturity.index');
    Route::post('emergancy-maturity-listing', 'Admin\EmergancyMaturityController@emergancyListing')->name('admin.emergancymaturity.listing');
    Route::post('get-emergancy-tds', 'Admin\EmergancyMaturityController@getTds')->name('admin.emergancymaturity.tds');
    Route::post('emergancy-maturity/demand-advice-approve', 'Admin\DemandAdvice\DemandAdviceController@approveDemandAdviceView')->name('admin.demandadvice.approveemergancy');
    Route::post('get_head_details', 'Admin\DemandAdvice\DemandAdviceController@get_head_details')->name('admin.get_head_details');
    /************* Emergancy Maturity *************/
    /************* Withdraw system ******************/
    Route::get('withdrawal', 'Admin\PaymentManagement\WithdrawalController@index')->name('admin.withdraw.ssb');
    Route::post('getAccountDetails', 'Admin\PaymentManagement\WithdrawalController@accountDetails')->name('admin.withdraw.accountdetails');
    Route::post('save-withdrawal', 'Admin\PaymentManagement\WithdrawalController@saveWithdrawal')->name('admin.withdrawal.save');
    Route::post('micro-day-book-amount', 'Admin\PaymentManagement\WithdrawalController@getMicroDayBookAmount')->name('admin.withdraw.getdaybookdata');
    /************* Withdraw system ******************/
    /************* Asset Management *****************************/
    Route::get('asset', 'Admin\Asset\AssetController@assetReport')->name('admin.asset');
    Route::get('asset/edit/{id}', 'Admin\Asset\AssetController@edit_asset')->name('admin.edit_asset');
    Route::get('depreciation/edit/{id}', 'Admin\Asset\AssetController@edit_depreciation')->name('admin.edit_depreciation');
    Route::get('depreciation', 'Admin\Asset\AssetController@depreciationReport')->name('admin.depreciation');
    Route::post('asset/listing', 'Admin\Asset\AssetController@assetListing')->name('admin.asset.lists');
    Route::post('assetExport', 'Admin\ExportController@assetExportList')->name('admin.asset.exportLists');
    Route::post('depreciation/listing', 'Admin\Asset\AssetController@depreciationListing')->name('admin.depreciation.lists');
    Route::post('depreciationExport', 'Admin\ExportController@depreciationExportList')->name('admin.depreciation.exportLists');
    Route::post('depreciation/edit_save', 'Admin\Asset\AssetController@depreciationSave')->name('admin.asset.depreciation_save');
    Route::post('asset/edit_save', 'Admin\Asset\AssetController@assetSave')->name('admin.asset.asset_save');
    /****************BRS ************************************/
    Route::get('brs/report', 'Admin\Brs\BrsController@index')->name('admin.brs.report');
    Route::get('brs/bank_charge', 'Admin\Brs\BankChargeController@bank_charge')->name('admin.brs.bank_charge');
    Route::post('brs/report', 'Admin\Brs\BrsController@getBRSDATA');
    Route::get('brs/report/print', 'Admin\Brs\BrsController@print_brs')->name('admin.brs.report.print');
    Route::post('brs/bank_charge/save', 'Admin\Brs\BankChargeController@chargeSave')->name('admin.brs.bank_charge_save');
    /****************Account Head Report ********************/
    Route::get('account_head_ledger/{head_id}/{label}', 'Admin\AccountHeadReport\AccountHeadReportController@index')->name('admin.accountHeadLedger');
    Route::get('account_head_ledger/transaction/{head_id}/{label}', 'Admin\AccountHeadReport\AccountHeadReportController@transaction')->name('admin.accountHeadLedger.transaction');
    Route::post('account_head_ledger', 'Admin\AccountHeadReport\AccountHeadReportController@ledgerListing')->name('admin.account_head.ledger.list');
    Route::post('transaction_list', 'Admin\AccountHeadReport\AccountHeadReportController@transaction_list')->name('admin.account_head.ledger.transaction_list');
    Route::post('export/account_head_ledger', 'Admin\ExportController@exportAccoutHeadReport')->name('admin.account_head.ledger.export');
    Route::post('export/account_head_ledger/transaction', 'Admin\ExportController@exportAccoutHeadReporttranscation')->name('admin.account_head_transcation.ledger.export');
    /***************************************HeadController Start **************/
    Route::get('head', 'Admin\AccountHeadReport\HeadController@index')->name('admin.head');
    Route::post('getHeadlistbyCompany', 'Admin\AccountHeadReport\HeadController@getHeadlistbyCompany')->name('admin.head.getHeadlistbyCompany');
    Route::get('create_head', 'Admin\AccountHeadReport\HeadController@create_head')->name('admin.create_head');
    Route::post('save/head', 'Admin\AccountHeadReport\HeadController@save')->name('admin.save.head');
    Route::get('edit/head/{id}/{label}', 'Admin\AccountHeadReport\HeadController@edit_head')->name('admin.edit.head');
    Route::post('update/head', 'Admin\AccountHeadReport\HeadController@update_head')->name('admin.head.update');
    Route::get('delete/head/{id}/{label}', 'Admin\AccountHeadReport\HeadController@deleteHead')->name('admin.delete.head');
    Route::post('getchildHead', 'Admin\AccountHeadReport\HeadController@getChildAsset')->name('admin.get.child_head');
    Route::post('getparentHeadbyCompany', 'Admin\AccountHeadReport\HeadController@getparentHeadbyCompany')->name('admin.get.parentheadbycompany');
    Route::post('getHeadlistbyCompany', 'Admin\AccountHeadReport\HeadController@getHeadlistbyCompany')->name('admin.head.getHeadlistbyCompany');

    Route::post('check-head', 'Admin\AccountHeadReport\HeadController@checkHeadTitle')->name('admin.checkhead.title');
    Route::post('Update-head', 'Admin\AccountHeadReport\HeadController@updateComanyHead')->name('admin.updateCompanyHead');
    Route::post('Update-companies', 'Admin\AccountHeadReport\HeadController@updateComanies')->name('admin.updateComaiesHeads');
    Route::post('get-companies', 'Admin\AccountHeadReport\HeadController@getCompanies')->name('admin.getCompanies');

    /**************************************HeadController End****************
    /************** ShareHolder/Director ********************/
    Route::get('shareholder_director', 'Admin\Shareholder\ShareHolderController@shareholderReport')->name('admin.shareholder');
    Route::get('shareholder_director/1', 'Admin\Shareholder\ShareHolderController@createShareHolder')->name('admin.shareholder.create');
    Route::post('verify/member', 'Admin\Shareholder\ShareHolderController@verifyMember')->name('admin.verify.member');
    Route::post('verify/ssbAccount', 'Admin\Shareholder\ShareHolderController@verifyssbAccount')->name('admin.verify.ssbAccount');
    Route::post('save/shareholder', 'Admin\Shareholder\ShareHolderController@storeShareHolder')->name('admin.shareholder.save');
    Route::post('share-holder-listing', 'Admin\Shareholder\ShareHolderController@reportListing')->name('admin.share.report.listing');
    Route::get('shareholder_director/edit/{type}/{id}', 'Admin\Shareholder\ShareHolderController@edit')->name('admin.share.holder.director');
    Route::post('share-holder-update', 'Admin\Shareholder\ShareHolderController@update')->name('admin.holder.director.update');
    Route::post('share-holder/updateStatus', 'Admin\Shareholder\ShareHolderController@updateStatus')->name('admin.update.status.share-holder');
    Route::get('shareholder_director/transfer_share', 'Admin\Shareholder\ShareHolderController@transfer_share')->name('admin.share_holder.transfer_share');
    Route::post('get_transfer_share_detail', 'Admin\Shareholder\ShareHolderController@get_share_holder_detail')->name('admin.get_share_holder_detail');
    Route::post('headDetailGetAll', 'Admin\Shareholder\ShareHolderController@headDetailGetAll')->name('admin.headDetailGetAll');
    Route::get('shareholder_director/director_deposit_payment', 'Admin\Shareholder\ShareHolderController@share_holder_deposit_payment')->name('admin.share_holder_deposit_payment');
    Route::get('shareholder_director/deposit_payment', 'Admin\Shareholder\ShareHolderController@director_deposit_payment')->name('admin.director_deposit_payment');
    Route::post('store/director/deposit_payment', 'Admin\Shareholder\ShareHolderController@director_deposit_payment_transaction')->name('admin.store_director_deposit_payment');
    Route::post('store/director/withdrawal_payment', 'Admin\Shareholder\ShareHolderController@director_withdrawal_payment_transaction')->name('admin.director_withdrawal_payment_transaction');
    Route::get('shareholder_director/withdrawal_payment', 'Admin\Shareholder\ShareHolderController@director_withdrawal_payment')->name('admin.director_withdrawal_payment');
    Route::post('transfer-save/shareholder', 'Admin\Shareholder\ShareHolderController@save_transfer')->name('admin.shareholder.save_transfer');
    /**********************Eli Loan *********************************/
    Route::get('eli-loan', 'Admin\EliLoan\EliLoanController@index')->name('admin.eli-loan');
    Route::get('create/eli_Loan', 'Admin\EliLoan\EliLoanController@createEliLoan')->name('admin.create.eli-loan');
    Route::post('save/eli_Loan', 'Admin\EliLoan\EliLoanController@storeEliLoan')->name('admin.eliLoan.save');
    Route::post('update/eli_Loan', 'Admin\EliLoan\EliLoanController@updateEliLoan')->name('admin.eliLoan.update');
    Route::post('eli_Loan/listing', 'Admin\EliLoan\EliLoanController@eliLoanListing')->name('admin.eli-loan.listing');
    Route::get('eli_loan/edit/{id}', 'Admin\EliLoan\EliLoanController@editEliLoan')->name('admin.edit.eli-loan');
    Route::post('eli-loan/updateStatus', 'Admin\EliLoan\EliLoanController@updateStatus')->name('admin.update.status.eli-loan');
    /*************************** Loan From Bank Start *****************************/
    Route::get('loanFromBank', 'Admin\LoanFromBank\LoanFromBankController@index')->name('admin.loan_from_bank');
    Route::get('loanFromBank/create', 'Admin\LoanFromBank\LoanFromBankController@createLoanFromBank')->name('admin.create.loan-from-bank');
    Route::post('store/loanFromBank', 'Admin\LoanFromBank\LoanFromBankController@storeLoanFromBank')->name('admin.save.loan-from-bank');
    Route::post('loan-from-bank-listing', 'Admin\LoanFromBank\LoanFromBankController@reportListing')->name('admin.loan_from_bank.report.listing');
    Route::get('loanFromBank/edit/{id}', 'Admin\LoanFromBank\LoanFromBankController@edit_loan_from_bank')->name('admin.edit.loan_from_bank');
    Route::post('update/loan_from_bank', 'Admin\LoanFromBank\LoanFromBankController@update_loan_from_bank')->name('admin.loan_from_bank.update');
    Route::post('loan_from_bank/updateStatus', 'Admin\LoanFromBank\LoanFromBankController@updateStatus')->name('admin.update.status.loan_from_bank');
    Route::get('loanFromBank/loan_emi', 'Admin\LoanFromBank\LoanFromBankController@loan_emi')->name('admin.loan_emi');
    Route::post('get_loan_account_detail', 'Admin\LoanFromBank\LoanFromBankController@get_loan_account_detail')->name('admin.get_loan_account_detail');
    Route::post('loan_emi/save', 'Admin\LoanFromBank\LoanFromBankController@save_loan_emi')->name('admin.store_loan_emi');
    Route::get('loanFromBank/loan_emi_report', 'Admin\LoanFromBank\LoanFromBankController@loan_emi_report')->name('admin.loan_emi_report');
    Route::post('loan-emi-listing', 'Admin\LoanFromBank\LoanFromBankController@loanemiReportListing')->name('admin.loan_from_bank.loan_emi.listing');
    Route::post('getLoanAccount', 'Admin\LoanFromBank\LoanFromBankController@getloanaccount')->name('admin.getloanAccount');
    //---------------------------lllllllllllllllllllllll-----------------
    Route::get('loanFromBank/ledger/{id}/{id1}/{id2}', 'Admin\LoanFromBank\LoanFromBankController@ledger')->name('admin.loanFromBank.ledger');
    Route::post('loanFromBank/ledger_listint', 'Admin\LoanFromBank\LoanFromBankController@ledgerListing')->name('admin.loanFromBank.ledger_listing');
    Route::post('loanFromBank/exportLedger', 'Admin\ExportController@LoanFromBankLedger')->name('admin.loanFromBank.ledger_listing_export');
    Route::post('loanFromBank/delete', 'Admin\LoanFromBank\LoanFromBankController@delete')->name('admin.loanFromBank.delete');
    /******************************** Loan From Bank End ************************************/
    /********************Bank Account **************************/
    Route::get('/bank_account', 'Admin\BankAccount\BankAccountController@index')->name('admin.bank_account');
    Route::get('create/bank_account', 'Admin\BankAccount\BankAccountController@create_bank_account')->name('admin.create.bank_account');
    Route::post('store/bank_account', 'Admin\BankAccount\BankAccountController@store_bank_account')->name('admin.save.bank_account');
    Route::post('bank_account_listing', 'Admin\BankAccount\BankAccountController@bankAccountListing')->name('admin.bank_account.report.listing');
    Route::get('bank_account/edit/{id}', 'Admin\BankAccount\BankAccountController@edit_bank_account')->name('admin.edit.bank_account');
    Route::post('update/bank_account', 'Admin\BankAccount\BankAccountController@update_bank_account')->name('admin.update.bank_account');
    Route::post('bank-account/updateStatus', 'Admin\BankAccount\BankAccountController@updateStatus')->name('admin.update.status.bank_account');
    /*****************Fixed Asset **********************/
    Route::get('/fixed_asset', 'Admin\FixedAsset\FixedAssetController@index')->name('admin.fixed_asset');
    Route::get('create/fixed_asset', 'Admin\FixedAsset\FixedAssetController@create_fixed_asset')->name('admin.create.fixed_asset');
    Route::post('/getChildAsset', 'Admin\FixedAsset\FixedAssetController@getChildAsset')->name('admin.get.child_asset');
    Route::post('store/fixed_asset', 'Admin\FixedAsset\FixedAssetController@store_fixed_asset')->name('admin.save.fixed_asset');
    Route::get('fixed_asset/edit/{id}', 'Admin\FixedAsset\FixedAssetController@edit_fixed_asset')->name('admin.edit.fixed_asset');
    Route::post('fixed_asset_listing', 'Admin\FixedAsset\FixedAssetController@fixedAssetListing')->name('admin.fixed_asset.report.listing');
    Route::post('update/fixed_asset', 'Admin\FixedAsset\FixedAssetController@update_fixed_asset')->name('admin.fixed_asset.update');
    Route::post('fixed_asset/updateStatus', 'Admin\FixedAsset\FixedAssetController@updateStatus')->name('admin.update.status.fixed_asset');
    /*****************Indirect Expense *****************/
    Route::get('/indirect_expense', 'Admin\IndirectExpense\IndirectExpenseController@index')->name('admin.indirect_expense');
    Route::get('create/indirect_expense', 'Admin\IndirectExpense\IndirectExpenseController@create_indirect_expense')->name('admin.create.indirect_expense');
    Route::post('store/indirect_expense', 'Admin\IndirectExpense\IndirectExpenseController@store_indirect_expense')->name('admin.save.indirect_expense');
    Route::get('indirect_expense/edit/{id}', 'Admin\IndirectExpense\IndirectExpenseController@edit_indirect_expense')->name('admin.edit.indirect_expense');
    Route::post('indirect_expense/updateStatus', 'Admin\IndirectExpense\IndirectExpenseController@updateStatus')->name('admin.update.status.indirect_expense');
    Route::post('update/indirect_expense', 'Admin\IndirectExpense\IndirectExpenseController@update_indirect_expense')->name('admin.indirect_expense.update');
    Route::post('indirect_expense/updateStatus', 'Admin\IndirectExpense\IndirectExpenseController@updateStatus')->name('admin.update.status.indirect_expense');
    /************* Fund Transfer ******************/
    Route::get('fund-transfer', 'Admin\PaymentManagement\FundTransferController@index')->name('admin.fund.transfer');
    Route::get('fund-transfer-detail/{id}/{chequeId}', 'Admin\PaymentManagement\FundTransferController@edit')->name('admin.fund.transfer.edit');
    Route::get('fund-transfer/update-status/{id}/{status}/{branchid}/{companyId}', 'Admin\PaymentManagement\FundTransferController@updateStatus')->name('admin.fund.transfer.updateStatus');
    Route::post('fund-transfer-listing', 'Admin\PaymentManagement\FundTransferController@fundTransferListing')->name('admin.fund.transfer.listing');
    Route::get('fund-transfer/branch-to-ho/create', 'Admin\PaymentManagement\FundTransferController@createBranchToHo')->name('admin.fund-transfer.branchToHo.create');
    Route::get('fund-transfer/branch-to-ho', 'Admin\PaymentManagement\FundTransferController@BranchToHo')->name('admin.fund-transfer.branchToHo');
    Route::get('delete/branch-to-ho/{id}', 'Admin\PaymentManagement\FundTransferController@deleteBranchToHo')->name('admin.delete.fund-transfer.branchToHo');
    Route::post('fund-transfer/branch-to-ho-listing', 'Admin\PaymentManagement\FundTransferController@branchToHoListing')->name('admin.fundtransfer.branchtoholisting');
    Route::get('fund-transfer/report', 'Admin\PaymentManagement\FundTransferController@fundTransferReport')->name('admin.fund-transfer.report');
    Route::post('fund-transfer/reportListing', 'Admin\PaymentManagement\FundTransferController@fundTransferReportListing')->name('admin.fund-transfer.report_lisiting');
    Route::post('exportFundTransferList', 'Admin\PaymentManagement\FundTransferController@exportFundTransfer')->name('admin.fundTransfer.export');
    Route::post('fund-transfer/bank-to-bank-listing', 'Admin\PaymentManagement\FundTransferController@bankTobankListing')->name('admin.fundtransfer.banktobranchlisting');
    Route::post('admin-fund-transfer-bankTobank', 'Admin\PaymentManagement\FundTransferController@fundTransferBankToBank')->name('admin.fund.transfer.bankTobank');
    Route::get('admin-fund-transfer-bankTobank', 'Admin\PaymentManagement\FundTransferController@createBankToBank')->name('admin.fund.transfer.bankTobank');
    Route::post('fund-transfer-head-office', 'Admin\PaymentManagement\FundTransferController@fundTransferHeadOffice')->name('admin.fund.transfer.head.office');
    Route::get('edit/branch_to_ho/{id}', 'Admin\PaymentManagement\FundTransferController@edit_branch_to_ho')->name('admin.edit.fund.transfer.head.office');
    Route::post('edit/branch_to_ho', 'Admin\PaymentManagement\FundTransferController@update_branch_to_ho')->name('admin.edit.fund.transfer.head.office');
    Route::post('update/bank_to_bank', 'Admin\PaymentManagement\FundTransferController@update_bank_to_bank')->name('admin.update.bank.to.bank.fund.transfer');
    Route::post('fund-transfer/daybookamount', 'Admin\PaymentManagement\FundTransferController@getDayBookAmount')->name('admin.getdaybookamount');
    Route::post('fund-transfer/bankdaybookamount', 'Admin\PaymentManagement\FundTransferController@getBankDayBookAmount')->name('admin.getbankdaybookamount');
    Route::post('fund-transfer/get-to-bank-record', 'Admin\PaymentManagement\FundTransferController@getToBankRecord')->name('admin.gettobankrecord');
    Route::post('fetchBranchByCompanyId', 'Admin\PaymentManagement\FundTransferController@fetchbranchbycompanyid')->name('admin.fetchbranchbycompanyid');
    Route::post('fetchbranchbycompanyidinactive', 'Admin\PaymentManagement\FundTransferController@fetchbranchbycompanyidinactive')->name('admin.fetchbranchbycompanyid.inactive');
    Route::post('branchBalance', 'Admin\PaymentManagement\FundTransferController@getbranchbankbalanceamount')->name('admin.branchBankBalance');
    Route::post('getBankAccountNo', 'Admin\PaymentManagement\FundTransferController@getBankAccountNo')->name('admin.getBankAccountNo');
    Route::post('getBankAccountNos', 'Admin\PaymentManagement\FundTransferController@getBankAccountNos')->name('admin.getBankAccountNos');
    Route::post('banktobankbalance', 'Admin\PaymentManagement\FundTransferController@banktobankbalance')->name('admin.bankToBankBalance');
    Route::post('getchecqsByBankIdAndAccountno', 'Admin\PaymentManagement\FundTransferController@getchecqsByBankIdAndAccountno')->name('admin.getchecks');
    Route::post('fetchbranchbycompanyidd', 'Admin\PaymentManagement\FundTransferController@fetchbranchbycompanyidd')->name('admin.fetchbranchbycompanyidd');
    // END
    /************* Fund Transfer ******************/
    /**************Bank Ledger Report ****************/
    Route::get('bank-ledger-report', 'Admin\BankLedgerController@bankLedgerreport')->name('admin.bank-ledger.report');
    Route::post('bank-ledger-report-listing', 'Admin\BankLedgerController@bankLedgerListing')->name('admin.bank-ledger.report.listing');
    Route::post('bank-ledger-opening-balance', 'Admin\BankLedgerController@getBalance')->name('admin.bank-ledger.balance');
    Route::post('bank-ledger-closing-balance', 'Admin\BankLedgerController@getclosingBalance')->name('admin.bank-ledger.closingbalance');
    /**************End*******************************/
    /*CashInHand*/
    Route::get('cash-in-hand', 'Admin\CashInHandController@index')->name('admin.cash-in-hand');
    Route::post('cash-in-hand-listing', 'Admin\CashInHandController@cashInHandListing')->name('admin.cash-in-hand.listing');
    /*End CashInHand/*
    /*E-Investment Maturity */
    Route::get('e-investment-maturity', 'Admin\EInvestmentController@index')->name('admin.e_investment_maturity');
    Route::post('cash-in-hand-listing', 'Admin\CashInHandController@cashInHandListing')->name('admin.cash-in-hand.listing');
    /*End E-Investment Maturity /*
    /*Profit and Loss */
    Route::get('profit-loss', 'Admin\ProfitLossController@index')->name('admin.profit&loss');
    Route::post('profit-loss/export', 'Admin\ExportController@profitLossExport')->name('admin.profit-loss.report.export');
    Route::post('profit-loss/detailed/export', 'Admin\ExportController@profitLossDetailExport')->name('admin.profit-loss.detail.report.export');
    Route::post('profit-loss/branchwise/export', 'Admin\ExportController@profitLossBranchWiseExport')->name('admin.profit-loss.branch_wise.report.export');
    Route::post('profit-loss/stationary_chrg/export', 'Admin\ExportController@profitLossStationaryChargeExport')->name('admin.profit-loss.stationary_chrg.report.export');
    Route::post('profit-loss/file_chrg/export', 'Admin\ExportController@profitLossFileChargeExport')->name('admin.profit-loss.file_chrg.report.export');
    Route::post('profit-loss/interest_on_deposite/export', 'Admin\ExportController@profitLossInterestonDepositeExport')->name('admin.profit-loss.interest_on_deposite.report.export');
    Route::post('profit-loss/loan_taken/export', 'Admin\ExportController@profitLossLoanTakenExport')->name('admin.profit-loss.loan_taken.report.export');
    Route::post('profit-loss/salary/export', 'Admin\ExportController@profitLossSalaryExport')->name('admin.profit-loss.salary.report.export');
    Route::post('profit-loss/panel/export', 'Admin\ExportController@profitLossPanelExport')->name('admin.profit-loss.panel.report.export');
    Route::post('profit-loss/report/export', 'Admin\ExportController@profitLossReportExport')->name('admin.profit-loss.detailreport.export');
    Route::post('profit-loss/depreciation/export', 'Admin\ExportController@profitLossdepreciationExport')->name('admin.profit-loss.depreciation.export');
    Route::post('profit-loss/rent/export', 'Admin\ExportController@ProfitLossrentExport')->name('admin.profit-loss.rent.export');
    Route::post('profit-loss/commission/export', 'Admin\ExportController@ProfitLosscommissionExport')->name('admin.profit-loss.commission.export');
    Route::post('profit-loss/late_panelty/export', 'Admin\ExportController@profitLossLatePaneltyExport')->name('admin.profit-loss.late_panelty.report.export');
    Route::post('profit-loss/interest_on_loan/export', 'Admin\ExportController@interestOnLoanExport')->name('admin.profit-loss.interest_on_loan.report.export');
    Route::post('profit-loss-fillter', 'Admin\ProfitLossController@profitLossAjax')->name('admin.profit_loss_fillter');
    Route::get('profit-loss/detail/{id1}/{id2}', 'Admin\ProfitLossController@labelTwo')->name('admin.profit-loss.labelTwo');
    Route::get('profit-loss/detail/{id}', 'Admin\ProfitLossController@detail')->name('admin.profit-loss.detailed');
    Route::get('profit-loss/detail/branch_wise/{head_id}/{label}', 'Admin\ProfitLossController@branch_wise_detail')->name('admin.profit-loss.branch_wise_detail');
    Route::post('profit-loss/detailed/branch_wise/', 'Admin\ProfitLossController@branch_wise_detailed')->name('admin.detailed.branch_wise');
    Route::get('profit-loss/detailed/commission/', 'Admin\ProfitLossController@commission_detail')->name('admin.detailed.commission_detail');
    Route::post('profit-loss/detail/commission_list', 'Admin\ProfitLossController@commission_list')->name('admin.rofit-loss.commission_list');
    Route::get('profit-loss/detailed/penal/', 'Admin\ProfitLossController@penal_interest')->name('admin.detailed.penal_interest');
    Route::post('profit-loss/detail/penal_interest_list', 'Admin\ProfitLossController@penal_interest_list')->name('admin.profit-loss.penal_interest_list');
    Route::get('profit-loss/detailed/panelty/', 'Admin\ProfitLossController@late_penalty')->name('admin.detailed.late_penalty');
    Route::post('profit-loss/detail/late_penalty_list', 'Admin\ProfitLossController@late_penalty_list')->name('admin.rofit-loss.late_penalty_list');
    Route::get('profit-loss/detailed/interest_on_loan/', 'Admin\ProfitLossController@interest_on_loan_detail')->name('admin.detailed.interest_on_loan_detail');
    Route::post('profit-loss/detail/interest_on_loan_list', 'Admin\ProfitLossController@interest_on_loan_list')->name('admin.rofit-loss.interest_on_loan_list');
    Route::get('profit-loss/detailed/interest_on_deposit/', 'Admin\ProfitLossController@interest_on_deposit')->name('admin.detailed.interest_on_deposit');
    Route::get('profit-loss/detailed/stationary_charge/', 'Admin\ProfitLossController@stationary_charge')->name('admin.detailed.stationary_charge');
    Route::post('profit-loss/detail/stationary_chrg_listing', 'Admin\ProfitLossController@stationary_charge_listing')->name('admin.profit-loss.stationary_chrg_listing');
    Route::get('profit-loss/head_detail_report/{head_id}/{label}', 'Admin\ProfitLossController@head_report_detail')->name('admin.detailed.head_report_detail');
    Route::post('profit-loss/get_head_report_listing', 'Admin\ProfitLossController@get_report_data')->name('admin.profit-loss.get_report_data');
    Route::get('profit-loss/duplicate_passbook/', 'Admin\ProfitLossController@duplicate_passbook')->name('admin.detailed.duplicate_passbook');
    Route::post('profit-loss/duplicate_passbook_listing', 'Admin\ProfitLossController@duplicate_passbook_listing')->name('admin.profit-loss.duplicate_passbook_listing');
    Route::post('profit-loss/detail/interest_on_deposit_list', 'Admin\ProfitLossController@interest_on_deposit_list')->name('admin.profit-loss.interest_on_deposit_list');
    Route::get('profit-loss/detailed/interest_on_loan_taken/{head_id}/{label}', 'Admin\ProfitLossController@interest_on_loan_taken')->name('admin.detailed.interest_on_loan_taken');
    Route::post('profit-loss/detail/interest_on_loan_taken_list', 'Admin\ProfitLossController@interest_on_loan_taken_list')->name('admin.profit-loss.interest_on_loan_taken_list');
    Route::get('profit-loss/detailed/salary/', 'Admin\ProfitLossController@salary')->name('admin.detailed.salary');
    Route::post('profit-loss/detail/salary_list', 'Admin\ProfitLossController@salary_list')->name('admin.profit-loss.salary_list');
    Route::get('profit-loss/detailed/fuel_charge/', 'Admin\ProfitLossController@fuel_charge')->name('admin.detailed.fuel_charge');
    Route::post('profit-loss/detail/fuel_charge_list', 'Admin\ProfitLossController@fuel_charge_list')->name('admin.rofit-loss.fuel_charge_list');
    Route::post('profit-loss/fuel_charge/export', 'Admin\ExportController@ProfitLossFuelChargeExport')->name('admin.profit-loss.fuel_charge.export');
    Route::get('profit-loss/detailed/depreciation/', 'Admin\ProfitLossController@depreciation')->name('admin.detailed.depreciation');
    Route::post('profit-loss/detail/depreciation_list', 'Admin\ProfitLossController@depreciation_list')->name('admin.rofit-loss.depreciation_list');
    Route::get('profit-loss/detailed/rent/', 'Admin\ProfitLossController@rent')->name('admin.detailed.rent');
    Route::post('profit-loss/detail/rent_list', 'Admin\ProfitLossController@rent_list')->name('admin.rofit-loss.rent_list');
    Route::get('profit-loss/detailed/file_charge/', 'Admin\ProfitLossController@file_charge')->name('admin.detailed.file_charge');
    Route::post('profit-loss/detail/file_charge_list', 'Admin\ProfitLossController@file_charge_list')->name('admin.profit-loss.file_charge_list');
    Route::post('profit-loss/detail_new_export', 'Admin\ProfitLossController@exportDetailNew')->name('admin.profit-loss.depreciation.export.new');
    Route::post('profit-loss/current_liability/branch_wise_list', 'Admin\ProfitLossController@current_liabilityDetailBranchWiseListing')->name('admin.profit-loss.curr_liability_detailBranchWise_listing');
    Route::get('profit-loss/datatable/{key}', 'Admin\ProfitLossController@datatable')->name('profit-loss.page');
    Route::get('profit-loss/page/', 'Admin\ProfitLossController@page')->name('admin.profit_losss.page');
    //new routes by tansukh
    Route::get('profit-loss/head', 'Admin\ProfitLossController@currentDetail')->name('profit_loss.head');
    Route::post('profit-loss/detail_new_ajax', 'Admin\ProfitLossController@detailNewAjax')->name('admin.detailNewAjax');
    Route::get('profit-loss/{key}', 'Admin\ProfitLossController@datatable')->name('profit-loss.page');
    //--------------------------------- Cron Log Start --------------------------------------//
    Route::group(['prefix' => 'cron_management'], function () {
        /** created by gaurav */
        //--------------------------------- Cron Log Start --------------------------------------//
        Route::get('/', 'Admin\Cron\CronController@index')->name('admin.cron.index');
        Route::post('listing', 'Admin\Cron\CronController@listing')->name('admin.cron.listing');
        Route::post('delete', 'Admin\Cron\CronController@delete')->name('admin.cron.delete');
        Route::post('export', 'Admin\Cron\CronController@export')->name('admin.cron.export');
        //--------------------------------- Cron Log End ---------------------------------------//
        /** created and modify by sourab pn 03-11-2023 */
        Route::get('money_back_amount_transfer_cron', 'Admin\Cron\CronController@money_back_amount_transfer_cron')->name('admin.cron.money_back_amount_transfer_cron');
        Route::post('amount_transfer_cron', 'Admin\Cron\CronController@amount_transfer_cron')->name('admin.cron.amount_transfer_cron.run');
        Route::get('monthly_income_scheme_interest_transfer_cron', 'Admin\Cron\CronController@monthly_income_scheme_interest_transfer_cron')->name('admin.cron.monthly_income_scheme_interest_transfer_cron');
        Route::post('investmentdetails', 'Admin\Cron\CronController@investmentdetails')->name('admin.cron.investmentdetails');
        // cron for Saturday / Sunday Holiday created by Sourab on 13-11-2023
        Route::get('bank_holidays_cron', 'Admin\Cron\CronController@bank_holidays_cron')->name('bank_holidays_cron');
        Route::post('getSaturday', 'Admin\Cron\CronController@getSaturday')->name('admin.cron.getSaturday.run');
    });
    //--------------------------------- Cron Log End ---------------------------------------//
    /********************** Report Manangement start **************************/
    Route::get('report/daybook', 'Admin\Report\ReportController@index')->name('admin.daybook_report');
    Route::get('report/associate_business', 'Admin\Report\ReportController@associateBusinessReport')->name('admin.report.associate_business_report');
    Route::post('associate_business', 'Admin\Report\ReportController@associateBusinessList')->name('admin.report.associate_business');
    Route::get('report/associate_business_summary', 'Admin\Report\ReportController@associateBusinessSummaryReport')->name('admin.report.associate_business_summary_report');
    Route::post('associate_business_summary', 'Admin\Report\ReportController@associateBusinessSummaryList')->name('admin.report.associate_business_summary');
    // Route::get('report/associate_business_compare', 'Admin\Report\ReportController@associateBusinessCompareReport')->name('admin.report.associate_business_compare_report');
    Route::post('associate_business_compare', 'Admin\Report\ReportController@associateBusinessCompareList')->name('admin.report.associate_business_compare');
    Route::get('report/cash_report', 'Admin\Report\ReportController@cashInHand')->name('admin.report.cash_in_hand');
    Route::get('report/maturity_report', 'Admin\Report\ReportController@maturityReport')->name('admin.report.maturity_report');
    Route::post('cashInHandDetail', 'Admin\Report\ReportController@cashInHandDetail')->name('admin.report.cashInHandDetail');
    Route::get('report/transaction', 'Admin\Report\ReportController@transaction')->name('admin.report.transaction');
    Route::post('transactionDetail', 'Admin\Report\ReportController@transactionDetail')->name('admin.report.transactionDetail');
    Route::post('transactionDetailSSB', 'Admin\Report\ReportController@transactionDetailSSB')->name('admin.report.transactionDetailSSB');
    Route::post('transactionDetailOther', 'Admin\Report\ReportController@transactionDetailOther')->name('admin.report.transactionDetailOther');
    Route::post('transactionDetailExportt', 'Admin\ExportController@transactionDetailExport')->name('admin.report.transactionDetailExport');
    Route::post('bankLedgerExport', 'Admin\ExportController@bank_ledger_export')->name('admin.bankLedger.report.export');
    Route::post('transactionDetailSsbExport', 'Admin\ExportController@transactionDetailSsbExport')->name('admin.report.transactionDetailSsbExport');
    Route::post('transactionDetailOtherbExport', 'Admin\ExportController@transactionDetailOtherbExport')->name('admin.report.transactionDetailOtherbExport');
    Route::post('associateBusinessListExport', 'Admin\ExportController@associateBusinessListExport')->name('admin.report.associateBusinessListExport');
    Route::post('associateBusinessSummaryExport', 'Admin\ExportController@associateBusinessSummaryExport')->name('admin.report.associateBusinessSummaryExport');
    Route::post('associateBusinessCompareExport', 'Admin\ExportController@associateBusinessCompareExport')->name('admin.report.associateBusinessCompareExport');
    Route::post('adminBusinessListExport', 'Admin\ExportController@adminBusinessListExport')->name('admin.report.adminBusinessListExport');
    Route::post('branchRegionByZone', 'Admin\Report\ReportController@branchRegionByZone')->name('admin.report.branchRegionByZone');
    Route::post('branchSectorByRegion', 'Admin\Report\ReportController@branchSectorByRegion')->name('admin.report.branchSectorByRegion');
    Route::post('branchBySector', 'Admin\Report\ReportController@branchBySector')->name('admin.report.branchBySector');
    Route::get('report/loan', 'Admin\Report\ReportController@loan')->name('admin.report.loan');
    Route::post('report/companyIdToLoan', 'Admin\Report\ReportController@companyIdToLoan')->name('admin.report.companyIdToLoan');
    Route::post('report/loan-list', 'Admin\Report\ReportController@loanListing')->name('admin.report.loanlist');
    Route::get('report/group-loan', 'Admin\Report\ReportController@groupLoan')->name('admin.report.grouploan');
    Route::post('report/group-loan-list', 'Admin\Report\ReportController@groupLoanListing')->name('admin.report.grouploanlist');
    Route::post('loanDetailExport', 'Admin\ExportController@loanListExport')->name('admin.loan.report.export');
    Route::post('grouploanDetailExport', 'Admin\ExportController@groupLoanListExport')->name('admin.grouploan.report.export');
    Route::get('report/maturity', 'Admin\Report\ReportController@maturity')->name('admin.report.maturity');
    Route::post('report/maturity', 'Admin\Report\ReportController@maturityplans')->name('admin.report.maturityListing.plans');
    Route::post('report/maturityListing', 'Admin\Report\ReportController@maturityReportListing')->name('admin.report.maturityListing');
    Route::post('maturityDetailExport', 'Admin\ExportController@maturityListExport')->name('admin.maturity.report.export');
    Route::get('report/day_business', 'Admin\Report\BranchBusinessController@branch_business')->name('admin.report.day_business');
    Route::get('report/day_book', 'Admin\Report\DayBookController@day_bookReport')->name('admin.report.day_book');
    Route::post('report/day_book', 'Admin\Report\ReportController@day_filterbookReport')->name('admin.report.filtered_day_book');
    Route::post('daybookReportExport', 'Admin\ExportController@daybookReportExport')->name('admin.daybook.report.export');
    Route::post('daybook/transaction_list', 'Admin\Report\DayBookController@transaction_list')->name('admin.daybook.transaction_listing');
    // Route::post('report/day_business', 'Admin\Report\ReportController@filtered_day_business_report')->name('admin.report.filtered_day_business');
    Route::get('print/report/day_book', 'Admin\Report\DayBookController@print_day_bookReport')->name('admin.print.report.day_book');
    Route::post('report/day_book_list', 'Admin\Report\DayBookController@day_filterbookReport')->name('admin.report.day_booklisting');
    Route::post('branchBusinessReportExport', 'Admin\ExportController@branchBusinessReportExport')->name('admin.branch_business.report.export');
    Route::post('branch_business_listing', 'Admin\Report\BranchBusinessController@branch_business_listing')->name('admin.report.branch_business_listing');
    Route::get('report/branch_business', 'Admin\Report\AdminBusinessController@index')->name('admin.report.branch_business');
    Route::post('adminBusinessReportExport', 'Admin\ExportController@adminBusinessReportExport')->name('admin.admin_business.report.export');
    Route::post('admin_business_listing', 'Admin\Report\AdminBusinessController@admin_business_listing')->name('admin.report.admin_business_listing');
    /********************** Report Manangement End **************************/
    /**********   Report Managemrent cash in hand  ************ */
    Route::get('report/cash_in_hand', 'Admin\Report\CashInHandController@cashInHand')->name('admin.report.cashinhand');
    Route::post('report/cash_in_hand_Listing', 'Admin\Report\CashInHandController@cash_in_hand_Listing')->name('admin.report.cashinhandListing');
    Route::post('report/cashinhand_demandlistexport', 'Admin\Report\CashInHandController@cashinhand_demandlistExport')->name('admin.cashinhand_demandlist_Export.report.export');
    /**********   Report Managemrent cash in hand  ************ */
    // Ledger Listing
    Route::get('view-ledger-listing', 'Admin\LedgerController@index')->name('admin.view-ledger-listing');
    Route::get('view-ledger-records', 'Admin\LedgerRecordController@index')->name('admin.view-ledger-records');
    Route::post('ledger-listing', 'Admin\LedgerController@ledgerListing')->name('admin.ledger-listing');
    Route::post('ledger-records-listing', 'Admin\LedgerRecordController@ledgerRecordListing')->name('admin.ledger-records-listing');
    Route::post('getHeadLedgerData', 'Admin\LedgerController@getHeadLedgerData')->name('admin.getHeadLedgerData');
    Route::post('getHeadLedgerUsersData', 'Admin\LedgerController@getHeadLedgerUsersData')->name('admin.getHeadLedgerUsersData');
    Route::post('get-members-ledger', 'Admin\LedgerController@getMembersDatas')->name('admin.ledger_data.get-members-ledger');
    Route::post('get-employee-ledger', 'Admin\LedgerController@getEmployeeDatas')->name('admin.ledger_data.get-employee-ledger');
    Route::post('get-associate-ledger', 'Admin\LedgerController@getAssociateDatas')->name('admin.ledger_data.get-associate-ledger');
    Route::post('get-rent-owner-ledger', 'Admin\LedgerController@getRentOwnerDatas')->name('admin.ledger_data.get-rent-owner-ledger');
    Route::post('get-vendor-ledger', 'Admin\LedgerController@getVendorDatas')->name('admin.ledger_data.get-vendor-ledger');
    Route::post('get-director-ledger', 'Admin\LedgerController@getDirectorDatas')->name('admin.ledger_data.get-director-ledger');
    Route::post('get-share-holder-ledger', 'Admin\LedgerController@getShareHolderDatas')->name('admin.ledger_data.get-share-holder-ledger');
    Route::post('get-customer-ledger', 'Admin\LedgerController@getCustomerDatas')->name('admin.ledger_data.get-customer-ledger');
    /********************** Cheque Management start **************************/
    Route::get('cheque', 'Admin\ChequeController@index')->name('admin.cheque_list');
    Route::get('cheque/add', 'Admin\ChequeController@add')->name('admin.cheque_add');
    Route::post('cheque-save', 'Admin\ChequeController@chequeSave')->name('admin.cheque_save');
    Route::post('getBankAccount', 'Admin\ChequeController@getBankAccount')->name('admin.bank_account_list');
    Route::post('cheque_listing', 'Admin\ChequeController@chequeListing')->name('admin.cheque_listing');
    Route::get('cheque/delete', 'Admin\ChequeController@delete')->name('admin.cheque_delete');
    Route::post('cheque-delete', 'Admin\ChequeController@chequeDelete')->name('admin.delete_cheque');
    Route::post('chequeExport', 'Admin\ExportController@chequeExport')->name('admin.cheque.export');
    Route::post('bank_cheque_list', 'Admin\ChequeController@bankChequeList')->name('admin.bank_cheque_list');
    Route::get('cheque/cancel', 'Admin\ChequeController@cancel')->name('admin.cheque_cancel');
    Route::post('cheque-cancel', 'Admin\ChequeController@chequeCancel')->name('admin.cancel_cheque');
    Route::get('cheque/cancel/{id}', 'Admin\ChequeController@chequeCancelView')->name('admin.cancel_cheque_view');
    Route::post('getBankAccountCheque', 'Admin\ChequeController@getBankAccountCheque')->name('admin.bank_account_cheque_list');
    Route::post('getChequeForCancel', 'Admin\ChequeController@getChequeForCancel')->name('admin.bank_cheque_list_cancel');
    Route::get('cheque/view/{id}', 'Admin\ChequeController@chequeView')->name('admin.cheque_view');
    /********************** Cheque received.cheque.exportManagement End **************************/
    /********************** Received Cheque Management start **************************/
    Route::get('received/cheque', 'Admin\ChequeController@receivedChequeList')->name('admin.received.cheque_list');
    Route::get('received/cheque/add', 'Admin\ChequeController@receivedChequeAdd')->name('admin.received.cheque_add');
    Route::get('received/cheque/edit/{id}', 'Admin\ChequeController@receivedChequeEdit')->name('admin.received.cheque_edit');
    Route::post('received/cheque_listing', 'Admin\ChequeController@receivedChequeListing')->name('admin.received.cheque_listing');
    Route::post('received/chequeExport', 'Admin\ExportController@receivedChequeExport')->name('admin.received.cheque.export');
    Route::get('received/cheque/approved/{id}', 'Admin\ChequeController@receivedChequeApproved')->name('admin.received.cheque_approved');
    Route::post('received/cheque/approvedcustom/', 'Admin\ChequeController@customReceivedChequeApproved')
        ->name('admin.received.cheque_approved_custom');
    Route::get('received/cheque/delete/{id}', 'Admin\ChequeController@receivedChequeDelete')->name('admin.received.cheque_delete');
    Route::post('received/cheque-save', 'Admin\ChequeController@receivedChequeSave')->name('admin.received.cheque_save');
    Route::post('received/cheque-update', 'Admin\ChequeController@receivedChequeUpdate')->name('admin.received.cheque_update');
    Route::post('received/receivedChequeExport', 'Admin\ExportController@receivedChequeExport')->name('admin.received.cheque.export');
    Route::get('received/cheque/view/{id}', 'Admin\ChequeController@receivedChequeView')->name('admin.received.cheque_view');
    /********************** Received Cheque Management End **************************/
    /***********************Investment update functionality *********************/
    Route::post('registerplan/approve_cheques', 'Admin\CommanController@approveReceivedCheque')->name('admin.approve_recived_cheque_lists');
    Route::post('registerplan/approve_cheques_new', 'Admin\CommanController@approveReceivedChequeNew')->name('admin.approve_recived_cheque_lists_new');
    Route::post('registerplan/approve_cheque_details', 'Admin\CommanController@approveReceivedChequeDetail')->name('admin.approve_cheque_details');
    Route::post('registerplan/assign_cheque_detail', 'Admin\CommanController@assignChequeDetail')->name('admin.assign_cheque_details');
    /***********************Investment update functionality *********************/
    /********************** Associate model updates start **************************/
    Route::get('associate-senior', 'Admin\AssociateController@senior')->name('admin.associate.senior_change');
    Route::post('associate-seniorsave', 'Admin\AssociateController@senior_save')->name('admin.associate.senior_save');
    Route::post('associterSeniorDataGet', 'Admin\AssociateController@associterSeniorDataGet')->name('admin.associterSeniorDataGet');
    Route::get('associatecollection', 'Admin\AssociateController@associateCommissionCollection')->name('admin.associate.collection');
    Route::post('associatecollectionlist', 'Admin\AssociateController@associateCollectionList')->name('admin.associate.collectionlist');
    Route::post('exportassociatecommissionCollectionlist', 'Admin\ExportController@exportAssociateCommissionCollection')->name('admin.associate.exportcommissionCollection');
    /**********************Associate model updates End **************************/
    /********************** Investment model updates start **************************/
    Route::get('investment-associate', 'Admin\InvestmentplanController@investmentAssociate')->name('admin.investment.associate_change');
    Route::post('investment-associatesave', 'Admin\InvestmentplanController@investmentAssociateSave')->name('admin.investment.associate_save');
    Route::post('investmentDataGet', 'Admin\InvestmentplanController@investmentDataGet')->name('admin.investmentDataGet');
    /********************** Investment model updates end **************************/
    /********************** HR Management start **************************/
    /********************** Designation start **************************/
    Route::get('hr/designation', 'Admin\HrManagement\DesignationController@index')->name('admin.hr.designation_list');
    Route::get('hr/designation/add', 'Admin\HrManagement\DesignationController@add')->name('admin.hr.designation_add');
    Route::get('hr/designation/edit/{id}', 'Admin\HrManagement\DesignationController@edit')->name('admin.hr.designation_edit');
    Route::get('hr/designation/detail/{id}', 'Admin\HrManagement\DesignationController@detail')->name('admin.hr.designation_detail');
    Route::post('hr/designation_listing', 'Admin\HrManagement\DesignationController@designationListing')->name('admin.hr.designation_listing');
    Route::get('hr/designation/delete/{id}', 'Admin\HrManagement\DesignationController@designationDelete')->name('admin.hr.designation_delete');
    Route::post('hr/designationExport', 'Admin\ExportController@designationExport')->name('admin.hr.designation_export');
    Route::post('hr/designation-save', 'Admin\HrManagement\DesignationController@designationSave')->name('admin.hr.designation_save');
    Route::post('hr/designation-update', 'Admin\HrManagement\DesignationController@designationUpdate')->name('admin.hr.designation_update');
    /********************** Designation End **************************/
    /****get Bank Name*/
    Route::post('getBankList', 'Admin\InvestmentplanController@getBankList')->name('admin.getBankList');
    /********************** Employee start **************************/
    Route::get('hr/employee', 'Admin\HrManagement\EmployeeController@index')->name('admin.hr.employee_list');
    Route::get('hr/employee/register', 'Admin\HrManagement\EmployeeController@add')->name('admin.hr.employee_add');
    Route::get('hr/employee/edit/{id}', 'Admin\HrManagement\EmployeeController@edit')->name('admin.hr.employee_edit');
    Route::get('hr/employee/detail/{id}', 'Admin\HrManagement\EmployeeController@detail')->name('admin.hr.employee_detail');
    Route::post('hr/employee_listing', 'Admin\HrManagement\EmployeeController@employeeListing')->name('admin.hr.employee_listing');
    Route::post('hr/employee-save', 'Admin\HrManagement\EmployeeController@employeeSave')->name('admin.hr.employee_save');
    Route::post('hr/employee-update', 'Admin\HrManagement\EmployeeController@employeeUpdate')->name('admin.hr.employee_update');
    Route::get('hr/employee/application', 'Admin\HrManagement\EmployeeController@applicationList')->name('admin.hr.employee_application_list');
    Route::post('hr/employee_application', 'Admin\HrManagement\EmployeeController@employeeApplicationListing')->name('admin.hr.employee_application');
    Route::post('hr/employee_application_delete', 'Admin\HrManagement\EmployeeController@employeeApplicationDelete')->name('admin.hr.delete_employee_application');
    Route::post('hr/employee_application_approve', 'Admin\HrManagement\EmployeeController@employeeApplicationApprove')->name('admin.hr.approve_employee_application');
    Route::post('hr/employee_application_reject', 'Admin\HrManagement\EmployeeController@employeeApplicationReject')->name('admin.hr.reject_employee_application');
    Route::post('designationDataGet', 'Admin\HrManagement\EmployeeController@designationDataGet')->name('admin.designationDataGet');
    Route::get('hr/employee/resign_letter/{id}', 'Admin\HrManagement\EmployeeController@resignLetter')->name('admin.hr.employee_resign_letter');
    Route::post('hr/employeeExport', 'Admin\ExportController@employeeExport')->name('admin.hr.employee_export');
    Route::post('hr/employeeApplicationExport', 'Admin\ExportController@employeeApplicationExport')->name('admin.hr.employee_application_export');
    Route::get('hr/employee/resign_request', 'Admin\HrManagement\EmployeeController@resignRequest')->name('admin.hr.employee_resign_request');
    Route::post('hr/resign_save', 'Admin\HrManagement\EmployeeController@resignRequestSave')->name('admin.hr.resign_save');
    Route::post('employeeDataGet', 'Admin\HrManagement\EmployeeController@employeeDataGet')->name('admin.employeeDataGet');
    Route::get('hr/employee/transfer_letter/{id}', 'Admin\HrManagement\EmployeeController@transferLetter')->name('admin.hr.employee_transfer_letter');
    Route::get('hr/employee/termination_letter/{id}', 'Admin\HrManagement\EmployeeController@terminationLetter')->name('admin.hr.employee_termination_letter');
    Route::get('hr/employee/terminate', 'Admin\HrManagement\EmployeeController@terminateRequest')->name('admin.hr.employee_terminate_request');
    Route::post('hr/terminate_save', 'Admin\HrManagement\EmployeeController@terminateRequestSave')->name('admin.hr.terminate_save');
    Route::get('hr/employee/transfer-request', 'Admin\HrManagement\EmployeeController@transferRequest')->name('admin.hr.employee_transfer_request');
    Route::post('hr/transfer_save', 'Admin\HrManagement\EmployeeController@transferRequestSave')->name('admin.hr.employ.transfer_save');
    Route::post('designationByCategory', 'Admin\HrManagement\EmployeeController@designationByCategory')->name('admin.designationByCategory');
    Route::get('hr/employee/transfer', 'Admin\HrManagement\EmployeeController@transferList')->name('admin.hr.employee_transfer_list');
    Route::post('hr/employee_transfer', 'Admin\HrManagement\EmployeeController@employeeTransferListing')->name('admin.hr.employee_transfer');
    Route::post('hr/employeeTransferExport', 'Admin\ExportController@employeeTransferExport')->name('admin.hr.employee_transfer_export');
    Route::get('hr/employee/transfer/detail/{id}', 'Admin\HrManagement\EmployeeController@transferDetail')->name('admin.hr.employee_transfer_detail');
    Route::post('trasnsferCount', 'Admin\HrManagement\EmployeeController@trasnsferCount')->name('admin.trasnsferCount');
    Route::get('hr/employee/application_edit/{id}', 'Admin\HrManagement\EmployeeController@application_edit')->name('admin.hr.application_edit');
    Route::post('delete_qualification', 'Admin\HrManagement\EmployeeController@delete_qualification')->name('admin.delete_qualification');
    Route::post('delete_diploma', 'Admin\HrManagement\EmployeeController@delete_diploma')->name('admin.delete_diploma');
    Route::post('delete_experience', 'Admin\HrManagement\EmployeeController@delete_experience')->name('admin.delete_experience');
    Route::get('hr/employee/application_print/{id}', 'Admin\HrManagement\EmployeeController@application_print')->name('admin.hr.application_print');
    Route::get('hr/employee/application_approve/{id}/{type}', 'Admin\HrManagement\EmployeeController@application_approve')->name('admin.hr.application_approve');
    Route::post('hr/employee-approve', 'Admin\HrManagement\EmployeeController@employee_approve')->name('admin.hr.employee_approve');
    Route::post('hr/employeeApplicationExportpdf', 'Admin\ExportController@employeeApplicationExportpdf')->name('admin.hr.employee_application_export_pdf');
    Route::post('employ-check-ssb-account', 'Admin\HrManagement\EmployeeController@checkSsbAccount')->name('admin.employ.check.ssb.account');
    Route::get('hr/employee/ledger/{id}', 'Admin\HrManagement\EmployeeController@ledgerEmployee')->name('admin.hr.employee.ledger_report');
    Route::get('hr/employ/ledger/{id}', 'Admin\HrManagement\EmployeeController@ledgerEmploy')->name('admin.hr.employee.ledger_report');
    Route::post('hr/employ/ledger/', 'Admin\HrManagement\EmployeeController@ledgerEmployListing')->name('admin.hr.employee.ledger_listing');
    Route::post('ledger-employee', 'Admin\HrManagement\EmployeeController@employeeLedgerListing')->name('admin.hr.employee.ledger_list');
    /********************** Employee End **************************/
    /********************** Salary start **************************/
    Route::match (['get', 'post'], 'hr/salary/payable', 'Admin\HrManagement\SalaryController@payable')->name('admin.hr.salary_payable');
    Route::post('designationByCategorySalary', 'Admin\HrManagement\SalaryController@designationByCategorySalary')->name('admin.designationByCategorySalary');
    Route::post('hr/salary/salary_generate', 'Admin\HrManagement\SalaryController@salary_generate')->name('admin.hr.salary_generate');
    Route::get('hr/salary/transfer/{id}', 'Admin\HrManagement\SalaryController@transfer')->name('admin.hr.transfer');
    Route::get('hr/salary/employ_leaser', 'Admin\HrManagement\SalaryController@employ_salary_leaser')->name('admin.hr.employ_salary_leaser');
    Route::post('hr/salary/transfer_next', 'Admin\HrManagement\SalaryController@transfer_next')->name('admin.hr.transfer_next');
    Route::post('hr/salary/transfer_save', 'Admin\HrManagement\SalaryController@transfer_save')->name('admin.hr.transfer_save');
    Route::get('hr/salary', 'Admin\HrManagement\SalaryController@index')->name('admin.hr.salary_leaser');
    Route::post('salary_leaser_listing', 'Admin\HrManagement\SalaryController@salary_leaser_listing')->name('admin.hr.salary_leaser_listing');
    Route::get('hr/salary/list/{id}', 'Admin\HrManagement\SalaryController@transferred')->name('admin.hr.transferred');
    Route::post('salary_listing', 'Admin\HrManagement\SalaryController@salary_listing')->name('admin.hr.salary_listing');
    Route::post('employ_salary_listing', 'Admin\HrManagement\SalaryController@employ_salary_listing')->name('admin.hr.employ_salary_listing');
    Route::get('hr/salary/advice/{id}', 'Admin\HrManagement\SalaryController@advice')->name('admin.hr.salary_advice');
    Route::post('hr/salary_list_export', 'Admin\ExportController@exportSalaryList')->name('admin.hr.salary_list_export');
    Route::post('hr/employ_salary_list_export', 'Admin\ExportController@exportEmploySalaryList')->name('admin.hr.employ_salary_list_export');
    Route::get('hr/salary/transfer-advance/{id}/{l}', 'Admin\HrManagement\SalaryController@advancePayble')->name('admin.rent.rent_transfer_advance');
    Route::post('salary_transfer_advance_save', 'Admin\HrManagement\SalaryController@salaryTransferAdvanceSave')->name('admin.hr.salary_transfer_advance_save');
    Route::post('hr/exportSalaryTransfer', 'Admin\ExportController@exportSalaryTransfer')->name('admin.hr.salary.exportSalaryTransfer');
    Route::post('hr/salary_ledger_export', 'Admin\ExportController@exportSalaryLedger')->name('admin.hr.salary_ledger_export');
    Route::post('salary_ledger_delete', 'Admin\HrManagement\SalaryController@ledgerDelete')->name('admin.hr.salary.salary_ledger_delete');
    /********************** Salary End **************************/
    /********************** HR Management End **************************/
    /********************** Notice Board Start **************************/
    Route::get('notice-board', 'Admin\NoticeboardController@index')->name('admin.noticeboard');
    Route::get('notice-board-create', 'Admin\NoticeboardController@create')->name('admin.noticeboard.create');
    Route::post('notice-board-listing', 'Admin\NoticeboardController@listing')->name('admin.notice.listing');
    Route::post('notice-board-save', 'Admin\NoticeboardController@store')->name('admin.notice.store');
    Route::post('notice-board-delete', 'Admin\NoticeboardController@destroy')->name('admin.notice.delete');
    Route::post('notice-board-status-change', 'Admin\NoticeboardController@update_status')->name('admin.notice.status_change');
    /********************** Notice Board End **************************/
    /**************************** Associate Changes Start ***************/
    Route::post('inactiveassociate_list', 'Admin\AssociateController@inactiveAssociateListing')->name('admin.inactive_associate_listing');
    Route::post('exportassociateInactivelist', 'Admin\ExportController@exportInactiveAssociate')->name('admin.inactive_associate.export');
    Route::get('associate/loan-commission-detail/{id}', 'Admin\AssociateController@associateCommissionDetailLoan')->name('admin.associate.commission.detail_loan');
    Route::post('associatecommissionDetaillistLoan', 'Admin\AssociateController@associateCommissionDetailListLoan')->name('admin.associate.commissionDetaillistLoan');
    Route::post('exportassociatecommissionDetaillistLoan', 'Admin\ExportController@exportAssociateCommissionDetailLoan')->name('admin.associate.exportcommissionDetailLoan');
    /**************************** Associate Changes End ***************/
    /**************************** Balance Sheet ***************/
    Route::get('balance-sheet/', 'Admin\BalanceSheetController@index')->name('admin.balance.sheet');
    Route::get('detailed/balance-sheet', 'Admin\BalanceSheetController@detailedReportBalanceSheet')->name('admin.detail.balance.sheet');
    Route::post('balance_sheet_fillter', 'Admin\BalanceSheetController@balanceSheetAjax')->name('admin.balance_sheet_fillter');
    Route::get('balance-sheet/detail/{id1}/{id2}', 'Admin\BalanceSheetController@labelTwo')->name('admin.balance-sheet.labelTwo');
    Route::get('balance-sheet/current_liability/{id1}', 'Admin\BalanceSheetController@current_liabilityDetail')->name('admin.balance-sheet.curr_liability_detail');
    Route::get('balance-sheet/current_liability/branch_wise/{head_id}/{label}', 'Admin\BalanceSheetController@current_liabilityDetailBranchWise')->name('admin.balance-sheet.curr_liability_detailBranchWise');
    Route::get('balance-sheet/current_liability/bank_wise/{head_id}/{label}', 'Admin\BalanceSheetController@bankwiseTransaction')->name('admin.balance-sheet.bank_wise_transaction');
    Route::post('balance-sheet/current_liability/bank_wise/listing', 'Admin\BalanceSheetController@bankwiseTransactionList')->name('admin.balance-sheet.bank_wise_transaction_list');
    Route::post('balance-sheet/current_liability/branch_wise_list', 'Admin\BalanceSheetController@current_liabilityDetailBranchWiseListing')->name('admin.balance-sheet.curr_liability_detailBranchWise_listing');
    Route::get('balance-sheet/current_liability/branch_wise/rent_creditors', 'Admin\BalanceSheetController@rent_creditors_report')->name('admin.balance-sheet.branch_wise.rent_creditors');
    Route::post('balance-sheet/get_rent_creditors_report_listing', 'Admin\BalanceSheetController@get_rent_creditors_report_listing')->name('admin.balance-sheet.get_rent_creditors_report_listing');
    Route::get('balance-sheet/current_liability/branch_wise/salary_creditors', 'Admin\BalanceSheetController@salary_creditors_report')->name('admin.balance-sheet.branch_wise.salary_creditors');
    Route::post('loan-list-export', 'Branch\ExportController@loan_list_export')->name('branch.loan_list_export');
    Route::post('balance-sheet/get_salary_creditors_report_listing', 'Admin\BalanceSheetController@get_salary_creditors_report_listing')->name('admin.balance-sheet.get_salary_creditors_report_listing');
    Route::get('balance-sheet/current_liability/branch_wise/case_in_hand', 'Admin\BalanceSheetController@case_in_hand_report')->name('admin.balance-sheet.branch_wise.case_in_hand');
    Route::post('balance-sheet/get_case_in_hand_report_listing', 'Admin\BalanceSheetController@get_case_in_hand_report_listing')->name('admin.balance-sheet.get_case_in_hand_report_listing');
    Route::get('balance-sheet/current_liability/branch_wise/fixed_assets', 'Admin\BalanceSheetController@fixed_assets_report')->name('admin.balance-sheet.branch_wise.fixed_assets');
    Route::post('balance-sheet/get_fixed_assets_report_listing', 'Admin\BalanceSheetController@get_fixed_assets_report_listing')->name('admin.balance-sheet.get_fixed_assets_report_listing');
    Route::get('balance-sheet/current_liability/branch_wise/advance_payment', 'Admin\BalanceSheetController@advance_payment_report')->name('admin.balance-sheet.branch_wise.advance_payment');
    Route::post('balance-sheet/get_advance_payment_report_listing', 'Admin\BalanceSheetController@get_advance_payment_report_listing')->name('admin.balance-sheet.get_advance_payment_report_listing');
    Route::get('balance-sheet/current_liability/branch_wise/membership_fee', 'Admin\BalanceSheetController@membership_fee_report')->name('admin.balance-sheet.branch_wise.membership_fee_report');
    Route::post('balance-sheet/get_member_ship_report_data', 'Admin\BalanceSheetController@get_member_ship_report_data')->name('admin.balance-sheet.get_member_ship_report_data');
    Route::get('balance-sheet/current_liability/branch_wise/fixed_deposite', 'Admin\BalanceSheetController@fixed_deposite_report')->name('admin.balance-sheet.branch_wise.fixed_deposite_report');
    Route::post('balance-sheet/get_fixed_deposite_report_data', 'Admin\BalanceSheetController@get_fixed_deposite_report_data')->name('admin.balance-sheet.get_fixed_deposite_report_data');
    Route::get('balance-sheet/current_liability/branch_wise/report', 'Admin\BalanceSheetController@tds_report')->name('admin.balance-sheet.branch_wise.tds_report_report');
    Route::post('balance-sheet/get_tds_report_data', 'Admin\BalanceSheetController@get_tds_report_data')->name('admin.balance-sheet.get_tds_report_data');
    Route::get('balance-sheet/current_liability/branch_wise/saving', 'Admin\BalanceSheetController@saving')->name('admin.balance-sheet.branch_wise.tds_report_report');
    Route::get('balance-sheet/current_liability/branch_wise/loan_asset', 'Admin\BalanceSheetController@loan_asset')->name('admin.balance-sheet.branch_wise.loan_asset');
    Route::post('balance-sheet/saving_listing', 'Admin\BalanceSheetController@saving_listing')->name('admin.balance-sheet.saving_listing');
    Route::post('balance-sheet/loan_listing', 'Admin\BalanceSheetController@get_loan_asset_data')->name('admin.balance-sheet.loan_asset_listing');
    Route::get('balance-sheet/current_liability/branch_wise/loan_asset', 'Admin\BalanceSheetController@loan_asset')->name('admin.balance-sheet.branch_wise.loan_asset');
    Route::get('balance-sheet/getAllHeads', 'Admin\BalanceSheetController@getHeads')->name('admin.balance-sheet.getAllHeads');
    Route::post('balanceSheetReportExport', 'Admin\ExportController@balanceSheetReportExport')->name('admin.balance_sheet.report.export');
    Route::post('balanceSheetReportExportDetail', 'Admin\ExportController@balanceSheetReportExportDetail')->name('admin.balance_sheet_details.report.export');
    Route::post('balanceSheetReportBranchWiseLoanAsset', 'Admin\ExportController@balanceSheetReportBranchWiseLoanAsset')->name('admin.balance_sheet.branch_wise.loan_asset.export');
    Route::post('balanceSheetReportBranchWise', 'Admin\ExportController@balanceSheetReportBranchWise')->name('admin.balance_sheet.branch_wise.export');
    Route::post('balanceSheetReportDetailsExport', 'Admin\ExportController@balanceSheetReportDetailsExport')->name('admin.balance_sheet.report_details.export');
    Route::post('balanceSheetReportBranchWiseTds', 'Admin\ExportController@balanceSheetReportBranchWiseTds')->name('admin.balance_sheet.branch_wise.tds.export');
    Route::post('balanceSheetReportBranchWiseCashInHand', 'Admin\ExportController@balanceSheetReportBranchWiseCashInHand')->name('admin.balance_sheet.branch_wise.case_in_hand.export');
    Route::post('balanceSheetReportBranchWiseAdvancePayment', 'Admin\ExportController@balanceSheetReportBranchWiseAdvancePayment')->name('admin.balance_sheet.branch_wise.advance_payment.export');
    Route::post('balanceSheetReportBranchWiseMembership', 'Admin\ExportController@balanceSheetReportBranchWiseMembership')->name('admin.balance_sheet.branch_wise.membership.export');
    Route::post('balanceSheetReportBranchWiseSaving', 'Admin\ExportController@balanceSheetReportBranchWiseSaving')->name('admin.balance_sheet.branch_wise.saving.export');
    Route::post('balanceSheetReportBranchWiseDeposite', 'Admin\ExportController@balanceSheetReportBranchWiseDeposite')->name('admin.balance_sheet.branch_wise.deposite.export');
    Route::post('balanceSheetReportBranchWiseRent', 'Admin\ExportController@balanceSheetReportBranchWiseRent')->name('admin.balance_sheet.branch_wise.rent.export');
    Route::post('balanceSheetReportBankwise', 'Admin\ExportController@balanceSheetReportBankWise')->name('admin.balance_sheet.bank_wise.export');
    Route::get('balance-sheet/detail_ledger', 'Admin\BalanceSheetController@loan_from_bank_view')->name('admin.balance-sheet.branch_wise.loan_from_bank');
    Route::post('balanceSheetReportLoanFromBank', 'Admin\BalanceSheetController@loan_from_bank')->name('admin.balance-sheet.loan_from_bank_detail_ledger');
    Route::post('balance_sheet_fixedAssets_export', 'Admin\ExportController@get_fixed_assets_report')->name('admin.balance_sheet_fixedAssets_export');
    /**************************** Balance Sheet ***************/
    /**************************** Balance Sheet ***************/
    Route::post('bankChkbalance', 'Admin\CommanController@bankChkbalance')->name('admin.bankChkbalance');
    Route::post('branchChkbalance', 'Admin\CommanController@branchChkbalance')->name('admin.branchChkbalance');
    Route::post('ssbDateBalanceChk', 'Admin\CommanController@ssbDateBalanceChk')->name('admin.ssbDateBalanceChk');
    Route::post('directorBalanceDate', 'Admin\CommanController@directorBalanceDate')->name('admin.directorBalanceDate');
    Route::post('getsubhead', 'Admin\CommanController@getSubHead')->name('admin.account_head_get');
    /**************************Bill Management Start***************************************************************/
    Route::get('bill_listing', 'Admin\BillManagement\BillController@index')->name('admin.bill_management.bill');
    Route::post('bill_listing_record', 'Admin\BillManagement\BillController@bill_listing')->name('admin.bill_management.bill_listing');
    Route::post('bill_vendor', 'Admin\BillManagement\BillController@vendor_bill')->name('admin.bill_management.vendor_bill');
    Route::post('bill_detail', 'Admin\BillManagement\BillController@getBillDetails')->name('admin.bill_management.getBillDetails');
    Route::post('export_bill_report', 'Admin\ExportController@export_bill_listing')->name('admin.bill_management.export_bill_listing');
    Route::get('print_bill', 'Admin\BillManagement\BillController@print_bill')->name('admin.bill_management.print_bill');
    /****************************Bill Management End*************************************************************/
    /**************Voucher start *************************/
    Route::get('voucher', 'Admin\Voucher\VoucherController@index')->name('admin.voucher');
    Route::post('get-member-details', 'Admin\Voucher\VoucherController@getMemberDetails')->name('admin.voucher.memberdetails');
    Route::get('/voucher/create', 'Admin\Voucher\VoucherController@create')->name('admin.voucher.create');
    Route::post('/voucher/save', 'Admin\Voucher\VoucherController@save')->name('admin.voucher.save');
    Route::get('voucher/print/{id}', 'Admin\Voucher\VoucherController@print')->name('admin.voucher.print');
    Route::post('/voucher_listing', 'Admin\Voucher\VoucherController@voucherList')->name('admin.voucher.lists');
    Route::post('voucher_export', 'Admin\ExportController@voucherExport')->name('admin.voucher.exportLists');
    /**************Voucher end *************************/
    //--------------------  head query -------------------------------
    Route::get('ssbUpdate/ssb_data', 'Admin\ImplementHeadController@ssb_data')->name('admin.ssb_data');
    Route::get('accountHead/member_register_head', 'Admin\AccountHeadImplementController@member_register_head')->name('admin.member_register_head');
    Route::get('accountHead/investment_register_daybook_cash', 'Admin\AccountHeadImplementController@investment_register_daybook_cash')->name('admin.investment_register_daybook_cash');
    Route::get('accountHead/investment_renew_daybook_cash', 'Admin\AccountHeadImplementController@investment_renew_daybook_cash')->name('admin.investment_renew_daybook_cash');
    Route::get('accountHead/investment_renew_daybook_other', 'Admin\AccountHeadImplementController@investment_renew_daybook_other')->name('admin.investment_renew_daybook_other');
    Route::get('accountHead/ssb_register_cash', 'Admin\AccountHeadImplementController@ssb_register_cash')->name('admin.ssb_register_cash');
    Route::get('accountHead/ssb_deposit_cash', 'Admin\AccountHeadImplementController@ssb_deposit_cash')->name('admin.ssb_deposit_cash');
    Route::get('accountHead/associate_commission_gv_ssb', 'Admin\AccountHeadImplementController@associate_commission_gv_ssb')->name('admin.associate_commission_gv_ssb');
    Route::get('accountHead/ssb_withdraw_cash', 'Admin\AccountHeadImplementController@ssb_withdraw_cash')->name('admin.ssb_withdraw_cash');
    Route::get('accountHead/branch_balance_update_cash', 'Admin\AccountHeadImplementController@branch_balance_update_cash')->name('admin.branch_balance_update_cash');
    Route::get('accountHead/branch_balance_update_closing', 'Admin\AccountHeadImplementController@branch_balance_update_closing')->name('admin.branch_balance_update_closing');
    Route::get('accountHead/bank_balance_update', 'Admin\AccountHeadImplementController@bank_balance_update')->name('admin.bank_balance_update');
    Route::get('accountHead/branch_balance_update', 'Admin\AccountHeadImplementController@branch_balance_update')->name('admin.branch_balance_update');
    Route::get('amount/test', 'Admin\TestController@test')->name('admin.test');
    Route::get('insert/branch_daybook_record', 'Admin\TestController@insert_data_branch_daybook')->name('admin.insert_data_branch_daybook');
    Route::get('get-bank-loan-transactions', 'Admin\TestController@getBankLoanTransactions');
    Route::get('insertTransactions', 'Admin\TestController@insertTransactions');
    Route::get('updateBranch', 'Admin\TestController@update_branch');
    Route::get('updateBranchCash', 'Admin\TestController@updateBranchCash');
    Route::post('update_branch_cash_daywise', 'Admin\TestController@update_branch_cash_daywise')->name('admin.update_branch_cash_daywise');
    Route::get('update_branch_cash_daywise2', 'Admin\TestController@update_branch_cash_daywise2');
    Route::post('update_bank_balance_daywise', 'Admin\TestController@update_bank_balance_daywise')->name('admin.update_bank_balance_daywise');
    Route::get('getreinvestRecords', 'Admin\TestController@reinvestRecords');
    Route::get('getInvestmentMonthlyInterestDeposit', 'Admin\TestController@getInvestmentMonthlyInterestDeposit');
    Route::get('update_emergency_maturity_type_amount_branch_daybook', 'Admin\TestController@update_emergency_maturity_type_amount_branch_daybook');
    Route::get('update_eli_transaction_all_transaction', 'Admin\TestController@update_eli_transaction_all_transaction');
    Route::get('update_transaction_date_all_transaction', 'Admin\TestController@update_transaction_date_all_transaction');
    Route::get('insert_saving_record', 'Admin\TestController@insert_saving_record');
    Route::get('correct_loan_payment_mode', 'Admin\TestController@correct_loan_payment_mode');
    Route::get('getAccountNumberdaybook', 'Admin\TestController@getAccountNumberdaybook');
    Route::get('update_parent_auto_id', 'Admin\TestController@update_parent_auto_id');
    Route::get('update_maturity_amount', 'Admin\TestController@update_maturity_amount');
    Route::get('update_cash_in_hand', 'Admin\TestController@update_cash_in_hand');
    Route::get('add_file_charge_type', 'Admin\TestController@add_file_charge_type');
    Route::get('insert_transaction_type', 'Admin\TestController@insert_transaction_type');
    Route::get('update_reinvest_date', 'Admin\TestController@update_reinvest_date');
    Route::get('get_ssb_ac_more_than_one_transaction', 'Admin\TestController@get_ssb_ac_more_than_one_transaction');
    Route::get('get_record_not_in_all_transaction', 'Admin\TestController@get_record_not_in_all_transaction');
    Route::get('insert_loan_emi_record', 'Admin\TestController@insert_loan_emi_record');
    Route::get('deleteLoanEmi', 'Admin\TestController@deleteLoanEmi');
    Route::get('insertMI_record', 'Admin\TestController@insertMI_record');
    Route::get('get_emergancy_maturity_account', 'Admin\TestController@get_emergancy_maturity_account');
    Route::get('insert_saving_withrawal_record', 'Admin\TestController@insert_saving_withrawal_record');
    Route::get('insert_file_charge_in_cash_head', 'Admin\TestController@insert_file_charge_in_cash_head');
    Route::get('update_loan_amount', 'Admin\TestController@update_loan_amount');
    Route::get('update_grploan_amount', 'Admin\TestController@update_grploan_amount');
    Route::get('insert_mb_interest_in_deposite', 'Admin\TestController@insert_mb_interest_in_deposite');
    Route::get('update_stationary_payment_mode', 'Admin\TestController@update_stationary_payment_mode');
    Route::get('update_emi_transaction_date', 'Admin\TestController@update_emi_transaction_date');
    Route::get('update_emi_transaction_delete_status', 'Admin\TestController@update_emi_transaction_delete_status');
    Route::get('update_same_daybook_entry_date', 'Admin\TestController@update_same_daybook_entry_date');
    Route::get('update_daybook_ssb_type', 'Admin\TestController@update_daybook_ssb_type');
    Route::get('getMaturityEliInvestment2', 'Admin\TestController@getMaturityEliInvestment2');
    Route::get('updateRecordFDAndFFD', 'Admin\TestController@updateRecordFDAndFFD');
    Route::get('file_charge_in_branch', 'Admin\TestController@file_charge_in_branch');
    Route::get('insert_cash_in_hand_withdrawal', 'Admin\TestController@insert_cash_in_hand_withdrawal');
    Route::get('update_expense_head_date', 'Admin\TestController@update_expense_head_date');
    Route::get('update_salary_branch_id', 'Admin\TestController@update_salary_branch_id');
    Route::get('deleteStationaryanddateChangeDummyBranch', 'Admin\TestController@deleteStationaryanddateChangeDummyBranch');
    Route::get('update_grp_loan_type', 'Admin\TestController@update_grp_loan_type');
    Route::get('updateTransferdateFundTransfer', 'Admin\TestController@updateTransferdateFundTransfer');
    Route::get('/sendAmountSher', 'Admin\TestController@sendAmountSher');
    Route::get('/updateamountLoan', 'Admin\TestController@updateamountLoan');
    Route::get('/insertfileCHrage', 'Admin\TestController@insertfileCHrage');
    Route::get('/updateemiDate', 'Admin\TestController@updateemiDate');
    Route::get('/updatessbAmount', 'Admin\TestController@updatessbAmount');
    Route::get('checkstatus', 'Admin\TestController@checkstatus');
    Route::get('updateTimeSSb', 'Admin\TestController@updateTimeSSb');
    Route::get('update_BRANHC_samraddh', 'Admin\TestController@update_BRANHC_samraddh');
    Route::get('insertBilldate', 'Admin\TestController@insertBilldate');
    Route::get('updateChildHead', 'Admin\TestController@insertBilldate');
    Route::get('updateBalanceDayWiseInhead', 'Admin\TestController@updateBalanceDayWiseInhead');
    Route::get('updateChildHead', 'Admin\TestController@updateChildHead');
    Route::get('outstandingAmount_update', 'Admin\TestController@outstandingAmount_update');
    Route::get('updateBalanceSheetInterest', 'Admin\TestController@updateBalanceSheetInterest');
    Route::get('outstandingAmount_updateDaily', 'Admin\TestController@outstandingAmount_updateDaily');
    Route::get('outstandingAmount_updateWeekly', 'Admin\TestController@outstandingAmount_updateWeekly');
    Route::get('updateOutstandingGrploan', 'Admin\TestController@updateOutstandingGrploan');
    Route::get('updateOutstandingGrploanDaily', 'Admin\TestController@updateOutstandingGrploanDaily');
    Route::get('updateloanAmountOfinsuranmce', 'Admin\TestController@updateloanAmountOfinsuranmce');
    Route::get('updateInterestOutstanding', 'Admin\TestController@updateInterestOutstanding');
    Route::get('updateDaybookDAte', 'Admin\TestController@updateDaybookDAte');
    Route::get('deleteStationaryCharges', 'Admin\TestController@deleteStationaryCharges');
    Route::get('updateInsurance', 'Admin\TestController@updateInsurance');
    Route::get('mismatchrecord', 'Admin\TestController@mismatchrecord');
    Route::get('bank_balance_update', 'Admin\TestController@bank_balance_update')->name('admin.bank-balance.update');
    Route::get('cash_balance_update', 'Admin\TestController@cash_balance_update')->name('admin.cash-balance.update');
    Route::get('updateDaybookloan', 'Admin\TestController@updateDaybookloan');
    Route::get('updatebankIdloan', 'Admin\TestController@updatebankIdloan');
    Route::get('getMismAtchDate', 'Admin\TestController@getMismAtchDate');
    Route::get('getMismAtchDate2', 'Admin\TestController@getMismAtchDate2');
    Route::get('update_emi_transaction_date2', 'Admin\TestController@update_emi_transaction_date2');
    Route::get('update_emi_transaction_date3', 'Admin\TestController@update_emi_transaction_date3');
    Route::get('update_emi_transaction_date4', 'Admin\TestController@update_emi_transaction_date4');
    Route::get('tdsData', 'Admin\TestController@tdsData');
    Route::get('maturityRecords', 'Admin\TestController@maturityRecords');
    Route::get('update_bank_balance_daywise2', 'Admin\TestController@update_bank_balance_daywise2');
    Route::get('updateInvestmentDate', 'Admin\TestController@updateInvestmentDate');
    Route::get('interestRecord', 'Admin\TestController@interestRecord');
    Route::get('updateSalaryDate/{id}', 'Admin\TestController@updateSalaryDate');
    Route::get('ROIAmountUpdate', 'Admin\TestController@ROIAmountUpdate');
    Route::get('stationatyChargeInsert', 'Admin\TestController@insertStationaryCharge');
    Route::get('getNotMatche', 'Admin\TestController@getNotMatche');
    Route::get('updateNotMatche', 'Admin\TestController@updateNotMatche');
    Route::get('updateNotMatche2', 'Admin\TestController@updateNotMatche2');
    Route::get('deleteEmiDataEmiLoan', 'Admin\TestController@deleteEmiDataEmiLoan');
    Route::get('deleteEmiDataLoanDaybook', 'Admin\TestController@deleteEmiDataLoanDaybook');
    Route::get('executeCron', 'Admin\TestController@executeCron');
    Route::get('updateDaybookTransaction', 'Admin\TestController@updateDaybookTransaction');
    //----------------------  head query -------------------------------------------
    //--------------------------------- TDS Deposit  --------------------
    Route::get('tds_deposit', 'Admin\TdsdepositController@index')->name('admin.tds_deposit');
    Route::get('tds_deposit/create', 'Admin\TdsdepositController@create')->name('admin.create.tds_deposit');
    Route::post('tds_deposit_listing', 'Admin\TdsdepositController@tds_deposite_listing')->name('admin.tds_deposite_listing');
    Route::post('get_tds_detail', 'Admin\TdsdepositController@get_tds_deposite_detail')->name('admin.get_tds_deposite_detail');
    Route::post('tds_deposit_save', 'Admin\TdsdepositController@save')->name('admin.tds_deposit_save');
    Route::post('tds_deposit_export', 'Admin\ExportController@tds_deposite_export')->name('admin.tds_deposite_export');
    //--------------------------------- TDS Deposit  --------------------
    // ------------------------- Associate App Functionality -----------------------
    Route::get('associate-app-status', 'Admin\AssociateAppController@appStatus')->name('admin.associate.status_app');
    Route::post('associate-app-statussave', 'Admin\AssociateAppController@status_save')->name('admin.associate.status_save_app');
    Route::post('getAssociateDetailAllApp', 'Admin\AssociateAppController@getAssociateDataAll')->name('admin.associate_dataGetAll_app');
    Route::post('app_inactiveassociate_list', 'Admin\AssociateAppController@inactiveAssociateListing')->name('admin.app_inactive_associate_listing');
    Route::post('app_exportassociateInactivelist', 'Admin\ExportController@exportAppInactiveAssociate')->name('admin.app_inactive_associate.export');
    Route::get('associate-app-transaction', 'Admin\AssociateAppController@transactionDetail')->name('admin.associate.app_transaction');
    Route::post('app_transaction_list', 'Admin\AssociateAppController@transactionList')->name('admin.associate.app_transaction_list');
    Route::get('associate-app-permission', 'Admin\AssociateAppController@permission')->name('admin.associate.app_permission');
    Route::post('app_permission_save', 'Admin\AssociateAppController@permissionSave')->name('admin.associate.app_permission_save');
    // ------------------------- Associate App Functionality End -----------------------
    // ------------------------- Dublicate Daybook -----------------------
    Route::get('report/day_book_duplicate', 'Admin\Report\DayBookDublicateController@day_bookReport')->name('admin.report.day_book_dublicate');
    Route::post('report/day_book_dublicate', 'Admin\Report\ReportController@day_filterbookReport')->name('admin.report.filtered_day_book_dublicate');
    Route::post('daybookReportExportDublicate', 'Admin\ExportController@daybookReportExportDublicate')->name('admin.daybook.report.exportDublicate');
    Route::get('print/report/day_book_duplicate', 'Admin\Report\DayBookDublicateController@print_day_bookReport')->name('admin.print.report.day_book_dublicate');
    Route::post('report/day_book_list_dublicate', 'Admin\Report\DayBookDublicateController@day_filterbookReport')->name('admin.report.day_booklisting_dublicate');
    Route::post('dublicate_daybook/transaction_list', 'Admin\Report\DayBookDublicateController@transaction_list')->name('admin.dublicate_daybook.transaction_listing');
    // ------------------------- Dublicate Daybook End -----------------------
    // Balance sheet export
    Route::post('balanceSheetReportExport', 'Admin\ExportController@balanceSheetReportExport')->name('admin.balance_sheet.report.export');
    Route::post('balanceSheetReportExportDetail', 'Admin\ExportController@balanceSheetReportExportDetail')->name('admin.balance_sheet_details.report.export');
    Route::post('balanceSheetReportBranchWise', 'Admin\ExportController@balanceSheetReportBranchWise')->name('admin.balance_sheet.branch_wise.export');
    Route::post('balanceSheetReportDetailsExport', 'Admin\ExportController@balanceSheetReportDetailsExport')->name('admin.balance_sheet.report_details.export');
    Route::post('balanceSheetReportBranchWiseTds', 'Admin\ExportController@balanceSheetReportBranchWiseTds')->name('admin.balance_sheet.branch_wise.tds.export');
    Route::post('balanceSheetReportBranchWiseCashInHand', 'Admin\ExportController@balanceSheetReportBranchWiseCashInHand')->name('admin.balance_sheet.branch_wise.case_in_hand.export');
    Route::post('balanceSheetReportBranchWiseAdvancePayment', 'Admin\ExportController@balanceSheetReportBranchWiseAdvancePayment')->name('admin.balance_sheet.branch_wise.advance_payment.export');
    Route::post('balanceSheetReportBranchWiseMembership', 'Admin\ExportController@balanceSheetReportBranchWiseMembership')->name('admin.balance_sheet.branch_wise.membership.export');
    Route::post('balanceSheetReportBranchWiseSaving', 'Admin\ExportController@balanceSheetReportBranchWiseSaving')->name('admin.balance_sheet.branch_wise.saving.export');
    Route::post('balanceSheetReportBranchWiseDeposite', 'Admin\ExportController@balanceSheetReportBranchWiseDeposite')->name('admin.balance_sheet.branch_wise.deposite.export');
    Route::post('balanceSheetReportBranchWiseRent', 'Admin\ExportController@balanceSheetReportBranchWiseRent')->name('admin.balance_sheet.branch_wise.rent.export');
    //----------------------  head query -------------------------------------------
    /************** Vendor Management Start *************************/
    /************ Category Start ****************/
    Route::get('vendor/category', 'Admin\vendorManagement\VendorCategoryController@index')->name('admin.vendor.category');
    Route::get('/bill/vendordate', 'Admin\vendorManagement\BillCreateController@vendordate')->name('admin.vendor.vendor_create_date');
    Route::post('/vendor/category/listing', 'Admin\vendorManagement\VendorCategoryController@list')->name('admin.vendor.category.lists');
    Route::get('/vendor/category/add', 'Admin\vendorManagement\VendorCategoryController@add')->name('admin.vendor.category.add');
    Route::post('/vendor/category/save', 'Admin\vendorManagement\VendorCategoryController@categorySave')->name('admin.vendor.category.save');
    Route::get('/vendor/category/edit/{id}', 'Admin\vendorManagement\VendorCategoryController@edit')->name('admin.vendor.category.edit');
    Route::post('/vendor/category/update', 'Admin\vendorManagement\VendorCategoryController@categoryUpdate')->name('admin.vendor.category.update');
    Route::get('/vendor/category/delete/{id}', 'Admin\vendorManagement\VendorCategoryController@categoryDelete')->name('admin.vendor.category.delete');
    Route::post('vendor/category_export', 'Admin\ExportController@vendorCategoryExport')->name('admin.vendor.category.export');
    /************ Category End ****************/
    /************ Vendor Start ****************/
    Route::get('vendor', 'Admin\vendorManagement\VendorController@index')->name('admin.vendor');
    Route::post('/vendor/listing', 'Admin\vendorManagement\VendorController@list')->name('admin.vendor.lists');
    Route::post('/vendor/listsCustomer', 'Admin\vendorManagement\VendorController@listsCustomer')->name('admin.vendor.listsCustomer');
    Route::post('/vendor/employee_listing', 'Admin\vendorManagement\VendorController@employeeListing')->name('admin.vendor.employee_listing');
    Route::post('/vendor/rent_listing', 'Admin\vendorManagement\VendorController@rentListing')->name('admin.vendor.rent_listing');
    Route::post('/vendor/associate_listing', 'Admin\vendorManagement\VendorController@associateListing')->name('admin.vendor.associate_listing');
    Route::post('vendor/export', 'Admin\ExportController@vendorExport')->name('admin.vendor.export');
    Route::post('rent/export', 'Admin\ExportController@rentVendorExport')->name('admin.vendor_rent.export');
    Route::post('salary/export', 'Admin\ExportController@salaryVendorExport')->name('admin.vendor_salary.export');
    Route::post('associate/export', 'Admin\ExportController@associateVendorExport')->name('admin.vendor_associate.export');
    Route::get('/vendor/add', 'Admin\vendorManagement\VendorController@add')->name('admin.vendor.add');
    Route::post('/vendor/save', 'Admin\vendorManagement\VendorController@save')->name('admin.vendor.save');
    Route::get('/vendor/edit/{id}', 'Admin\vendorManagement\VendorController@edit')->name('admin.vendor.edit');
    Route::post('/vendor/update', 'Admin\vendorManagement\VendorController@update')->name('admin.vendor.update');
    Route::get('/vendor/detail/{id}', 'Admin\vendorManagement\VendorController@detail')->name('admin.vendor.detail');
    Route::post('/vendor/transaction_list', 'Admin\vendorManagement\VendorController@transaction_list')->name('admin.vendor.transaction_list');
    Route::post('/vendor_category_add', 'Admin\vendorManagement\VendorController@categorySave')->name('admin.vendor_category_add');
    Route::get('/vendor/status/{id}/{id2}', 'Admin\vendorManagement\VendorController@changeStatus')->name('admin.vendor.status_change');
    Route::get('/vendor/delete/{id}', 'Admin\vendorManagement\VendorController@delete')->name('admin.vendor.delete');
    Route::get('/vendor/detail_rent/{id}', 'Admin\vendorManagement\VendorController@detail_rent')->name('admin.vendor.detail_rent');
    Route::get('/vendor/detail_employee/{id}', 'Admin\vendorManagement\VendorController@detail_salary')->name('admin.vendor.detail_salary');
    Route::post('/vendor/transaction_detail/', 'Admin\vendorManagement\VendorController@vendor_transaction')->name('admin.vendor.transaction.lists');
    Route::post('vendor/transaction/export', 'Admin\ExportController@VendorTransactionExport')->name('admin.vendor.transaction.export');
    Route::get('/vendor/associate_detail/{id}', 'Admin\vendorManagement\VendorController@associate_detail')->name('admin.vendor.associate_detail');
    Route::get('/credit_node/transaction', 'Admin\vendorManagement\VendorController@credit_node_transaction')->name('admin.credit_node_transaction');
    Route::post('/credit_node/transaction_list', 'Admin\vendorManagement\VendorController@credit_node_transaction_list')->name('admin.credit_node_transaction.list');
    Route::get('/advance-transaction', 'Admin\vendorManagement\VendorController@advance_transaction')->name('admin.advance_transaction');
    Route::post('/advance/transaction_list', 'Admin\vendorManagement\VendorController@advance_transaction_list')->name('admin.advance_transaction.list');
    Route::get('/jv-transaction', 'Admin\vendorManagement\VendorController@jv_transaction')->name('admin.jv_transaction');
    Route::post('/jv/transaction_list', 'Admin\vendorManagement\VendorController@jv_transaction_list')->name('admin.jv_transaction.list');
    Route::get('vendor/log/{id}', 'Admin\vendorManagement\VendorController@transactionLog')->name('admin.vendor.transaction.log');
    /************ Vendor End ****************/
    /************ Bill Start ****************/
    Route::post('/vendor/companydate', 'Admin\vendorManagement\VendorController@companydate')->name('admin.vendor.companydate');
    Route::get('/bill/create', 'Admin\vendorManagement\BillCreateController@index')->name('admin.bill.create');
    Route::get('/bill/payment', 'Admin\vendorManagement\BillPaymentController@index')->name('admin.bill.payment');
    Route::post('get_item_details', 'Admin\vendorManagement\BillCreateController@get_item_details')->name('admin.get_item_details');
    Route::post('get_item_details_edit', 'Admin\vendorManagement\BillCreateController@get_item_details_edit')->name('admin.get_item_details_edit');
    Route::post('get_items', 'Admin\vendorManagement\BillCreateController@get_items')->name('admin.get_items');
    Route::post('save_vender_transfer', 'Admin\vendorManagement\BillCreateController@save_vender_transfer')
        ->name('admin.save_vender_transfer');
    Route::post('get_item_details_view', 'Admin\vendorManagement\BillCreateController@get_item_details_view')
        ->name('admin.get_item_details_view');
    Route::post('/bill/save', 'Admin\vendorManagement\BillCreateController@save')->name('admin.bill.save');
    Route::get('/bill/edit/{id}', 'Admin\vendorManagement\BillCreateController@edit')->name('admin.bill.edit');
    Route::post('delete_vender_transfer', 'Admin\vendorManagement\BillCreateController@delete_vender_transfer')
        ->name('admin.delete_vender_transfer');
    Route::post('edit_vender_transfer', 'Admin\vendorManagement\BillCreateController@edit_vender_transfer')
        ->name('admin.edit_vender_transfer');
    Route::get('/bill/view-listing/{id}', 'Admin\vendorManagement\BillCreateController@view_listing')
        ->name('admin.bill.view.listing');
    Route::get('/bill/view-edit/{id}', 'Admin\vendorManagement\BillCreateController@view_listing_edit')
        ->name('admin.bill.view.edit');
    Route::post('/bill/update', 'Admin\vendorManagement\BillCreateController@update')->name('admin.bill.update');
    Route::get('/bill/delete/{id}', 'Admin\vendorManagement\BillCreateController@delete')->name('admin.bill.delete');
    Route::get('bill-expense', 'Admin\vendorManagement\BillExpenseController@index')->name('admin.bill-expense');
    Route::post('get_item_details1', 'Admin\BillExpenseController@get_item_details')->name('admin.get_item_details1');
    Route::post('get_items1', 'Admin\vendorManagement\BillExpenseController@get_items')->name('admin.get_items1');
    Route::post('createItem', 'Admin\vendorManagement\BillCreateController@createItem')->name('admin.bill.item_create');
    Route::get('bill-payment', 'Admin\vendorManagement\BillPaymentController@index')->name('admin.bill-payment');
    Route::post('export_bill_payment', 'Admin\ExportController@export_bill_payment')->name('admin.bill_payment.export_bill_payment');
    Route::post('bill-payment-save', 'Admin\vendorManagement\BillPaymentController@save')->name('admin.vendor_bill_payment.save');
    /************ Bill End ****************/
    /************ Vendor credit start  ****************/
    Route::get('/vendor-credit/create/{id}', 'Admin\vendorManagement\VendorCredit@index')->name('admin.vendor-credit.create');
    Route::post('/vendor-credit/save', 'Admin\vendorManagement\VendorCredit@save')->name('admin.vendor-credit.save');
    Route::get('/vendor-credit/edit/{id}', 'Admin\vendorManagement\VendorCredit@edit')->name('admin.vendor-credit.edit');
    Route::post('/vendor-credit/update', 'Admin\vendorManagement\VendorCredit@update')->name('admin.vendor-credit.update');
    Route::get('/vendor-credit/delete/{id}', 'Admin\vendorManagement\VendorCredit@delete')->name('admin.vendor-credit.delete');
    Route::get('/vendor-credit/apply', 'Admin\vendorManagement\VendorCredit@apply')->name('admin.vendor-credit.apply');
    /************ Vendor  create End ****************/
    /************** Vendor Management End *************************/
    // Payment History Start
    Route::get('payment_list', 'Admin\PaymentHistory\PaymentHistoryController@index')->name('admin.payment.list');
    Route::post('payment_list_report', 'Admin\PaymentHistory\PaymentHistoryController@payment_list')->name('admin.payment.list_detail');
    Route::get('edit_payment/1', 'Admin\PaymentHistory\PaymentHistoryController@edit_payment')->name('admin.payment.edit_payment');
    Route::post('getPaymentBillDetails', 'Admin\PaymentHistory\PaymentHistoryController@getPaymentBillDetails')->name('admin.payment.getPaymentBillDetails');
    // Payment History End
    /****************JV Management Start **************************/
    Route::get('/jv', 'Admin\JvManagement\JvController@index')->name('admin.jv.list');
    Route::get('/jv/create', 'Admin\JvManagement\JvController@create')->name('admin.jv.create');
    Route::post('/jv/save', 'Admin\JvManagement\JvController@saveJV')->name('admin.jv.save');
    Route::post('/jv/update', 'Admin\JvManagement\JvController@updateJV')->name('admin.jv.update');
    Route::post('jv/listing', 'Admin\JvManagement\JvController@designationListing')->name('admin.jv.listing');
    Route::get('jv/edit/{id}', 'Admin\JvManagement\JvController@edit')->name('admin.jv.edit');
    Route::get('jv/delete/{id}', 'Admin\JvManagement\JvController@delete')->name('admin.jv.delete');
    Route::get('jv/detail/{id}', 'Admin\JvManagement\JvController@jv_detail')->name('admin.jv.detail');
    Route::get('export/jv/detail', 'Admin\ExportController@export_jv_detail')->name('admin.jv.detail.export');
    Route::post('getHeads', 'Admin\JvManagement\JvController@getHead')->name('admin.jv.getHeads');
    Route::post('jv/list_export', 'Admin\ExportController@export_jv_list')->name('admin.jv.list.export');
    Route::post('get-saving-accounts', 'Admin\JvManagement\JvController@getSavingAccounts')->name('admin.jv.getsavingaccounts');
    Route::post('get-saving-accounts-details', 'Admin\JvManagement\JvController@getSavingAccountsdetails')->name('admin.jv.getsavingaccountsdetails');
    // Route::post('get-bank-accounts-details', 'Admin\JvManagement\JvController@getbankAccountsdetails')->name('admin.jv.getbankAccountdetails');
    Route::post('get-members', 'Admin\JvManagement\JvController@getMembers')->name('admin.jv.getmembers');
    Route::post('get-associates', 'Admin\JvManagement\JvController@getAssociates')->name('admin.jv.getassociates');
    Route::post('get-loan-accounts', 'Admin\JvManagement\JvController@getLoanAccounts')->name('admin.jv.getloanaccounts');
    Route::post('get-loan-accounts-details', 'Admin\JvManagement\JvController@getLoanAccountsDetails')->name('admin.jv.getloanaccountsdetails');
    Route::post('get-investments-accounts', 'Admin\JvManagement\JvController@getInvestmentAccounts')->name('admin.jv.getinvestmentsaccounts');
    Route::post('get-investments-accounts-details', 'Admin\JvManagement\JvController@getinvestmentsaccountsdetails')->name('admin.jv.getinvestmentsaccountsdetails');
    Route::post('get-reinvestments-accounts', 'Admin\JvManagement\JvController@getReInvestmentAccounts')->name('admin.jv.getreinvestmentsaccounts');
    Route::post('get-reinvestments-accounts-details', 'Admin\JvManagement\JvController@getReInvestmentAccountsDetails')->name('admin.jv.getreinvestmentsaccountsDetails');
    Route::post('get-employees', 'Admin\JvManagement\JvController@getEmployees')->name('admin.jv.getemployees');
    Route::post('get-shareholders', 'Admin\JvManagement\JvController@getShareholders')->name('admin.jv.getshareholders');
    Route::post('get-banks', 'Admin\JvManagement\JvController@getBank')->name('admin.jv.getbank');
    Route::post('get-bank-accounts-details', 'Admin\JvManagement\JvController@getbankAccountsdetails')->name('admin.jv.getbankAccountdetails');
    Route::post('get-branches', 'Admin\JvManagement\JvController@getBranch')->name('admin.jv.getbranch');
    Route::post('get-rent-liability', 'Admin\JvManagement\JvController@getRentLiability')->name('admin.jv.getrentliability');
    Route::post('get-loanfrom-bank', 'Admin\JvManagement\JvController@getLoanFromBank')->name('admin.jv.getloanfrombank');
    Route::post('get-loanfrom-bank-detail', 'Admin\JvManagement\JvController@getLoanFromBankDetail')->name('admin.jv.getloanfrombankdetail');
    Route::post('get-memebr-details', 'Admin\JvManagement\JvController@getMemberDetails')->name('admin.jv.getmemberdetails');
    Route::post('get-employee-details', 'Admin\JvManagement\JvController@getEmployeeDetails')->name('admin.jv.getemployeedetails');
    Route::post('get-rent-details', 'Admin\JvManagement\JvController@getRentDetails')->name('admin.jv.getrentdetails');
    Route::post('get_investment_account', 'Admin\JvManagement\JvController@get_investment_account')->name('admin.get_investment_account');
    Route::post('get-investment-details', 'Admin\JvManagement\JvController@getInvestmentDetails')->name('admin.jv.getinvestmentdetails');
    Route::post('get-associate-details', 'Admin\JvManagement\JvController@getAssociateDetails')->name('admin.jv.getassociatesdetails');
    Route::post('get-shareholder-details', 'Admin\JvManagement\JvController@getshareholdersdetails')->name('admin.jv.getshareholdersdetails');
    Route::post('get-customer', 'Admin\JvManagement\JvController@getCustomer')->name('admin.jv.getcustomer');
    Route::post('get-customer-detail', 'Admin\JvManagement\JvController@getCustomerDetail')->name('admin.jv.getcustomerdetail');
    Route::post('get-creditCard', 'Admin\JvManagement\JvController@getCreditCard')->name('admin.jv.getcreditCard');
    Route::post('get-creditCardDetail', 'Admin\JvManagement\JvController@getcreditcarddetail')->name('admin.jv.getcreditcarddetail');
    Route::post('get-companyBondFDDetail', 'Admin\JvManagement\JvController@getCompanyBondDetails')->name('admin.jv.getcompanybondFd');
    Route::post('get-companyBondFDDetailComplete', 'Admin\JvManagement\JvController@getCompleteCompanyBondDetails')->name('admin.jv.getcompanybondFd.completeDetail');
    /**************** JV Management End *****************************/
    /**************** Banking *******************************/
    Route::get('banking', 'Admin\BankingManagement\BankingController@index')->name('admin.banking.index');
    Route::get('banking/create', 'Admin\BankingManagement\BankingController@create')->name('admin.banking.create');
    Route::post('getAccountNumberOfBank', 'Admin\BankingManagement\BankingController@getAccountNumberOfBank')->name('admin.getAccountNumberOfBank');
    Route::post('getChequeNumberOfBank', 'Admin\BankingManagement\BankingController@getChequeNumberOfBank')->name('admin.getChequeNumberOfBank');
    Route::post('banking/save', 'Admin\BankingManagement\BankingController@save')->name('admin.banking.save');
    Route::get('banking/innerlisting', 'Admin\BankingManagement\BankingController@innerListing')->name('admin.banking.innerlisting');
    Route::post('banking/ajax-inner-listing', 'Admin\BankingManagement\BankingController@ajaxInnerListing')->name('admin.banking.ajaxinnerlisting');
    Route::get('banking/edit/{id}', 'Admin\BankingManagement\BankingController@edit')->name('admin.banking.edit');
    Route::post('banking/update', 'Admin\BankingManagement\BankingController@update')->name('admin.banking.update');
    Route::get('banking/delete/{id}', 'Admin\BankingManagement\BankingController@delete')->name('admin.banking.delete');
    Route::get('banking/delete-advanced/{id}', 'Admin\BankingManagement\BankingController@deleteAdvanced')->name('admin.banking.delete');
    Route::post('banking/ledger-transaction', 'Admin\BankingManagement\BankingController@ledgerTransaction')->name('admin.banking.transaction');
    Route::post('banking/advanced-amount', 'Admin\BankingManagement\BankingController@advancedAmount')->name('admin.banking.advancedamount');
    Route::post('banking/get-customers', 'Admin\BankingManagement\BankingController@getVendorCustomer')->name('admin.banking.getcustomers');
    Route::post('banking/get-vendors', 'Admin\BankingManagement\BankingController@getVendors')->name('admin.banking.getvendor');
    /**************** Banking *******************************/
    /*************  head data move  */
    Route::get('/changeDrCr', 'Admin\AccountDataTransferController@changeDrCr')->name('admin.changeDrCr');
    Route::get('/dataMoveAllHeadTransaction', 'Admin\AccountDataTransferController@dataMoveAllHeadTransaction')->name('admin.dataMoveAllHeadTransaction');
    /****************************** Commission New Functionality ***************/
    Route::match (['get', 'post'], 'associate-commission-calculate', 'Admin\AssociateCommissionController@index')->name('admin.associate.commissionCreate');
    Route::match (['get', 'post'], 'associate-commission-create', 'Admin\AssociateCommissionController@commissionTransfer')->name('admin.associate.commissionTransferNew');
    Route::post('associate-commission-Ledger-create', 'Admin\AssociateCommissionController@commissionLedgerCreate')->name('admin.associate.commissionLedgerCreate');
    Route::get('associate-commission-ledger-payment/{id}', 'Admin\AssociateCommissionController@commissionPayment')->name('admin.associate.commissionPayment');
    Route::post('commissionPaymentSave', 'Admin\AssociateCommissionController@commissionPaymentSave')->name('admin.associate.commissionPaymentSave');
    Route::get('associate-commissionUpdate/{id}/{id1}', 'Admin\AssociateCommissionController@CommissionUpdate')->name('admin.associate.CommissionUpdate');
    /****************************  Loan Report New Starts ***********************************************/
    Route::get('report/loan_application', 'Admin\Report\LoanController@loanapplication')->name('admin.report.loanapplication');
    Route::post('report/loanapplicationlist', 'Admin\Report\LoanController@loanApplicationList')->name('admin.report.loanapplicationlist');
    Route::post('planByLoanCategory', 'Admin\Report\LoanController@planByLoanCategory')->name('admin.planByLoanCategory');
    Route::post('loanapplicationlistexport', 'Admin\LoanApplicationExportController@loanApplicationlistExport')->name('admin.loanapplicationlist.report.export');
    Route::get('report/loan_issued', 'Admin\Report\LoanController@loanissued')->name('admin.report.loanissue');
    Route::post('report/loanissuelist', 'Admin\Report\LoanController@loanIssueList')->name('admin.report.loanissuelist');
    Route::post('loanissuelistexport', 'Admin\LoanApplicationExportController@loanissuelistExport')->name('admin.loanissuelist.report.export');
    Route::get('report/loan_closed', 'Admin\Report\LoanController@loanclosed')->name('admin.report.loanclosed');
    Route::post('report/loanclosedlist', 'Admin\Report\LoanController@loanClosedList')->name('admin.report.loanclosedlist');
    Route::post('loanissueClosedexport', 'Admin\LoanApplicationExportController@loanissueClosedExport')->name('admin.loanclosedlist.report.export');
    /****************************  Loan Report New End ***********************************************/
    /********************** maturiyt Report New start **************************/
    Route::get('report/maturity_demand', 'Admin\Report\MaturityController@maturityReportdemand')->name('admin.report.maturity_report_demand');
    Route::post('report/maturitydemandlist', 'Admin\Report\MaturityController@maturityDemandlist')->name('admin.report.maturityDemandlist');
    Route::post('report/maturitydemandlistexport', 'Admin\MaturityDemandExportController@maturitydemandlistExport')->name('admin.maturitydemandlistExport.report.export');
    Route::get('report/maturity_payment', 'Admin\Report\MaturityController@maturityReportpayment')->name('admin.report.maturity_report_payment');
    Route::post('report/maturitypaymentlist', 'Admin\Report\MaturityController@maturityPaymentlist')->name('admin.report.maturityPaymentlist');
    Route::post('report/maturitypaymentlistexport', 'Admin\MaturityDemandExportController@maturitypaymentlistExport')->name('admin.maturitypaymentlistExport.report.export');
    Route::get('report/maturity_over_due', 'Admin\Report\MaturityController@maturityReportoverdue')->name('admin.report.maturity_report_overdue');
    Route::post('report/maturityoverduelist', 'Admin\Report\MaturityController@maturityOverdduelist')->name('admin.report.maturityOverdduelist');
    Route::post('report/maturityoverduelistexport', 'Admin\MaturityDemandExportController@maturityoverdueExport')->name('admin.maturityoverdueExport.report.export');
    Route::get('report/maturity_upcoming', 'Admin\Report\MaturityController@maturityReportupcomings')->name('admin.report.maturity_reportupcoming');
    Route::post('report/maturityupcominglist', 'Admin\Report\MaturityController@maturityUpcominglist')->name('admin.report.maturityUpcominglist');
    Route::post('report/maturityupcominglistexport', 'Admin\MaturityDemandExportController@maturityUpcomingExport')->name('admin.maturityUpcomingExport.report.export');
    /********************** maturiyt Report New  End **************************/
    // New Test Controller Route For Testing Purpose
    // Route::get('update-demandadvice-newaccountnumberfield-data', 'Admin\TestController@update_demandadvice_newaccountnumberfield_data');
    // Route::get('update-demandadvice-paymentdate-data', 'Admin\TestController@update_demandadvice_paymentdate_data');
    // Route::get('update-demandadvice-finalamount-data', 'Admin\TestController@update_demandadvice_finalamount_data');
    // Route::get('update-memberinvestment-maturity-date', 'Admin\TestController@update_memberinvestment_maturity_date');
    /****************************  Loan From Bank  & Bill Payment Update ***********************************************/
    Route::post('eli_amount_get', 'Admin\CommanController@getEliAmount')->name('admin.eli_amount_get');
    Route::post('get_vendor_billDue', 'Admin\CommanController@vendorBillDue')->name('admin.get_vendor_bill_due');
    Route::post('get_vendor_bill', 'Admin\CommanController@vendorBillget')->name('admin.get_vendor_bill');
    /****************************  Loan From Bank  & Bill Payment Update End***********************************************/
    /******************** head closing Balance *******************/
    Route::get('head/closing-listing', 'Admin\HeadClosingController@index')
        ->name('admin.head.closing_list');
    Route::get('head-closing-save', 'Admin\HeadClosingController@add')
        ->name('admin.head.closing_save');
    Route::post('head/getheadclosingList', 'Admin\HeadClosingController@getHeadClosingList')
        ->name('admin.get.closing_head_list');
    Route::post('head/closing_save', 'Admin\HeadClosingController@headClosingSave')
        ->name('admin.closing_head.save');
    Route::post('head/reset-closing-head', 'Admin\HeadClosingController@resetClosingHead')
        ->name('admin.reset-closing_head');
    Route::post('balance_sheet_closed', 'Admin\HeadClosingController@saveClosedBalanceSheet')->name('admin.balance.closed_balanceSheet');
    Route::post('export_head_closing', 'Admin\HeadClosingController@exportHeadClosing')->name('admin.balance_sheet.head_closing.export');
    Route::post('head_closing/export', 'Admin\HeadClosingController@export')->name('admin.head_closing.export');
    /************************  Trial Balance Start ******************************/
    Route::get('trail_balance', 'Admin\TrailBalanceController@index')->name('admin.trail_balance');
    Route::get('trail_balance/sub_head/{data?}', 'Admin\TrailBalanceController@indexsub')->name('admin.trail_balance.sub_head');
    Route::post('trail_balance/head_list', 'Admin\TrailBalanceController@getHeadClosingList')
        ->name('admin.trail_balance.headlist');
    Route::post('trail_balance/updatechangedata', 'Admin\TrailBalanceController@updateChangeTrialBlanaceData')->name('admin.trail_balance.updatechangedata');
    Route::post('trail_balance/cron', 'Admin\TrailBalanceController@runCronTrailBalance')->name('admin.run_cron');
    Route::post('trail_balance/export', 'Admin\TrailBalanceController@export')->name('admin.trail_balance.export');
    /************************  Trial Balance close ******************************/
    /************************* Account branch transfer Start ***************/
    /********************** Associate branch transfer updates start **************************/
    Route::get('associate-branch-transfer', 'Admin\AssociateController@assbranchtransfer')
        ->name('admin.associate.branchtransfer_change');
    Route::post('getAssociateBrnachDetail', 'Admin\AssociateController@getAssociatebrtansferData')
        ->name('admin.associter_brnachtransferdataGets');
    Route::post('associate-branch-save', 'Admin\AssociateController@assbranchtransfersave')
        ->name('admin.associate.branch_save');
    /********************** Associate branch transfer updates end **************************/
    /********************** Investment branch transfer updates start **************************/
    Route::get('investment-branch-transfer', 'Admin\InvestmentplanController@investmentbranchtransfer')
        ->name('admin.investment.investementbranch_change');
    Route::post('investmentBranchrrtransferDataGet', 'Admin\InvestmentplanController@investmentBrtransferDataGet')
        ->name('admin.investmentBranchtransferDataGet');
    Route::post('investment-branch-save', 'Admin\InvestmentplanController@invsbranchtransfersave')
        ->name('admin.investment.branch_save');
    /********************** Investment branch transfer updates end **************************/
    /********************** loan branch transfer updates start **************************/
    Route::get('loan-branch-transfer', 'Admin\LoanBranchTransferController@loanbranchtransfer')
        ->name('admin.loan.loanbranchtransfer_change');
    Route::post('GetLoanBrnachDetail', 'Admin\LoanBranchTransferController@getLoanbrtansferData')
        ->name('admin.loan_brnachtransferdataGets');
    Route::post('loan-branch-save', 'Admin\LoanBranchTransferController@loanbranchtransfersave')
        ->name('admin.loan.branch_save');
    Route::get('account-log', 'Admin\LoanBranchTransferController@loanbranchtransferlog')->name('admin.loan.account_log');
    Route::post('GetLoanBrnachLogDetail', 'Admin\LoanBranchTransferController@getLoanbrtansferLogData')
        ->name('admin.loan_brnachtransferdataLogGets');
    /********************** loan branch transfer updates end **************************/
    /********************** associate branch transfer log updates start **************************/
    /********************** loan branch transfer updates end **************************/
    /********************** loan Plan transfer updates start **************************/
    Route::get('loan-plan-transfer', 'Admin\LoanController@loanPlanTransfer')
        ->name('admin.loan.plantransfer.loanplantransfer');
    Route::post('get-loan-plan-detail', 'Admin\LoanController@getLoanPlanTansferData')
        ->name('admin.loan.plantransfer.loan_plantransferdataget');
    Route::post('new-loanplan-detail', 'Admin\LoanController@getnewLoanPlanData')
        ->name('admin.loan.plantransfer.new_loanplan_detailget');
    Route::post('loan-plan-tansfer-save', 'Admin\LoanController@loanPlanTransferSave')
        ->name('admin.loan.plantransfer.plan_save');
    Route::get('plan-transfer-log', 'Admin\LoanController@loanPlanTransferLog')->name('admin.loan.plantransfer.account_log');
    Route::post('get-loan-plan-log-detail', 'Admin\LoanController@getLoanPlanTansferLogData')
        ->name('admin.loan.plantransfer.plan_transfer_data_log_get');
    /********************** loan Plan transfer updates end **************************/
    Route::get('associate-log/{type}/{id}', 'Admin\AccountLogController@loglist')->name('admin.associate.log');
    Route::get('investment-log/{type}/{id}', 'Admin\AccountLogController@loglist')->name('admin.investement.log');
    //Route::get('loan-log/{type}/{id}', 'Admin\AccountLogController@loglist')->name('admin.loan.log');
    /********************** associate branch transfer log updates **************************/
    /************************* Account branch transfer End ***************/
    /****************************** Commission New Functionality Monthly Start ***************/
    Route::match (['get', 'post'], 'commission/ledger_create', 'Admin\AssociateCommissionMonthlyController@commissionTransfer')->name('admin.associate.commission.commissionTransfer');
    Route::post('commission/ledger_save', 'Admin\AssociateCommissionMonthlyController@commissionLedgerCreate')->name('admin.associate.commission.commissionLedgerCreate');
    Route::get('commission/ledger_payment/{id}/{id1}', 'Admin\AssociateCommissionMonthlyController@commissionPayment')->name('admin.associate.commission.commissionPayment');
    Route::get('commission/commissionUpdate/{id}/{id1}/{id2}', 'Admin\AssociateCommissionMonthlyController@CommissionUpdate')->name('admin.associate.commission.CommissionUpdate');
    Route::post('commission/commissionPaymentSave', 'Admin\AssociateCommissionMonthlyController@commissionPaymentSave')->name('admin.associate.commission.commissionPaymentSave');
    Route::get('commission/ledger_list', 'Admin\AssociateCommissionMonthlyController@commissionTransferList')->name('admin.associate.commission.ledgerList');
    Route::post('commission/leaserList', 'Admin\AssociateCommissionMonthlyController@leaserList')->name('admin.associate.commission.leaserList');
    Route::get('commission/transfer-detail/{id}', 'Admin\AssociateCommissionMonthlyController@commissionTransferDetail')->name('admin.associate.commission.commissionTransferDetail');
    Route::post('commission/leaserDetailList', 'Admin\AssociateCommissionMonthlyController@leaserDetailList')->name('admin.associate.commission.leaserDetailList');
    Route::post('commission/leaserDetailExport', 'Admin\AssociateCommissionMonthlyController@leaserDetailExport')->name('admin.associate.commission.leaserDetailExport');
    Route::post('commission/leaserExport', 'Admin\AssociateCommissionMonthlyController@leaserExport')->name('admin.commission.associate.leaserExport');
    /****************************** Commission New Functionality Monthly Start ***************/
    /********************** Investment Collector Change start **************************/
    Route::get('investment_management/collector-change', 'Admin\InvestmentCollector\InvestmentCollectorChangeController@investmentCollectorChange')->name('admin.investment_management.investmentcollector.collectorchangeindex');
    Route::post('investment_management/investment-collector-data-get', 'Admin\InvestmentCollector\InvestmentCollectorChangeController@investmentCollectorDataGet')->name('admin.investmentcollectordataget');
    Route::post('investment_management/getnewcollectordata', 'Admin\InvestmentCollector\InvestmentCollectorChangeController@getnewCollectorData')->name('admin.getnewCollectorData');
    Route::post('investment_management/investment-collector-changesave', 'Admin\InvestmentCollector\InvestmentCollectorChangeController@investmentCollectorChangeSave')->name('admin.investment_management.investmentcollector.collector_changesave');
    /********************** Investment Collector Change End **************************/
    /********************** Loan Collector Change start **************************/
    Route::get('loan/loancollector/collector-change', 'Admin\LoanCollector\LoanCollectorChangeController@loanCollectorChange')->name('admin.loan.loancollector.collectorchangeindex');
    Route::post('loan/loancollector/loan-collector-data-get', 'Admin\LoanCollector\LoanCollectorChangeController@loanCollectorDataGet')->name('admin.loancollectordataget');
    Route::post('loan/loancollector/getnewassocitedata', 'Admin\LoanCollector\LoanCollectorChangeController@getnewAssociteData')->name('admin.getnewAssociteData');
    Route::post('loan/loancollector/loan-collector-changesave', 'Admin\LoanCollector\LoanCollectorChangeController@loanCollectorChangeSave')->name('admin.loan.loancollector.collector_changesave');

    // Emi due date and emi amount change 17-04-24
    Route::get('loan/updates/emi_due_date/correction', 'Admin\LoanCollector\EmiDueDateChangeController@index')->name('admin.loan.emi_due_date.change');
    Route::post('loan/due-date/correction', 'Admin\LoanCollector\EmiDueDateChangeController@emiDueDateChangeSave')->name('admin.loan.emiDueDate.correction');
    Route::post('loan/updates/emi_due_date/data-get', 'Admin\LoanCollector\EmiDueDateChangeController@loanCollectorDataGet')->name('admin.loan-data-get');
    /********************** Loan Collector Change End **************************/
    /*******************  Associate Commision Management start   ***********************/
    Route::get('commision/exception-list', 'Admin\CommissionController@exceptionList')->name('admin.associatecommision.exceptionList');
    Route::post('commision/exception_list', 'Admin\CommissionController@exceptionListing')->name('admin.commison.exception.lists');
    Route::get('commision/exception_logs/{id}', 'Admin\CommissionController@exceptionLogDetail')->name('admin.commison.exception.status');
    Route::get('associate-exception', 'Admin\CommissionController@assocaiteException')->name('admin.associate.exception');
    Route::post('getAssociateexceptionDetail', 'Admin\CommissionController@getAssociateextansferData')
        ->name('admin.associter_exceptiontransferdataGets');
    Route::post('associate-exception-save', 'Admin\CommissionController@exceptionSave')
        ->name('admin.associate.exception_save');
    Route::post('exportcommisionlist', 'Admin\ExportController@exportCommision')->name('admin.commision.export');
    Route::get('associate/commision/month-end-comission-list', 'Admin\CommissionController@monthList')->name('admin.associatecommision.monthList');
    Route::post('commision/month-list', 'Admin\CommissionController@list')->name('admin.commison.month.lists');
    Route::get('associate/commission/month-end-comission-create', 'Admin\CommissionController@assmonth')->name('admin.associate.month');
    Route::post('associate-month-save', 'Admin\CommissionController@monthtransfersave')
        ->name('admin.commision.month_save');
    Route::get('daily-account-setting', 'Admin\CommissionController@dailyacc')->name('admin.dailyacc.setting');
    Route::post('daily-account-setting-save', 'Admin\CommissionController@accountsettingsave')
        ->name('admin.dailyaccount.setting_save');
    Route::post('checkCommissionData', 'Admin\CommissionController@checkCommissionData')
        ->name('admin.dailyaccount.check_commission');
    /*******************  Associate Management END   ***********************/
    Route::post('printpassbook/updateprintstatus', 'Admin\CorrectionController@updateprintstatus')->name('admin.printpassbook.updateprintstatus');
    /******************* Route for Inserting data in collector Account Table using test controller***********************/
    // Route::get('insert-loan-data-collector-account', 'Admin\TestController@insert_loan_data_collector_account');
    // Route::get('insert-grouploan-data-collector-account', 'Admin\TestController@insert_grouploan_data_collector_account');
    // Route::get('insert-savingaccount-data-collector-account', 'Admin\TestController@insert_savingaccount_data_collector_account');
    // Route::get('insert-memberinvestmentaccount-data-collector-account', 'Admin\TestController@insert_memberinvestmentaccount_data_collector_account');
    //**************Rent management export route start ******///////
    Route::post('rent/ledger-export', 'Admin\RentManagement\RentController@export')->name('admin.rent.ledger-export');
    //**************Rent management export route end ******///////
    // *************** HR payble export strat  ********************* //
    Route::post('hr/salary/payable/export', 'Admin\HrManagement\SalaryController@export')->name('admin.hr.salary_export');
    // *************** HR payble export end   ********************* //
    // Deposit Amount Rposrt
    /**********   Report Managemrent deposit amount report start ************ */
    Route::get('report/deposit_amount_report', 'Admin\Report\DepositAmountReportController@index')->name('admin.report.deposit_amount_report');
    Route::post('report/deposit_amount_report_listing', 'Admin\Report\DepositAmountReportController@listing')->name('admin.report.deposit_amount_report_listing');
    Route::post('report/deposit_amount_report_Export', 'Admin\Report\DepositAmountReportController@export')->name('admin.deposit_amount_report_Export.report.export');
    Route::post('report/allplans', 'Admin\Report\DepositAmountReportController@allplans')->name('admin.deposit_amount_report_Export.report.plans');
    /**********   Report Managemrent deposit amount report end ************ */
    //**************Rent management export route start ******///////
    Route::post('rent/ledger-export', 'Admin\RentManagement\RentController@export')->name('admin.rent.ledger-export');
    //**************Rent management export route end ******///////
    // *************** HR Management > Employee status  Start  ********************* //
    Route::get('hr/employee_status', 'Admin\HrManagement\EmployeeStatusController@index')->name('admin.hr.employeestatus');
    Route::post('hr/employee_status/account', 'Admin\HrManagement\EmployeeStatusController@show')->name('admin.ht.employeestatus.show');
    Route::post('hr/employee_status/status_check', 'Admin\HrManagement\EmployeeStatusController@status_check')->name('admin.hr.employeestatus.status_check');
    Route::post('hr/employee_status/listing', 'Admin\HrManagement\EmployeeStatusController@listing')->name('admin.hr.employeestatus.listing');
    Route::post('hr/employee_status/export', 'Admin\HrManagement\EmployeeStatusController@export')->name('admin.hr.employeestatus.export');
    // *************** HR Management > Employee status  End  ********************* //
    /**********   Report Managemrent npa start ************ */
    Route::get('report/npa', 'Admin\Report\NonPerformingAssetsReportController@index')->name('admin.report.npa');
    Route::post('non_Performing_assets_listing', 'Admin\Report\NonPerformingAssetsReportController@non_Performing_assets_listing')->name('admin.report.non_Performing_assets_listing');
    Route::post('report/non_Performing_assets_export', 'Admin\Report\NonPerformingAssetsReportController@export')->name('admin.npa_export.report.export');
    /**********   Report Managemrent npa end  ************ */
    /* BranchBankBalanceAmount */
    Route::post('BranchBankBalanceAmount', 'Admin\CommanController@getbranchbankbalanceamount')->name('admin.branchBankBalanceAmount');
    /* BranchBankBalanceAmount */
    /*------QuotaBusiness start -----*/
    Route::get('quotabusiness', 'Admin\QuotaController@index')->name('admin.quotabusiness.index');
    Route::post('quotabusiness/listing', 'Admin\QuotaController@listing')->name('admin.quotabusiness.listing');
    Route::post('quotabusiness/export', 'Admin\ExportController@exportKotaBusinessReport')->name('admin.quotabusiness.export');
    /*------QuotaBusiness end -----*/
    /****** part payment  */
    Route::get('rent/part-payment/{id}/{l}', 'Admin\RentManagement\RentController@partPayment')->name('admin.rent.part_payment');
    Route::post('rent/part-payment_savee', 'Admin\RentManagement\RentController@partPaymentSave')->name('admin.rent.part_payment_save');
    /****** Salary part payment  */
    Route::get('salary/part-payment/{id}/{l}', 'Admin\HrManagement\SalaryController@partPayment')->name('admin.salary.part_payment');
    Route::post('part-payment_save', 'Admin\HrManagement\SalaryController@partPaymentSave')->name('admin.salary.part_payment_save');
    //Company Resistration
    Route::get('company/register', 'Admin\Company\CompanyController@index')->name('admin.companies.index');
    Route::get('company/companies-list', 'Admin\Company\CompanyController@show')->name('admin.companies.show');
    Route::post('companies/listing', 'Admin\Company\CompanyController@listing')->name('admin.companies.listing');
    Route::post('companies/companyRegisterForm', 'Admin\Company\CompanyController@companyRegisterForm')->name('admin.companies.companyRegisterForm');
    Route::post('companies/companyAccountHead', 'Admin\Company\CompanyController@companyAccountHead')->name('admin.companies.companyAccountHead');
    Route::post('companies/companyFaCode', 'Admin\Company\CompanyController@companyFaCode')->name('admin.companies.companyFaCode');
    Route::post('companies/companyBranch', 'Admin\Company\CompanyController@companyBranch')->name('admin.companies.companyBranch');
    Route::post('companies/companyRegisterForm_update', 'Admin\Company\CompanyController@companyRegisterForm_update')->name('admin.companies.companyRegisterForm_update');
    Route::post('companies/companyAccountHead_update', 'Admin\Company\CompanyController@companyAccountHead_update')->name('admin.companies.companyAccountHead_update');
    Route::post('companies/companyFaCode_update', 'Admin\Company\CompanyController@companyFaCode_update')->name('admin.companies.companyFaCode_update');
    Route::post('companies/companyBranch_update', 'Admin\Company\CompanyController@companyBranch_update')->name('admin.companies.companyBranch_update');
    Route::post('companies/status', 'Admin\Company\CompanyController@status')->name('admin.companies.status');
    Route::get('companies/edit/{id}', 'Admin\Company\CompanyController@edit')->name('admin.companies.edit');
    Route::get('companies/update/{id}', 'Admin\Company\CompanyController@update')->name('admin.companies.update');
    Route::get('companies/view/{id}', 'Admin\Company\CompanyController@view')->name('admin.companies.view');
    Route::post('companies/name_unique', 'Admin\Company\CompanyController@name_unique')->name('admin.companies.name_unique');
    Route::post('companies/fa_code_from_unique', 'Admin\Company\CompanyController@fa_code_from_unique')->name('admin.companies.fa_code_from_unique');
    Route::post('companies/fa_code_to_unique', 'Admin\Company\CompanyController@fa_code_to_unique')->name('admin.companies.fa_code_to_unique');
    Route::post('companies/tin_no_unique', 'Admin\Company\CompanyController@tin_no_unique')->name('admin.companies.tin_unique');
    Route::post('companies/pan_no_unique', 'Admin\Company\CompanyController@pan_no_unique')->name('admin.companies.pan_unique');
    Route::post('companies/cin_no_unique', 'Admin\Company\CompanyController@cin_no_unique')->name('admin.companies.cin_unique');
    Route::get('company/associate-setting', 'Admin\Company\CompanyController@associateSetting')->name('admin.companies.associateSetting');
    Route::post('company/associate_store', 'Admin\Company\CompanyController@associate_store')->name('admin.companies.associate_store');
    Route::post('company/companyAssociatesListing', 'Admin\Company\CompanyController@companyAssociatesListing')->name('admin.companies.companyAssociatesListing');
    Route::post('company/company_default_settings', 'Admin\Company\CompanyController@company_default_settings')->name('admin.companies.company_default_settings');
    Route::post('company/company_default_settings_update', 'Admin\Company\CompanyController@company_default_settings_update')->name('admin.companies.company_default_settings_update');
    Route::post('company/company_default_fa_code_from_check', 'Admin\Company\CompanyController@fa_code_from_check')->name('admin.companies.fa_code_from_check');
    //Route Created Branach By Rajat
    Route::post('branch-assigned', 'Admin\BranchController@AssigendBranch')->name('admin.branch_assigned');
    Route::post('branch-assigned-model', 'Admin\BranchController@AssigendBranchModel')->name('admin.branch_assigned_model');
    Route::get('branch/company_view/{id}', 'Admin\BranchController@CompanyBranchView')->name('admin.branch_company_view');
    /*Plan Category Controller Start*/
    Route::get('plan-categories', 'Admin\InvestmentPlanCreate\PlanCategoryController@index')->name('admin.planCategory');
    Route::post('plan-categories/listing', 'Admin\InvestmentPlanCreate\PlanCategoryController@categoryListing')->name('investment.planCategory.listing');
    Route::post('plan-categories/listing/status', 'Admin\InvestmentPlanCreate\PlanCategoryController@status')->name('investment.planCategory.listing.status');
    Route::post('plan-categories/create/check', 'Admin\InvestmentPlanCreate\PlanCategoryController@check')->name('admin.planCategoryCreate.check');
    Route::get('plan-categories/create', 'Admin\InvestmentPlanCreate\PlanCategoryController@addPage')->name('add_category');
    Route::post('plan-categories/create', 'Admin\InvestmentPlanCreate\PlanCategoryController@addCategory')->name('add_category');
    /*Plan Category Controller End*/
    /*Plan Deno Controller Start*/
    Route::get('py-plans/plan-deno/{slug}', 'Admin\PlanDenoController@index')->name('planDeno');
    Route::post('py-plans/plan-deno', 'Admin\PlanDenoController@listing')->name('admin.planDenoListing');
    Route::post('py-plans/plan-deno/status', 'Admin\PlanDenoController@status')->name('admin.planDenoStatus');
    Route::post('py-plans/plan-deno/insert', 'Admin\PlanDenoController@insert')->name('planDenoInsert');
    Route::post('py-plans/plan-deno/destroy', 'Admin\PlanDenoController@destroy')->name('admin.planDenoDelete');
    /*Plan Deno Controller End*/
    /*--------------- Bank Management Start (GAURAV) ----------- */
    Route::get('bank', 'Admin\BankManagement\BankController@index')->name('admin.bank');
    Route::post('bank/listing', 'Admin\BankManagement\BankController@listing')->name('admin.bank.listing');
    Route::post('bank/create', 'Admin\BankManagement\BankController@create')->name('admin.bank.create');
    Route::post('bank/bank_name_check', 'Admin\BankManagement\BankController@bank_name_check')->name('admin.bank_name.check');
    Route::post('bank/fetch', 'Admin\BankManagement\BankController@fetch')->name('admin.bank.fetch');
    Route::post('bank/update', 'Admin\BankManagement\BankController@update')->name('admin.bank.update');
    Route::post('bank/status', 'Admin\BankManagement\BankController@status')->name('admin.bank.status');
    Route::post('bank/add-account/fetch', 'Admin\BankManagement\BankController@collect')->name('admin.bank.add-account.fetch');
    Route::post('bank/add-account/create', 'Admin\BankManagement\BankController@accountCreate')->name('admin.bank.add-account.create');
    Route::get('bank-accounts', 'Admin\BankManagement\AccountController@index')->name('admin.bank-accounts');
    Route::post('bank-accounts/listing', 'Admin\BankManagement\AccountController@listing')->name('admin.bank-accounts.listing');
    Route::post('bank-accounts/create', 'Admin\BankManagement\AccountController@create')->name('admin.bank-accounts.create');
    Route::post('bank-accounts/fetch', 'Admin\BankManagement\AccountController@fetch')->name('admin.bank-accounts.fetch');
    Route::post('bank-accounts/status', 'Admin\BankManagement\AccountController@status')->name('admin.bank-accounts.status');
    Route::post('bank-accounts/collect', 'Admin\BankManagement\AccountController@collect')->name('admin.bank-accounts.collect');
    Route::post('bank-accounts/update', 'Admin\BankManagement\AccountController@update')->name('admin.bank-accounts.update');
    /*--------------- Bank Management End (GAURAV) ----------- */
    /*--------------- Loan Management Start (GAURAV) ----------- */
    Route::post('loans', 'Admin\LoanController@planListing')->name('admin.loan.planlist');
    Route::post('loans/delete', 'Admin\LoanController@delete')->name('admin.loan.delete_loan_tenure_charge');
    Route::post('loans/fetch', 'Admin\LoanController@fetch')->name('admin.loan.fetch');
    /*--------------- Loan Management End (GAURAV) ----------- */
    /**************  get bank by company id  Durgesh*************** */
    Route::post('admin/getBankByCompany', 'Admin\ChequeController@getBankByCompany')->name('admin.bank_list_by_company');
    /************* ssb account widrawal banktocompnay listing **************/
    Route::post('ssb/branchtocompany', 'Admin\PaymentManagement\WithdrawalController@branchtocompany')->name('admin.ssbAccountDetails.branchToCompany.details');
    /** Admin Associate Registration Start */
    Route::post('get_customer', 'Admin\AssociateController@getCustomerData')->name('admin.customerDataGet');
    Route::post('associateCustomerSsbAccountCustomerGet', 'Admin\AssociateController@associateSsbAccountGet')->name('admin.associateSsbAccountGet.customer');
    Route::post('associate/registration/customer/store', 'Admin\AssociateController@store')->name('admin.associate.store.customer');
    Route::post('associate/registration/customer/dependents', 'Admin\AssociateController@create')->name('admin.associate.dependents.customer');
    Route::post('get_senior/customer', 'Admin\AssociateController@getSeniorDetail')->name('admin.seniorDetail.customer');
    Route::post('getCarderAssociate/customer', 'Admin\AssociateController@getCarderAssociate')->name('admin.getCarderAssociate.customer');
    Route::post('associate_ssb_check', 'Admin\AssociateController@checkSsbAcount')->name('admin.associatessbaccountcheck');
    Route::get('defact_code/{customerId}', 'Admin\AssociateController@defact_code')->name('defact_code');
    // Route::post('registerplan/approve_cheque_detail', 'Branch\CommanTransactionsController@approveReceivedChequeDetail')->name('branch.approve_cheque_detail');
    /** Admin Associate Registration End */
    // //============= customer associate registration start
// Route::group(['middleware' => ['permission:Associate Create']], function () {
//     // Route::get('associate/registration2', 'Branch\MemberAssociateController2@register2')->name('branch.associate_register2');
//     Route::get('associate/registration/company', 'Branch\AssociateRegistrationController@index')->name('branch.associateregistercompany.index');
//     // Route::post('associate/registration2', 'Branch\MemberAssociateController2@save2')->name('branch.associate_save2');
//     Route::post('associate/registration/save', 'Branch\AssociateRegistrationController@store')->name('branch.customer.associate.save');
// });
    //============= customer associate registration end
    Route::get('daily/report', 'CommonController\Investment\InvestmentReportController@dailyReport')->name('common.investment.daily.report');
    Route::get('monthly/report', 'CommonController\Investment\InvestmentReportController@monthlyReport')->name('common.investment.monthly.report');
    Route::post('daily/report/listing', 'CommonController\Investment\InvestmentReportController@dailyReportListing')->name('common.investement.dailyReportListing');
    Route::post('report/listing/export', 'CommonController\Investment\InvestmentReportController@export')->name('common.investement_report.export');
    //form 15 g data check start
    Route::post('admin.form15g.datacheck', 'Admin\FormGController@datacheck')->name('admin.form15g.datacheck');
    //form 15 g data check End
    //Npa Report for laon  plans Accounrding to company id start
    Route::post('report/allnpaplans', 'Admin\Report\NonPerformingAssetsReportController@allloanplans')->name('admin.npa.report.loanplans');
    //Npa Report for laon  plans Accounrding to company id End
    // Tanuskh code aupdate on commission Start
    Route::get('new-associate-commission-list', 'Admin\AssociateController@newAssociateCommissionList')->name('admin.associate.commission.new');
    Route::post('new-associate-commission-detail-listing', 'Admin\AssociateController@newassociatecommissiondetaillist')->name('admin.associate.companycommission.detaillist');
    Route::post('commission/companyComissionDetailExport', 'Admin\AssociateController@companycomissiondetailexport')->name('admin.associate.commission.companycomissiondetailexport');
    // Tanuskh code aupdate on commission End
    // voucher uodates by tansukh changes Start //
    Route::post('/voucher/checkGstData', 'Admin\Voucher\VoucherController@checkGstData')->name('admin.voucher.checkGstData');
    Route::post('/associateDetail', 'Admin\Voucher\VoucherController@associateDetail')->name('admin.voucher.associate');
    // voucher uodates by tansukh changes End //
    //check renewal Ammount limit Start
    Route::post('investment/renewlimit', 'Admin\NewRenewalController@renewlimit')->name('admin.investment.renewlimit');
    //check renewal Ammount limit End
    // saving Account export start
    Route::post('savingaccountreportlisting/export', 'Admin\SavingaccountreportController@export')->name('admin.savingaccountreport.export');
    // saving Account export end
    // by Durgesh  Fundtransfer filter start
    Route::post('fetchBranchByCompanyBank', 'Admin\PaymentManagement\FundTransferController@fetchbranchbycompanyBank')->name('admin.fetchbranchbycompanyBank');
    Route::post('getBankAccountNo', 'Admin\PaymentManagement\FundTransferController@getBankAccountNumber')->name('admin.bank_accountNumber');
    Route::post('getBankAccountNoinactive', 'Admin\PaymentManagement\FundTransferController@getBankAccountNumberinactive')->name('admin.bank_account_list.inactive');
    //  Fundtransfer filter end
    // Mother Branch Business  by mahesh  start
    Route::get('report/mother_branch_business', 'Admin\Report\MotherBranchBusinessController@index')->name('admin.report.mother_branch_business');
    Route::post('mother_branch_business_listing', 'Admin\Report\MotherBranchBusinessController@mother_branch_business_listing')->name('admin.report.mother_branch_business_listing');
    Route::post('motherBranchBusinessReportExport', 'Admin\Report\MotherBranchBusinessController@motherBranchBusinessReportExport')->name('admin.motherbranch_business.report.export');
    // Mother Branch Business  by mahesh  End
    // Advance Payment Section by Mahesh  start
    Route::get('/advancePayment/{id}/{paymenttype}', 'Admin\AdvancePayment\AdvancePaymentController@add')->name('admin.advancePayment.add');
    Route::get('/addRequest', 'Admin\AdvancePayment\AdvancePaymentController@add_request')->name('admin.advancePayment.add_request');
    Route::get('/requestList', 'Admin\AdvancePayment\AdvancePaymentController@requestList')->name('admin.advancePayment.requestList');
    Route::get('/paymentList', 'Admin\AdvancePayment\AdvancePaymentController@paymentList')->name('admin.advancePayment.paymentList');
    Route::post('/advancePayment/getemployee', 'Admin\AdvancePayment\AdvancePaymentController@getemployee')->name('admin.advancePayment.getemployee');
    Route::post('/advancePayment/saveadvancepayment', 'Admin\AdvancePayment\AdvancePaymentController@saveTAadvancepayment')->name('admin.advancePayment.saveadvancepayment');
    Route::post('/advancePayment/advancerequest', 'Admin\AdvancePayment\AdvancePaymentController@advancerequest')->name('admin.advancePayment.advancerequest');
    Route::post('/AdvancedRequestListing', 'Admin\AdvancePayment\AdvancePaymentController@AdvancedRequestListing')->name('admin.advancePayment.AdvancedRequestListing');
    Route::post('/PaymentListing', 'Admin\AdvancePayment\AdvancePaymentController@PaymentListing')->name('admin.advancePayment.PaymentListing');
    Route::get('/changestatus/{id}', 'Admin\AdvancePayment\AdvancePaymentController@advanceTrasectionStatus')->name('admin.advancePayment.changestatus');
    Route::post('/advanceTrasectionReject/{id}', 'Admin\AdvancePayment\AdvancePaymentController@advanceTrasectionReject')->name('admin.advancePayment.advanceTrasectionReject');
    Route::post('/getOwnerName', 'Admin\AdvancePayment\AdvancePaymentController@getOwnerNames')->name('admin.advancePayment.getOwnerNames');
    Route::post('/getemployeee', 'Admin\AdvancePayment\AdvancePaymentController@getemployeee')->name('admin.advancePayment.getemployeee');
    Route::get('/addAdjestment/{id}', 'Admin\AdvancePayment\AdvancePaymentController@addAdjestment')->name('admin.advancePayment.addAdjestment');
    Route::get('/Adjestmentview/{id}', 'Admin\AdvancePayment\AdvancePaymentController@Adjestmentview')->name('admin.advancePayment.Adjestmentview');
    Route::post('/AdjListingtable', 'Admin\AdvancePayment\AdvancePaymentController@AdjListingtable')->name('admin.advancePayment.AdjListingtable');
    Route::post('bankChequeList', 'Admin\AdvancePayment\AdvancePaymentController@bankChequeList')->name('bankChequeList');
    Route::post('/advancepayment/getHeadsdetails', 'Admin\AdvancePayment\AdvancePaymentController@get_expense')->name('admin.advancePayment.getHeads');
    Route::post('/advancePayment/get_indirect_expense_sub_head', 'Admin\AdvancePayment\AdvancePaymentController@get_indirect_expense_sub_head')->name('admin.advancePayment.get_indirect_expense_sub_head');
    Route::post('/advancepayment/save', 'Admin\AdvancePayment\AdvancePaymentController@addAdjestmentSave')->name('admin.advancePaymentAdjestment.save');
    Route::post('/advancepayment/branchCurrentBalance', 'Admin\AdvancePayment\AdvancePaymentController@branchCurrentBalance')->name('admin.advancePaymentAdjestment.branchCurrentBalance');
    Route::post('/exportAdvanceRequestList', 'Admin\AdvancePayment\AdvancePaymentController@exportAdvanceRequestList')->name('admin.exportAdvanceRequestList');
    Route::post('/exportAdvancePaymentList', 'Admin\AdvancePayment\AdvancePaymentController@exportAdvancePaymentList')->name('admin.exportAdvancePaymentList');
    // Advance Payment Section by Mahesh  end
    //by durgesh
    Route::get('member-saving/{id}', 'Admin\MemberController@saving')->name('admin.member.saving');
    Route::post('member-saving', 'Admin\MemberController@membersSaving')->name('admin.member_saving');
    // By Durgesh Employee Management
    Route::post('hr/employee/companydate', 'Admin\HrManagement\EmployeeController@companydate')->name('admin.hr.employee.companydate');
    // Loan From Bank start Mahesh
    Route::post('Vendor/loanFromBank', 'Admin\LoanFromBank\LoanFromBankController@getVendorByCompany')->name('admin.vendorList.loan-from-bank');
    Route::Post('loanFromBank/loan_emi_bank', 'Admin\LoanFromBank\LoanFromBankController@loan_emi_bank')->name('admin.loan_from_bank.bank_account_list');
    Route::Post('loanFromBank/loan_emi_accounr', 'Admin\LoanFromBank\LoanFromBankController@loan_emi_report_bank')->name('admin.bank_account_no');
    Route::post('loanFromBank/exportLedger', 'Admin\LoanFromBank\LoanFromBankController@Loanfrombankexport')->name('admin.loanFromBank.ledger_listing_export');
    Route::post('loanFromBank/exportLoan', 'Admin\LoanFromBank\LoanFromBankController@Loansfrombankexport')->name('admin.loanFromBank.loan_listing_export');
    Route::post('loanFromBank/exportLoanemi', 'Admin\LoanFromBank\LoanFromBankController@Loanemiexport')->name('admin.loanFromBank.loan_emi_listing_export');
    // Loan From Bank end
    //tds transfer listiong start
    Route::post('pay-tds-transfer-payable-amount', 'Admin\TdspayableController@payTdsTransferAmount')->name('admin.payTdsTransferAmount');
    Route::post('tds_transfer_listing', 'Admin\TdspayableController@tds_transfer_listing')->name('admin.tds_transfer_listing');
    Route::get('tds_transfer_pay/{companyId}/{id}', 'Admin\TdspayableController@tds_transfer_pay')->name('admin.tds_transfer_pay');
    Route::get('tds_transfer_view/{companyId}/{id}', 'Admin\TdspayableController@tds_transfer_view')->name('admin.tds_transfer_pay.view');
    Route::post('tds_transfer_pay/chalandownload', 'Admin\TdspayableController@chalandownload')->name('admin.tds_payable_chalan.download');
    Route::get('tds_transfer_pay/displayImage', 'Admin\TdspayableController@displayImage')->name('admin.tds_payable_chalan.view');
    Route::post('export_tds_transafer', 'Admin\TdspayableController@export_tds_transafer')->name('admin.export_tds_transafer');
    //tds transfer listiong end
    // bankchange  durgesh   start
    Route::post('bankTitle', 'Admin\Brs\BankChargeController@banktitle')->name('admin.banktitle');
    Route::post('brs/companydate', 'Admin\Brs\BankChargeController@companydate')->name('admin.brs.companydate');
    // --------------------------------Associate busnisses --------------------------------
    Route::get('report/associate_business_report', 'CommonController\associate\AssociateBusinessController@index')->name('admin.common.associate_busniss_report');
    Route::post('associate_business_report/list', 'CommonController\associate\AssociateBusinessController@listing')->name('admin.common.associate_busniss_report_list');
    Route::post('associate_business_report/export', 'CommonController\associate\AssociateBusinessController@export')->name('admin.common.associate_busniss_report_export');
    Route::get('report/associate_business_compare', 'CommonController\associate\AssociateBusinessController@compare')->name('admin.common.associate_busniss_compare');
    Route::post('associate_business_compare/list', 'CommonController\associate\AssociateBusinessController@comparelisting')->name('admin.common.associate_busniss_compare_list');
    Route::post('associate_business_report/exportcompare', 'CommonController\associate\AssociateBusinessController@exportcompare')->name('admin.common.associate_busniss_report_exportcompare');
    Route::get('vendor/advancePaymentList', 'Admin\vendorManagement\VendorController@advancepaymentduelist')->name('admin.vendor.advancepayment.due');
    Route::post('advance-payment-save', 'Admin\vendorManagement\VendorController@advancepayment')->name('admin.vendor.advancepayment.settlment');
    /// ---- Branch Managements start  by durgesh -----------
    // OTP setting ------- 15 sep 2023
    Route::post('branch/branch_status', 'Admin\BranchController@BranchStatus')->name('admin.branchStatus');
    //  branch name change -- 15 sep 2023
    Route::post('branch/branch_update', 'Admin\BranchController@BranchUpdate')->name('admin.branchUpdate');
    /// ---- Branch Managements End  -----------
    /// ---- Branch Managements End  -----------
    Route::get('branch_Balance_crone', 'Admin\BranchController@branch_Balance_crone');
    Route::get('branchbalanceInableOrDescablecrone/{id}', 'Admin\BranchController@branchbalanceInableOrDescablecrone');
    // ------------------------------ Branch log start---------------------------------------////
    Route::post('branch/branch_remove_cash', 'Admin\BranchController@BranchRemoveCash')->name('admin.branchRemoveCash');
    Route::get('branch/branch_limit_change', 'Admin\BranchController@BranchLimitChange');
    Route::post('branch/branch_limit_update', 'Admin\BranchController@Branchlimitupdate')->name('admin.branchlimitupdate');
    Route::get('branch-log', 'Admin\BranchController@branch_log')->name('admin.branch.logs');
    Route::post('branch-log/filter', 'Admin\BranchController@branch_log_filter')->name('admin.branch.logs.filter');
    /** this is moneyBackCron for money back on relavent account number on relevent date
     * Created and modify by Sourab on 05-10-2023
     */
    Route::get('money-back-cron/{account_no}/{date}', 'Admin\CommanController@moneyBackCron');
    /**
     * Assest route from shahid     
     * create by shahid in shareholder controller on 13-10-23
     */
    Route::post('aadharExist', 'Admin\Shareholder\ShareHolderController@aadharExist')->name('admin.aadhar.exist');
    Route::post('panExist', 'Admin\Shareholder\ShareHolderController@panExist')->name('admin.pan.exist');
    Route::post('directorCompany', 'Admin\Shareholder\ShareHolderController@directorCompany')->name('admin.director.company');
    Route::post('shareholderCompany', 'Admin\Shareholder\ShareHolderController@shareholderCompany')->name('admin.shareholder.company');
    Route::post('shareholderCompanydeposite', 'Admin\Shareholder\ShareHolderController@shareholderDepositeCompany')->name('admin.shareholder.deposite.company');
    Route::post('registerplan/approve_cheques_company', 'Admin\CommanController@approveReceivedChequeCompany')->name('admin.approve_recived_cheque_list_company');
    Route::post('getBanks', 'Admin\ChequeController@getbanks')->name('admin.banks_list');
    Route::get('copanyBranch', 'Admin\Shareholder\ShareHolderController@getbranches')->name('admin.getBranches');
    /* on 10-oct-2023 11:01:54 ist
     */
    Route::post('asset-items-get', 'Admin\Asset\AssetController@asset_items')->name('admin.asset.get.items');

    /** loan status by gaurav start */
    Route::post('loan/plan_tenure/status', 'Admin\LoanController@loan_tenure_status')->name('admin.loan.loan_tenure_status');
    /** loan status by gaurav end */

    //Plan Account Details created by Durgesh 29-09-2023
    Route::get('plan_account_management', 'Admin\PschemeController@index')->name('admin.planLog.detail');
    Route::post('plan_account_log', 'Admin\PschemeController@index')->name('admin.Log.detail');
    Route::post('plan_log_management', 'Admin\PschemeController@getPlanName')->name('admin.planLog.name');
    //Plan Account Details created by Durgesh 29-09-2023

    //-------------------------Create by Gaurav start on 16-10-2023-----------------------------//
    Route::post('py-plans/tenure/status', 'Admin\PyschemeController@tenure_status')->name('admin.py-plans.tenure.status');
    //-------------------------Create by Gaurav end-----------------------------//

    /** plan tenure reate start */
    Route::post('py-plans/tenure/plan_tenure_listing', 'Admin\PyschemeController@plan_tenure_listing')->name('admin.py-plans.tenure.plan_tenure_listing');
    Route::post('investment/plan/commission/percentage/modelShow', 'Admin\PyschemeController@modelShow')->name('admin.investment.plan.commissionPercentage.modelShow');
    /** plan tenure reate end */

    /** Member blaklist for loan start  on 16 nov-2023 by Mahesh */
    Route::post('member_blacklist_on_loan_listing', 'CommonController\MemberBlacklist\MemberBlacklistController@member_blacklist_on_loan_listing')->name('admin.member_blacklist_on_loan_listing');
    Route::get('blacklist-members-on-loan', 'CommonController\MemberBlacklist\MemberBlacklistController@index')->name('admin.blacklist-members-on-loan');
    Route::post('exportblacklist_memberlist_export', 'CommonController\MemberBlacklist\MemberBlacklistController@exportMemberBlacklistOnLoan')->name('admin.member_blacklist_on_loan_listing_export');
    Route::post('action_blacklist_member_for_loan', 'CommonController\MemberBlacklist\MemberBlacklistController@actionBlacklistMemberForLoan')->name('admin.common_controller.action_blacklist_member_for_loan');
    Route::get('add-blacklistmember-on-loan', 'CommonController\MemberBlacklist\MemberBlacklistController@addBlacklist')->name('add-blacklist-member-on-loan');
    Route::post('block_details', 'CommonController\MemberBlacklist\MemberBlacklistController@blockDetails')->name('admin.block_details');
    Route::post('member_blacklist_member_data', 'CommonController\MemberBlacklist\MemberBlacklistController@getBlacklistMemberData')->name('admin.member_blacklist_member_data');
    /** Member blaklist for loan end */
    /**
     * loan emi payment new module create by shahid 21/11/23
     */
    Route::post('common_loan_account_details', 'CommonController\LoanEmiPayment\LoanEmiPaymentController@getAccountDetails')->name('admin.common.LoanAccountDetails');
    Route::post('getAccountDetailsAdmin', 'Branch\PaymentManagement\WithdrawalController@accountDetails')->name('common.withdraw.accountdetails');
    Route::post('send-ssb-otp-admin', 'Branch\PaymentManagement\WithdrawalController@sendOtpToSSB')->name('admin.send.ssb.otp');
    Route::post('verify-ssb-otp-admin', 'Branch\PaymentManagement\WithdrawalController@verifySSbOtp')->name('admin.verify.ssb_otp');
    Route::post('update-ssb-otp', 'Branch\PaymentManagement\WithdrawalController@updateSSbOtp')->name('admin.update.ssb.otp');
    Route::get('common_loan_emi_payment', 'CommonController\LoanEmiPayment\LoanEmiPaymentController@index')->name('admin.common.LoanEmiPayment');
    Route::post('deposite-loan-emi', 'Admin\LoanController@depositeLoanEmi')->name('admin.loan.depositeloanemi');


    // Add by shahid on 02/02/2024 for ecs charge
    Route::post('ref-no-store', 'Admin\LoanController@refNoStore')->name('admin.loan.refNoStore');
    // create by shahid for common use ecs
    Route::post('refNoExist', 'Branch\LoanController@refNoExist')->name('ecs.refNo.exist');


    /**
     * end rout loan emi payment module new
     */

    //SSB Member - Account Status created by Durgesh 17-10-2023
    Route::get('ssbaccountstatus', 'Admin\InvestmentCollector\SSBAccountStatusController@index')->name('admin.investment.ssbaccountstatus');
    Route::post('ssbaccountstatus/account', 'Admin\InvestmentCollector\SSBAccountStatusController@show')->name('admin.investment.ssbaccountstatus.show');
    Route::post('ssbaccountstatus/status_check', 'Admin\InvestmentCollector\SSBAccountStatusController@status_check')->name('admin.investment.ssbaccountstatus.status_check');
    Route::post('ssbaccountstatus/listing', 'Admin\InvestmentCollector\SSBAccountStatusController@listing')->name('admin.investment.ssbaccountstatus.listing');
    Route::post('ssbaccountstatus/export', 'Admin\InvestmentCollector\SSBAccountStatusController@export')->name('admin.investment.ssbaccountstatus.export');

    /**rcvd chq route by mahesh 12-12-2023 */
    Route::post('advancePayment/approve_cheque_details', 'Admin\AdvancePayment\AdvancePaymentController@approveReceivedChequeDetails')->name('admin.approve_cheques_details');

    // renewal on admin
    Route::get('correction/requests', 'Admin\CorrectionController@correctionRequestviewnew')->name('admin.correctionrequest.view');
    Route::post('correction/requestlist', 'Admin\CorrectionController@correctionRequestlists')->name('admin.correctionrequest.list');
    Route::post('reject/correctionrequest', 'Admin\CorrectionController@rejectCorrectionRequest')->name('correction.reject.request');
    Route::post('approve/correctionrequest', 'Admin\CorrectionController@approveCorrectionRequest')->name('correction.approve.request');
    Route::post('export/correctionrequest', 'Admin\CorrectionController@exportcorrection')->name('correction.export.request');

    // create 15-12-2023
    Route::post('branchBalanceGet', 'Admin\HrManagement\SalaryController@getbranchbankbalanceamount')->name('admin.hr.salary.getbranchbankbalanceamount');

    /** Investment account commission detail get  created by Durgesh 10-01-2023  */
    Route::get('investment/commission/{id}', 'Admin\CommissionDetailReportController@investmentCommission')->name('admin.investment.commission');
    Route::post('investmentcommissionlisting', 'Admin\CommissionDetailReportController@investmentCommissionListing')->name('admin.investment.commissionlisting');
    Route::post('investmentcommissionexport', 'Admin\CommissionDetailReportController@exportInvestmentCommission')->name('admin.investmentcommission.export');
    Route::get('loan/commission/{id}', 'Admin\CommissionDetailReportController@loanCommission')->name('admin.loan_commission');
    Route::post('loan-commission', 'Admin\CommissionDetailReportController@loanCommissionList')->name('admin.loan_commission_list');
    Route::post('loanCommissionExport', 'Admin\CommissionDetailReportController@loanCommissionExport')->name('admin.loan.loanCommissionExport');

    /** Investment account commission detail end  created by Durgesh 10-01-2023  */
    Route::get('employe/details', 'Admin\HrManagement\EmployeeController@all_img')->name('admin.employe');

    /** make code live by sourab on 29-001-2024 */
    /**Created by Gaurav 19-12-2023 */
    Route::get('member-registration', 'CommonController\Member\MemberController@register')->name('admin.member.registration');
    Route::post('member-registration/emp_detail', 'CommonController\Member\MemberController@empDetail')->name('admin.member.empDetail');
    Route::post('member-registration-save', 'CommonController\Member\MemberController@save')->name('admin.member-registration.save');
    Route::post('get_associateMember', 'CommonController\Member\MemberController@getAssociateMember')->name('admin.associate_member');

    Route::post('check_idProof', 'CommonController\Member\MemberController@getMemberFromIdProof')->name('admin.check_idProof');
    Route::post('member/update', 'CommonController\Member\MemberController@save')->name('admin.memberUpdate');
    Route::get('members', 'CommonController\Member\MemberController@index')->name('admin.member.index');
    /**End */

    /********************** Employee Comman Register  start **************************/

    /** Created by Durgesh 20-12-2023 */
    Route::get('employee/register', 'CommonController\Employee\EmployeeController@add')->name('admin.employee_add');
    Route::post('employee/companydate', 'CommonController\Employee\EmployeeController@companydate')->name('admin.employee.companydate');
    Route::post('employee-save', 'CommonController\Employee\EmployeeController@employeeSave')->name('admin.employee_save');
    /********************** Employee Comman Register  end **************************/

    //created by Durgesh 19-12-2023--------
    Route::post('employeeDetail', 'CommonController\Employee\EmployeeController@employeeDetail')->name('admin.employeeDetail');
    Route::post('designationByCategory', 'CommonController\Employee\EmployeeController@designationByCategory')->name('admin.designationByCategory');
    Route::post('check-ssb-account', 'CommonController\Employee\EmployeeController@checkSsbAccount')->name('admin.check.ssb.account');
    Route::post('designationDataGet', 'CommonController\Employee\EmployeeController@designationDataGet')->name('admin.designationDataGet');
    Route::post('ssbDataGet', 'CommonController\Employee\EmployeeController@ssbDataGet')->name('admin.ssbDataGet');

    // route created by sourab on 19-12-2023 for head-leadger_listing export
    Route::post('head_ledger_listing_export', 'Admin\LedgerController@head_ledger_listing_export')->name('admin.head_ledger_listing_export.export');

    // Created by shahid 12/01/24 ledger listing
    Route::post('ledger_listing_export', 'Admin\LedgerRecordController@exportledgerRecordListing')->name('admin.ledger_listing_export.export');
    Route::post('getDistrict', 'Admin\MemberController@getDistrict')->name('admin.districtlists');
    Route::post('getCity', 'Admin\MemberController@getCity')->name('admin.citylists');
    Route::post('get_District', 'CommonController\Member\MemberController@getDistrict')->name('admin.district_lists');
    Route::post('get_City', 'CommonController\Member\MemberController@getCity')->name('admin.city_lists');
    /** created by sourab on 17-01-24 start for adding cash in hand feedback*/
    Route::post('region/sector', 'Admin\CashInHandController@region_sector')->name('admin.region_sector');
    Route::post('sector/branch', 'Admin\CashInHandController@sector_branch')->name('admin.sector_branch');
    Route::post('cash_in_hand_listing/export', 'Admin\CashInHandController@export')->name('admin.cashInHand.list.export');
    /** created by sourab on 17-01-24 end for adding cash in hand feedback*/
    /** ecs type update by sourab on 20-02-2024 start */
    Route::get('loan/ecs_change', 'Admin\LoanCollector\LoanCollectorChangeController@index')->name('admin.loan.loancollector.ecschangeindex');
    /** ecs type update by sourab on 20-02-2024 end */
    // created by sourab biswas for cron testing perpose only
    Route::get('command/{command}', 'Admin\Cron\CronController@command');

    Route::post('save_temp_data', 'Admin\CorrectionController@registerSSbRequiredData')->name('admin.registerSSbRequiredData.data');
    // created by sourab biswas for cron testing perpose only transfer
    Route::get('command/{command}', 'Admin\Cron\CronController@command');

    /**Below Routes are made by mahesh on 07 march 2024 for loan ecs bank import */
    Route::post('/ecs_import_listing', 'Admin\Loan\EcsController@ecs_import_listing')->name('admin.loan.ecs_import_listing');
    Route::get('/loan/bank-ecs-import', 'Admin\Loan\EcsController@importview')->name('admin.loan.importView');
    Route::post('/loan/import-view', 'Admin\Loan\EcsController@import')->name('admin.import-csv');
    Route::post('/loan/import_data', 'Admin\Loan\EcsController@import_data')->name('admin.loan.import_data');
    /**Above Routes are made by mahesh on 07 march 2024 for loan ecs bank import */
    /** Created by Durgesh 29-02-2024 */
    Route::get('loan/ecs/ecs_transactions', 'CommonController\Ecs\EcsController@index')->name('admin.ecs.ecs.transactions_list');
    Route::post('loan/ecs/ecs_transactions_list', 'CommonController\Ecs\EcsController@getData')->name('admin.ecs.ecs.transactions_listing');
    Route::post('loan/ecs/ecs_transactions_list_export', 'CommonController\Ecs\EcsController@ecsExport')->name('admin.ecs.ecs.transactions_export');
    // Created by Durgesh End here  ------------------------------------>
    Route::get('report/day_business_report', 'CommonController\DayBussinessReportController@index')->name('admin.bussiness.report');
    //Day Bussiness Report created by Durgesh 04-10-2023
    Route::post('day_book_report', 'CommonController\DayBussinessReportController@reportDetail')->name('admin.dayBook.report');
    Route::post('fetchBranch', 'CommonController\DayBussinessReportController@getBranch')->name('admin.fetchbranch');
    Route::post('day_book_report_export', 'CommonController\DayBussinessReportController@reportExport')->name('admin.dayBook.report.Export');

    // salary transfer delete route created by mahesh on 27-03-2024
        Route::post('salary_delete', 'Admin\HrManagement\SalaryController@salary_delete')->name('admin.hr.salary_delete');
    Route::get('hr/salary/salary_edit/{id}', 'Admin\HrManagement\SalaryController@salary_edit')->name('admin.hr.salary_edit');
    Route::post('hr/salary/salary_edit_save', 'Admin\HrManagement\SalaryController@salary_edit_save')->name('admin.hr.salary_edit_save');
    Route::get('rent/ledger-edit/{id}', 'Admin\RentManagement\RentController@rent_edit')->name('admin.rent_ledger.edit');
    Route::post('rent/ledger-edit-save', 'Admin\RentManagement\RentController@rent_edit_save')->name('admin.rent.rent_ledger_save');
    Route::post('payment_delete', 'Admin\RentManagement\RentController@payment_delete')->name('admin.rent.payment_delete');
    Route::get('hr/salary/regenerate/{id}', 'Admin\HrManagement\SalaryController@regenerate_salary')->name('admin.hr.regenerate_salary');
    Route::post('hr/salary/salary_regenerate', 'Admin\HrManagement\SalaryController@salary_regenerate')->name('admin.hr.salary_regenerate');
    Route::get('rent/regenerate/{id}', 'Admin\RentManagement\RentController@regenerate_rent')->name('admin.rent.regenerate_rent');
    Route::post('rent/rent_regenerate', 'Admin\RentManagement\RentController@rent_regenerate')->name('admin.rent.rent_regenerate');
    // created by mahesh for part paymnet in ta on 04-03-2024
    Route::get('/part_payment/{id}', 'Admin\AdvancePayment\AdvancePaymentController@part_payment')->name('admin.advancePayment.part_payment');
    Route::post('/partpayment/save', 'Admin\AdvancePayment\AdvancePaymentController@partpaymentsave')->name('admin.advancepartPayment.save');
    Route::post('/partpayment/recived_cheque', 'Admin\AdvancePayment\AdvancePaymentController@recived_cheque')->name('admin.advancePayment.recived_cheque');
    //GST Payable transfer listiong start
    Route::post('pay-gst-transfer-payable-amount', 'Admin\GstController@paygstTransferAmount')->name('admin.paygstTransferAmount');
    Route::post('gst_payable_listing', 'Admin\GstController@gst_payable_listing')->name('admin.gst_payable_listing');
    Route::get('add-gst-payable', 'Admin\GstController@add_gst_payable')->name('admin.add-gst-payable');
    Route::post('gst_transfer_listing', 'Admin\GstController@gst_transfer_listing')->name('admin.gst_transfer_listing');
    Route::get('gst_transfer_pay/{companyId}/{id}', 'Admin\GstController@gst_transfer_pay')->name('admin.gst_transfer_pay');
    Route::get('gst_transfer_view/{companyId}/{id}', 'Admin\GstController@gst_transfer_view')->name('admin.gst_transfer_pay.view');
    Route::get('gst_transfer_pay/displayImage', 'Admin\GstController@displayImage')->name('admin.gst_payable_chalan.view');
    Route::post('get-gst-payable-amount', 'Admin\GstController@getgstPayableAmount')->name('admin.gstpayableamount');
    Route::post('pay-gst-payable-amount', 'Admin\GstController@payGstPayableAmount')->name('admin.paygstpayableamount');
    Route::post('export_gst_payable', 'Admin\GstController@export_gst_payable')->name('admin.gst_payable.export_gst_payable');
    Route::post('export_gst_transafer', 'Admin\GstController@export_gst_transafer')->name('admin.gst_transafer.export_gst_transafer');
    Route::post('compay_to_state', 'Admin\GstController@compay_to_state')->name('admin.compay_to_state');
    Route::post('transactionNumberCheck', 'Admin\GstController@transactionNumberCheck')->name('admin.gst.transactionNumberCheck');
    Route::get('gst-transfer-listing', 'Admin\GstController@gst_transferlisting')->name('admin.gstTransfer-listing');
    Route::post('gst_transfer_pay/chalandownload', 'Admin\GstController@chalandownload')->name('admin.gst_payable_chalan.download');
    Route::get('gst_setoff', 'Admin\GstController@gst_setoff')->name('admin.gst_setoff');
    Route::post('gst_setoff_listing', 'Admin\GstController@gst_setoff_listing')->name('admin.gst_setoff_listing');

    // wrong emi outstanding update 
    Route::get('loan/update/emioutstanding', 'Admin\Loan\EmiOutstandingUpdateController@index')->name('admin.loan.emioutstanding.update');
    Route::post('emi/emioutstanding/update', 'Admin\Loan\EmiOutstandingUpdateController@update')->name('admin.outstanding.emi.update');
    Route::post('emi/account/details', 'Admin\Loan\EmiOutstandingUpdateController@accountDetails')->name('admin.emi.account.details');
    //GST Payable end
    // created by sourab biswas for tds / gst payabkle entry atart.
    Route::group(['prefix' => 'duties_taxes'], function () {
        // gst
        Route::group(['prefix' => 'gst'], function () {
            Route::group(['prefix' => 'setting'], function () {
                Route::get('company_settings', 'Admin\DutiesAndTaxesController@company_settings')->name('admin.duties_taxes.gst.setting.company_settings');
                Route::get('add_company_settings', 'Admin\DutiesAndTaxesController@add_company_settings')->name('admin.duties_taxes.gst.setting.add_company_settings');
                Route::post('save_company_settings', 'Admin\DutiesAndTaxesController@save_company_settings')->name('admin.duties_taxes.gst.setting.save_company_settings');
                Route::get('edit_company_settings/{id}', 'Admin\DutiesAndTaxesController@edit_company_settings')->name('admin.duties_taxes.gst.setting.edit_company_settings');
                Route::get('log_company_settings/{id}/{type}', 'Admin\DutiesAndTaxesController@log_company_settings')->name('admin.duties_taxes.gst.setting.log_company_settings');
                Route::post('update_company_settings', 'Admin\DutiesAndTaxesController@update_company_settings')->name('admin.duties_taxes.gst.setting.update_company_settings');

                Route::post('company_settings_listing', 'Admin\DutiesAndTaxesController@company_settings_listing')->name('admin.duties_taxes.gst.setting.company_settings_listing');

                Route::get('head_settings', 'Admin\DutiesAndTaxesController@head_settings')->name('admin.duties_taxes.gst.setting.head_settings');
                Route::get('add_head_settings', 'Admin\DutiesAndTaxesController@add_head_settings')->name('admin.duties_taxes.gst.setting.add_head_settings');
                Route::get('edit_head_settings/{id}', 'Admin\DutiesAndTaxesController@edit_head_settings')->name('admin.duties_taxes.gst.setting.edit_head_settings');
                Route::post('save_head_settings', 'Admin\DutiesAndTaxesController@save_head_settings')->name('admin.duties_taxes.gst.setting.save_head_settings');
                Route::post('update_head_settings', 'Admin\DutiesAndTaxesController@update_head_settings')->name('admin.duties_taxes.gst.setting.update_head_settings');
                Route::get('head_settings_list', 'Admin\DutiesAndTaxesController@head_settings_list')->name('admin.duties_taxes.gst.setting.head_settings_list');
                Route::post('head_settings_listing', 'Admin\DutiesAndTaxesController@head_settings_listing')->name('admin.duties_taxes.gst.setting.head_settings_listing');

                Route::get('log_detail', 'Admin\DutiesAndTaxesController@log_detail')->name('admin.duties_taxes.gst.setting.log_detail');
                Route::post('log_detail_listing', 'Admin\DutiesAndTaxesController@log_detail_listing')->name('admin.duties_taxes.gst.setting.listing');
            });
            Route::group(['prefix' => 'report'], function () {
                Route::get('outward_supply', 'Admin\DutiesAndTaxesController@outward_supply')->name('admin.duties_taxes.gst.report.outward_supply');
                Route::post('export_outward_supply', 'Admin\DutiesAndTaxesController@export_outward_supply')->name('admin.duties_taxes.gst.report.export_outward_supply');
                Route::get('cr_dr_note', 'Admin\DutiesAndTaxesController@cr_dr_note')->name('admin.duties_taxes.gst.report.cr_dr_note');
                Route::get('summary_supply', 'Admin\DutiesAndTaxesController@summary_supply')->name('admin.duties_taxes.gst.report.summary_supply');
                Route::get('collection', 'Admin\DutiesAndTaxesController@collection')->name('admin.duties_taxes.gst.report.collection');
                Route::post('collection/listing', 'Admin\DutiesAndTaxesController@collection_listing')->name('admin.duties_taxes.gst.report.collection.listing');
                Route::post('collection/listing_export', 'Admin\DutiesAndTaxesController@collection_listing_export')->name('admin.duties_taxes.gst.report.collection.listing.export');
            });
            Route::get('customer_transactions', 'Admin\DutiesAndTaxesController@gst_customer_transactions')->name('admin.duties_taxes.gst.customer_transactions');
            Route::post('gst_customer_transactions_listing', 'Admin\DutiesAndTaxesController@gst_customer_transactions_listing')->name('admin.duties_taxes.gst.gst_customer_transactions_listing');
        });
        // tds
        Route::group(['prefix' => 'tds'], function () {
            Route::group(['prefix' => 'setting'], function () {
                Route::get('tds_settings', 'Admin\DutiesAndTaxesController@tds_settings')->name('admin.duties_taxes.tds.setting.tds_settings');
                Route::get('tds_settings/add', 'Admin\DutiesAndTaxesController@add_tds_settings')->name('admin.duties_taxes.tds.setting.add_tds_settings');
                Route::get('tds_log_detail', 'Admin\DutiesAndTaxesController@tds_log_detail')->name('admin.duties_taxes.tds.setting.tds_log_detail');
                Route::get('customer_transactions', 'Admin\DutiesAndTaxesController@tds_customer_transactions')->name('admin.duties_taxes.tds.setting.customer_transactions');
            });
        });
        Route::get('transfer', 'Admin\DutiesAndTaxesController@transfer')->name('admin.duties_taxes.transfer');
        Route::post('transfer', 'Admin\DutiesAndTaxesController@transferReq')->name('admin.duties_taxes.tds.transfer');
        Route::get('transfer_list', 'Admin\DutiesAndTaxesController@transferlist')->name('admin.duties_taxes.transfer_list');
        Route::post('transfer_list', 'Admin\DutiesAndTaxesController@transferListing')->name('admin.duties_taxes.transfer_listing');
        Route::post('export_transafer_list', 'Admin\DutiesAndTaxesController@export_transafer_list')->name('admin.duties_taxes.export_transafer_list');
        Route::get('payable', 'Admin\DutiesAndTaxesController@payable')->name('admin.duties_taxes.tds.payable');
        Route::post('payable', 'Admin\DutiesAndTaxesController@pay')->name('admin.duties_taxes.pay');
        Route::get('payable_list', 'Admin\DutiesAndTaxesController@payable_list')->name('admin.duties_taxes.payable_list');
        Route::post('payable_listing', 'Admin\DutiesAndTaxesController@payable_listing')->name('admin.duties_taxes.payable_listing');
        Route::post('payable_listing_export', 'Admin\DutiesAndTaxesController@payable_listing_export')->name('admin.duties_taxes.payable_listing_export');
        Route::post('payableHeadAmount', 'Admin\DutiesAndTaxesController@payableHeadAmount')->name('admin.duties_taxes.payable');
    });
    Route::get('transfer_pay', 'Admin\TdspayableController@transfer_pay')->name('admin.transfer_pay');
    Route::post('gettdspayable/transfer_pay/gettdspayable', 'Admin\DutiesAndTaxesController@gettdspayabledetails')->name('admin.gettdspayable.head_type');
    // created by sourab biswas for tds / gst payabkle entry end.
    // All Holidays cron Routs

    Route::get('settings/allholiday/crons', 'Admin\AllHolidayCronsController@index')->name('admin.allholiday.crons');
    Route::post('settings/holiday/crons/save', 'Admin\AllHolidayCronsController@save')->name('admin.allholiday.crons.save');
    Route::get('settings/allholiday/crons/logs/{id?}', 'Admin\AllHolidayCronsController@cronLogs')->name('admin.check.holiday.cron.logs');
    Route::post('settings/allholiday/crons/status', 'Admin\AllHolidayCronsController@cronStatus')->name('admin.allholiday.crons.status');

    // fund transfer delete route created by sourab biswas on 12-04-24
    Route::post('fund-transfer/delete','Admin\PaymentManagement\FundTransferController@deleteFundTransfer')->name('admin.fund_transfer.delete');
    Route::get('fund-transfer/logs/{id}','Admin\PaymentManagement\FundTransferController@fundtransferlogs')->name('admin.fund_transfer.logs');
    Route::post('fund-transfer/logs/listing','Admin\PaymentManagement\FundTransferController@fundtransferlogslisting')->name('admin.fund-transfer.logs.listing');
    Route::get('base64/{code}','Admin\PaymentManagement\FundTransferController@base64')->name('admin.base64');
    // fund transfer delete route created by sourab biswas on 12-04-24
    
    // Created by shahid 08/01/24 loan edit
    Route::get('loan/log', 'Admin\LoanController@getLoanLogs')->name('getLoanlogs');
    Route::get('loan/ecs_deduction', 'Admin\Loan\EcsDeductionController@index')->name('admin.loan.ecsDeduction');
    Route::post('loan/ecs_deduction/listing', 'Admin\Loan\EcsDeductionController@listing')->name('admin.loan.ecsDeduction.listing');
    Route::post('loan/ecs_deduction/export', 'Admin\Loan\EcsDeductionController@export')->name('admin.loan.ecs_deduction.export');
    Route::post('loan/ecs_deduction/bankexport', 'Admin\Loan\EcsDeductionController@bankExport')->name('admin.loan.ecs_deduction.bankexport');

    Route::get('loan/ecs/bounce_charges/current_status', 'Admin\Settings\LoanEcsBounceChargesController@ecsBounceChargesStatus')->name('admin.loan.ecs.bounce_charge.status');
    Route::post('loan/ecs/bounce_charges/current_status/listing', 'Admin\Settings\LoanEcsBounceChargesController@ecsBounceChargesStatusListing')->name('admin.loan.ecs.bounce_charge.status.listing');
    Route::post('loan/ecs/bounce_charges/current_status/listing/export', 'Admin\Settings\LoanEcsBounceChargesController@ecsBounceChargesStatusListingExport')->name('admin.loan.ecs.bounce_charge.status.listing.export');
   
});
/*-------------- Admin End--------------*/
Route::post('getPlanbyCompanyId', 'CommonController\Investment\InvestmentReportController@getmyplan')->name('getPlanByCompanyId');
/*-------------- Super Admin Start--------------*/
Route::group(['prefix' => 'super-admin'], function () {
    Route::get('/', 'SuperAdmin\AdminLoginController@index')->name('Admin.loginForm');
    Route::post('/', 'SuperAdmin\AdminLoginController@authenticate')->name('Admin.login');
});
Route::group(['prefix' => 'super-admin', 'middleware' => 'auth:superAdmin'], function () {
    Route::get('/logout', 'SuperAdmin\AdminController@logout')->name('Admin.logout');
    Route::get('/dashboard', 'SuperAdmin\AdminController@dashboard')->name('Admin.dashboard');
});
/*-------------- Super Admin End--------------*/