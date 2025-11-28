<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Session;
use Auth;
use App\Http\Controllers\Admin\CommanController;
use App\Models\LoanDayBooks;
use App\Models\Memberloans;
use App\Models\Grouploans;
use App\Models\SavingAccountTranscation;
use App\Models\BranchDaybook;
use App\Http\Controllers\Api\AssociateRegistration\AssociateLoanController;
use Illuminate\Http\Request;
use Exception;
use App\Models\SystemDefaultSettings;
use App\Models\Loans;
use App\Services\InvestmentService;
use App\Http\Resources\AssociateLoanResource;
use App\Services\Sms;


class LoanEmiPaymentService
{
    const PAYMENT_MODE_SSB = 3;
    const CURRENCY_CODE = "INR";
    const ASSOCIATE_WITHDRAWAL_LIMIT = "associate_withdrawal_limit";
    const IS_APP = 1;
    protected $errors;
    protected $associateLoan;
    private $errorMessage;

    /**
     * Recover laon Emi based on the provided accountNumbers
     * @param string loan_type
     * @param array data , it consist accountNumber and amount
     * @return AssociateLoanResource
     */
    public function depositeLoanEmi(
        $loan,
        $accountNumbers,
        $globalDate,
        $memberDetailSaving,
        $epassBook = null,
        $mLoan = null
    ) {
        $isEpassBook = $epassBook;
        $emiDate = date("Y-m-d", strtotime($globalDate));
        $entryTime = date("H:i:s");
        $created_at = $this->formatDateWithTime($emiDate, $entryTime);


        Session::put("created_at", $created_at);

        // Array for store Saving transaction data
        $ssbTransactionArray = [];

        // Array for store Branch Daybook transaction data
        $branchDaybookTransaction = [];

        // Payment Form Associate  Saving Account So payment mode is  3
        $ssbpaymentMode = self::PAYMENT_MODE_SSB;

        // Currency Code
        $currency_code = self::CURRENCY_CODE;

        // Array for store loan Daybook transaction data
        $loanDaybookArray = [];
        // Transaction begin
        DB::beginTransaction();

        try {
            // Iterate the each accountNumber
            foreach ($accountNumbers as $accountNumber) {

                $memberLoanData = $loan::select('customer_id', 'approve_date')->where('account_number', $accountNumber['account_number'])->first();
                //    dd($memberLoanData);
                // Retrive Login Associate Saving Account Detail based on Company Id
                $ssbBalance = $this->getSavingBalance(
                    $memberDetailSaving["savingAccount_Custom3"],
                    $emiDate,
                    $accountNumber["amount"]
                );

                // Check status code of the response
                if (
                    method_exists($ssbBalance, "getStatusCode") &&
                    $ssbBalance->getStatusCode() === 400
                ) {
                    return $ssbBalance;
                }

                //  Check Saving
                // Processed Loan Emi based on provided details
                $processedEmi = $this->processLoanEmi(
                    $loan,
                    $accountNumber,
                    $emiDate,
                    $entryTime,
                    $created_at,
                    $memberDetailSaving,
                    $ssbTransactionArray,
                    $branchDaybookTransaction,
                    $ssbBalance,
                    $isEpassBook
                );
                $data[] = $processedEmi;
                if ($processedEmi) {
                    $totalDepsoit = LoanDaybooks::where('account_number', $accountNumber["account_number"])->where('loan_sub_type', 0)->where('is_deleted', 0)->sum('deposit');


                    $text = 'Dear Member, Received Rs.' . $accountNumber['amount'] . ' as EMI of Loan A/C ' . $accountNumber["account_number"] . ' on ' . date('d/m/Y', strtotime(convertDate($emiDate ?? ''))) . ' Total recovery Rs. ' . $totalDepsoit . ' Thank You. Samraddh Bestwin Microfinance';

                    //  $text = 'Dear Member,Received Rs.' .  $request['deposite_amount'] . ' as EMI of Loan A/C ' . $mLoan->account_number . ' on ' . date('d/m/Y', strtotime(convertDate($request['application_date']))) . ' Total recovery Rs. ' . $totalDepsoit . ' Thank You. Samraddh Bestwin Microfinance';;


                    $temaplteId = 1207166308935249821;
                    $contactNumber = array();

                    $contactNumber[] = $memberLoanData->loanMember->mobile_no;

                    $sendToMember = new Sms();
                    $sendToMember->sendSms($contactNumber, $text, $temaplteId);
                }

            }
            DB::commit();
            return response()->json([
                "status" => "success",
                "message" => "EMI deposited successfully",
                "data" => $data,
            ]);
        } catch (\Exception $ex) {
            DB::rollback();
            throw new Exception(
                "Error in function " .
                __FUNCTION__ .
                " at line " .
                $ex->getLine() .
                ": " .
                $ex->getMessage(),
                0,
                $ex
            );
        }
    }

    /**
     * Process a loan EMi Payment
     * @param mixed $loan The model based on Loan Type i:e Memberloans and Grouploans
     * @param array $accountNumber an array containing Emi  account Details (account_number and amount)
     * @param string $emiDate The date of EMI Payment
     * @param string $entryTime The time of EMI Payment
     * @param string $created_at  The creation timestamp for transaction
     * @param mixed $memberDetailSaving Detail of the Login Associate
     *
     */

