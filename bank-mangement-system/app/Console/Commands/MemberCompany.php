<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Interfaces\RepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\SavingAccount;
use Illuminate\Support\Facades\{Hash, Auth, DB, Response, Session, Image, Redirect, URL, Validator};
use App\Http\Requests;
use App\Models\{ReceivedCheque, Memberinvestmentsnominees, AssociateDependent, AssociateTree, AssociateGuarantor, FaCode, Member, Receipt, Memberinvestments, ReceiptAmount, SamraddhBank, Carder, MemberIdProof, MemberNominee};
use App\Http\Controllers\Branch\CommanTransactionsController;
use Yajra\DataTables\DataTables;
use App\Services\Sms;
use Investment;
use DateTime;

class MemberCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:membercompany';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'member registration in every member company table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        die('memberCompany');
        $globaldate = Session::get('created_at');
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $allCustomer = $this->repository->getAllMember()->whereIsAssociate(1)->whereIsDeleted(0)->get();
        foreach ($allCustomer as $customer) {
            $branchId = $customer->old_branch_id;
            $getBranchCode = getBranchCode($branchId);
            $branchCode = $getBranchCode->branch_code;
            dd($branchId, $branchCode);
            if (!empty($customer)) {
                $customerName = $customer->first_name . ' ' . $customer->last_name;
            }
            $associateSettings = $this->repository->getAllCompanies()->whereStatus('1')->whereDoesntHave('companyAssociate', function ($query) {
                $query->whereStatus('1')->select(['id', 'status', 'company_id']);
            })->first(['id', 'status']);
            dd($associateSettings);

            $associatecompanyId = $associateSettings->id;
            $planAssociateSettings = $this->repository->getAllPlans()->wherePlanCategoryCode('S')->whereStatus('1')->whereCompanyId($associatecompanyId)->first(['id', 'company_id', 'status', 'plan_category_code', 'plan_code', 'deposit_head_id']);
            $FaCode = FaCode::whereCompanyId($associatecompanyId)->whereStatus('1')->orderBy('code', 'asc')->get(['id', 'name', 'code', 'status', 'company_id', 'slug']);

            $notInCompanyCustomer = $this->repository->getAllCompanies()->whereStatus(1)->whereDoesntHave('memberCompany', function ($query) use ($customerId) {
                $query->whereCustomerId($customerId)->select(['id', 'company_id', 'customer_id']);
            })->get();
            $memberInvestmet = Memberinvestments::whereHas('member', function ($q) use ($customerId) {
                $q->whereId($customerId);
            })->first(['id']);
            $memberInvestmetId = $memberInvestmet->id;
            $memberInvestmentNominee = Memberinvestmentsnominees::whereHas('memberinvestments', function ($q) use ($memberInvestmetId) {
                $q->where('id', $memberInvestmetId);
            })->get();
            // $memberCount = array();
            DB::beginTransaction();
            try {
                foreach ($notInCompanyCustomer as $company) {
                    $companyId = $company->id;
                    $ssb_amount = ($associatecompanyId == $companyId) ? 100 : 0;
                    // p('member company for customer ' . $customerId);
                    $faCode = $FaCode[1]->code;
                    $planIdGet = getPlanID($faCode);
                    $planId = $planIdGet->id;
                    $investmentMiCode = getInvesmentMiCodeNew($planId, $branchId);
                    if (!empty($investmentMiCode)) {
                        $miCodeAdd = $investmentMiCode->mi_code + 1;
                        if ($investmentMiCode->mi_code == 9999998) {
                            $miCodeAdd = $investmentMiCode->mi_code + 2;
                        }
                    } else {
                        $miCodeAdd = 1;
                    }
                    $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                    $investmentAccount = $branchCode . $faCode . $miCode;
                    $customerDetail = (object) [
                        'id' => $customerId,
                        'associate_code' => $customer->associate_code,
                        'associate_id' => $customer->associate_id,
                        'ssb_account' => $investmentAccount,
                        'rd_account' => 0,
                        'branch_mi' => $customer->branch_mi,
                        'reinvest_old_account_number' => NULL,
                        'old_c_id' => 0,
                        'otp' => NULL,
                        'varifiy_time' => NULL,
                        'is_varified' => NULL,
                        'upi' => NULL,
                        'token' => csrf_token(),
                    ];
                    $customerDetailsRequest = [
                        'company_id' => $companyId,
                        'create_application_date' => $globaldate,
                        'branchid' => $branchId,
                    ];
                    $membercompany = Investment::registerMember($customerDetail, $customerDetailsRequest);
                    $memberId = $membercompany->id;
                    // p('Memmber Company table New Id = '.$memberId);
                    // array_push($memberCount,$memberId);
                    $ssb_account_number = $this->repository->getAllSavingAccount()->whereCustomerId($customerId)->whereCompanyId($companyId);

                }
                // DB::commit();
                DB::rollback();
            } catch (\Exception $ex) {
                DB::rollback();
                $dataMsg['msg_type'] = 'error';
                $dataMsg['msg'] = $ex->getMessage();
                $dataMsg['line'] = $ex->getLine();
                $dataMsg['file'] = $ex->getFile();
            }
        }
    }
}