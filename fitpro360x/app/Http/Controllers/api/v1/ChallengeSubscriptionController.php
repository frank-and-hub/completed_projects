<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\ChallengePurchaseLog;
use App\Models\UserChallengeSubscription;
use App\Models\ChallengePackages;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\api\v1\BaseApiController;


class ChallengeSubscriptionController extends BaseApiController
{
    /**
     * Save challenge subscription.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function saveChallengeSubscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|string',
            'purchaseReceipt' => 'required|string',
            'challenge_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $challengePurchaseLog = new ChallengePurchaseLog();
        $challengePurchaseLog->user_id = $user->id;
        $challengePurchaseLog->transaction_id = $request->transaction_id;
        $challengePurchaseLog->purchaseReceipt = $request->purchaseReceipt;
        $challengePurchaseLog->verification_data = json_encode($request->verification_data);
        $challengePurchaseLog->save();

        $userchallenge = new UserChallengeSubscription();
        $userchallenge->user_id = $user->id;
        $userchallenge->subscription_id = $request->subscriptionType;
        $userchallenge->challenge_id = $request->challenge_id;
        $userchallenge->transaction_id = $request->transaction_id;
        $userchallenge->payment_gateway = $request->deviceType === 'android' ? 'android' : 'ios';
        $userchallenge->subscribed_at = now();
        $userchallenge->is_recurring = 1;
        $userchallenge->status = 'active';
        $userchallenge->created_at = now();
        $userchallenge->save();


        return $this->sendResponse(null, 'Challenge Unlocked Successfully.', 200);
    }
}