    private function processLoanEmi(
        $loan,
        $accountNumber,
        $emiDate,
        $entryTime,
        $created_at,
        $memberDetailSaving,
        $ssbTransactionArray,
        $branchDaybook,
        $ssbBalance,
        $isEpassBook
    ) {


        DB::beginTransaction();

        try {
            // Generate VNo for the transaction
            $vNo = $this->generateVoucherNumber();

            // Extract emiAmount and emiAccountNumber form array of AccountNumber
            $emiAmount = $accountNumber["amount"];
            $emiAccountNumber = $accountNumber["account_number"];


            // Retrive loan account Number detail based on accountNumber and $loan is  a Model Name (Memberloans or GroupLoans)
            $loanDetails = $loan
                ::with([
                    "loan" => function ($q) {
                        $q->select("id", "loan_type");
                    },
                ])
                ->where("account_number", $emiAccountNumber)
                ->first();


            // Check loan is exist or not
            if (!isset($loanDetails)) {
                $investmentService = new InvestmentService();
                return $investmentService->handleDataNotFound(
                    "Record Not Found"
                );

            }
            // Intialise Variables
            $roi = 0;

            $lData["accrued_interest"] = $loanDetails->accrued_interest - $roi;

            // Generate unique  daybook_ref_id from branch_daybook_reference table
            $daybookRefId = CommanController::createBranchDayBookReference(
                $emiAmount
            );

            // Create a description for an amount received from a member with the specified account number.
            $description =
                "Amount Transfer to " .
                $loanDetails->account_number;

            // Create a description_dr for amount debit from associate saving account
            $descriptionDR = "SSB A/C Dr " . $emiAmount . "";

            // Create a description_cr for amount credit from associate saving account

            $descriptionCR =
                "To " .
                $loanDetails->account_number .
                " A/C Cr " .
                $emiAmount .
                "";

            // Create a description for amount transfer
            $descriptionSSb =
                "Loan Emi Transfer To " . $loanDetails->account_number;

            // Prepare Loan Daybook   transaction data
            $loanDaybookArray = $this->prepareLoanDaybookData(
                $daybookRefId,
                $loanDetails,
                $emiAmount,
                "Amount Received from " .
                $memberDetailSaving["savingAccount_Custom3"]->account_no,
                self::CURRENCY_CODE,
                $emiDate,
                $entryTime,
                "CR",
                4,
                $memberDetailSaving,
                $isEpassBook
            );

            // Insert a record in a LoanDaybook table
            $createLoanDaybook = $this->insertLoanDaybook($loanDaybookArray);

            // Prepare SSB transaction data
            $ssbTransactionArray = $this->prepareSsbTransactionData(
                $memberDetailSaving["savingAccount_Custom3"]->id,
                $memberDetailSaving["savingAccount_Custom3"]->account_no,
                $ssbBalance->opening_balance - $emiAmount,
                $memberDetailSaving->branch_id,
                $emiAmount,
                $description,
                self::CURRENCY_CODE,
                "DR",
                $emiDate,
                $entryTime,
                $loanDetails,
                $daybookRefId,
                $isEpassBook
            );

            $createSavingAccountTransaction = $this->insertSavingAccountTransaction(
                $ssbTransactionArray
            );

            // Prepare Branch Daybook CR (credit) transaction data
            $branchDaybookTransaction[] = $this->prepareBranchDaybookTransactionData(
                $daybookRefId,
                $memberDetailSaving->branch_id,
                5,
                52,
                $loanDetails->id,
                $createLoanDaybook->id,
                $memberDetailSaving->id,
                $loanDetails->applicant_id,
                $emiAmount,
                $description,
                $descriptionDR,
                $descriptionCR,
                "CR",
                self::PAYMENT_MODE_SSB,
                self::CURRENCY_CODE,
                $vNo,
                $memberDetailSaving["savingAccount_Custom3"]->id,
                $emiDate,
                $entryTime,
                $loanDetails,
                $isEpassBook
            );

            // Prepare Branch Daybook DR (debit) transaction data
            $branchDaybookTransaction[] = $this->prepareBranchDaybookTransactionData(
                $daybookRefId,
                $memberDetailSaving->branch_id,
                4,
                48,
                $memberDetailSaving->id, // $loanDetails->id,
                $memberDetailSaving->savingAccount[0]->id, // $createLoanDaybook->id,
                $memberDetailSaving["savingAccount_Custom3"]->customer_id,
                $memberDetailSaving->savingAccount[0]->id, // $loanDetails->applicant_id,
                $emiAmount,
                $description,
                $descriptionDR,
                $descriptionCR,
                "DR",
                self::PAYMENT_MODE_SSB,
                self::CURRENCY_CODE,
                $vNo,
                $memberDetailSaving["savingAccount_Custom3"]->id,
                $emiDate,
                $entryTime,
                $loanDetails,
                $isEpassBook
            );

            $createBranchDaybookTransaction = $this->insertBranchDaybookTransaction(
                $branchDaybookTransaction
            );

            $checkErrorHandling = $this->verifyErrorAndTransactions(
                $createSavingAccountTransaction,
                $branchDaybookTransaction,
                $createLoanDaybook->id,
                $loanDetails,
                $isEpassBook
            );
            DB::commit();
            return $createLoanDaybook;
            // Call the headTransaction function for handle all_head_transaction and branch_daybooks transaction
        } catch (\Exception $ex) {
            DB::rollback();
            throw new Exception(
                "Error in function " .
                __FUNCTION__ .
                " at line " .
                $ex->getLine() .
                ": " .
                $ex->getMessage(),
                0,
                $ex
            );
        }
    }

    /**
     * Prepare data for SSB (Savings Account) transaction.
     *
     * @param int $savingAccountId
     * @param string $accountNo
     * @param float $openingBalance
     * @param int $branchId
     * @param float $withdrawal
     * @param string $description
     * @param string $currencyCode
     * @param string $paymentType
     * @param int $daybookRefId
     * @param string $emiDate
     * @param string $entryTime
     * @return array
     */
    private function prepareSsbTransactionData(
        $savingAccountId,
        $accountNo,
        $openingBalance,
        $branchId,
        $withdrawal,
        $description,
        $currencyCode,
        $paymentType,
        $emiDate,
        $entryTime,
        $loanDetails,
        $daybookRefId,
        $isEpassBook
    ) {
        try {
            // Check if $loanDetails is available (you need to pass it as an argument or retrieve it here)
            if (!isset($loanDetails)) {
                throw new \Exception("Loan details not available.");
            }

            // Other input validation and error checking can be added here

            // If everything is okay, return the data
            return [
                "saving_account_id" => $savingAccountId,
                "account_no" => $accountNo,
                "opening_balance" => $openingBalance,
                "branch_id" => $branchId,
                "type" => 9,
                "deposit" => 0,
                "withdrawal" => $withdrawal,
                "description" => $description,
                "currency_code" => $currencyCode,
                "payment_type" => $paymentType,
                "company_id" => $loanDetails->company_id,
                "payment_mode" => 4,
                "daybook_ref_id" => $daybookRefId,
                "created_at" => $this->formatDateWithTime($emiDate, $entryTime),
                "is_app" => ($isEpassBook == 1) ? 2 : self::IS_APP,
            ];
        } catch (\Exception $ex) {
            // Handle the exception by logging, returning an error response, or taking appropriate action
            throw new Exception(
                "Error in function " .
                __FUNCTION__ .
                " at line " .
                $ex->getLine() .
                ": " .
                $ex->getMessage(),
                0,
                $ex
            );
        }
    }

