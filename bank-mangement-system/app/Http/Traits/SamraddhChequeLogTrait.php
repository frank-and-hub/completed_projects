<?php

namespace App\Http\Traits;

trait SamraddhChequeLogTrait
{
    public function chequeLog($daybookRefId, $id, $created_at, $cheque_no, $name, $member_id, $account)
    {
        $update['is_use'] = 1;
        $update['status'] = 3;
        $update['updated_at'] = $createdAt = date('Y-m-d' . " " . date('H:i:s') . "", strtotime(convertdate($created_at)));
       
        if ($cheque_no) {
            // Retrieve cheque data by cheque number
            $chequeData = \App\Models\SamraddhCheque::whereChequeNo($cheque_no)->first()->toArray();
            $oldValue = json_encode($chequeData);
            // Update cheque data using the provided update
            \App\Models\SamraddhCheque::whereChequeNo($cheque_no)->update($update);    
            // Retrieve the updated cheque data after the update
            $chequeDataOld = \App\Models\SamraddhCheque::whereChequeNo($cheque_no)->first()->toArray();
            $newValue = json_encode($chequeDataOld);
        } else if($id){ 
            $chequeData = \App\Models\SamraddhCheque::whereId($id)->first()->toArray();
            $oldValue = json_encode($chequeData);
            \App\Models\SamraddhCheque::find($id)->update($update);
            $chequeDataOld = \App\Models\SamraddhCheque::whereId($id)->first()->toArray();
            $newValue = json_encode($chequeDataOld);
            $cheque_no = \App\Models\SamraddhCheque::whereId($id)->value('cheque_no');
        } else{
            return 0;
        }
        
        $chequeId = $cheque_no ? \App\Models\SamraddhCheque::whereChequeNo($cheque_no)->value('id') : $id;
        $title = 'Cheque Clear';
        $u = auth()->user()->role_id == 3 ? 'Branch' : 'Admin';
        $userType = auth()->user()->role_id == 3 ? '2' : '1';
        $user = auth()->user()->username;
        $userId = auth()->user()->id;
        $currentDateTime = \Carbon\Carbon::now()->format('d/m/Y');
        if (strpos($name, 'Payment') !== false) {
            $name = u(str_replace(' Payment', '', $name));
        }
        $member = $member_id ?  \App\Models\Member::whereId($member_id)->first() : '';
        $user_name = $member ? u($member->first_name . ' ' . ($member->last_name ?? '')) : '';
        $a = ($account != null) ? "cleared for A/C no $account" : " cleared ";
        $userName = $user_name ? "of $user_name" : "" ;
        $description = "Cheque No. $cheque_no was $a by $user via the $u Panel on $currentDateTime for the $name payment $userName";
        // $updatechequeId = cheque_logs(2, $chequeId, $title, $description, $newValue, $oldValue, $status = 1, $daybookRefId, $userType, $userId, $createdAt);
        // if ($daybookRefId != null && $daybookRefId > 0) {
        //     return \App\Models\ChequeLog::whereId($updatechequeId)->update(['day_ref_id' => $daybookRefId]);
        // } else {
        //     return $updatechequeId;
        // }
    }
}