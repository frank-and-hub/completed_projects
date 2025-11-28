<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\ApiResponse;
use App\Models\{SubscriptionPlan, Purchase};
use App\Traits\PurchaseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log};

class ApiSubscriptionController extends BaseApiController
{
    use PurchaseTrait;
    public function buyProducts(Request $req)
    {
        $req->validate([
            'purchase_token' => 'required|string',
            'platform' => 'required|in:ios,android',
            'product_id' => 'required|string',
            'transaction_date' => 'required',
            'amount' => 'nullable'
        ]);
        $user = $req->user();
        $msg = 'Subscription activated successfully.';
        $plan = SubscriptionPlan::whereProductId($req->product_id)
            ->whereStatus('1')
            ->first();
        if (!$plan) {
            return ApiResponse::error('Invalid product selected', 404);
        }
        DB::beginTransaction();
        try {
            $transactionId = $req->purchase_token;
            $verified = $this->verifyPlatformPurchase($req);
            if (empty($verified['status']) || $verified['status'] === false) {
                return ApiResponse::error('Token verification failed', 401);
            }
            if (!empty($verified['transaction_id'])) {
                $transactionId = $verified['transaction_id'];
            }
            $this->addPurchaseHistory($req, $user, $plan, $req->amount, $transactionId);
            $user->update(['is_subscribe' => 1]);
            DB::commit();
            return ApiResponse::success(['message' => $msg,]);
        } catch (\Exception $e) {
            DB::rollBack();
            $error = $e->getMessage() . ' - ' . $e->getLine();
            Log::error('buy-products API Log: \n' . $error);
            return ApiResponse::error(__('messages.something_went_wrong'));
        }
    }

    private function verifyPlatformPurchase(Request $req): array
    {
        if ($req->platform === 'android') {
            return $this->verifyAndroidPurchase($req->purchase_token, $req->product_id);
        }
        if ($req->platform === 'ios') {
            $verified = $this->verifyIosPurchase($req->purchase_token);
            $decoded  = $this->decodeIosTransaction($req->purchase_token) ?? [];
            $transactionId = $decoded['transactionId'] ?? null;
            return [
                'status' => $verified['status'] ?? false,
                'transaction_id' => $transactionId
            ];
        }
        throw new \Exception('Invalid platform provided.');
    }

    protected function addPurchaseHistory($req, $user, $plan, $amount = 0,  $transactionId)
    {
        $transactionDate = make_transaction_date($req->transaction_date);
        $purchasedData = [
            'user_id' => $user->id,
            'product_id' => $req->product_id,
            'platform' => $req->platform,
            'amount' => $amount == 0 ? $plan->price : $amount,
            'purchase_token' => $req->purchase_token,
            'transaction_id' => $transactionId ?? null,
            'status' => 2,
            'transaction_date' => $transactionDate,
        ];
        return  Purchase::create($purchasedData);
    }

    public function getSubscription(Request $request)
    {
        $cUser = $request->user();
        return ApiResponse::success(['is_subscription' => $cUser->is_subscribe == 1 ? true : false], 200);
    }
}