    /**
     * Prepare data for Branch Daybook transaction.
     *
     * @param int $daybookRefId
     * @param int $branchId
     * @param int $subType
     * @param int $typeId
     * @param int $typeTransactionId
     * @param int $associateId
     * @param int $memberId
     * @param float $amount
     * @param string $description
     * @param string $descriptionDR
     * @param string $descriptionCR
     * @param string $paymentType
     * @param int $paymentMode
     * @param string $currencyCode
     * @param string $vNo
     * @param int $ssbAccountIdFrom
     * @param string $emiDate
     * @param string $entryTime
     * @param int $company_id
     * @return array
     */
    private function prepareBranchDaybookTransactionData(
        $daybookRefId,
        $branchId,
        $type,
        $subType,
        $typeId,
        $transactionId,
        $associateId,
        $memberId,
        $amount,
        $description,
        $descriptionDR,
        $descriptionCR,
        $paymentType,
        $paymentMode,
        $currencyCode,
        $vNo,
        $ssbAccountIdFrom,
        $emiDate,
        $entryTime,
        $loanDetails,
        $isEpassBook
    ) {
        try {
            // Check if $loanDetails is available (you need to pass it as an argument or retrieve it here)
            if (!isset($loanDetails)) {
                throw new \Exception("Loan details not available.");
            }

            return [
                "daybook_ref_id" => $daybookRefId,
                "branch_id" => $branchId,
                "type" => $type,
                "sub_type" => $loanDetails["loan"]->loan_type === "L" ? 52 : 55,
                "type_id" => $typeId,
                "type_transaction_id" => $transactionId,
                "associate_id" => $associateId,
                "member_id" => $memberId,
                "amount" => $amount,
                "description" => $description,
                "description_dr" => $descriptionDR,
                "description_cr" => $descriptionCR,
                "payment_type" => $paymentType,
                "payment_mode" => $paymentMode,
                "currency_code" => $currencyCode,
                "v_no" => $vNo,
                "ssb_account_id_from" => $ssbAccountIdFrom,
                "ssb_account_id_to" => $ssbAccountIdFrom,
                "entry_date" => $this->formatDateWithTime($emiDate, $entryTime),
                "entry_time" => $entryTime,
                "company_id" => $loanDetails->company_id,
                "is_app" => ($isEpassBook == 1) ? 1 : self::IS_APP,
                'created_by' => ($isEpassBook == 1) ? 4 : 3,
                "created_at" => $this->formatDateWithTime($emiDate, $entryTime),
                "created_by_id" => $associateId,

            ];
            $createBranchDaybook = BranchDaybook::insertGetId(
                $branchDaybookTransaction
            );
        } catch (\Exception $ex) {
            // Handle the exception by logging, returning an error response, or taking appropriate action
            throw new \Exception(
                "Error in " .
                __FUNCTION__ .
                ": Unable to create Branch Daybook Transaction. Details: " .
                $ex->getMessage(),
                0,
                $ex
            );
        }
    }

    /**
     * Prepare data for Loan Daybook transaction.
     *
     * @param int $daybookRefId
     * @param mixed $loanDetails
     * @param float $amount
     * @param string $description
     * @param string $currencyCode
     * @param string $emiDate
     * @param string $entryTime
     * @return array
     */
    private function prepareLoanDaybookData(
        $daybookRefId,
        $loanDetails,
        $amount,
        $description,
        $currencyCode,
        $emiDate,
        $entryTime,
        $paymentType,
        $paymentMode,
        $memberDetailSaving,
        $isEpassBook
    ) {
        try {
            // Check if $loanDetails is available (you need to pass it as an argument or retrieve it here)
            if (!isset($loanDetails)) {
                throw new \Exception("Loan details not available.");
            }
            return [
                "daybook_ref_id" => $daybookRefId,
                "day_book_id" => $daybookRefId,
                "account_number" => $loanDetails->account_number,
                "associate_id" => $loanDetails->associate_member_id,
                "branch_id" => $memberDetailSaving->branch_id,
                "branch_code" => getBranchCode($memberDetailSaving->branch_id)->branch_code,
                "loan_type" => $loanDetails->loan_type,
                "loan_sub_type" => 0,
                "loan_id" => $loanDetails->id,
                "description" => $description,
                "payment_type" => $paymentType,
                "payment_mode" => $paymentMode,
                "applicant_id" =>
                    $loanDetails["loan"]->loan_type === "L"
                    ? $loanDetails->applicant_id
                    : $loanDetails->member_id,
                "created_by" => ($isEpassBook == 1) ? 4 : 3,
                "status" => 1,
                "is_app" => ($isEpassBook == 1) ? 2 : 1,
                "deposit" => $amount,
                "currency_code" => $currencyCode,
                "payment_date" => $this->formatDateWithTime(
                    $emiDate,
                    $entryTime
                ),
                'amount_deposit_by_name'=>(($memberDetailSaving->first_name) . ' ' . ($memberDetailSaving->last_name??'')),
                'amount_deposit_by_id'=>$memberDetailSaving->branch_id,
                "company_id" => $loanDetails->company_id,
                "created_at" => $this->formatDateWithTime($emiDate, $entryTime),
            ];
        } catch (\Exception $ex) {
            // Handle the exception by logging, returning an error response, or taking appropriate action
            throw new Exception(
                "Error in function " .
                __FUNCTION__ .
                " at line " .
                $ex->getLine() .
                ": " .
                $ex->getMessage(),
                0,
                $ex
            );
        }
    }

