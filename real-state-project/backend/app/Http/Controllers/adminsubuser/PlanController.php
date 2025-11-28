<?php

namespace App\Http\Controllers\adminsubuser;

use App\Helpers\Helper;
use App\Helpers\ResponseBuilder;
use App\Http\Controllers\api\paymentController;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminSubscription;
use App\Models\Plans;
use App\Models\Roleplan;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class PlanController extends Controller
{
    public function subscribe_list(Request $request)
    {
        if ($request->ajax()) {
            $order = $request->get('order')[0]['dir'];
            $auth = auth()->user();
            Admin::find($request->input('user_id') ? $request->input('user_id') : $auth->id)->isAvailableSubscription();
            $data = $auth->admin_subscription();
            if ($user_search_property_id = $request->user_search_property_id) {
                $data = $data->where('search_id', $user_search_property_id);
            }
            $column = $request->get('columns')[$request->get('order')[0]['column']]['data'];
            $data = $data->orderBy($column, $order);
            $status = [
                'pending' => 'warrning',
                'ongoing' => 'success',
                'cancelled' => 'danger',
                'expired' => 'secondary'
            ];
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('DT_RowId', function ($row) {
                    return $row->id;
                })
                ->addColumn('plan_name', function ($row) {
                    return $row->plan_name;
                })
                ->addColumn('can_add_property', function ($row) {
                    return $row->can_add_property ?? "<i class='fas fa-infinity' style='font-size:24px;color: black !important;'></i>";
                })
                ->addColumn('amount', function ($row) {
                    return 'R' . number_format($row->amount, 2);
                })
                ->addColumn('status', function ($row) use ($status) {
                    $btn = '<div class="actions-container">';
                    // $btn .= "<button type = 'button' class='btn btn-".$status[$row->status]." btn-rounded btn-fw'>".ucwords($row->status)."</button>";
                    $btn .= "<div class='badge badge-outline-" . $status[$row->status] . " badge-pill'>" . ucwords($row->status) . "</div>";
                    $btn .= '</div>';
                    return $btn;
                })
                // ->addColumn('action', function ($row){
                // $btn = '<div class="actions-container">';
                //     $btn .= '<a href="' . route('admin_user.match_property_view', $row->id) . '"  class="btn btn-xs" data-toggle="tooltip" data-placement="top" data-original-title="View"><i class="fa fa-eye"></i></a>';
                // $btn .= '</div>';
                //     return $btn;
                // })
                // ->addColumn('created_at', function ($row) {
                //     return $this->convertToForShow($row->created_at)->format('d M y, g:i A');
                // })
                ->addColumn('expired_at', function ($row) {
                    return $this->convertToForShow($row->expired_at)->format('d M y');
                })
                ->rawColumns(['created_at', 'status', 'can_add_property'])
                ->make(true);
        }

        $title = 'Subscription';
        return view('adminsubuser.subscription.index', compact('title'));
    }

    public function checkPlan(Request $request)
    {
        $auth = auth()->user();
        $role = $auth->roles()->first();
        $roleName = $role->name;
        $is_plan_running = $auth->isAvailableSubscription() ? 1 : 0;
        if ($is_plan_running == 0) {
            $plan = Plans::select('id', 'amount')->where('type', $roleName)->first();
            if ($plan && $plan->amount == 0) {
                $this->planCreate();
                $is_plan_running = $auth->isAvailableSubscription() ? 1 : 0;
            }
        }
        return response()->json([
            'status' => 'success',
            'msg' => '',
            'data' => [
                'is_plan_running' => $is_plan_running
            ]
        ]);
    }

    public function planCreate()
    {
        try {
            //code...
            DB::beginTransaction();

            $admin = auth()->user();
            $role = $admin->getRoleNames()->first();

            $plan = Plans::with(['planfeatures'])->where('type', $role)->first();
            $started_at = Carbon::now();
            $expired_at = null;
            $can_add_property = null; // set private landlord one per pay property

            foreach ($plan->planfeatures as $planFeature) {
                $planType_value = $planFeature->planType_value;
                if ($planFeature->planType == 'months') {
                    $expired_at = $started_at->copy()->addMonth($planType_value);
                } else {
                    $can_add_property = $planType_value;
                }
            }
            $data = [
                'admin_id' => $admin->id,
                'subscription_id' => $plan->id,
                'plan_name' => $plan->plan_name,
                'amount' => $plan->amount,
                'status' => ($plan->amount == 0) ? 'ongoing' : 'pending',
                'expired_at' => $expired_at,
                'can_add_property' => $can_add_property,
            ];

            $adminSubscription = AdminSubscription::create($data);
            $admin->update(['subscription' => 1]);
            DB::commit();

            return $adminSubscription;
        } catch (\Throwable $th) {
            Log::error('admin plan create error');
            Log::error($th);
            DB::rollBack();
            throw $th;
        }
    }

    public function subscribe(Request $request)
    {
        $admin = auth()->user();
        $role = $admin->getRoleNames()->first();

        if ($role == 'agent') {
            throw new Exception('Something went wrong.');
        }

        try {
            $plan = Plans::where('type', $role)->first();
            // Ensure the plan exists
            if (!$plan) {
                throw new Exception('Plan not found for the specified role and plan name.');
            }

            if ($subscription = $admin->isAvailableSubscription()) {
                return response()->json([
                    'status' => 'error',
                    'msg' => 'you have already ongoing plan',
                ]);
            }

            $adminSubscription = $this->planCreate();

            if ($plan->amount == 0) {
                return response()->json([
                    'status' => 'success',
                    'is_free' => 1,
                    'msg' => 'Successfully! Your Free Plan is activated.',
                ]);
            }

            $request->merge(['subscription_id' => $plan->id]);

            $form_data = [
                'merchant_id' => config('services.payfast.merchant_id'),
                'merchant_key' => config('services.payfast.merchant_key'),
                'return_url' => route('adminSubUser.payfast_success', [
                    'adminSubscription_id' => $adminSubscription->id,
                    'admin_id' => $admin->id,
                ]),
                'cancel_url' => route('adminSubUser.payfast_cancel', [
                    'adminSubscription_id' => $adminSubscription->id,
                    'admin_id' => $admin->id,
                ]),
                'notify_url' => route('web.payfast_notify', [
                    'adminSubscription_id' => $adminSubscription->id,
                    'admin_id' => $admin->id,
                ]),
                'name_first' => $admin->name,
                'name_last' => $admin->name,
                'email_address' => $admin->email,
                'm_payment_id' => $admin->id . uniqid(),
                'amount' => number_format(sprintf('%.2f', $plan->amount), 2, '.', ''),
                'item_name' => $plan->plan_name,
            ];

            $signature = app(paymentController::class)->generateSignature($form_data, config('services.payfast.passphrase'));
            $form_data['signature'] = $signature;

            // return $form_data;
            return response()->json([
                'status' => 'success',
                'url' => config('constants.PAYFAST_URL'),
                'msg' => 'Subscription created successfully.',
                'data' => $form_data
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => 'error',
                'msg' => 'Failed to create subscription. ' . $th->getMessage(),
            ], 500);
        }
    }

    public function payfast_success(Request $request)
    {
        $adminSubscription_id = $request->adminSubscription_id;
        $admin_id = $request->admin_id;

        // $this->payfast_payment_done($adminSubscription_id, $admin_id);
        // return "done";
        return redirect()->route('adminSubUser.property.index')->with('success', 'Successfully! Plan Activate');
    }

    public function payfast_cancel(Request $request)
    {
        $adminSubscription_id = $request->adminSubscription_id;
        $admin_id = $request->admin_id;

        $this->payfast_payment_cancel($adminSubscription_id, $admin_id);
        return redirect()->route('adminSubUser.property.index')->with('error', 'Your Payment is process is cancelled!');
    }

    public function payfast_notify1(Request $request, $adminSubscription_id, $admin_id)
    {
        Log::error('hello hjjkjk');
        Log::error($adminSubscription_id);
        Log::error($admin_id);

        try {
            // $this->payfast_payment_done($adminSubscription_id, $admin_id);
            $pfData = $_POST;
            Log::info('Payfast notify: ' . json_encode($pfData));
            Log::info($request->all());
            //code...
        } catch (\Throwable $th) {
            Log::error('hello hjjkjk');
        }

        return 1;
    }

    public function payfast_notify(Request $request, $adminSubscription_id, $admin_id)
    {
        $pfData = $_POST;
        Log::info('Payfast notify: ' . json_encode($pfData));
        $pfParamString = '';
        // Strip any slashes in data
        foreach ($pfData as $key => $val) {
            $pfData[$key] = stripslashes($val);
            if ($key !== 'signature') {
                $pfParamString .= $key . '=' . urlencode($val) . '&';
            }
        }
        // Remove the last '&' from the parameter string
        $pfParamString = substr($pfParamString, 0, -1);

        $paymentController = app(paymentController::class);

        if ($paymentController->pfValidSignature($pfData, $pfParamString) && $paymentController->pfValidIP()) {
            Log::info('Payfast notify: Signature and IP are valid');
            try {
                $payment_status = $pfData['payment_status'];
                $m_payment_id = $pfData['m_payment_id'];
                $pf_payment_id = $pfData['pf_payment_id'];
                $amount_gross = $pfData['amount_gross'];
                $amount_fee = $pfData['amount_fee'];
                $amount_net = $pfData['amount_net'];

                if ($payment_status == 'COMPLETE') {
                    Log::info('Payfast notify: Payment status is complete');

                    $adminSubscription = $this->payfast_payment_done($adminSubscription_id, $admin_id, $pf_payment_id, $amount_gross, $amount_fee, $amount_net);


                    $user = Admin::find($adminSubscription->admin_id);
                    if (!$user) {
                        throw new \Exception('User not found');
                    }

                    $started_at = Carbon::now();
                    $expired_at = $adminSubscription->expired_at;

                    $receiptData = [
                        'user_name' => $user->name,
                        'user_email' => $user->email,
                        'plan_name' => $adminSubscription->plan->plan_name,
                        'receipt_no' => $adminSubscription->pf_payment_id,
                        'amount' => $adminSubscription->amount,
                        'amount_net' => $adminSubscription->amount_net,
                        'amount_fee' => $adminSubscription->amount_fee,
                        'receipt_date' => Carbon::parse($adminSubscription->created_at, 'Africa/Johannesburg')->format('y-m-d'),
                    ];
                    // Helper::sendReceiptMail($receiptData);
                }
                Log::info('Payfast notify: Subscription successfully created');
                return ResponseBuilder::success(null, 'Subscription successfully created', $this->successStatus);
            } catch (\Exception $e) {
                Log::error('payfast_notify');
                Log::error($e);
                // DB::rollBack();
                return ResponseBuilder::error('Subscription creation failed: ' . $e->getMessage(), $this->errorStatus);
            }
        }
    }

    private function payfast_payment_done($adminSubscription_id, $admin_id, $pf_payment_id, $amount_gross, $amount_fee, $amount_net)
    {
        $adminSubscription = AdminSubscription::find($adminSubscription_id);
        DB::beginTransaction();
        $adminSubscription->status = "ongoing";
        $adminSubscription->pf_payment_id = $pf_payment_id;
        $adminSubscription->amount_gross = $amount_gross;
        $adminSubscription->amount_fee = $amount_fee;
        $adminSubscription->amount_net = $amount_net;
        $adminSubscription->save();
        DB::commit();
        return $adminSubscription;
    }

    private function payfast_payment_cancel($adminSubscription_id, $admin_id)
    {
        if ($adminSubscription = AdminSubscription::where('status', 'pending')->find($adminSubscription_id)) {
            DB::beginTransaction();
            $adminSubscription->status = "cancelled";
            $adminSubscription->save();
            DB::commit();
        }
    }
}
