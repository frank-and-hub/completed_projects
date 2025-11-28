<?php

namespace App\Http\Controllers\Api\AssociateRegistration;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\AssociateInvestmentResource;
use App\Models\Daybook;
use App\Models\Branch;
use App\Models\Plans;
use App\Models\Member;
use App\Services\InvestmentService;

class AssociateReportController extends Controller
{
    // Add commenting
    public function renewalTransaction(Request $request)
    {
        // dd($request->all());
        // if date is exist then set a date that provided in request otherwise empty
        $startDate = $request->input('start_date', "");
        // if date is exist then set a date that provided in request otherwise empty
        $endDate = $request->input('end_date', "");
        // if plan_id is exist then planId that provided in request otherwise empty
        $planId = $request->input('plan_id', "");
        // if branch_id is exist then set a branchId that provided in request otherwise empty
        $branchId = $request->input('branch_id', "");
        // if transaction_type is exist then set a transactionType that provided in request otherwise empty
        $transactionType = $request->input('transaction_type', "");

        $account_number = $request->input('account_number', "");
        // Get the page number from the request, default to 0 if not present
        $page = $request->input("page_no", 1);
        // Define the number of items to display per page
        $limit = 20;
        // Calculate the starting index for the current page
        $start = ($page - 1) * $limit;
        $associateId = $request->associate_no;
        // Error Handling Apply using try and catch
        try {
            // Retrive data from daybooks table based on provided filters
            //plan id
            $pid = 1; // Exclude saving Account
            $data = Daybook::whereHas('company')
                ->with([
                    'dbranch:id,name,branch_code',
                    'MemberCompany:id,member_id,customer_id',
                    'company:id,name',
                    'member:id,member_id,first_name,last_name',
                    'investment' => function ($query) {
                        $query->select('id', 'plan_id', 'account_number', 'created_at')
                            ->with(['plan:id,name']);
                    }
                ])
                ->whereHas('investment', function ($query) use ($pid) {
                    $query->where('plan_id', '!=', $pid);
                })
                ->whereHas('associateMember', function ($q) use ($associateId) {
                    $q->where('associate_no', $associateId);
                })
                ->where('transaction_type', 4)
                ->where('is_deleted', 0);

            // Filter queries   
            // Apply a date range filter when both $startDate and $endDate are not empty.            
            $data->when(!empty($startDate) && !empty($endDate), function ($query) use ($startDate, $endDate) {
                // Convert the provided $startDate to 'Y-m-d' format.
                $startDateC = date("Y-m-d", strtotime(convertDate($startDate)));
                // Check if $endDate is provided; if so, convert it to 'Y-m-d' format, otherwise, leave it empty.
                $endDateC = $endDate ? date("Y-m-d", strtotime(convertDate($endDate))) : '';
                // Filter records where 'created_at' is within the date range defined by $startDateC and $endDateC.
                $query->whereBetween(\DB::raw('DATE(created_at)'), [$startDateC, $endDateC]);
            });
            // Apply a branch name filter based on $branchId if it's not empty and not equal to '0'.
            $data->when(!empty($branchId) && $branchId !== '0', function ($query) use ($branchId) {
                // Use a subquery to filter records based on the 'id' of the 'dbranch' relationship.
                $query->whereHas('dbranch', function ($subQuery) use ($branchId) {
                    $subQuery->where('id', $branchId);
                });
            });
            // Apply a filter based on $planId if it's not empty and not equal to '0'.
            $data->when(!empty($planId) && $planId !== '0', function ($query) use ($planId) {
                $query->whereHas('investment', function ($subQuery) use ($planId) {
                    $subQuery->where('plan_id', $planId);
                });
            });
            // Apply a database query condition to filter by 'is_app' if $transactionType is not null and not an empty string.
            $data->when($transactionType !== null && $transactionType !== '', function ($query) use ($transactionType) {
                $query->where('is_app', $transactionType);
            });
            /** this code is added by sourab */
            $data->when(!empty($account_number) && $account_number !== '0', function ($query) use ($account_number) {
                $query->whereHas('investment', function ($subQuery) use ($account_number) {
                    $subQuery->where('account_no', $account_number);
                });
            });
            // Apply a database query condition to filter by 'created_by' for getting only Associate App id
            $data->where('created_by', '3');
            /** upto ths line. */
            // Pagination
            $totalrecords = $data->count('id');
            $data = $data->orderBy('id', 'DESC')->offset($start)
                ->limit($limit)->get(['branch_id', 'created_at', 'is_app', 'account_no', 'amount', 'payment_mode', 'member_id', 'company_id', 'investment_id']);
            $sno = $data->count();
            $sno = 1;
            $rowReturn = [];
            $paymentMode = [
                '0' => 'Cash',
                '1' => 'Cheque',
                '2' => 'DD',
                '3' => 'Online',
                '4' => 'By Saving Account',
                '5' => 'From Loan Amount',
            ];
            $transactionBy = [
                '0' => 'Software',
                '1' => 'Associate',
                '2' => 'E-Passbook',
            ];
            foreach ($data as $key => $row) {
                $val['sno'] = $sno++;
                $val['renewal_date'] = date("d/m/Y", strtotime($row->created_at));
                $val['branch'] = $row->dbranch->name . '(' . $row->dbranch->branch_code . ')';
                $val['customer_id'] = $row->MemberCompany->member->member_id ?? 'N/A';
                $val['name'] = $row->MemberCompany->member->first_name . ' ' . $row->MemberCompany->member->last_name ?? 'N/A';
                $val['account_number'] = $row->account_no ?? 'N/A';
                $val['company'] = $row->company->name ?? 'N/A';
                $planId = $row->investment->plan_id ?? 'N/A';
                $planName = '';
                if ($planId > 0 && isset($row->investment->plan)) {
                    $planName = $row['investment']->plan->name;
                }
                $val['plan'] = $planName;
                $val['amount'] = number_format((float)$row->amount, 2, '.', '') ?? 'N/A';
                $val['payment_mode'] = $paymentMode[$row->payment_mode] ?? 'N/A';
                $val['transaction_by'] = $transactionBy[$row->is_app] ?? 'N/A';
                $rowReturn[] = $val;
            }
            return response()->json([
                'status' => 'success',
                'message' => "Retrive Details",
                'data' => $rowReturn,
                'page_no' => $page,
                'length' => $limit,
                'totalrecords' => $totalrecords,
            ], 200);
            // If Record Found then Send to Resource-
            //return new AssociateInvestmentResource($rowReturn,$totalrecords);
        }
        // If any Exception Throw
        catch (\Exception $ex) {
            return response()->json([
                'data' => '',
                'status' => 'error',
                'message' => $ex->getMessage(),
            ]);
        }
    }
    // All Active & Deactive branch list Show
    public function branch(Request $request)
    {
        $data = Branch::select('id', 'name')->get();
        return new AssociateInvestmentResource($data);
    }
    // All Plan list Show
    public function reportPlans(Request $request)
    {
        $data = Plans::select('id', 'name')->whereHas('company',function ($q) {
            $q->where('status',1);
        })->where(function ($q) {
            $q->where('plan_category_code', '!=', 'S')
                ->where('plan_category_code', '!=', 'F');
        })->when($request->plan_category!='',function($q) use ($request){
            $q->where('plan_category_code',$request->plan_category);
        })->get();
        return new AssociateInvestmentResource($data);
    }
    // Payment type dropdown
    public function transactions(Request $request)
    {
        // Set array renewal transaction by
        $data = [
            ['id' => 0, 'name' => 'Software'],
            ['id' => 1, 'name' => 'Associate'],
            ['id' => 2, 'name' => 'E-Passbook']
        ];
        return new AssociateInvestmentResource($data);
    }
}