    /**
     * Insert a record into the LoanDaybook table.
     *
     * @param array $loanDaybookArray
     * @return int
     */
    private function insertLoanDaybook($loanDaybookArray)
    {
        DB::beginTransaction();
        try {
            $insertedID = LoanDayBooks::create($loanDaybookArray);
            DB::commit();
            return $insertedID;
        } catch (\Exception $ex) {
            // Handle the exception by logging, returning an error response, or taking appropriate action
            DB::rollback();
            throw new Exception(
                $ex->getMessage() . " " . $ex->getLine(),
                0,
                $ex
            );
        }
    }

    /**
     * Insert a record into the Saving Account Tra table.
     *
     * @param array $loanDaybookArray
     * @return int
     */
    private function insertSavingAccountTransaction($ssbTransactionArray)
    {
        // Wrap the insertion in a try-catch block
        DB::beginTransaction();
        try {
            // Insert the record and get the ID
            $insertedId = SavingAccountTranscation::insertGetId(
                $ssbTransactionArray
            );
            DB::commit();
            return $insertedId;
        } catch (\Exception $ex) {
            DB::rollback();
            // Handle the exception by logging, and return an error response
            throw new Exception(
                "Error in function " .
                __FUNCTION__ .
                " at line " .
                $ex->getLine() .
                ": " .
                $ex->getMessage(),
                0,
                $ex
            );
        }
    }

    /**
     * Insert a record into the Branch Daybook  Transactions table.
     *
     * @param array $loanDaybookArray
     * @return int
     */
    private function insertBranchDaybookTransaction(
        $insertBranchDaybookTransaction
    ) {
        DB::beginTransaction();
        try {
            $insertedId = BranchDaybook::insert(
                $insertBranchDaybookTransaction
            );
            DB::commit();
            return $insertedId;
        } catch (\Exception $ex) {
            DB::rollback();

            // Handle the exception by logging, and return an error response
            throw new Exception(
                "Error in function " .
                __FUNCTION__ .
                " at line " .
                $ex->getLine() .
                ": " .
                $ex->getMessage(),
                0,
                $ex
            );
        }
    }

    /**
     * Retrieves the balance of the savings account using SavingAccountTransactionView.
     * @param string $ssb_account_number The account number of the savings account.
     * @param string $transDate The transaction date.
     * @return \App\Models\SavingAccountTransactionView|null The retrieved saving account balance.
     */
    public function getSavingBalance($ssb_account_number, $transDate, $amount)
    {

        try {
            $balance = \App\Models\SavingAccountTransactionView::where(
                "account_no",
                $ssb_account_number->account_no
            )
                ->whereDate("opening_date", "<=", $transDate)
                ->orderByDesc("id")
                ->first();

            // To check Balance
            if (isset($balance->opening_balance)) {
                if (
                    !$this->checkMinimumBalance(
                        $balance->opening_balance,
                        $ssb_account_number,
                        $amount
                    )
                ) {
                    return response()->json(
                        [
                            "status" => "error",
                            "message" => $this->getErrorMessage(),
                            "data" => "",
                        ],
                        400
                    );
                }
            } else {
                return response()->json(
                    [
                        "status" => "error",
                        "message" => 'Insufficient fund in saving account',
                        "data" => "",
                    ],
                    400
                );
            }


            // Log the response for debugging

            return $balance;
        } catch (\Exception $ex) {
            throw new Exception(
                "Error in function " .
                __FUNCTION__ .
                " at line " .
                $ex->getLine() .
                ": " .
                $ex->getMessage(),
                0,
                $ex
            );
        }
    }

    /**
     * Check withdrawal limit and saving account balance
     * If saving account balance less than the limit then show alert
     * @param string balance
     * @param array ssb_account_number
     * @return mixed data
     */

    public function checkMinimumBalance($balance, $ssb_account_number, $amount)
    {

        try {
            $limit = $this->withdrawalLimit($ssb_account_number->company_id);
            $dueBalance = $balance - $limit;

            if ($dueBalance < $amount) {
                // Handle the case where balance difference is less than the amount
                $this->errorMessage = "Insufficient funds.";
                return false;
            }
            return true;
        } catch (\Exception $ex) {
            // Log the error for debugging
            throw new Exception(
                "Error in function " .
                __FUNCTION__ .
                " at line " .
                $ex->getLine() .
                ": " .
                $ex->getMessage(),
                0,
                $ex
            );
        }
    }

    /**
     * Retrive associate_withdrawal_limit from `system_default_settings`
     * @param self::ASSOCIATE_WITHDRAWAL_LIMIT
     * @param string companyId
     * @return array $data
     */

    public function withdrawalLimit($companyId)
    {
        try {
            // $limit = SystemDefaultSettings::whereShortName(
            //     self::ASSOCIATE_WITHDRAWAL_LIMIT)
            //     ->whereCompanyId($companyId)
            //     ->first();

            // return $limit->value ?? 0;
            $limit = 500;
        } catch (\Exception $ex) {
            // Log the error for debugging
            throw new Exception(
                "Error in function " .
                __FUNCTION__ .
                " at line " .
                $ex->getLine() .
                ": " .
                $ex->getMessage(),
                0,
                $ex
            );
            // Return a default value or handle the error based on your application's logic
            return 0;
        }
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Generates a unique voucher number.
     * @return string The generated voucher number.
     */
    public function generateVoucherNumber()
    {
        $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $voucherNumber = "";

        for ($i = 0; $i < 10; $i++) {
            $voucherNumber .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $voucherNumber;
    }

    /**
     * Creates new records in the Head Transactions and Emiloan1 table based on the provided details.
     * Calculates outstanding amounts and executes the calculate_loan_interest_update procedure.
     *
     * @param string $loanDaybookId The ID of the loan daybook.
     * @param string $paymentMode The payment mode for the transaction.
     * @param string $loanType The type of loan ('L' for individual loans, 'G' for group loans).
     */
    private function headTransaction($loanDaybookId, $paymentMode, $loanType, $isEpassBook)
    {

        DB::beginTransaction();
        try {
            $allHeadAccruedEntry = [];
            $allHeadPrincipleEntry = [];
            $allHeadpaymentEntry = [];
            $allHeadpaymentEntry2 = [];
            $calculatedDate = "";
            $value = \App\Models\LoanDayBooks::findorfail($loanDaybookId);
            $loansDetail = \App\Models\Loans::where(
                "id",
                $value->loan_type
            )->first();

            if ($loanType == "L") {
                $loansRecord = Memberloans::where(
                    "account_number",
                    $value->account_number
                )->first();
                $subType = 545;
            } else {
                $loansRecord = Grouploans::where(
                    "account_number",
                    $value->account_number
                )->first();
                $subType = 546;
            }
            $calculatedDate = date("Y-m-d", strtotime($value->created_at));
            $date = $value;
            $rr = \App\Models\LoanDayBooks::where(
                "account_number",
                $value->account_number
            )
                ->where("loan_sub_type", 0)
                ->where("is_deleted", 0)
                ->where("id", "<", $value->id)
                ->orderBY("created_at", "desc")
                ->first();
            $rangeDate = isset($date->created_at)
                ? date("Y-m-d", strtotime($date->created_at))
                : $calculatedDate;
            $currentDate = checkMonthAvailability(
                date("d"),
                date("m"),
                date("Y"),
                33
            );
            $currentDate = date("Y-m-d", strtotime($currentDate));

            $dataTotalCount = DB::select(
                "call calculate_loan_interest_update(?,?,?)",
                [$currentDate, $value->account_number, 0]
            );
            if (isset($rr->created_at)) {
                $strattDate = date("Y-m-d", strtotime($rr->created_at));
                $endDate = date("Y-m-d", strtotime($date->created_at));
            } else {
                $strattDate = date(
                    "Y-m-d",
                    strtotime($loansRecord->approve_date)
                );
                $endDate = $calculatedDate;
            }

            $accuredSumCR = \App\Models\AllHeadTransaction::where("type", "5")
                ->where("sub_type", $subType)
                ->where("head_id", $loansDetail->ac_head_id)
                ->where("type_transaction_id", $loansRecord->id)
                ->where("payment_type", "CR")
                ->where("entry_date", ">", $strattDate)
                ->where("entry_date", "<=", $endDate)
                ->sum("amount");
            $accuredSumDR = \App\Models\AllHeadTransaction::where("type", "5")
                ->where("sub_type", $subType)
                ->where("head_id", $loansDetail->ac_head_id)
                ->where("type_transaction_id", $loansRecord->id)
                ->where("payment_type", "DR")
                ->where("entry_date", ">", $strattDate)
                ->where("entry_date", "<=", $endDate)
                ->sum("amount");
            $emiData = \App\Models\LoanEmiNew1::where("emi_date", $rangeDate)
                ->where("loan_type", $value->loan_type)
                ->where("loan_id", $value->loan_id)
                ->where("is_deleted", "0")
                ->first();

            $accuredSum = $accuredSumDR - $accuredSumCR;
            if ($value->deposit <= $accuredSum) {
                $accruedAmount = $value->deposit;
                $principalAmount = 0;
            } else {
                $accruedAmount = $accuredSum;
                $principalAmount = $value->deposit - $accuredSum;
            }
            $paymentHead = "";

            if ($value->payment_mode == 4) {
                $ssbHead = \App\Models\Plans::where(
                    "company_id",
                    $loansRecord->company_id
                )
                    ->where("plan_category_code", "S")
                    ->first();
                $paymentHead = $ssbHead->deposit_head_id;
            }


            $allHeadAccruedEntry = [
                "daybook_ref_id" => $value->daybook_ref_id,
                "branch_id" => $value->branch_id,
                "head_id" => $loansDetail->ac_head_id,
                "type" => 5,
                "bank_id" => $bankId ?? null,
                "bank_ac_id" => $bankAcId ?? null,
                "sub_type" => $subType,
                "type_id" => $emiData->id,
                "type_transaction_id" => $value->loan_id,
                "associate_id" => $loansRecord->associate_member_id,
                "member_id" =>
                    $loansDetail->loan_type != "G"
                    ? $loansRecord->applicant_id
                    : $loansRecord->member_id,
                "branch_id_from" => $value->branch_id,
                "amount" => $accruedAmount,
                "description" => $value->account_number . " EMI collection",
                "payment_type" => "CR",
                "payment_mode" => $paymentMode,
                "currency_code" => "INR",
                "entry_date" => date("Y-m-d", strtotime($value->created_at)),
                "entry_time" => date("H:i:s", strtotime($value->created_at)),
                "created_by" => ($isEpassBook == 1) ? 4 : 3,
                "created_by_id" => $loansRecord->customer_id,
                "created_at" => $value->created_at,
                "updated_at" => $value->updated_at,
                "company_id" => $value->company_id,
                "cheque_id" => $value->cheque_dd_id ?? null,
                "transction_no" => $value->online_payment_id ?? null,
                // "is_app" => self::IS_APP,
                "is_app" => ($isEpassBook == 1) ? 2 : self::IS_APP,

            ];
            $allHeadPrincipleEntry = [
                "daybook_ref_id" => $value->daybook_ref_id,
                "branch_id" => $value->branch_id,
                "head_id" => $loansDetail->head_id,
                "bank_id" => $bankId ?? null,
                "bank_ac_id" => $bankAcId ?? null,
                "type" => 5,
                "sub_type" => $loansDetail->loan_type != "G" ? 52 : 55,
                "type_id" => $emiData->id,
                "type_transaction_id" => $value->loan_id,
                "associate_id" => $loansRecord->associate_member_id,
                "member_id" =>
                    $loansDetail->loan_type != "G"
                    ? $loansRecord->applicant_id
                    : $loansRecord->member_id,
                "branch_id_from" => $value->branch_id,
                "amount" => $principalAmount,
                "description" => $value->account_number . " EMI collection",
                "payment_type" => "CR",
                "payment_mode" => $paymentMode,
                "currency_code" => "INR",
                "entry_date" => date("Y-m-d", strtotime($value->created_at)),
                "entry_time" => date("H:i:s", strtotime($value->created_at)),
                "created_by" => ($isEpassBook == 1) ? 4 : 3,

                "created_by_id" => $loansRecord->customer_id,
                "created_at" => $value->created_at,
                "updated_at" => $value->updated_at,
                "company_id" => $value->company_id,
                "cheque_id" => $value->cheque_dd_id ?? null,
                "transction_no" => $value->online_payment_id ?? null,
                // "is_app" => self::IS_APP,
                "is_app" => ($isEpassBook == 1) ? 2 : self::IS_APP,

            ];
            $allHeadpaymentEntry = [
                "daybook_ref_id" => $value->daybook_ref_id,
                "branch_id" => $value->branch_id,
                "head_id" => $paymentHead,
                "bank_id" => $bankId ?? null,
                "bank_ac_id" => $bankAcId ?? null,
                "type" => 5,
                "sub_type" => $loansDetail->loan_type != "G" ? 52 : 55,
                "type_id" => $emiData->id,
                "type_transaction_id" => $value->loan_id,
                "associate_id" => $loansRecord->associate_member_id,
                "member_id" =>
                    $loansDetail->loan_type != "G"
                    ? $loansRecord->applicant_id
                    : $loansRecord->member_id,
                "branch_id_from" => $value->branch_id,
                "amount" => $value->deposit,
                "description" => $value->account_number . " EMI collection",
                "payment_type" => "DR",
                "payment_mode" => $paymentMode,
                "currency_code" => "INR",
                "entry_date" => date("Y-m-d", strtotime($value->created_at)),
                "entry_time" => date("H:i:s", strtotime($value->created_at)),
                "created_by" => ($isEpassBook == 1) ? 4 : 3,

                "created_by_id" => $loansRecord->customer_id,
                "created_at" => $value->created_at,
                "updated_at" => $value->updated_at,
                "company_id" => $value->company_id,
                "cheque_id" => $value->cheque_dd_id ?? null,
                "transction_no" => $value->online_payment_id ?? null,
                "is_app" => ($isEpassBook == 1) ? 2 : self::IS_APP,


            ];

            $dataInsert1 = \App\Models\AllHeadTransaction::insertGetId(
                $allHeadAccruedEntry
            );
            $dataInsert2 = \App\Models\AllHeadTransaction::insertGetId(
                $allHeadPrincipleEntry
            );
            $dataInsert3 = \App\Models\AllHeadTransaction::insertGetId(
                $allHeadpaymentEntry
            );
            DB::commit();
            return [
                "allHead" => $dataInsert1,
                "branchDaybook" => $dataInsert2,
                "allHeadpaymentEntry" => $dataInsert3,
            ];
        } catch (\Exception $ex) {
            //dd( $ex->getMessage().''.$ex->getLine());
            DB::rollback();
            throw new Exception(
                "Error in function " .
                __FUNCTION__ .
                " at line " .
                $ex->getLine() .
                ": " .
                $ex->getMessage(),
                0,
                $ex
            );
        }
    }

    /**
     * Format a date with time.
     *
     * @param string $date
     * @param string $time
     * @return string
     */
    public function formatDateWithTime($date, $time)
    {
        return date("Y-m-d H:i:s", strtotime($date . " " . $time));
    }

    /**
     * It will Verify if any variable return error or not
     * @param  mixed createSavingAccountTransaction
     * @param mixed branchDaybookTransaction
     * @param string createLoanDaybook
     * @param array loanDetails
     */

    private function verifyErrorAndTransactions(
        $createSavingAccountTransaction,
        $branchDaybookTransaction,
        $createLoanDaybook,
        $loanDetails,
        $isEpassBook

    ) {
        if (!$createSavingAccountTransaction || !$branchDaybookTransaction) {
            // If there was an error in creating the Branch Daybook Transaction
            // You can handle it here
            // Throw an exception, log an error, or return an error response
            throw new \Exception("Error creating Branch Daybook Transaction");
        } else {
            // Generate All Head Transaction
            return $this->headTransaction(
                $createLoanDaybook,
                3,
                $loanDetails["loan"]->loan_type,
                $isEpassBook
            );
        }
    }

    /**
     * Fetches laons based on provided loan type and comapny ID.
     * @param Request $request
     * @return AssociateLoanResource
     * @throws \Illuminate\Validation\ValidationException if validation false
     */

    public function fetchLoans($request)
    {
        // validate input parameter
        $request->validate([
            "loan_type" => "required",
            "company_id" => "required",
        ]);

        // Get comapny Id and laon Type from request
        $getCompanies = $request["company_id"];
        $loanType = $request["loan_type"];

        //Retrive laons from database based on laon_type and company_id
        $loans = Loans::select("id", "name", "code")
            ->where("loan_type", $loanType)
            ->where("company_id", $getCompanies)
            ->get();

        // Return the fetched laons wrapped in AssociateLoanResource
        return $loans;
    }

    /**
     * Retrive a list of loan based on specified filters
     * @param Request $request
     * @return AssociateLoanResource
     */

    public function getLoanListing($request, $appType = Null)
    {
        // Extract company id from the incoming request data
        $companyId = $request->input("company_id", "");

        $loanType = $request->input("loan_type", "");

        // Retrive model based on the loan_type
        $model = $this->getModelLoanType($loanType);
        // Instantiate the model
        $model = new $model;

        // Build the query to retrive loan data with the necessary relationships
        $data = $model
            ::with([
                "loans:id,loan_type,name,slug,code",
                "loanMember:id,member_id,first_name,last_name,mobile_no",
                "LoanGuarantorOne:id,member_loan_id,member_id,name,mobile_number",
                "loanBranch",
            ])->whereHas('company')
            ->select(
                "id",
                "loan_type",
                "account_number",
                "approve_date",
                "created_at",
                "amount",
                "emi_option",
                "due_amount",
                "emi_period",
                "emi_amount",
                "customer_id",
                "status",
                "branch_id",
                'closing_date',
                'ROI',
                'company_id',
                'associate_member_id',
                'ecs_type',
                $model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'member_loan_id') ? 'member_loan_id' : DB::raw('Null as member_loan_id')
            )
            ->when($companyId, function ($q) use ($companyId) {
                $q->where("company_id", $companyId);

            })

            ->when($appType === 0, function ($q) {
                $q->whereIn('status', [0, 1, 4]);
            });



        // Return the filtered loan data
        return $data;
    }



    /**
     * Retrive a list of loan based on specified filters
     * @param Request $request
     * @return AssociateLoanResource
     */

    //  public function getLoanListingEpass($request,$appType = Null)
    //  {

    //      // Extract company id from the incoming request data
    //      $companyId = $request->input("company_id", "");

    //      $loanType = $request->input("loan_type", "");


    //      // Retrive model based on the loan_type
    //      $model = $this->getModelLoanType($loanType);
    //      // Instantiate the model


    //      $model = new $model;

    //      // Build the query to retrive loan data with the necessary relationships
    //      $data = $model
    //     ->with([
    //         "loans:id,loan_type,name,slug,code",
    //         "loanMember:id,member_id,first_name,last_name,mobile_no",
    //         "LoanGuarantorOne:id,member_loan_id,member_id,name,mobile_number",
    //         "loanBranch",
    //         "company",
    //         "company.plans",
    //         "member.savingAccount_Custom3",
    //         "member",
    //         "savingAccount.savingAccountTransactionViewOrderBy",
    //         'loanSavingAccount2.savingAccountTransactionViewOrderBy'
    //     ])
    //     ->when($model == 'Grouploans', function ($q) {
    //         $q->with("loanSavingAccount2.savingAccountTransactionViewOrderBy");
    //     })
    //     ->select(
    //         "id",
    //         "loan_type",
    //         "account_number",
    //         "approve_date",
    //         "created_at",
    //         "amount",
    //         "emi_option",
    //         "due_amount",
    //         "emi_period",
    //         "emi_amount",
    //         "customer_id",
    //         "status",
    //         "branch_id",
    //         'closing_date',
    //         'member_id',
    //         'ROI',
    //         'company_id',
    //         'applicant_id',
    //         'transfer_amount',
    //         $model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'member_loan_id') ? 'member_loan_id' : DB::raw('Null as member_loan_id')
    //     )
    //     ->when($companyId, function ($q) use ($companyId) {
    //         $q->where("company_id", $companyId);
    //     })
    //     ->when($appType === 0, function ($q) {
    //         $q->whereIn('status', [0, 1, 4]);
    //     });




    //      // Return the filtered loan data
    //      return $data;
    //  }


    public function getLoanListingEpass($request, $appType = null)
    {
        // Extract company id from the incoming request data
        $companyId = $request->input("company_id", "");
        $loanType = $request->input("loan_type", "");

        // Retrieve model based on the loan_type
        $model = $this->getModelLoanType($loanType);
        // Instantiate the model
        $model = new $model;

        // Build the query to retrieve loan data with the necessary relationships
        $data = $model
            ->with([
                "loans:id,loan_type,name,slug,code",
                "loanMember:id,member_id,first_name,last_name,mobile_no",
                "LoanGuarantorOne:id,member_loan_id,member_id,name,mobile_number",
                "loanBranch",
                "company",
                "company.plans",
                "member.savingAccount_Custom3" => function ($query) use ($model) {
                    // Filter based on the equality of company_id
                    $query->where('company_id', '=', $model->company_id);
                },
                "member",
                // "savingAccount.savingAccountTransactionViewOrderBy",
                // 'loanSavingAccount2.savingAccountTransactionViewOrderBy'
            ])
            ->when($loanType == 'G', function ($q) {
                $q->with("loanSavingAccount2.savingAccountTransactionViewOrderBy");
            })
            ->select(
                "id",
                "loan_type",
                "account_number",
                "approve_date",
                "created_at",
                "amount",
                "emi_option",
                "due_amount",
                "emi_period",
                "emi_amount",
                "customer_id",
                "status",
                "branch_id",
                'closing_date',
                'ROI',
                'company_id',
                'applicant_id',
                'transfer_amount'
            );

        if ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'member_loan_id')) {
            $data->addSelect('member_loan_id');
        } else {
            $data->addSelect(DB::raw('Null as member_loan_id'));
        }

        $data->when($loanType == 'G', function ($q) {
            $q->addSelect("member_id");
        });

        $data->when($companyId, function ($q) use ($companyId) {
            $q->where("company_id", $companyId);
        })
            ->when($appType === 0, function ($q) {
                $q->whereHas('company', function ($companyQuery) {
                    $companyQuery->where('status', 1);
                });
                $q->whereIn('status', [0, 1, 4]);
            });


        // Return the filtered loan data
        return $data;
    }



    /**
     * Fetches laons based on provided loan type and comapny ID.
     * @param Request $request
     * @return AssociateLoanResource
     * @throws \Illuminate\Validation\ValidationException if validation false
     */

    public function getLoanRecord($request, $memberDetails, $column)
    {

        $request->validate([
            'company_id' => 'required',
            'loan_type' => 'required',
            // 'page_no' => 'required',
            'token' => 'required',

        ]);
        $accountNumber = $request->input("account_number", "");
        $fromDate = $request->has("from_date")
            ? date("Y-m-d", strtotime(convertDate($request["from_date"])))
            : "";
        $toDate = $request->has("to_date")
            ? date("Y-m-d", strtotime(convertDate($request["to_date"])))
            : "";
        $status = $request->input("status", "");
        $planId = $request->input("plan_id", "");
        $page = $request->input("page_no", "0");
        $limit = 20;
        $start = ($page - 1) * $limit;


        $result = $this->getLoanListing($request)
            ->where($column, $memberDetails->id) // need to update
            ->when(
                $accountNumber,
                fn($q) => $q->where("account_number", $accountNumber)
            )
            ->when(
                $fromDate && $toDate,
                fn($q) => $q->whereBetween("approve_date", [$fromDate, $toDate])
            )
            ->when($status != '', fn($q) => $q->whereStatus($status))
            ->when($planId, fn($q) => $q->where('loan_type', $planId))
            ->where('is_deleted','0')
            ->where('status', '!=', 3);

        $totalCount = $result->count('id');
        $result = ($page > 0) ? $result->offset($start)->limit($limit) : $result;
        $result = $result->get();

        // To add a Key closer amount in every data
        $result->transform(function ($value) {
            // Retrive outstanding Amount
            $outstandingAmount = $value
                ->getOutstanding()
                ->latest("created_at")
                ->first()
                ? $value
                    ->getOutstanding()
                    ->latest("created_at")
                    ->first()->out_standing_amount
                : $value->amount;
            // Ensure Outstanding Amount is not negative
            $outstandingAmount = isset($outstandingAmount)
                ? ($outstandingAmount > 0
                    ? $outstandingAmount
                    : 0)
                : $value->amount;
            // Retrive Emi details
            $emiDetail = $this->emiDetails($value->id, $value->loan_type);

            // Calculate lastEmi date
            $lastEmidate = isset($emiDetail->emi_date)
                ? date("d/m/Y", strtotime($emiDetail->emi_date))
                : date("d/m/Y", strtotime($value->approve_date));
            // Calculate Closure Amount
            $closerAmount = calculateCloserAmount(
                $outstandingAmount,
                $lastEmidate,
                $value->ROI,
                $value["loanBranch"]->state_id
            );
            // Add closer key
            $value["closer_amount"] = $closerAmount;
            return $value;
        });

        return response()->json([
            'status' => 'success',
            'message' => "Retrive Details",
            'data' => $result,
            'totalCount' => $totalCount,
            'length' => $limit,
            'page' => $page
        ], 200);

        // return new AssociateLoanResource($result);
    }

    public function getLoanRecordEpass($request, $memberDetails, $column)
    {


        $request->validate([
            // 'company_id' => 'required',
            'loan_type' => 'required',
            // 'page_no' => 'required',
            'token' => 'required',

        ]);

        $accountNumber = $request->input("account_number", "");
        $fromDate = $request->has("from_date")
            ? date("Y-m-d", strtotime(convertDate($request["from_date"])))
            : "";
        $toDate = $request->has("to_date")
            ? date("Y-m-d", strtotime(convertDate($request["to_date"])))
            : "";
        $status = $request->input("status", "");
        $planId = $request->input("plan_id", "");

        $appType = 0;

        $result = $this->getLoanListingEpass($request, $appType)
            ->where($column, $memberDetails->id) // need to update
            ->when(
                $accountNumber,
                fn($q) => $q->where("account_number", $accountNumber)
            )
            ->when(
                $fromDate && $toDate,
                fn($q) => $q->whereBetween("approve_date", [$fromDate, $toDate])
            )
            ->when($status != '', fn($q) => $q->whereStatus($status))
            ->when($planId, fn($q) => $q->where('loan_type', $planId));


        $totalCount = $result->count('id');

        $result = $result->get();

        // To add a Key closer amount in every data
        $result->transform(function ($value) {
            // Retrive outstanding Amount
            $outstandingAmount = $value
                ->getOutstanding()
                ->latest("emi_date")
                ->first()
                ? $value
                    ->getOutstanding()
                    ->latest("emi_date")
                    ->first()->out_standing_amount
                : $value->amount;
            // Ensure Outstanding Amount is not negative
            $outstandingAmount = isset($outstandingAmount)
                ? ($outstandingAmount > 0
                    ? $outstandingAmount
                    : $value->amount)
                : $value->amount;
            // Retrive Emi details
            $emiDetail = $this->emiDetails($value->id, $value->loan_type);
// dd(date("d/m/Y", strtotime(convertDate($value->approve_date))));
            // Calculate lastEmi date
            $lastEmidate = isset($emiDetail->emi_date)
                ? date("d/m/Y", strtotime(convertDate($emiDetail->emi_date)))
                : date("d/m/Y", strtotime(convertDate($value->approve_date)));

                

            // Calculate Closure Amount
            // dd($emiDetail, $lastEmidate,$value->ROI, $value["loanBranch"]->state_id );

            $closerAmount = calculateCloserAmount(
                $outstandingAmount,
                $lastEmidate,
                $value->ROI,
                $value["loanBranch"]->state_id
            );
            // Add closer key
            $value["closer_amount"] = $closerAmount;
            return $value;
        });
        return response()->json([
            'status' => 'success',
            'message' => "Retrive Details",
            'data' => $result,
            'totalCount' => $totalCount,
        ], 200);

        // return new AssociateLoanResource($result);
    }

    /**
     * Determine the appropriate loan  model class based on provided loan type.
     * @param string $loanType . The loanType sent in the request.
     * @return string Returns the fully qualified class name of the corrosponding loan model.
     */

    public function getModelLoanType($loanType)
    {
        return $loanType === "L" ? Memberloans::class : Grouploans::class;
    }

    public function checkLoanType($loanType)
    {
        return $loanType === "L" ? "applicant_id" : "";
    }

    /**
     * Retrive the loan detail , saving detail , customer detail and  loan plan detail based on provided loan id and laonType
     * @param string loanType
     * @param array loanId
     * @return array of loan detail
     * @return AssociateLoanResource
     */

    public function fetchLoanRecord($loanType, $loanId)
    {
        $loanIds = explode(",", $loanId);
        // Ensure loanid in array
        $model = $this->getModelLoanType($loanType);

        $data = $model
            ::whereIn("account_number", $loanIds)
            ->with([
                "loanMember:id,member_id,first_name,last_name",
                "loans:id,loan_type,name,slug,code",
            ])
            ->select(
                "account_number",
                "associate_member_id",
                "customer_id",
                "emi_amount",
                "id",
                "loan_type"
            )
            ->get();

        // Iterate through the fetched data and add the new property directly

        return $data;
    }

    /**
     * Check Associate Saving Account is activate or Inactivate
     * @return boolean
     */

    public function checkSavingAccountStatus($memberDetails)
    {
        if (!isset($memberDetails["savingAccount_Custom3"])) {
            return response()->json([
                'status' => 'error',
                'message' => "Record Not Found on Provided Customer Id",
                'data' => ''
            ], 400);

        } else if (isset($memberDetails["savingAccount_Custom3"]->transaction_status) && $memberDetails["savingAccount_Custom3"]->transaction_status === '0') {

            return response()->json([
                'status' => 'error',
                'message' => "Saving account is inactive",
                'data' => ''
            ], 400);

        }

    }

    /**
     * Retrive loan emi form emiloan table
     * @method App\Models\LoanEmisNew
     * @param loanId ,loanType
     * @return mixed
     */

    public function emiDetails($loanId, $loanType)
    {

        // Retrive emi detail 
        return \App\Models\LoanEmisNew::where("loan_id", $loanId)
            ->where("loan_type", $loanType)
            ->where("is_deleted", "0")
            ->orderBY("id", "desc")
            ->first();
    }
}
