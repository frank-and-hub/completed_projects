<?php
use App\Models\Etemplate;
use App\Models\Settings;
use Illuminate\Http\Request;
use Twilio\Rest\Client;
use App\Models\Daybook;
use App\Scopes\ActiveScope;
use App\Models\InvestmentMonthlyYearlyInterestDeposits;
use App\Models\MemberCompany;
use Carbon\Carbon;
use Illuminate\Support\Arr;
// use App\Models\SmsSetting;

if (!function_exists('send_email')) {
    function send_email($to, $name, $subject, $message)
    {
        $temp = Etemplate::first();
        $gnl = Settings::first();
        $template = $temp->emessage;
        $from = $temp->esender;
        if ($gnl->email_notify == 1) {
            $headers = "From: $gnl->site_name <$from> \r\n";
            $headers .= "Reply-To: $gnl->site_name <$from> \r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $mm = str_replace("{{name}}", $name, $template);
            $message = str_replace("{{message}}", $message, $mm);
            if (mail($to, $subject, $message, $headers)) {
                // echo 'Your message has been sent.';
            } else {
                //echo 'There was a problem sending the email.';
            }
        }
    }
}
if (!function_exists('getVendorCategory')) {
    function getVendorCategory($category_all)
    {
        $category = explode(',', $category_all);
        $getName = App\Models\VendorCategory::whereIn('id', $category)->get();
        $gt = '';
        foreach ($getName as $val) {
            if (count($getName) > 1) {
                $gt .= $val->name . ', ';
            } else {
                $gt .= $val->name;
            }
        }
        return $gt;
    }
}
if (!function_exists('user_ip')) {
    function user_ip()
    {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
}
if (!function_exists('send_sms')) {
    function send_sms($recipients, $message)
    {
        $temp = Etemplate::first();
        $account_sid = $temp->twilio_sid;
        $auth_token = $temp->twilio_auth;
        $twilio_number = $temp->twilio_number;
        $client = new Client($account_sid, $auth_token);
        try {
            $client->messages->create(
                $recipients,
                [
                    'from' => $twilio_number,
                    'body' => $message
                ]
            );
        } catch (TwilioException $e) {
        } catch (Exception $e) {
        }
    }
}
if (!function_exists('notify')) {
    function notify($user, $subject, $message)
    {
        send_email($user->email, $user->name, $subject, $message);
        send_sms($user->mobile, strip_tags($message));
    }
}
if (!function_exists('send_email_verification')) {
    function send_email_verification($to, $name, $subject, $message)
    {
        $temp = Etemplate::first();
        $gnl = Settings::first();
        $template = $temp->emessage;
        $from = $temp->esender;
        $headers = "From: $gnl->site_name <$from> \r\n";
        $headers .= "Reply-To: $gnl->site_name <$from> \r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        $mm = str_replace("{{name}}", $name, $template);
        $message = str_replace("{{message}}", $message, $mm);
        if (mail($to, $subject, $message, $headers)) {
            // echo 'Your message has been sent.';
        } else {
            //echo 'There was a problem sending the email.';
        }
    }
}
if (!function_exists('send_sms_verification')) {
    function send_sms_verification($to, $message)
    {
        $temp = Etemplate::first();
        $gnl = Settings::first();
        if ($gnl->sms_verification == 1) {
            $sendtext = urlencode($message);
            $appi = $temp->smsapi;
            $appi = str_replace("{{number}}", $to, $appi);
            $appi = str_replace("{{message}}", $sendtext, $appi);
            $result = file_get_contents($appi);
        }
    }
}
if (!function_exists('castrotime')) {
    function castrotime($timestamp)
    {
        $datetime1 = new DateTime("now");
        $datetime2 = date_create($timestamp);
        $diff = date_diff($datetime1, $datetime2);
        $timemsg = '';
        if ($diff->y > 0) {
            $timemsg = $diff->y * 12;
        } else if ($diff->m > 0) {
            $timemsg = $diff->m * 30;
        } else if ($diff->d > 0) {
            $timemsg = $diff->d * 1;
        }
        if ($timemsg == "")
            $timemsg = 0;
        else
            $timemsg = $timemsg;
        return $timemsg;
    }
}
if (!function_exists('timeAgo')) {
    function timeAgo($timestamp)
    {
        //$time_now = mktime(date('h')+0,date('i')+30,date('s'));
        $datetime1 = new DateTime("now");
        $datetime2 = date_create($timestamp);
        $diff = date_diff($datetime1, $datetime2);
        $timemsg = '';
        if ($diff->y > 0) {
            $timemsg = $diff->y . ' year' . ($diff->y > 1 ? "s" : '');
        } else if ($diff->m > 0) {
            $timemsg = $diff->m . ' month' . ($diff->m > 1 ? "s" : '');
        } else if ($diff->d > 0) {
            $timemsg = $diff->d . ' day' . ($diff->d > 1 ? "s" : '');
        } else if ($diff->h > 0) {
            $timemsg = $diff->h . ' hour' . ($diff->h > 1 ? "s" : '');
        } else if ($diff->i > 0) {
            $timemsg = $diff->i . ' minute' . ($diff->i > 1 ? "s" : '');
        } else if ($diff->s > 0) {
            $timemsg = $diff->s . ' second' . ($diff->s > 1 ? "s" : '');
        }
        if ($timemsg == "")
            $timemsg = "Just now";
        else
            $timemsg = $timemsg . ' ago';
        return $timemsg;
    }
}
if (!function_exists('convertCurrency')) {
    function convertCurrency($amount, $from_currency, $to_currency)
    {
        $gnl = Settings::first();
        $apikey = $gnl->api;
        $from_Currency = urlencode($from_currency);
        $to_Currency = urlencode($to_currency);
        $query = "{$from_Currency}_{$to_Currency}";
        // change to the free URL if you're using the free version
        $json = file_get_contents("https://free.currconv.com/api/v7/convert?q={$query}&compact=ultra&apiKey={$apikey}");
        $obj = json_decode($json, true);
        $val = floatval($obj["$query"]);
        $total = $val * $amount;
        return $total;
    }
}
if (!function_exists('boomtime')) {
    function boomtime($timestamp)
    {
        //$time_now = mktime(date('h')+0,date('i')+30,date('s'));
        $datetime1 = new DateTime("now");
        $datetime2 = date_create($timestamp);
        $diff = date_diff($datetime1, $datetime2);
        $timemsg = '';
        if ($diff->h > 0) {
            $timemsg = $diff->h * 1;
        }
        if ($timemsg == "")
            $timemsg = 0;
        else
            $timemsg = $timemsg;
        return $timemsg;
    }
}
/**
 * get active State list.
 * @return  array()  Response
 */
if (!function_exists('stateList')) {
    function stateList()
    {
        $states = App\Models\State::where([['status', '=', '1'], ['is_deleted', '=', '0'],])->get(['id', 'name', 'code', 'country_id']);
        return $states;
    }
}
/**
 * get all active city list according to states.
 * @param $districtId
 * @return  array()  Response
 */
if (!function_exists('cityList')) {
    function cityList($districtId)
    {
        $cities = App\Models\City::where([['district_id', '=', $districtId], ['status', '=', '1'], ['is_deleted', '=', '0'],])->get(['id', 'name', 'district_id']);
        return $cities;
    }
}
/**
 * get all active district list according to states.
 * @param $stateId
 * @return  array()  Response
 */
if (!function_exists('districtList')) {
    function districtList($stateId)
    {
        $districts = App\Models\District::where([['state_id', '=', $stateId], ['status', '=', '1'], ['is_deleted', '=', '0'],])->get(['id', 'name', 'state_id']);
        return $districts;
    }
}
/**
 * get all active occupation list.
 * @param
 * @return  array()  Response
 */
if (!function_exists('occupationList')) {
    function occupationList()
    {
        $occupations = App\Models\Occupation::where([['status', '=', '1'], ['is_deleted', '=', '0'],])->get(['id', 'name']);
        return $occupations;
    }
}
/**
 * get all active religion list.
 * @param
 * @return  array()  Response
 */
if (!function_exists('religionList')) {
    function religionList()
    {
        $religions = App\Models\Religion::where([['status', '=', '1'], ['is_deleted', '=', '0'],])->get(['id', 'name']);
        return $religions;
    }
}
/**
 * get all active special category list.
 * @param
 * @return  array()  Response
 */
if (!function_exists('specialCategoryList')) {
    function specialCategoryList()
    {
        $specialCategory = App\Models\SpecialCategory::where([['status', '=', '1'], ['is_deleted', '=', '0'],])->get(['id', 'name']);
        return $specialCategory;
    }
}
/**
 * get all active id type list.
 * @param
 * @return  array()  Response
 */
if (!function_exists('idTypeList')) {
    function idTypeList()
    {
        $idTypes = App\Models\IdType::where([['status', '=', '1'], ['is_deleted', '=', '0'],])->orderBy('created_at', 'ASC')->get(['id', 'name']);
        return $idTypes;
    }
}
/**
 * get members field data .
 * @param   $column(table column name),$code(for search member)
 * @return   Response (return column value)
 */
if (!function_exists('memberFieldData')) {
    function memberFieldData($column, $code, $wherefield)
    {
        $data = App\Models\Member::where($wherefield, $code)->where('associate_status', 1)->where('status', 1)->where('is_deleted', 0)->get($column);
        return $data;
    }
}
/**
 * get members field data .
 * @param   $column(table column name),$code(for search member)
 * @return   Response (return column value)
 */
if (!function_exists('memberFieldDataStatus')) {
    function memberFieldDataStatus($column, $code, $wherefield)
    {
        $data = App\Models\Member::where($wherefield, $code)->where('status', 1)->where('is_deleted', 0)->get($column);
        return $data;
    }
}
/**
 * get last mi code for member.
 * @param   $role(member role id),$branch(member branch),$branch(FA code)
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getLastMiCodeCustomer')) {
    function getLastMiCodeCustomer($role, $branch)
    {
        $data = App\Models\Member::where([['role_id', '=', $role], ['branch_id', '=', $branch]])->orderBy('mi_code', 'desc')->first('mi_code');
        return $data;
    }
}
if (!function_exists('getLastMiCode')) {
    function getLastMiCode($role, $branch, $member_type, $companyid = NULL)
    {
        $data = App\Models\MemberCompany::where([['role_id', '=', $role], ['branch_id', '=', $branch]])->whereCompanyId($companyid)->orderBy('mi_code', 'desc')->first('mi_code');
        return $data;
    }
}
/**
 * get branch code by branch id.
 * @param   $id(table column name)
 * @return   Response (return column value -- branch code)
 */
if (!function_exists('getBranchCode')) {
    function getBranchCode($id)
    {
        $data = App\Models\Branch::whereId($id)->first('branch_code');
        return $data;
    }
}
/**
 * get branch code by branch id.
 * @param   $id(table column name)
 * @return   Response (return column value -- branch code)
 */
if (!function_exists('getBranchName')) {
    function getBranchName($id)
    {
        $data = App\Models\Branch::where('manager_id', $id)->first('name');
        return $data;
    }
}
/**
 * get FA code by id.
 * @param $faCodeId
 * @return  array()  Response
 */
if (!function_exists('getFaCode')) {
    function getFaCode($faCodeId, $companyId)
    {
        $facode = App\Models\FaCode::where([['slug', '=', $faCodeId], ['status', '=', '1'], ['is_deleted', '=', '0'], ['company_id', '=', $companyId]])->first('code');
        return $facode;
    }
}
/**
 * get branch id by manager id.
 * @param   $managerId
 * @return   Response (return column value -- branch id)
 */
if (!function_exists('getUserBranchId')) {
    function getUserBranchId($managerId)
    {
        $data = App\Models\Branch::where('manager_id', $managerId)->first('id');
        return $data;
    }
}
/**
 * get branch phone number by name.
 * @param   $managerId
 * @return   Response (return column value -- branch id)
 */
if (!function_exists('getUserBranchPhoneNumber')) {
    function getUserBranchPhoneNumber($name)
    {
        $data = App\Models\Branch::where('name', 'like', '%' . $name . '%')->first('phone');
        return $data;
    }
}
/**
 * get branch phone number by name.
 * @param   $managerId
 * @return   Response (return column value -- branch id)
 */
if (!function_exists('getUserBranchOtpPermission')) {
    function getUserBranchOtpPermission($name)
    {
        $data = App\Models\Branch::where('name', 'like', '%' . trim($name) . '%')->first('otp_login');
        return $data;
    }
}
/**
 * get last mi code for Saving account.
 * @param
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getSsbAccountLastMiCode')) {
    function getSsbAccountLastMiCode()
    {
        $data = App\Models\SavingAccount::orderBy('mi_code', 'desc')->first('mi_code');
        return $data;
    }
}
/**
 * get account no.
 * @param   $id
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getSsbAccountNumber')) {
    function getSsbAccountNumber($id, $company_id = null)
    {
        $query = App\Models\SavingAccount::whereId($id);
        if (!empty($company_id)) {
            $query->where('company_id', $company_id);
        }
        $data = $query->first('account_no');
        return $data;
    }
}
if (!function_exists('getSsbNewAccountNumber')) {
    function getSsbNewAccountNumber($id, $company_id = null)
    {
        $query = App\Models\SavingAccount::whereId($id);
        if (!empty($company_id)) {
            $query->where('company_id', $company_id);
        }
        $data = $query->first();
        return $data;
    }
}
if (!function_exists('getSsbPlanName')) {
    function getSsbPlanName($cattype, $company_id = null)
    {
        $query = App\Models\Plans::where('plan_category_code', $cattype);
        $data = $query->value('name');
        return $data;
    }
}
/**
 * get member data by email id
 * @param   $email,role
 * @return   Response (return count)
 */
if (!function_exists('checkMemberEmail')) {
    function checkMemberEmail($email, $role)
    {
        $data = App\Models\Member::where([['email', '=', $email], ['role_id', '=', $role],])->count();
        return $data;
    }
}
/**
 * get member data by form_no
 * @param   $form_no
 * @return   Response (return count)
 */
if (!function_exists('checkMemberFormNo')) {
    function checkMemberFormNo($form_no)
    {
        $data = App\Models\Member::where('form_no', $form_no)->count();
        return $data;
    }
}
/**
 * get  Occupation Name by id.
 * @param   $id
 * @return   Response (return name)
 */
if (!function_exists('getOccupationName')) {
    function getOccupationName($id)
    {
        $name = '';
        $data = App\Models\Occupation::whereId($id)->first('name');
        if ($data) {
            $name = $data->name;
        }
        return $name;
    }
}
/**
 * get  Religion Name by id.
 * @param   $id
 * @return   Response (return name)
 */
if (!function_exists('getReligionName')) {
    function getReligionName($id)
    {
        $name = '';
        $data = App\Models\Religion::whereId($id)->first('name');
        if ($data) {
            $name = $data->name;
        }
        return $name;
    }
}
/**
 * get  Special Categories Name by id.
 * @param   $id
 * @return   Response (return name)
 */
if (!function_exists('getSpecialCategoryName')) {
    function getSpecialCategoryName($id)
    {
        $name = '';
        $data = App\Models\SpecialCategory::whereId($id)->first('name');
        if ($data) {
            $name = $data->name;
        }
        return $name;
    }
}
/**
 * get  state Name by id.
 * @param   $id
 * @return   Response (return name)
 */
if (!function_exists('getStateName')) {
    function getStateName($id)
    {
        $name = '';
        $data = App\Models\State::whereId($id)->first('name');
        if ($data) {
            $name = $data->name;
        }
        return $name;
    }
}
/**
 * get  district Name by id.
 * @param   $id
 * @return   Response (return name)
 */
if (!function_exists('getDistrictName')) {
    function getDistrictName($id)
    {
        $name = '';
        $data = App\Models\District::whereId($id)->first('name');
        if ($data) {
            $name = $data->name;
        }
        return $name;
    }
}
/**
 * get  city  Name by id.
 * @param   $id
 * @return   Response (return name)
 */
if (!function_exists('getCityName')) {
    function getCityName($id)
    {
        $name = '';
        $data = App\Models\City::whereId($id)->first('name');
        if ($data) {
            $name = $data->name;
        }
        return $name;
    }
}
/**
 * get  Id Proof Type   Name by id.
 * @param   $id
 * @return   Response (return name)
 */
if (!function_exists('getIdProofName')) {
    function getIdProofName($id)
    {
        $name = '';
        $data = App\Models\IdType::whereId($id)->first('name');
        if ($data) {
            $name = $data->name;
        }
        return $name;
    }
}
/**
 * menu active from url
 */
if (!function_exists('set_active')) {
    function set_active($path, $classes = ['nav-item-open'])
    {
        $isCurrentUrl = call_user_func_array('Request::is', (array) $path);
        $class = implode(' ', $classes);
        return $isCurrentUrl ? $class : '';
    }
}
/**
 * branch menu active from url
 */
if (!function_exists('set_active_branch')) {
    function set_active_branch($path, $class = 'true')
    {
        $isCurrentUrl = call_user_func_array('Request::is', (array) $path);
        return $isCurrentUrl ? $class : 'false';
    }
}
/**
 * get  Carder  Name by id.
 * @param   $id
 * @return   Response (return name)
 */
if (!function_exists('getCarderName')) {
    function getCarderName($id)
    {
        $name = '';
        $data = App\Models\Carder::whereId($id)->first(['name', 'short_name']);
        if ($data) {
            $name = $data->name . '(' . $data->short_name . ')';
        }
        return $name;
    }
}
/**
 * get  Senior  data by id.
 * @param   $id
 * @return   Response (return name)
 */
if (!function_exists('getSeniorData')) {
    function getSeniorData($id, $field)
    {
        $name = '';
        $data = App\Models\Member::whereId($id)->first($field);
        if ($data) {
            $name = $data[$field];
        }
        return $name;
    }
}
/**
 * get last mi code for associate.
 * @param   $branch(associate branch)
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getLastMiCodeAssociate')) {
    function getLastMiCodeAssociate($branch)
    {
        $data = App\Models\Member::where([['is_associate', '=', 1], ['branch_id', '=', $branch]])->orderBy('associate_micode', 'desc')->first('associate_micode');
        return $data;
    }
}
/**
 * get ssb account detail.
 * @param   $memberid
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getMemberSsbAccountDetail')) {
    function getMemberSsbAccountDetail($memberId)
    {
        $data = App\Models\SavingAccount::where('member_id', $memberId)->first();
        return $data;
    }
}
if (!function_exists('getMemberCompanySsbAccountDetail')) {
    function getMemberCompanySsbAccountDetail($customerId, $companyId)
    {
        //  member_id in saving aaccount is now membercompany table primary key 'Id'
        $data = App\Models\SavingAccount::whereCustomerId($customerId)->whereCompanyId($companyId)->first();
        return $data;
    }
}
/**
 * get last mi code for  investments.
 * @param   $planId,$branch_id
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getInvesmentMiCode')) {
    function getInvesmentMiCode($planId, $branch_id)
    {
        $data = App\Models\Memberinvestments::Where('plan_id', $planId)->Where('branch_id', $branch_id)->orderBy('mi_code', 'desc')->first('mi_code');
        return $data;
    }
}
/**
 * get last mi code for loan.
 * @param   $planId,$branch_id
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getLoanMiCode')) {
    function getLoanMiCode($loantypeId, $branch_id)
    {
        $data = App\Models\Memberloans::Where('loan_type', $loantypeId)->Where('branch_id', $branch_id)->orderBy('mi_code', 'desc')->first('mi_code');
        return $data;
    }
}
/**
 * get last mi code for loan.
 * @param   $planId,$branch_id
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getGroupLoanMiCode')) {
    function getGroupLoanMiCode($branch_id)
    {
        $data = App\Models\Grouploans::Where('branch_id', $branch_id)->orderBy('mi_code', 'desc')->first('mi_code');
        return $data;
    }
}
/**
 * get Member investment Account exits or not.
 * @param   $planId,$branch_id
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getInvestmentAccount')) {
    function getInvestmentAccount($member_id, $account_no)
    {
        $data = App\Models\Memberinvestments::Where('account_number', $account_no)->Where('customer_id', $member_id)->first();
        return $data;
    }
}
/**
 * get plan detail .
 * @param   $code
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getPlanID')) {
    function getPlanID($code)
    {
        //dd($code);
        $data = DB::table('plans')->Where('plan_code', $code)->first();
        return $data;
    }
}
if (!function_exists('getPlanIDCustom')) {
    function getPlanIDCustom()
    {
        $results = array();
        $data = App\Models\Plans::whereIn('plan_code', array('710', '703', '709', '708', '707', '713', '704', '718', '712', '705', '706'))->get(['id', 'plan_code']);
        foreach ($data as $row) {
            $results[$row->plan_code] = $row->id;
        }
        return $results;
    }
}
function branchName()
{
    return App\Models\Branch::where('manager_id', Auth::user()->id)->first();
}
/**
 * get branch state .
 * @param   $code
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getBranchState')) {
    function getBranchState($username)
    {
        $data = App\Models\Branch::where('name', 'like', '%' . $username . '%')->first('state_id');
        if ($data) {
            return $data->state_id;
        }
    }
}
/**
 * get file data .
 * @param   $fileid
 * @return   Response
 */
if (!function_exists('getFileData')) {
    function getFileData($fileId)
    {
        $data = App\Models\Files::where('id', $fileId)->get();
        return $data;
    }
}
/**
 * get file data .
 * @param   $fileid
 * @return   Response
 */
if (!function_exists('getFirstFileData')) {
    function getFirstFileData($fileId)
    {
        $data = App\Models\Files::where('id', $fileId)->first();
        return $data;
    }
}
/**
 * get plan code .
 * @param   $planId
 * @return   Response
 */
if (!function_exists('getPlanCode')) {
    function getPlanCode($planId)
    {
        $data = App\Models\Plans::select('plan_code')->Where('id', $planId)->first();
        return $data['plan_code'];
    }
}
/**
 * get lone code .
 * @param   $loanId
 * @return   Response
 */
if (!function_exists('getLoanCode')) {
    function getLoanCode($loanId)
    {
        $data = App\Models\Loans::select('code')->Where('id', $loanId)->first();
        return $data['code'];
    }
}
/**
 * get lone code .
 * @param   $loanId
 * @return   Response
 */
if (!function_exists('getLoanData')) {
    function getLoanData($loanId)
    {
        $data = App\Models\Loans::Where('id', $loanId)->first();
        return $data;
    }
}
/**
 * get plan code .
 * @param   $id
 * @return   Response
 */
if (!function_exists('getSpecialCategory')) {
    function getSpecialCategory($id)
    {
        $data = App\Models\SpecialCategory::select('name')->whereId($id)->first();
        return $data['name'];
    }
}
/**
 * get  mi code for member by id.
 * @param   $member_id
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getMemberMiCode')) {
    function getMemberMiCode($member_id)
    {
        $data = App\Models\Member::where('id', $member_id)->first('mi_code');
        return $data;
    }
}
/**
 * get  member data by id.
 * @param   $member_id
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getMemberData')) {
    function getMemberData($member_id, $company_id = null)
    {
        $query = App\Models\MemberCompany::with('memberIdProofs', 'savingAccount', 'memberBankDetails', 'member');
        if (!empty($company_id)) {
            $query->where('company_id', $company_id);
        }
        $data = $query->where('member_id', $member_id)->first();
        return $data;
    }
}
if (!function_exists('getMemberAllData')) {
    function getMemberAllData($id, $company_id = null)
    {
        $query = App\Models\MemberCompany::whereNotNull('id');
        if (!empty($company_id)) {
            $query->where('company_id', $company_id);
        }
        $data = $query->where('customer_id', $id)->first();
        return $data;
    }
}
if (!function_exists('getDefaultCompanyId')) {
    function getDefaultCompanyId()
    {
        $data = App\Models\Companies::whereStatus('1')->whereHas('companyAssociate', function ($query) {
            $query->whereStatus('1')->select(['id', 'status', 'company_id']);
        })->first();
        return $data;
    }
}
if (!function_exists('getMemberDataAssociateId')) {
    function getMemberDataAssociateId($associate_id)
    {
        $query = App\Models\Member::with('memberIdProofs', 'savingAccount', 'memberBankDetails');
        $data = $query->where('associate_id', $associate_id)->first();
        return $data;
    }
}
/**
 * get ssb account detail by accont no.
 * @param   $account
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getSsbAccountDetail')) {
    function getSsbAccountDetail($account)
    {
        $data = App\Models\SavingAccount::where('account_no', $account)->first();
        return $data;
    }
}
if (!function_exists('getSsbAccountDetailNew')) {
    function getSsbAccountDetailNew($account)
    {
        $data = App\Models\SavingAccount::where('account_no', $account)->first(['balance']);
        return $data;
    }
}
/**
 * get ssb primary account detail by Member id .
 * @param   $account
 * @return   Response (return column value -- all detail)
 */
if (!function_exists('getSsbDetailPrimaryMember')) {
    function getSsbDetailPrimaryMember($member)
    {
        $data = App\Models\SavingAccount::where('member_id', $member)->where('is_primary', 1)->first();
        return $data;
    }
}
/**
 * get all active city list according to states.
 * @param $state_id
 * @return  array()  Response
 */
if (!function_exists('cityListState')) {
    function cityListState($state_id)
    {
        $cities = App\Models\City::where([['state_id', '=', $state_id], ['status', '=', '1'], ['is_deleted', '=', '0'],])->get(['id', 'name', 'state_id']);
        return $cities;
    }
}
/**
 * get all active relations.
 * @param
 * @return  array()  Response
 */
if (!function_exists('relationsList')) {
    function relationsList()
    {
        $relation = App\Models\Relations::get(['id', 'name']);
        return $relation;
    }
}
/**
 * get  relations Name by id.
 * @param   $id
 * @return   Response (return name)
 */
if (!function_exists('getRelationsName')) {
    function getRelationsName($id)
    {
        $name = '';
        $data = App\Models\Relations::whereId($id)->first('name');
        if ($data) {
            $name = $data->name;
        }
        return $name;
    }
}
/**
 * get  relations Name by id.
 * @param   $id
 * @return   Response (return name)
 */
if (!function_exists('amountINWord')) {
    function amountINWord($number)
    {
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $hundred = null;
        $digits_length = strlen($no);
        $i = 0;
        $str = array();
        $words = array(
            0 => '',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen',
            20 => 'Twenty',
            30 => 'Thirty',
            40 => 'Forty',
            50 => 'Fifty',
            60 => 'Sixty',
            70 => 'Seventy',
            80 => 'Eighty',
            90 => 'Ninety'
        );
        $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
        while ($i < $digits_length) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += $divider == 10 ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' And ' : null;
                $str[] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
            } else
                $str[] = null;
        }
        $Rupees = implode('', array_reverse($str));
        $paise = ($decimal > 0) ? "." . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
        return ($Rupees ? $Rupees . 'Rupees ' : '') . $paise;
    }
}
/**
 * get  member details.
 * @param   $id
 * @return   Response (return name)
 */
if (!function_exists('getMemberDetails')) {
    function getMemberDetails($id)
    {
        $data = App\Models\Member::leftJoin('member_bank_details', 'members.id', '=', 'member_bank_details.member_id')->leftJoin('saving_accounts', 'members.id', '=', 'saving_accounts.member_id')->where('members.id', $id)->select('members.*', 'member_bank_details.*', 'saving_accounts.*')->first();
        return $data;
    }
}
/**
 * get  member investment.
 * @param   $id
 * @return   Response (return name)
 */
if (!function_exists('getMemberInvestment')) {
    function getMemberInvestment($id, $company_id = null)
    {
        $query = App\Models\Memberinvestments::leftJoin('plans', 'member_investments.plan_id', '=', 'plans.id')->where('member_investments.id', $id);
        if (!empty($company_id)) {
            $query->where('member_investments.company_id', $company_id);
        }
        $data = $query->select('member_investments.*', 'plans.*')->first();
        return $data;
    }
}
/**
 * get  member investment.
 * @param   $id
 * @return   Response (return name)
 */
if (!function_exists('getDueDate')) {
    function getDueDate($date, $year)
    {
        $m = $year * 12;
        return date('d/m/Y', strtotime("" . $m . " months", strtotime($date)));
    }
}
/**
 * get  mi code for member by id.
 * @param   $member_id
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getApplicantid')) {
    function getApplicantid($member_id)
    {
        $aid = '';
        $data = App\Models\Member::where('id', $member_id)->first('member_id');
        if ($data) {
            $aid = $data->member_id;
        }
        return $aid;
    }
}
/**
 * get  mi code for member by id.
 * @param   $member_id
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getAssociateId')) {
    function getAssociateId($member_id, $company_id = null)
    {
        $associate_code = '';
        $query = App\Models\Member::where('id', $member_id);
        if (!empty($company_id)) {
            $query->first('company_id', $company_id);
        }
        $data = $query->first('associate_code');
        if ($data) {
            $associate_code = $data->associate_code;
        }
        return $associate_code;
    }
}
if (!function_exists('getAssociateNo')) {
    function getAssociateNo($member_id)
    {
        $associate_no = '';
        $data = App\Models\Member::where('id', $member_id)->first('associate_no');
        if ($data) {
            $associate_no = $data->associate_no;
        }
        return $associate_no;
    }
}
/**
 * get associate  mi code for member by id.
 * @param   $member_id,$branch_id
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getAssociateMiCode')) {
    function getAssociateMiCode($member_id, $branch_id)
    {
        $data = App\Models\Member::where([['associate_branch_id', '=', $branch_id]])->orderBy('associate_micode', 'desc')->first('associate_micode');
        return $data;
    }
}
/**
 * get  Carder  Name by id.
 * @param   $id
 * @return   Response (return name)
 */
if (!function_exists('getCarderNameFull')) {
    function getCarderNameFull($id)
    {
        $name = '';
        $data = App\Models\Carder::whereId($id)->first(['name', 'short_name']);
        if ($data) {
            $name = $data->name;
        }
        return $name;
    }
}
/**
 * date formate convert
 * @param   $date
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('convertDate')) {
    function convertDate($date)
    {
        $data = str_replace("/", "-", $date);
        return $data;
    }
}
/**
 * get transction  plan name .
 * @param   $transaction_type_id
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('transactionPlanName')) {
    function transactionPlanName($transaction_type_id)
    {
        $data = App\Models\Memberinvestments::with('plan')->where('id', $transaction_type_id)->first();
        return $data->plan->name;
    }
}
/**
 * get investment details .
 * @param   $investmentId
 * @return   Response (return array)
 */
if (!function_exists('getInvestmentDetails')) {
    function getInvestmentDetails($id)
    {
        $data = App\Models\Memberinvestments::where('id', $id)->first();
        return $data;
    }
}
/**
 * get associate tree .
 * @param   $id,cader
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('associateTree')) {
    function associateTree($id, $cader)
    {
        $data = App\Models\AssociateTree::where('senior_id', $id)->where('carder', $cader)->get();
        return $data;
    }
}
/**
 * get investment deposite amount .
 * @param   $investment_id
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('investmentDepositeAmount')) {
    function investmentDepositeAmount($id)
    {
        $data = App\Models\Memberinvestments::select('deposite_amount', 'current_balance', 'created_at', 'plan_id')->where('id', $id)->first();
        return $data;
    }
}
/**
 * get monthly wise renewal .
 * @param   $investmentId,$renewAmount
 * @return   Response (return array)
 */
if (!function_exists('getMonthlyWiseRenewal')) {
    function getMonthlyWiseRenewal($investmentId, $renewAmount, $dateForRenew)
    {
        $resultArray = array();
        $renewMonth = 0;
        $invesmentData = investmentDepositeAmount($investmentId);
        $currentBalance = $invesmentData->current_balance;
        $invesmentDepositeAmount = $invesmentData->deposite_amount;
        $currentMonth = date("m", strtotime($dateForRenew));
        $currentDay = date("d", strtotime($dateForRenew));
        $lastRenewalDate = Daybook::select('created_at')->where('investment_id', $investmentId)->orderBy('opening_balance', 'DESC')->limit(1)->first();
        $lastRenewalTime = strtotime($lastRenewalDate['created_at']);
        $getLastMonth = date("m", $lastRenewalTime);
        $monthDiff = $currentMonth - $getLastMonth;
        $renewAmountMonthsNumber = $renewAmount / $invesmentDepositeAmount;
        $getEmiMonth = $currentBalance / $invesmentDepositeAmount;
        $b = strtotime($invesmentData->created_at);
        $y = date("m", $b);
        $emiDay = date("d", $b);
        $emiMont = ($currentMonth - $y) + 1;
        for ($i = 1; $i <= $renewAmountMonthsNumber; $i++) {
            $renewMonth = $getEmiMonth + $i;
            if ($invesmentData->plan_id == 7) {
                $j = $renewMonth + Date("t", strtotime($dateForRenew . " last month"));
                $month = ' + ' . $j . ' day';
                $createDateInvest = date('Y-m-d', strtotime($invesmentData->created_at . $month));
                // echo $createDateInvest;
                $j1 = $renewMonth;
                $month1 = ' + ' . $j1 . ' day';
                $createDateInvest1 = date('Y-m-d', strtotime($invesmentData->created_at . $month1));
                $emiDateInvest1 = strtotime($createDateInvest1);
                $emiDateInvest = strtotime($createDateInvest);
                $renewDateinvest = strtotime($dateForRenew);
                if ($renewDateinvest > $emiDateInvest) {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    //$resultArray[$i]['type'] = 1;
                    $resultArray[$i]['type'] = 2;
                } else if ($renewDateinvest == $emiDateInvest1) {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    $resultArray[$i]['type'] = 2;
                } else {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    if ($renewDateinvest >= $emiDateInvest1 && $renewDateinvest <= $emiDateInvest) {
                        $resultArray[$i]['type'] = 2;
                    } else {
                        //$resultArray[$i]['type'] = 3;
                        $resultArray[$i]['type'] = 2;
                    }
                }
            } else {
                $renewMonth1 = $renewMonth - 1;
                $month = ' + ' . $renewMonth . ' month';
                $month1 = ' + ' . $renewMonth1 . ' month';
                $createDateInvest = date('Y-m', strtotime($invesmentData->created_at . $month));
                $renewM = date("Y-m", strtotime($createDateInvest));
                $emiDateInvest = strtotime($renewM);
                $createDateInvest1 = date('Y-m', strtotime($invesmentData->created_at . $month1));
                $renewM1 = date("Y-m", strtotime($createDateInvest1));
                $emiDateInvest1 = strtotime($renewM1);
                $dateForRenewM = date("Y-m", strtotime($dateForRenew));
                $renewDateinvest = strtotime($dateForRenewM);
                if ($renewDateinvest > $emiDateInvest) {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    //$resultArray[$i]['type'] = 1;
                    $resultArray[$i]['type'] = 2;
                } elseif ($renewDateinvest == $emiDateInvest1) {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    $resultArray[$i]['type'] = 2;
                } else {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    $resultArray[$i]['type'] = 3;
                    if ($renewDateinvest >= $emiDateInvest1 && $renewDateinvest <= $emiDateInvest) {
                        $resultArray[$i]['type'] = 2;
                    } else {
                        //$resultArray[$i]['type'] = 3;
                        $resultArray[$i]['type'] = 2;
                    }
                }
            }
        }
        return $resultArray;
    }
}
/**
 * get associate tree .
 * @param   $id,cader
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('associateTreeChain')) {
    function associateTreeChain($id)
    {
        $data = App\Models\AssociateTree::where('member_id', $id)->first();
        return $data;
    }
}
/**
 * get associate tree .
 * @param   $id,cader
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('allCarder')) {
    function allCarder($x)
    {
        $data = App\Models\Carder::whereNotIn('id', $x)->get();
        return $data;
    }
}
/**
 * get associate commission count .
 * @param   $mId,$cType
 * @return   Response (return result count)
 */
if (!function_exists('countAssociateCommission')) {
    function countAssociateCommission($mId, $cType)
    {
        $data = App\Models\AssociateCommission::where('member_id', $mId)->where('commission_type', $cType)->where('type', 1)->count();
        return $data;
    }
}
/**
 * get associate commission count from carder.
 * @param   $mId,$cType,$carderId
 * @return   Response (return result count)
 */
if (!function_exists('countAssociateCommissionByCarder')) {
    function countAssociateCommissionByCarder($mId, $cType, $carderId)
    {
        $data = App\Models\AssociateCommission::where('member_id', $mId)->where('commission_type', $cType)->where('type', 2)->where('carder_id', $carderId)->count();
        return $data;
    }
}
/**
 * get commission amount sum.
 * @param   $mId,$cType,$carderId
 * @return   Response (return result count)
 */
if (!function_exists('getCommissionAmountSum')) {
    function getCommissionAmountSum($mId, $investmenttypeId, $cType, $isDistribute)
    {
        $data = App\Models\AssociateCommission::where('member_id', $mId)->where('type', 3)->where('type_id', $investmenttypeId)->where('commission_type', $cType)->where('is_distribute', $isDistribute)->sum('commission_amount');
        return $data;
    }
}
/**
 * get Commission detail .
 * @param   $plan_id,carder,$month
 * @return   Response (return column value )
 */
if (!function_exists('commissionDetail')) {
    function commissionDetail($plan_id, $carder, $month, $tenure)
    {
        $data = App\Models\CommissionDetail::where('plan_id', $plan_id)->where('tenure', $tenure)->where('carder_id', $carder)->whereRaw('? between tenure_to and tenure_from', [$month])->first();
        return $data;
    }
}
/**
 * Get Business target self amount.
 * Method: Post
 * @param  \Illuminate\Http\Request  $carderid,$commissiontype
 * @return array
 */
if (!function_exists('getBusinessTargetAmt')) {
    function getBusinessTargetAmt($carderid)
    {
        $businessTarget = App\Models\BusinessTarget::select('credit', 'self')->where('carder_id', $carderid)->first();
        return $businessTarget;
    }
}
/**
 * Get Achieved target self amount.
 * Method: Post
 * @param  \Illuminate\Http\Request  $carderid,$commissiontype
 * @return array
 */
if (!function_exists('getAchievedTargetAmt')) {
    function getAchievedTargetAmt($associateCommissions)
    {
        $achievedAmt = 0;
        foreach ($associateCommissions as $key => $value) {
            $businessTarget = App\Models\AssociateCommission::select('commission_amount')->where('id', $value->id)->where('commission_type', 0)->first();
            $achievedAmt = $achievedAmt + $businessTarget['commission_amount'];
        }
        return $achievedAmt;
    }
}
/**
 * Get Quota Business Target Percentage.
 * Method: Post
 * @param  \Illuminate\Http\Request  $carderid,$commissiontype
 * @return array
 */
if (!function_exists('getQuotaBusinessTargetPercentage')) {
    function getQuotaBusinessTargetPercentage($associateCommissions, $carderid, $field)
    {
        $achievedAmt = 0;
        $businessTarget = App\Models\BusinessTarget::select('' . $field . '')->where('carder_id', $carderid)->first();
        foreach ($associateCommissions as $key => $value) {
            $businessTarget = App\Models\AssociateCommission::select('commission_amount')->where('id', $value->id)->where('commission_type', 0)->first();
            $achievedAmt = $achievedAmt + $businessTarget['commission_amount'];
        }
        if ($achievedAmt > 0 && $businessTarget['self'] > 0) {
            $result = $achievedAmt * 100 / $businessTarget['self'];
        } else {
            $result = 0;
        }
        return $result;
    }
}
/**
 * Get Achieved Target Percentage.
 * Method: Post
 * @param  \Illuminate\Http\Request  $carderid,$commissiontype
 * @return array
 */
if (!function_exists('getAchievedTargetPercentage')) {
    function getAchievedTargetPercentage($associateCommissions, $carderid, $field)
    {
        $achievedAmt = 0;
        $result = '';
        $businessTargetQuery = App\Models\BusinessTarget::select('id', '' . $field . '')->where('carder_id', $carderid)->first();
        if (!empty($associateCommissions)) {
            foreach ($associateCommissions as $key => $value) {
                $businessTarget = App\Models\AssociateCommission::select('commission_amount')->where('id', $value->id)->where('commission_type', 0)->first();
                if (!empty($businessTarget)) {
                    $achievedAmt += (int) $achievedAmt + (int) $businessTarget['commission_amount'];
                }
            }
            if (($businessTargetQuery['' . $field . ''] - $achievedAmt) > 0 && $businessTargetQuery['' . $field . ''] > 0) {
                $result = ($businessTargetQuery['' . $field . ''] - $achievedAmt) * 100 / $businessTargetQuery['' . $field . ''];
            } else {
                $result = 0;
            }
            return 1000;
        } else {
            return 2000;
        }
    }
}
if (!function_exists('getSavingAccountMemberId')) {
    function getSavingAccountMemberId($id)
    {
        $data = App\Models\SavingAccount::whereId($id)->first();
        return $data;
    }
}
/**
 * Count associate's connectecd associate by carder or member_id.
 * Method: Post
 * @param  \Illuminate\Http\Request  $carderid,$startDate,$endDate
 * @return count
 */
if (!function_exists('countAssociateByCarder')) {
    function countAssociateByCarder($member_id, $carderid, $startDate, $endDate)
    {
        $businessTarget = App\Models\AssociateCommission::where('member_id', $member_id)->where('type', 2)->where('carder_id', $carderid)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->count();
        return ($businessTarget);
    }
}
/**
 * get associate tree .
 * @param   $id,cader
 * @return   Response
 */
if (!function_exists('associateTreeChainActiveGet')) {
    function associateTreeChainActiveGet($id)
    {
        $data1 = App\Models\AssociateTree::with('member')->where('member_id', $id)->first();
        if ($data1->member->associate_status == 1) {
            $data = App\Models\AssociateTree::where('member_id', $id)->first();
            return $data;
        } else {
            if ($data1->senior_id == 0) {
                $data = App\Models\AssociateTree::where('member_id', $data1->member_id)->first();
                return $data;
            } else {
                return associateTreeChainActiveChain($data1->senior_id);
            }
        }
    }
}
if (!function_exists('associateTreeChainActiveChain')) {
    function associateTreeChainActiveChain($id)
    {
        $data1 = App\Models\AssociateTree::with('member')->where('member_id', $id)->first();
        if ($data1->member->associate_status == 1) {
            $data = App\Models\AssociateTree::where('member_id', $id)->first();
            return $data;
        } else {
            if ($data1->senior_id == 0) {
                $data = App\Models\AssociateTree::where('member_id', $data1->member_id)->first();
                return $data;
            } else {
                return associateTreeChainActiveChain($data1->senior_id);
            }
        }
    }
}
/**
 * associate team business get .
 * Method: Post
 * @param  \Illuminate\Http\Request  $member_id,$startDate,$endDate
 * @return count
 */
if (!function_exists('getKotaBusinessTeam')) {
    function getKotaBusinessTeam($member_id, $startDate, $endDate)
    {
        $businessTarget = App\Models\AssociateKotaBusinessTeam::where('member_id', $member_id);
        if ($startDate != '') {
            $businessTarget = $businessTarget->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $businessTarget = $businessTarget->get('associate_kota_business_id');
        $team_business = \App\Models\AssociateKotaBusiness::whereIn('id', $businessTarget)->sum('business_amount');
        return $team_business;
    }
}
/**
 * get associate commission .
 * @param   $member_id startdate,enddate
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getAssociateTotalCommission')) {
    function getAssociateTotalCommission($member_id, $startDate, $endDate, $type)
    {
        if ($startDate != '') {
            $total_amount = App\Models\AssociateCommission::where('member_id', $member_id)->where('type', '>', 2)->where('is_deleted', '0')->where('is_distribute', 0)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum($type);
        } else {
            $total_amount = App\Models\AssociateCommission::where('member_id', $member_id)->where('type', '>', 2)->where('is_deleted', '0')->where('is_distribute', 0)->sum($type);
        }
        return number_format($total_amount, 2, '.', '');
    }
}
if (!function_exists('getAssociateTotalCommissionDistribute')) {
    function getAssociateTotalCommissionDistribute($member_id, $startDate, $endDate, $type)
    {
        if ($startDate != '') {
            $total_amount = App\Models\AssociateCommission::where('is_distribute', 1)->where('member_id', $member_id)->where('type', '>', 2)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum($type);
        } else {
            $total_amount = App\Models\AssociateCommission::where('is_distribute', 1)->where('member_id', $member_id)->where('type', '>', 2)->sum($type);
        }
        return number_format($total_amount, 2, '.', '');
    }
}
/**
 * get finacial year .
 * @param
 * @return   Response (return start date or end date
 */
if (!function_exists('getFinacialYear')) {
    function getFinacialYear()
    {
        if (date('m') > 3) {
            $syear = date('Y');
            $eyear = (date('Y') + 1);
        } else {
            $syear = (date('Y') - 1);
            $eyear = date('Y');
        }
        $startDate = $syear . '-04-01';
        $endDate = $eyear . '-03-31';
        $dateStart = date('Y-m-d', strtotime($startDate));
        $dateEnd = date('Y-m-d', strtotime($endDate));
        $return_array = compact('dateStart', 'dateEnd');
        return $return_array;
    }
}
/**
 * get  business target status .
 * @param   $member_id,$startDate,$endDate,$currentCarder
 * @return   Response (return start date or end date
 */
if (!function_exists('getFinacialYearBusinessTarget')) {
    function getFinacialYearBusinessTarget($member_id, $startDate, $endDate, $currentCarder)
    {
        $businessTarget = App\Models\BusinessTarget::where('carder_id', $currentCarder)->first();
        $achivedTargetStatus = 0;
        for ($i = 1; $i < $currentCarder; $i++) {
            $achevedMember = countAssociateByCarder($member_id, $i, $startDate, $endDate);
            $a = 'Carder-' . $i;
            $targetMember = $businessTarget->$a;
            if ($targetMember == $achevedMember) {
                $achivedTargetStatus = $achivedTargetStatus + 1;
            } else {
                $achivedTargetStatus = 0;
            }
        }
        $commissionSelf = App\Models\AssociateKotaBusiness::where('member_id', $member_id)->where('type', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount');
        if ($businessTarget->self == $commissionSelf) {
            $achivedTargetStatus = $achivedTargetStatus + 1;
        } else {
            $achivedTargetStatus = 0;
        }
        $commissionCredit = getKotaBusinessTeam($member_id, $startDate, $endDate);
        if ($businessTarget->credit == $commissionCredit) {
            $achivedTargetStatus = $achivedTargetStatus + 1;
        } else {
            $achivedTargetStatus = 0;
        }
        if ($achivedTargetStatus == (($currentCarder - 1) + 2)) {
            $achivedTargetStatus = ($currentCarder - 1) + 2;
        } else {
            $achivedTargetStatus = 0;
        }
        return $achivedTargetStatus;
    }
}
/**
 * get achived target member  .
 * @param
 * @return   Response (return start date or end date
 */
if (!function_exists('getBusinessTargetAchivedMember')) {
    function getBusinessTargetAchivedMember()
    {
        $mid = '';
        $member = App\Models\Member::where('member_id', '!=', '9999999')->where('is_associate', 1)->where('current_carder_id', '>', 0)->get();
        // print_r($member);die;
        $finacialYear = getFinacialYear();
        $startDate = $finacialYear['dateStart'];
        $endDate = $finacialYear['dateEnd'];
        foreach ($member as $val) {
            $currentCarder = $val->current_carder_id;
            $member_id = $val->id;
            $businessTarget = App\Models\BusinessTarget::where('carder_id', $currentCarder)->first();
            $achivedTargetStatus = 0;
            for ($i = 1; $i < $currentCarder; $i++) {
                $achevedMember = countAssociateByCarder($member_id, $i, $startDate, $endDate);
                $a = 'Carder-' . $i;
                $targetMember = $businessTarget->$a;
                if ($targetMember == $achevedMember) {
                    $achivedTargetStatus = $achivedTargetStatus + 1;
                } else {
                    $achivedTargetStatus = 0;
                }
            }
            $commissionSelf = App\Models\AssociateKotaBusiness::where('member_id', $member_id)->where('type', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount');
            //  echo $businessTarget->self;die;
            if ($businessTarget->self == $commissionSelf) {
                $achivedTargetStatus = $achivedTargetStatus + 1;
            } else {
                $achivedTargetStatus = 0;
            }
            $commissionCredit = getKotaBusinessTeam($member_id, $startDate, $endDate);
            if ($businessTarget->credit == $commissionCredit) {
                $achivedTargetStatus = $achivedTargetStatus + 1;
            } else {
                $achivedTargetStatus = 0;
            }
            //  echo $val->id.'=='.$businessTarget->credit.'='.$commissionCredit.'<br>';
            if ($achivedTargetStatus == (($currentCarder - 1) + 2)) {
                $mid .= $val->id . ',';
            }
        }
        return $mid;
    }
}
/**
 * get achived self business  .
 * @param   $id,$startDate,$endDate
 * @return   Response (return start date or end date
 */
if (!function_exists('getAchievedSelfBusiness')) {
    function getAchievedSelfBusiness($id, $startDate, $endDate)
    {
        $selfBusiness = App\Models\AssociateKotaBusiness::where('member_id', $id)->where('type', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount');
        return $selfBusiness;
    }
}
if (!function_exists('getCorrectionStatus')) {
    function getCorrectionStatus($correctionType, $correctionTypeId)
    {
        $data = App\Models\CorrectionRequests::select('status')->where('correction_type', $correctionType)->where('correction_type_id', $correctionTypeId)->first();
        if ($data && isset($data)) {
            return $data->status;
        } else {
            return '';
        }
    }
}
if (!function_exists('getCorrectionCertificateStatus')) {
    function getCorrectionCertificateStatus($correctionType, $correctionTypeId)
    {
        $data = App\Models\CorrectionRequests::select('status')->where('correction_type', $correctionType)->where('correction_type_id', $correctionTypeId)->orderBy('created_at', 'desc')->first();
        if ($data && isset($data)) {
            return $data->status;
        } else {
            return '';
        }
    }
}
if (!function_exists('checkInvestmentExists')) {
    function checkInvestmentExists($aNumber)
    {
        $data = App\Models\Memberinvestments::where('account_number', 'R-' . $aNumber . '')->count();
        return $data;
    }
}
if (!function_exists('countInvestmentExists')) {
    function countInvestmentExists($aNumbers)
    {
        $aNumbersArray = explode(',', $aNumbers);
        $i = 0;
        foreach ($aNumbersArray as $key => $value) {
            $data = App\Models\Memberinvestments::where('account_number', 'R-' . $value . '')->count();
            if ($data > 0) {
                $i++;
            }
        }
        return $i;
    }
}
/**
 * get monthly wise renewal .
 * @param   $investmentId,$renewAmount
 * @return   Response (return array)
 */
if (!function_exists('getMonthlyWiseRenewal1')) {
    function getMonthlyWiseRenewal1($investmentId, $renewAmount, $dateForRenew, $currentBalance, $dayBookId)
    {
        $resultArray = array();
        $renewMonth = 0;
        $invesmentData = investmentDepositeAmount($investmentId);
        $currentBalance = $currentBalance;
        $invesmentDepositeAmount = $invesmentData->deposite_amount;
        $currentMonth = date("m", strtotime($dateForRenew));
        $currentDay = date("d", strtotime($dateForRenew));
        $lastRenewalDate = Daybook::select('created_at')->where('investment_id', $investmentId)->where('id', '!=', $dayBookId)->orderBy('opening_balance', 'ASC')->limit(1)->first();
        $lastRenewalTime = strtotime($lastRenewalDate['created_at']);
        $getLastMonth = date("m", $lastRenewalTime);
        $monthDiff = $currentMonth - $getLastMonth;
        $renewAmountMonthsNumber = $renewAmount / $invesmentDepositeAmount;
        $getEmiMonth = $currentBalance / $invesmentDepositeAmount;
        $b = strtotime($invesmentData->created_at);
        $y = date("m", $b);
        $emiDay = date("d", $b);
        $emiMont = ($currentMonth - $y) + 1;
        for ($i = 1; $i <= $renewAmountMonthsNumber; $i++) {
            $renewMonth = $getEmiMonth + $i;
            if ($invesmentData->plan_id == 7) {
                $j = $renewMonth + Date("t", strtotime($dateForRenew . " last month"));
                $month = ' + ' . $j . ' day';
                $createDateInvest = date('Y-m-d', strtotime($invesmentData->created_at . $month));
                // echo $createDateInvest;
                $j1 = $renewMonth;
                $month1 = ' + ' . $j1 . ' day';
                $createDateInvest1 = date('Y-m-d', strtotime($invesmentData->created_at . $month1));
                $emiDateInvest1 = strtotime($createDateInvest1);
                $emiDateInvest = strtotime($createDateInvest);
                $renewDateinvest = strtotime($dateForRenew);
                if ($renewDateinvest > $emiDateInvest) {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    //$resultArray[$i]['type'] = 1;
                    $resultArray[$i]['type'] = 2;
                } else if ($renewDateinvest == $emiDateInvest1) {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    $resultArray[$i]['type'] = 2;
                } else {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    if ($renewDateinvest >= $emiDateInvest1 && $renewDateinvest <= $emiDateInvest) {
                        $resultArray[$i]['type'] = 2;
                    } else {
                        //$resultArray[$i]['type'] = 3;
                        $resultArray[$i]['type'] = 2;
                    }
                }
            } else {
                $renewMonth1 = $renewMonth - 1;
                $month = ' + ' . $renewMonth . ' month';
                $month1 = ' + ' . $renewMonth1 . ' month';
                $createDateInvest = date('Y-m', strtotime($invesmentData->created_at . $month));
                $renewM = date("Y-m", strtotime($createDateInvest));
                $emiDateInvest = strtotime($renewM);
                $createDateInvest1 = date('Y-m', strtotime($invesmentData->created_at . $month1));
                $renewM1 = date("Y-m", strtotime($createDateInvest1));
                $emiDateInvest1 = strtotime($renewM1);
                $dateForRenewM = date("Y-m", strtotime($dateForRenew));
                $renewDateinvest = strtotime($dateForRenewM);
                if ($renewDateinvest > $emiDateInvest) {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    //$resultArray[$i]['type'] = 1;
                    $resultArray[$i]['type'] = 2;
                } elseif ($renewDateinvest == $emiDateInvest1) {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    $resultArray[$i]['type'] = 2;
                } else {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    $resultArray[$i]['type'] = 3;
                    if ($renewDateinvest >= $emiDateInvest1 && $renewDateinvest <= $emiDateInvest) {
                        $resultArray[$i]['type'] = 2;
                    } else {
                        //$resultArray[$i]['type'] = 3;
                        $resultArray[$i]['type'] = 2;
                    }
                }
            }
        }
        return $resultArray;
    }
}
/**
 * get monthly wise renewal .
 * @param   $investmentId,$renewAmount
 * @return   Response (return array)
 */
if (!function_exists('getMonthlyWiseRenewalNew')) {
    function getMonthlyWiseRenewalNew($investmentId, $renewAmount, $dateForRenew)
    {
        $resultArray = array();
        $renewMonth = 0;
        $invesmentData = investmentDepositeAmount($investmentId);
        $currentBalance = $invesmentData->current_balance;
        $invesmentDepositeAmount = $invesmentData->deposite_amount;
        $currentMonth = date("m", strtotime($dateForRenew));
        $currentDay = date("d", strtotime($dateForRenew));
        $lastRenewalDate = Daybook::select('created_at')->where('investment_id', $investmentId)->orderBy('opening_balance', 'DESC')->limit(1)->first();
        $lastRenewalTime = strtotime($lastRenewalDate['created_at']);
        $getLastMonth = date("m", $lastRenewalTime);
        $monthDiff = $currentMonth - $getLastMonth;
        $renewAmountMonthsNumber = $renewAmount / $invesmentDepositeAmount;
        $getEmiMonth = $currentBalance / $invesmentDepositeAmount;
        $b = strtotime($invesmentData->created_at);
        $y = date("m", $b);
        $emiDay = date("d", $b);
        $emiMont = ($currentMonth - $y) + 1;
        //echo 'new==='.$renewAmountMonthsNumber.'<br>';
        for ($i = 1; $i <= $renewAmountMonthsNumber; $i++) {
            $renewMonth = $getEmiMonth + $i;
            if ($invesmentData->plan_id == 7) {
                $j = $renewMonth + Date("t", strtotime($dateForRenew . " last month"));
                $month = ' + ' . $j . ' day';
                $createDateInvest = date('Y-m-d', strtotime($invesmentData->created_at . $month));
                //     echo $createDateInvest.'==7';
                $j1 = $renewMonth;
                $month1 = ' + ' . $j1 . ' day';
                $createDateInvest1 = date('Y-m-d', strtotime($invesmentData->created_at . $month1));
                $emiDateInvest1 = strtotime($createDateInvest1);
                $emiDateInvest = strtotime($createDateInvest);
                $renewDateinvest = strtotime($dateForRenew);
                if ($renewDateinvest > $emiDateInvest) {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    //$resultArray[$i]['type'] = 1;
                    $resultArray[$i]['type'] = 2;
                } else if ($renewDateinvest == $emiDateInvest1) {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    $resultArray[$i]['type'] = 2;
                } else {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    if ($renewDateinvest >= $emiDateInvest1 && $renewDateinvest <= $emiDateInvest) {
                        $resultArray[$i]['type'] = 2;
                    } else {
                        //$resultArray[$i]['type'] = 3;
                        $resultArray[$i]['type'] = 2;
                    }
                }
            } else {
                // echo 'else other plan ';
                $renewMonth1 = $renewMonth - 1;
                $month = ' + ' . $renewMonth . ' month';
                $month1 = ' + ' . $renewMonth1 . ' month';
                $createDateInvest = date('Y-m', strtotime($invesmentData->created_at . $month));
                $renewM = date("Y-m", strtotime($createDateInvest));
                $emiDateInvest = strtotime($renewM);
                $createDateInvest1 = date('Y-m', strtotime($invesmentData->created_at . $month1));
                $renewM1 = date("Y-m", strtotime($createDateInvest1));
                $emiDateInvest1 = strtotime($renewM1);
                $dateForRenewM = date("Y-m", strtotime($dateForRenew));
                $renewDateinvest = strtotime($dateForRenewM);
                if ($renewDateinvest > $emiDateInvest) {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    //$resultArray[$i]['type'] = 1;
                    $resultArray[$i]['type'] = 2;
                } elseif ($renewDateinvest == $emiDateInvest1) {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    $resultArray[$i]['type'] = 2;
                } else {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    $resultArray[$i]['type'] = 3;
                    if ($renewDateinvest >= $emiDateInvest1 && $renewDateinvest <= $emiDateInvest) {
                        $resultArray[$i]['type'] = 2;
                    } else {
                        //$resultArray[$i]['type'] = 3;
                        $resultArray[$i]['type'] = 2;
                    }
                }
            }
        }
        return $resultArray;
    }
}
if (!function_exists('headerMonthAvailability')) {
    function headerMonthAvailability($day, $month, $year, $stateId)
    {
        $oddMonths = array(1, 3, 5, 7, 8, 10, 12);
        $evenMonths = array(2, 4, 6, 9, 11);
        if ($month == 2) {
            //$mDdays = 28;
            $mDdays = 29;
        } else {
            if (in_array($month, $oddMonths)) {
                $mDdays = 31;
            } elseif (in_array($month, $evenMonths)) {
                $mDdays = 30;
            }
        }
        if ($stateId == 13) {
            $mCount = App\Models\HolidaySettings::where('state_id', $stateId)->where('month_number', $month)->where('year', $year)->count('id');
        } else {
            $mCount = App\Models\HolidaySettings::where('state_id', $stateId)->where('month_number', $month)->where('year', $year)->count('id');
        }
        if ($mCount > 0) {
            $cDate = $year . '-' . $month . '-' . $day;
            $dCount = App\Models\Event::where('state_id', $stateId)->where('start_date', $cDate)->count('id');
            if ($dCount == 0) {
                return date("d/m/Y", strtotime($cDate));
            } else if ($day > 0) {
                for ($j = ($day); $j > 0; $j--) {
                    $date = $year . '-' . $month . '-' . $j;
                    $dCount = App\Models\Event::where('state_id', $stateId)->where('start_date', $date)->count('id');
                    if ($dCount == 0) {
                        return date("d/m/Y", strtotime($date));
                    }
                    if ($j == 1) {
                        if ($month > 1) {
                            for ($i = ($month - 1); $i > 0; $i--) {
                                $pmCount = App\Models\HolidaySettings::where('state_id', $stateId)->where('month_number', $i)->where('year', date('Y'))->count('id');
                                if ($pmCount > 0) {
                                    if ($i == 2) {
                                        //$mDdays = 28;
                                        $mDdays = 29;
                                    } else {
                                        if (in_array($i, $oddMonths)) {
                                            $mDdays = 31;
                                        } elseif (in_array($i, $evenMonths)) {
                                            $mDdays = 30;
                                        }
                                    }
                                    for ($j = $mDdays; $j > 0; $j--) {
                                        $date = $year . '-' . $i . '-' . $j;
                                        $dCount = App\Models\Event::where('state_id', $stateId)->where('start_date', $date)->count('id');
                                        if ($dCount == 0) {
                                            return date("d/m/Y", strtotime($date));
                                        }
                                    }
                                }
                            }
                        } else {
                            $preMonth = 12;
                            $preYear = date('Y') - 1;
                            for ($i = ($preMonth); $i > 0; $i--) {
                                $pmCount = App\Models\HolidaySettings::where('state_id', $stateId)->where('month_number', $i)->where('year', $preYear)->count('id');
                                if ($pmCount > 0) {
                                    if ($i == 2) {
                                        //$mDdays = 28;
                                        $mDdays = 29;
                                    } else {
                                        if (in_array($i, $oddMonths)) {
                                            $mDdays = 31;
                                        } elseif (in_array($i, $evenMonths)) {
                                            $mDdays = 30;
                                        }
                                    }
                                    for ($j = $mDdays; $j > 0; $j--) {
                                        $date = $preYear . '-' . $i . '-' . $j;
                                        $dCount = App\Models\Event::where('state_id', $stateId)->where('start_date', $date)->count('id');
                                        if ($dCount == 0) {
                                            return date("d/m/Y", strtotime($date));
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                if ($month > 1) {
                    for ($i = ($month - 1); $i > 0; $i--) {
                        $pmCount = App\Models\HolidaySettings::where('state_id', $stateId)->where('month_number', $i)->where('year', date('Y'))->count('id');
                        if ($pmCount > 0) {
                            if ($i == 2) {
                                //$mDdays = 28;
                                $mDdays = 29;
                            } else {
                                if (in_array($i, $oddMonths)) {
                                    $mDdays = 31;
                                } elseif (in_array($i, $evenMonths)) {
                                    $mDdays = 30;
                                }
                            }
                            for ($j = $mDdays; $j > 0; $j--) {
                                $date = $year . '-' . $i . '-' . $j;
                                $dCount = App\Models\Event::where('state_id', $stateId)->where('start_date', $date)->count('id');
                                if ($dCount == 0) {
                                    return date("d/m/Y", strtotime($date));
                                }
                            }
                        }
                    }
                } else {
                    $preMonth = 12;
                    $preYear = date('Y') - 1;
                    for ($i = ($preMonth); $i > 0; $i--) {
                        $pmCount = App\Models\HolidaySettings::where('state_id', $stateId)->where('month_number', $i)->where('year', $preYear)->count('id');
                        if ($pmCount > 0) {
                            if ($i == 2) {
                                //$mDdays = 28;
                                $mDdays = 29;
                            } else {
                                if (in_array($i, $oddMonths)) {
                                    $mDdays = 31;
                                } elseif (in_array($i, $evenMonths)) {
                                    $mDdays = 30;
                                }
                            }
                            for ($j = $mDdays; $j > 0; $j--) {
                                $date = $preYear . '-' . $i . '-' . $j;
                                $dCount = App\Models\Event::where('state_id', $stateId)->where('start_date', $date)->count('id');
                                if ($dCount == 0) {
                                    return date("d/m/Y", strtotime($date));
                                }
                            }
                        }
                    }
                }
            }
        } else {
            if ($month > 1) {
                for ($i = ($month - 1); $i > 0; $i--) {
                    $pmCount = App\Models\HolidaySettings::where('state_id', $stateId)->where('month_number', $i)->where('year', date('Y'))->count('id');
                    if ($pmCount > 0) {
                        if ($i == 2) {
                            //$mDdays = 28;
                            $mDdays = 29;
                        } else {
                            if (in_array($i, $oddMonths)) {
                                $mDdays = 31;
                            } elseif (in_array($i, $evenMonths)) {
                                $mDdays = 30;
                            }
                        }
                        for ($j = $mDdays; $j > 0; $j--) {
                            $date = $year . '-' . $i . '-' . $j;
                            $dCount = App\Models\Event::where('state_id', $stateId)->where('start_date', $date)->count('id');
                            if ($dCount == 0) {
                                return date("d/m/Y", strtotime($date));
                            }
                        }
                    }
                }
            } else {
                $preMonth = 12;
                $preYear = date('Y') - 1;
                for ($i = ($preMonth); $i > 0; $i--) {
                    $pmCount = App\Models\HolidaySettings::where('state_id', $stateId)->where('month_number', $i)->where('year', $preYear)->count('id');
                    if ($pmCount > 0) {
                        if ($i == 2) {
                            //   $mDdays = 28;
                            $mDdays = 29;
                        } else {
                            if (in_array($i, $oddMonths)) {
                                $mDdays = 31;
                            } elseif (in_array($i, $evenMonths)) {
                                $mDdays = 30;
                            }
                        }
                        for ($j = $mDdays; $j > 0; $j--) {
                            $date = $preYear . '-' . $i . '-' . $j;
                            $dCount = App\Models\Event::where('state_id', $stateId)->where('start_date', $date)->count('id');
                            if ($dCount == 0) {
                                return date("d/m/Y", strtotime($date));
                            }
                        }
                    }
                }
            }
        }
    }
}
if (!function_exists('checkMonthAvailability')) {
    function checkMonthAvailability($day, $month, $year, $stateId)
    {
        $t = time();
        $time = date("H:i:s");
        //echo $day.'/'.$month.'/'.$year.' '.$time; die;
        $oddMonths = array(1, 3, 5, 7, 8, 10, 12);
        $evenMonths = array(2, 4, 6, 9, 11);
        if ($month == 2) {
            // $mDdays = 28;
            $mDdays = 29;
        } else {
            if (in_array($month, $oddMonths)) {
                $mDdays = 31;
            } elseif (in_array($month, $evenMonths)) {
                $mDdays = 30;
            }
        }
        if ($stateId == 13) {
            $mCount = App\Models\HolidaySettings::where('state_id', $stateId)->where('month_number', $month)->where('year', $year)->count('id');
        } else {
            $mCount = App\Models\HolidaySettings::where('state_id', $stateId)->where('month_number', $month)->where('year', date('Y'))->count('id');
        }
        // $mCount = App\Models\HolidaySettings::where('state_id',$stateId)->where('month_number',$month)->where('year', date('Y'))->count();
        if ($mCount > 0) {
            $sDate = $year . '-' . $month . '-' . $day . ' ' . $time;
            $cDate = $year . '-' . $month . '-' . $day;
            $dCount = App\Models\Event::where('state_id', $stateId)->where('start_date', $cDate)->count('id');
            if ($dCount == 0) {
                //return date("d/m/Y", strtotime($cDate));
                return $sDate;
            } else if ($day > 0) {
                for ($j = ($day); $j > 0; $j--) {
                    $sDate = $year . '-' . $month . '-' . $j . ' ' . $time;
                    $date = $year . '-' . $month . '-' . $j;
                    $dCount = App\Models\Event::where('state_id', $stateId)->where('start_date', $date)->count('id');
                    if ($dCount == 0) {
                        //return date("d/m/Y", strtotime($date));
                        return $sDate;
                    }
                    if ($j == 1) {
                        if ($month > 1) {
                            for ($i = ($month - 1); $i > 0; $i--) {
                                $pmCount = App\Models\HolidaySettings::where('state_id', $stateId)->where('month_number', $i)->where('year', date('Y'))->count('id');
                                if ($pmCount > 0) {
                                    if ($i == 2) {
                                        //$mDdays = 28;
                                        $mDdays = 29;

                                    } else {
                                        if (in_array($i, $oddMonths)) {
                                            $mDdays = 31;
                                        } elseif (in_array($i, $evenMonths)) {
                                            $mDdays = 30;
                                        }
                                    }
                                    for ($j = $mDdays; $j > 0; $j--) {
                                        $sDate = $year . '-' . $month . '-' . $j . ' ' . $time;
                                        $date = $year . '-' . $i . '-' . $j;
                                        $dCount = App\Models\Event::where('state_id', $stateId)->where('start_date', $date)->count('id');
                                        if ($dCount == 0) {
                                            //return date("d/m/Y", strtotime($date));
                                            return $sDate;
                                        }
                                    }
                                }
                            }
                        } else {
                            $preMonth = 12;
                            $preYear = date('Y') - 1;
                            for ($i = ($preMonth); $i > 0; $i--) {
                                $pmCount = App\Models\HolidaySettings::where('state_id', $stateId)->where('month_number', $i)->where('year', $preYear)->count('id');
                                if ($pmCount > 0) {
                                    if ($i == 2) {
                                        //$mDdays = 28;
                                        $mDdays = 29;
                                    } else {
                                        if (in_array($i, $oddMonths)) {
                                            $mDdays = 31;
                                        } elseif (in_array($i, $evenMonths)) {
                                            $mDdays = 30;
                                        }
                                    }
                                    for ($j = $mDdays; $j > 0; $j--) {
                                        $sDate = $preYear . '-' . $i . '-' . $j . ' ' . $time;
                                        $date = $preYear . '-' . $i . '-' . $j;
                                        $dCount = App\Models\Event::where('state_id', $stateId)->where('start_date', $date)->count('id');
                                        if ($dCount == 0) {
                                            //return date("d/m/Y h:i:s", strtotime($date));
                                            return $sDate;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                if ($month > 1) {
                    for ($i = ($month - 1); $i > 0; $i--) {
                        $pmCount = App\Models\HolidaySettings::where('state_id', $stateId)->where('month_number', $i)->where('year', date('Y'))->count('id');
                        if ($pmCount > 0) {
                            if ($i == 2) {
                                //$mDdays = 28;
                                $mDdays = 29;
                            } else {
                                if (in_array($i, $oddMonths)) {
                                    $mDdays = 31;
                                } elseif (in_array($i, $evenMonths)) {
                                    $mDdays = 30;
                                }
                            }
                            for ($j = $mDdays; $j > 0; $j--) {
                                $sDate = $year . '-' . $i . '-' . $j . ' ' . $time;
                                $date = $year . '-' . $i . '-' . $j;
                                $dCount = App\Models\Event::where('state_id', $stateId)->where('start_date', $date)->count('id');
                                if ($dCount == 0) {
                                    //return date("d/m/Y h:i:s", strtotime($date));
                                    return $sDate;
                                }
                            }
                        }
                    }
                } else {
                    $preMonth = 12;
                    $preYear = date('Y') - 1;
                    for ($i = ($preMonth); $i > 0; $i--) {
                        $pmCount = App\Models\HolidaySettings::where('state_id', $stateId)->where('month_number', $i)->where('year', $preYear)->count('id');
                        if ($pmCount > 0) {
                            if ($i == 2) {
                                //$mDdays = 28;
                                $mDdays = 29;
                            } else {
                                if (in_array($i, $oddMonths)) {
                                    $mDdays = 31;
                                } elseif (in_array($i, $evenMonths)) {
                                    $mDdays = 30;
                                }
                            }
                            for ($j = $mDdays; $j > 0; $j--) {
                                $sDate = $preYear . '-' . $i . '-' . $j . ' ' . $time;
                                $date = $preYear . '-' . $i . '-' . $j;
                                $dCount = App\Models\Event::where('state_id', $stateId)->where('start_date', $date)->count('id');
                                if ($dCount == 0) {
                                    //return date("d/m/Y h:i:s", strtotime($date));
                                    return $sDate;
                                }
                            }
                        }
                    }
                }
            }
        } else {
            if ($month > 1) {
                for ($i = ($month - 1); $i > 0; $i--) {
                    $pmCount = App\Models\HolidaySettings::where('state_id', $stateId)->where('month_number', $i)->where('year', date('Y'))->count('id');
                    if ($pmCount > 0) {
                        if ($i == 2) {
                            //$mDdays = 28;
                            $mDdays = 29;
                        } else {
                            if (in_array($i, $oddMonths)) {
                                $mDdays = 31;
                            } elseif (in_array($i, $evenMonths)) {
                                $mDdays = 30;
                            }
                        }
                        for ($j = $mDdays; $j > 0; $j--) {
                            $sDate = $year . '-' . $i . '-' . $j . ' ' . $time;
                            $date = $year . '-' . $i . '-' . $j;
                            $dCount = App\Models\Event::where('state_id', $stateId)->where('start_date', $date)->count('id');
                            if ($dCount == 0) {
                                //return date("d/m/Y h:i:s", strtotime($date));
                                return $sDate;
                            }
                        }
                    }
                }
            } else {
                $preMonth = 12;
                $preYear = date('Y') - 1;
                for ($i = ($preMonth); $i > 0; $i--) {
                    $pmCount = App\Models\HolidaySettings::where('state_id', $stateId)->where('month_number', $i)->where('year', $preYear)->count('id');
                    if ($pmCount > 0) {
                        if ($i == 2) {
                            // $mDdays = 28;
                            $mDdays = 29;
                        } else {
                            if (in_array($i, $oddMonths)) {
                                $mDdays = 31;
                            } elseif (in_array($i, $evenMonths)) {
                                $mDdays = 30;
                            }
                        }
                        for ($j = $mDdays; $j > 0; $j--) {
                            $sDate = $preYear . '-' . $i . '-' . $j . ' ' . $time;
                            $date = $preYear . '-' . $i . '-' . $j;
                            $dCount = App\Models\Event::where('state_id', $stateId)->where('start_date', $date)->count('id');
                            if ($dCount == 0) {
                                //return date("d/m/Y h:i:s", strtotime($date));
                                return $sDate;
                            }
                        }
                    }
                }
            }
        }
    }
}
/**
 * get monthly wise renewal .
 * @param   $investmentId,$renewAmount
 * @return   Response (return array)
 */
if (!function_exists('getMonthlyWiseRenewal1New')) {
    function getMonthlyWiseRenewal1New($investmentId, $renewAmount, $dateForRenew, $currentBalance, $dayBookId)
    {
        $resultArray = array();
        $renewMonth = 0;
        $invesmentData = investmentDepositeAmount($investmentId);
        $currentBalance = $currentBalance;
        $invesmentDepositeAmount = $invesmentData->deposite_amount;
        $currentMonth = date("m", strtotime($dateForRenew));
        $currentDay = date("d", strtotime($dateForRenew));
        $lastRenewalDate = Daybook::select('created_at')->where('investment_id', $investmentId)->where('id', '!=', $dayBookId)->orderBy('opening_balance', 'ASC')->limit(1)->first();
        $lastRenewalTime = strtotime($lastRenewalDate['created_at']);
        $getLastMonth = date("m", $lastRenewalTime);
        $monthDiff = $currentMonth - $getLastMonth;
        $renewAmountMonthsNumber = $renewAmount / $invesmentDepositeAmount;
        $getEmiMonth = $currentBalance / $invesmentDepositeAmount;
        $b = strtotime($invesmentData->created_at);
        $y = date("m", $b);
        $emiDay = date("d", $b);
        $emiMont = ($currentMonth - $y) + 1;
        //echo 'new';
        for ($i = 1; $i <= $renewAmountMonthsNumber; $i++) {
            $renewMonth = $getEmiMonth + $i;
            if ($invesmentData->plan_id == 7) {
                $j = $renewMonth + Date("t", strtotime($dateForRenew . " last month"));
                $month = ' + ' . $j . ' day';
                $createDateInvest = date('Y-m-d', strtotime($invesmentData->created_at . $month));
                // echo $createDateInvest;
                $j1 = $renewMonth;
                $month1 = ' + ' . $j1 . ' day';
                $createDateInvest1 = date('Y-m-d', strtotime($invesmentData->created_at . $month1));
                $emiDateInvest1 = strtotime($createDateInvest1);
                $emiDateInvest = strtotime($createDateInvest);
                $renewDateinvest = strtotime($dateForRenew);
                if ($renewDateinvest > $emiDateInvest) {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    //$resultArray[$i]['type'] = 1;
                    $resultArray[$i]['type'] = 2;
                } else if ($renewDateinvest == $emiDateInvest1) {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    $resultArray[$i]['type'] = 2;
                } else {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    if ($renewDateinvest >= $emiDateInvest1 && $renewDateinvest <= $emiDateInvest) {
                        $resultArray[$i]['type'] = 2;
                    } else {
                        //$resultArray[$i]['type'] = 3;
                        $resultArray[$i]['type'] = 2;
                    }
                }
            } else {
                $renewMonth1 = $renewMonth - 1;
                $month = ' + ' . $renewMonth . ' month';
                $month1 = ' + ' . $renewMonth1 . ' month';
                $createDateInvest = date('Y-m', strtotime($invesmentData->created_at . $month));
                $renewM = date("Y-m", strtotime($createDateInvest));
                $emiDateInvest = strtotime($renewM);
                $createDateInvest1 = date('Y-m', strtotime($invesmentData->created_at . $month1));
                $renewM1 = date("Y-m", strtotime($createDateInvest1));
                $emiDateInvest1 = strtotime($renewM1);
                $dateForRenewM = date("Y-m", strtotime($dateForRenew));
                $renewDateinvest = strtotime($dateForRenewM);
                if ($renewDateinvest > $emiDateInvest) {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    //$resultArray[$i]['type'] = 1;
                    $resultArray[$i]['type'] = 2;
                } elseif ($renewDateinvest == $emiDateInvest1) {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    $resultArray[$i]['type'] = 2;
                } else {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    $resultArray[$i]['type'] = 3;
                    if ($renewDateinvest >= $emiDateInvest1 && $renewDateinvest <= $emiDateInvest) {
                        $resultArray[$i]['type'] = 2;
                    } else {
                        //$resultArray[$i]['type'] = 3;
                        $resultArray[$i]['type'] = 2;
                    }
                }
            }
        }
        return $resultArray;
    }
}
/**
 * get all active relations.
 * @param
 * @return  array()  Response
 */
if (!function_exists('get_member_id_proof')) {
    function get_member_id_proof($member_id, $type)
    {
        $no = '';
        $doc = \App\Models\MemberIdProof::where('member_id', $member_id)->where(function ($query) use ($type) {
            $query->where('first_id_type_id', $type)
                ->orWhere('second_id_type_id', $type);
        })->first();
        if ($doc) {
            if ($doc->first_id_type_id == $type) {
                $no = $doc->first_id_no;
            }
            if ($doc->second_id_type_id == $type) {
                $no = $doc->second_id_no;
            }
        } else {
            $no = '';
        }
        return $no;
    }
}
/**
 * get associate commission .
 * @param   $member_id startdate,enddate
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getAssociateTotalCommissionCollection')) {
    function getAssociateTotalCommissionCollection($member_id, $startDate, $endDate, $type)
    {
        if ($startDate != '') {
            $total_amount = App\Models\AssociateCommission::where('member_id', $member_id)->where('type', '=', 5)->where('status', 1)->where('is_distribute', 0)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum($type);
        } else {
            $total_amount = App\Models\AssociateCommission::where('member_id', $member_id)->where('type', '=', 5)->where('status', 1)->where('is_distribute', 0)->sum($type);
        }
        return number_format($total_amount, 4, '.', '');
    }
}
/**
 * get associate commission .
 * @param   $member_id startdate,enddate
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getTotalCollection')) {
    function getTotalCollection($associate_id, $start_date, $end_date)
    {
        $startDateDb = date("Y-m-d", strtotime(convertDate($start_date)));
        $endDateDb = date("Y-m-d", strtotime(convertDate($end_date)));
        if ($start_date != '') {
            $total_collection = App\Models\Daybook::join('member_investments', 'member_investments.id', '=', 'day_books.investment_id')
                ->select(DB::raw('sum(day_books.deposit) as total'), DB::raw('day_books.associate_id as associate_id'))->where('day_books.is_eli', '!=', 1)->where('day_books.transaction_type', '=', 4)->where('day_books.associate_id', '=', $associate_id)->whereNotIn('member_investments.plan_id', [4, 9])->whereBetween(\DB::raw('DATE(day_books.created_at)'), [$startDateDb, $endDateDb])->groupBy(DB::raw('day_books.associate_id'))->get();
        } else {
            $total_collection = App\Models\Daybook::join('member_investments', 'member_investments.id', '=', 'day_books.investment_id')
                ->select(DB::raw('sum(day_books.deposit) as total'), DB::raw('day_books.associate_id as associate_id'))->where('day_books.is_eli', '!=', 1)->where('day_books.transaction_type', '=', 4)->where('day_books.associate_id', '=', $associate_id)->where('day_books.is_deleted', 0)->whereNotIn('member_investments.plan_id', [4, 9])->groupBy(DB::raw('day_books.associate_id'))->get();
        }
        //print_r($total_collection);die;
        if (count($total_collection) > 0) {
            return $total_collection[0]->total;
        } else {
            return 0;
        }
    }
}
/**
 * get accounthead facode .
 * @param   $member_id startdate,enddate
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getAccountHeadFacode')) {
    function getAccountHeadFacode($accounttype, $type)
    {
        $accountNumber = array();
        $accountHeadFacode = App\Models\AccountHeads::select('account_head_code')->Where('account_type', $accounttype)->orderBy('fa_code', 'desc')->first();
        if (!empty($accountHeadFacode)) {
            if ($type == 'update') {
                $accountHeadCode = str_pad($accountHeadFacode->account_head_code, 2, '0', STR_PAD_LEFT);
            } else {
                $accountHeadCode = str_pad($accountHeadFacode->account_head_code + 1, 2, '0', STR_PAD_LEFT);
            }
        } else {
            if ($accounttype == 0) {
                $accountHeadCode = '01';
            } elseif ($accounttype == 1) {
                $accountHeadCode = '71';
            } elseif ($accounttype == 3) {
                $accountHeadCode = '91';
            }
        }
        return $accountHeadCode;
    }
}
/**
 * get accounthead facode .
 * @param   $member_id startdate,enddate
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getSubAccountHeadFacode')) {
    function getSubAccountHeadFacode($accountHeadId, $accounttype, $type)
    {
        $accountNumber = array();
        $accountHeadFacode = App\Models\AccountHeads::select('account_head_code')->Where('id', $accountHeadId)->first();
        $subAccountHeadFacode = App\Models\SubAccountHeads::select('account_head_code', 'sub_account_head_code', 'fa_code')->Where('account_type', $accounttype)->Where('account_head_code', $accountHeadFacode->account_head_code)->orderBy('fa_code', 'desc')->first();
        if (!empty($subAccountHeadFacode)) {
            if ($type == 'update') {
                $subAccountHeadCode = str_pad($subAccountHeadFacode->sub_account_head_code, 3, '0', STR_PAD_LEFT);
            } else {
                $subAccountHeadCode = str_pad($subAccountHeadFacode->sub_account_head_code + 1, 3, '0', STR_PAD_LEFT);
            }
            $faCode = $accountHeadFacode->account_head_code . $subAccountHeadCode;
        } else {
            if ($accounttype == 0) {
                $subAccountHeadCode = '001';
            } elseif ($accounttype == 1) {
                $subAccountHeadCode = '701';
            } elseif ($accounttype == 3) {
                $subAccountHeadCode = '901';
            }
            $faCode = $accountHeadFacode->account_head_code . $subAccountHeadCode;
        }
        $accountNumber['account_head_code'] = $accountHeadFacode->account_head_code;
        $accountNumber['sub_account_head_code'] = $subAccountHeadCode;
        $accountNumber['fa_code'] = $faCode;
        $accountNumber['account_number'] = $faCode;
        return $accountNumber;
    }
}
/**
 * get  account head title.
 * @param   $member_id
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getAcountHead')) {
    function getAcountHead($ahId)
    {
        $data = App\Models\AccountHeads::where('head_id', $ahId)->first('sub_head');
        if (isset($data->sub_head)) {
            return $data->sub_head;
        } else {
            return "N/A";
        }
    }
}
if (!function_exists('getAcountHeadData')) {
    function getAcountHeadData($ahId)
    {
        $data = App\Models\AccountHeads::where('head_id', $ahId)->first('sub_head');
        return $data->sub_head;
    }
}
/**
 * get sub account head title.
 * @param   $member_id
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getSubAcountHead')) {
    function getSubAcountHead($sahId)
    {
        $data = App\Models\SubAccountHeads::where('id', $sahId)->first('sub_head');
        return $data->title;
    }
}
if (!function_exists('getSubAcountHeadName')) {
    function getSubAcountHeadName($sahId)
    {
        $data = App\Models\SubAccountHeads::where('id', $sahId)->first();
        return $data->title;
    }
}
/**
 * get all active bank account number list according to bank.
 * @param $bank_id
 * @return  array()  Response
 */
if (!function_exists('accountListBank')) {
    function accountListBank($bank_id)
    {
        $account = App\Models\SamraddhBankAccount::where([['bank_id', '=', $bank_id], ['status', '=', '1'],])->get(['id', 'account_no', 'ifsc_code', 'bank_id', 'branch_name']);
        return $account;
    }
}
if (!function_exists('getSamraddhBankAccount')) {
    function getSamraddhBankAccount($acc_number, $company_id = null)
    {
        $query = App\Models\SamraddhBankAccount::where('account_no', $acc_number);
        if (!empty($company_id)) {
            $query->where('company_id', $company_id);
        }
        $account = $query->first();
        return $account;
    }
}
if (!function_exists('getSamraddhBank')) {
    function getSamraddhBank($bank_id, $company_id = null)
    {
        $query = App\Models\SamraddhBank::where('id', $bank_id);
        if (!empty($company_id)) {
            $query->where('company_id', $company_id);
        }
        $account = $query->first();
        return $account;
    }
}
/**
 * get achived target member  .
 * @param
 * @return   Response (return start date or end date
 */
if (!function_exists('getBusinessTargetAchivedMemberNew')) {
    function getBusinessTargetAchivedMemberNew($arrFormData)
    {
        //print_r($arrFormData);die;
        $mid = '';
        $member = App\Models\Member::where('member_id', '!=', '9999999')->where('current_carder_id', '>', 0)->where('is_associate', 1);
        if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
            if ($arrFormData['sassociate_code'] != '') {
                $associate_code = $arrFormData['sassociate_code'];
                $member = $member->where('associate_senior_code', '=', $associate_code);
            }
            if ($arrFormData['branch_id'] != '') {
                $id = $arrFormData['branch_id'];
                $member = $member->where('associate_branch_id', '=', $id);
            }
            if ($arrFormData['associate_code'] != '') {
                $meid = $arrFormData['associate_code'];
                $member = $member->where('associate_no', '=', $meid);
            }
            if ($arrFormData['name'] != '') {
                $name = $arrFormData['name'];
                $member = $member->where(function ($query) use ($name) {
                    $query->where('first_name', 'LIKE', '%' . $name . '%')->orWhere('last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(first_name," ",last_name)'), 'LIKE', "%$name%");
                });
            }
            if ($arrFormData['start_date'] != '') {
                $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                if ($arrFormData['end_date'] != '') {
                    $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                } else {
                    $endDate = '';
                }
                $member = $member->whereBetween(\DB::raw('DATE(associate_join_date)'), [$startDate, $endDate]);
            }
        }
        $member = $member->get();
        $finacialYear = getFinacialYear();
        $startDate = $finacialYear['dateStart'];
        $endDate = $finacialYear['dateEnd'];
        foreach ($member as $val) {
            $currentCarder = $val->current_carder_id;
            $member_id = $val->id;
            $businessTarget = App\Models\BusinessTarget::where('carder_id', $currentCarder)->first();
            $achivedTargetStatus = 0;
            for ($i = 1; $i < $currentCarder; $i++) {
                $achevedMember = countAssociateByCarder($member_id, $i, $startDate, $endDate);
                $a = 'Carder-' . $i;
                $targetMember = $businessTarget->$a;
                if ($targetMember == $achevedMember) {
                    $achivedTargetStatus = $achivedTargetStatus + 1;
                } else {
                    $achivedTargetStatus = 0;
                }
            }
            $commissionSelf = App\Models\AssociateKotaBusiness::where('member_id', $member_id)->where('type', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount');
            if ($businessTarget->self == $commissionSelf) {
                $achivedTargetStatus = $achivedTargetStatus + 1;
            } else {
                $achivedTargetStatus = 0;
            }
            $commissionCredit = getKotaBusinessTeam($member_id, $startDate, $endDate);
            if ($businessTarget->credit == $commissionCredit) {
                $achivedTargetStatus = $achivedTargetStatus + 1;
            } else {
                $achivedTargetStatus = 0;
            }
            //  echo $val->id.'=='.$businessTarget->credit.'='.$commissionCredit.'<br>';
            if ($achivedTargetStatus == (($currentCarder - 1) + 2)) {
                $mid .= $val->id . ',';
            }
        }
        return $mid;
    }
}
/**
 * get eli amount.
 * @param $investment_id
 * @return  array()  Response
 */
if (!function_exists('investmentEliAmount')) {
    function investmentEliAmount($investment_id)
    {
        $eliAmount = Daybook::select('deposit')->where('investment_id', $investment_id)->where('is_eli', 1)->first();
        if ($eliAmount) {
            return $eliAmount->deposit;
        } else {
            return 'N/A';
        }
    }
}
if (!function_exists('investmentEliAmountNew')) {
    function investmentEliAmountNew($investment_id)
    {
        $eliAmount = Daybook::select('deposit')->where('investment_id', $investment_id)->where('is_eli', 1)->first(['id', 'deposit']);
        if ($eliAmount) {
            return $eliAmount->deposit;
        } else {
            return 'N/A';
        }
    }
}
/**
 * get Employee  mi code for member by id.
 * @param   $category
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getEmployeeMiCode')) {
    function getEmployeeMiCode($category)
    {
        $data = App\Models\Employee::where('mi_category', $category)->orderBy('mi_code', 'desc')->first('mi_code');
        return $data;
    }
}
/**
 * get Designation field data .
 * @param   $column(table column name),$id)
 * @return   Response (return column value)
 */
if (!function_exists('getDesignationData')) {
    function getDesignationData($column, $id)
    {
        $data = App\Models\Designation::whereId($id)->where('status', 1)->first($column);
        return $data;
    }
}
/**
 * get loan detail.
 * @return   Response (return column value)
 */
if (!function_exists('getLoanDetail')) {
    function getLoanDetail($id, $company_id = null)
    {
        $query = App\Models\Memberloans::whereId($id)->with(['loanMemberCustom', 'loanMemberCompany', 'member']);
        if (!empty($company_id)) {
            $query->where('company_id', $company_id);
        }
        $data = $query->first();
        return $data;
    }
}
/**
 * get loan detail.
 * @return   Response (return column value)
 */
if (!function_exists('getGroupLoanDetail')) {
    function getGroupLoanDetail($id, $company_id = null)
    {
        $query = App\Models\Grouploans::Where('member_loan_id', $id);
        if (!empty($company_id)) {
            $query->where('company_id', $company_id);
        }
        $data = $query->first();
        return $data;
    }
}
if (!function_exists('getGroupLoanDetailById')) {
    function getGroupLoanDetailById($id)
    {
        $data = App\Models\Grouploans::whereId($id)->with(['loanMemberCompany.member', 'loan', 'member'])->first();
        return $data;
    }
}
/**
 * get plan name .
 * @param   $planId
 * @return   Response
 */
if (!function_exists('getPlanDetail')) {
    function getPlanDetail($planId, $companyId = NULL)
    {
        $data = App\Models\Plans::withoutGlobalScope(ActiveScope::class)->Where('id', $planId)->where('status', 1)
            ->when($companyId != null, function ($q) use ($companyId) {
                $q->Where('company_id', $companyId);
            })->first();
        return $data;
    }
}
if (!function_exists('getPlanDetailCheck')) {
    function getPlanDetailCheck($planId, $companyId = NULL)
    {
        $data = App\Models\Plans::withoutGlobalScope(ActiveScope::class)->Where('id', $planId)
            ->when($companyId != null, function ($q) use ($companyId) {
                $q->Where('company_id', $companyId);
            })->first();
        return $data;
    }
}
if (!function_exists('getPlanDetailByCompany')) {
    function getPlanDetailByCompany($companyId)
    {
        $data = App\Models\Plans::withoutGlobalScope(ActiveScope::class)->Where('company_id', $companyId)->where('plan_category_code', 'S')->where('plan_sub_category_code', NULL)->where('status', 1)->value('deposit_head_id');
        return $data;
    }
}
/**
 * get branch detail .
 * @param   $planId
 * @return   Response
 */
if (!function_exists('getBranchDetail')) {
    function getBranchDetail($id)
    {
        /** this function is only use for admin / or to get branch details by auto id not 'Auth::user()->id' */
        $data = App\Models\Branch::whereId($id)->first();
        return $data;
    }
}
if (!function_exists('getCompanyDetail')) {
    function getCompanyDetail($id)
    {
        return \App\Models\Companies::find($id);
    }
}
/************ Report Section Start  ********************/
/**
 * get no of investment account by plan id.
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('investNewAcCount')) {
    function investNewAcCount($associate_id, $start, $end, $plan_id, $branch_id)
    {
        $data = App\Models\Memberinvestments::where('associate_id', $associate_id)->where('plan_id', $plan_id)->where('account_number', 'Not Like', "%" . 'R-' . "%");
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->count('id');
        return $count;
    }
}
/**
 * get no of investment account by plan id.
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('investNewDenoSum')) {
    function investNewDenoSum($associate_id, $start, $end, $plan_id, $branch_id)
    {
        $data = App\Models\Memberinvestments::where('associate_id', $associate_id)->where('plan_id', $plan_id);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $sum = $data->sum('deposite_amount');
        return $sum;
    }
}
/**
 * get account no  of investment rwnew  by plan id.
 * @param  $associate_id,$start,$end,$planIds,$branch_id
 * @return   Response
 */
if (!function_exists('investRenewAc')) {
    function investRenewAc($associate_id, $start, $end, $planIds, $branch_id)
    {
        $data = App\Models\Daybook::with([
            'investment' => function ($query) {
                $query->select('id', 'plan_id', 'account_number');
            }
        ])->whereHas('investment', function ($query) use ($planIds) {
            $query->whereIn('member_investments.plan_id', $planIds);
        })->where('is_eli', '!=', 1)->where('associate_id', $associate_id)->where('transaction_type', 4)->where('payment_type', '!=', 'DR');
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $data = $data->get();
        $c = count($data);
        return $c;
    }
}
/**
 * get account no  of investment rwnew  by plan id.
 * @param  $associate_id,$start,$end,$planIds,$branch_id
 * @return   Response
 */
if (!function_exists('investRenewAmountSum')) {
    function investRenewAmountSum($associate_id, $start, $end, $planIds, $branch_id)
    {
        $data = App\Models\Daybook::with([
            'investment' => function ($query) {
                $query->select('id', 'plan_id', 'account_number');
            }
        ])->whereHas('investment', function ($query) use ($planIds) {
            $query->whereIn('member_investments.plan_id', $planIds);
        })->where('is_eli', '!=', 1)->where('associate_id', $associate_id)->where('transaction_type', 4)->where('payment_type', '!=', 'DR');
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $data = $data->sum('deposit');
        return $data;
    }
}
/**
 * get no of investment account by plan id. Plan type-- monthly, fd
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('investNewAcCountType')) {
    function investNewAcCountType($associate_id, $start, $end, $planIds, $branch_id)
    {
        $data = App\Models\Memberinvestments::where('associate_id', $associate_id)->whereIn('plan_id', $planIds);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->count('id');
        return $count;
    }
}
/**
 *  get renew ammount sum  of investment  by plan id. Plan type-- monthly, fd
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('investNewDenoSumType')) {
    function investNewDenoSumType($associate_id, $start, $end, $planIds, $branch_id)
    {
        $data = App\Models\Memberinvestments::where('associate_id', $associate_id)->whereIn('plan_id', $planIds);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $sum = $data->sum('deposite_amount');
        return $sum;
    }
}
/**
 *  get renew ammount sum  of investment  by plan id. Plan type-- monthly, fd
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('memberCountByType')) {
    function memberCountByType($associate_id, $start, $end, $branch_id, $is_associate, $is_total)
    {
        if ($is_associate == 1) {
            $data = App\Models\Member::where('is_associate', 1)->where('associate_senior_id', $associate_id);
        } else {
            $data = App\Models\Member::where('associate_id', $associate_id);
        }
        if ($is_total == 0) {
            if ($start != '') {
                $startDate = $start;
                if ($end != '') {
                    $endDate = $end;
                } else {
                    $endDate = '';
                }
                if ($is_associate == 1) {
                    $data = $data->whereBetween(\DB::raw('DATE(associate_join_date)'), [$startDate, $endDate]);
                } else {
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }
            }
        } else {
            if ($start != '') {
                $startDate = $start;
                if ($end != '') {
                    $endDate = $end;
                } else {
                    $endDate = '';
                }
                $data = $data->whereDate('created_at', '<=', $endDate);
            }
        }
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        $data = $data->get();
        return count($data);
    }
}
/**
 * get account no  of investment rwnew  by plan id.
 * @param  $associate_id,$start,$end,$planIds,$branch_id
 * @return   Response
 */
if (!function_exists('investRenewAcPlan')) {
    function investRenewAcPlan($associate_id, $start, $end, $planId, $branch_id)
    {
        $data = App\Models\Daybook::with([
            'investment' => function ($query) {
                $query->select('id', 'plan_id', 'account_number');
            }
        ])->whereHas('investment', function ($query) use ($planId) {
            $query->where('member_investments.plan_id', $planId);
        })->where('is_eli', '!=', 1)->where('associate_id', $associate_id)->where('transaction_type', 4)->where('payment_type', '!=', 'DR');
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $data = $data->get();
        $c = count($data);
        return $c;
    }
}
/**
 * get account no  of investment rwnew  by plan id.
 * @param  $associate_id,$start,$end,$planIds,$branch_id
 * @return   Response
 */
if (!function_exists('investRenewAmountSumPlan')) {
    function investRenewAmountSumPlan($associate_id, $start, $end, $planId, $branch_id)
    {
        $data = App\Models\Daybook::with([
            'investment' => function ($query) {
                $query->select('id', 'plan_id', 'account_number');
            }
        ])->whereHas('investment', function ($query) use ($planId) {
            $query->where('member_investments.plan_id', $planId);
        })->where('is_eli', '!=', 1)->where('associate_id', $associate_id)->where('transaction_type', 4)->where('payment_type', '!=', 'DR');
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $data = $data->sum('deposit');
        return $data;
    }
}
/************ Report Section End ********************/
/**
 * get Designation field data .
 * @param   $column(table column name),$id)
 * @return   Response (return column value)
 */
if (!function_exists('getLastLoanEmiDate')) {
    function getLastLoanEmiDate($loan_id)
    {
        $data = App\Models\LoanDayBooks::where('loan_id', $loan_id)->where('is_deleted', 0)->orderBy('id', 'desc')->first('created_at');
        if ($data) {
            return date("d/m/Y", strtotime($data->created_at));
        } else {
            return 'N/A';
        }
    }
}
/**
 * get Designation field data .
 * @param   $column(table column name),$id)
 * @return   Response (return column value)
 */
if (!function_exists('getLastGropLoanEmiDate')) {
    function getLastGropLoanEmiDate($loan_id)
    {
        $data = App\Models\LoanDayBooks::where('group_loan_id', $loan_id)->where('is_deleted', 0)->orderBy('id', 'desc')->first('created_at');
        if ($data) {
            return date("d/m/Y", strtotime($data->created_at));
        } else {
            return 'N/A';
        }
    }
}
/**
 * get loan outsanding amount .
 * @param   $column(table column name),$id)
 * @return   Response (return column value)
 */
if (!function_exists('loanOutsandingAmount')) {
    function loanOutsandingAmount($loan_id, $accountnumber = null)
    {
        $outsandingAmount = 0;
        $query = App\Models\LoanDayBooks::where('loan_id', $loan_id)->where('is_deleted', 0)->where('loan_sub_type', '!=', 2);
        if (!empty($accountnumber)) {
            $query->where('account_number', $accountnumber);
        }
        $records = $query->get();
        foreach ($records as $key => $value) {
            $outsandingAmount = $outsandingAmount + $value->deposit;
        }
        return $outsandingAmount;
    }
}

/**New loan outstanding created by shahid */
if (!function_exists('loanOutsandingAmountNew')) {
    function loanOutsandingAmountNew($loan_id, $accountnumber = null)
    {
        $outsandingAmount = 0;
        $query = App\Models\LoanDayBooks::where('loan_id', $loan_id)->where('is_deleted', 0)->where('loan_sub_type', '!=', 2);
        if (!empty($accountnumber)) {
            $query->where('account_number', $accountnumber);
        }
        $records = $query->get();
        foreach ($records as $key => $value) {
            $outsandingAmount += $value->deposit;
        }
        return $outsandingAmount;
    }
}
/**
 * get group loan outsanding amount .
 * @param   $column(table column name),$id)
 * @return   Response (return column value)
 */
if (!function_exists('loanGroupOutsandingAmount')) {
    function loanGroupOutsandingAmount($loan_id, $accountnumber = null)
    {
        $outsandingAmount = 0;
        $records = App\Models\LoanDayBooks::where('group_loan_id', $loan_id)->where('is_deleted', 0)->get();
        foreach ($records as $key => $value) {
            $outsandingAmount = $outsandingAmount + $value->deposit;
        }
        return $outsandingAmount;
    }
}
/**
 * get file charge payment mode .
 * @param   $column(table column name),$id)
 * @return   Response (return column value)
 */
if (!function_exists('fileChargePaymentMode')) {
    function fileChargePaymentMode($loan_id, $loan_type)
    {
        $records = App\Models\Daybook::where('loan_id', $loan_id)->where('transaction_type', $loan_type)->first();
        if ($records) {
            if ($records->payment_mode == 0) {
                return $file_charges_payment_mode = 'Cash';
            } elseif ($records->payment_mode == 5) {
                return $file_charges_payment_mode = 'Loan Amount';
            } else {
                return $file_charges_payment_mode = 'N/A';
            }
        } else {
            return 'N/A';
        }
    }
}
/**
 * get last recovered loan amount .
 * @param   $column(table column name),$id)
 * @return   Response (return column value)
 */
if (!function_exists('lastLoanRecoveredAmount')) {
    function lastLoanRecoveredAmount($loan_id, $filed)
    {
        $records = App\Models\LoanDayBooks::where('' . $filed . '', $loan_id)->where('is_deleted', 0)->orderBy('id', 'desc')->first();
        ;
        if ($records) {
            return $records->deposit;
        } else {
            return 0;
        }
    }
}
/**
 * get group loan common id .
 * @return   Response (return column value)
 */
if (!function_exists('groupLoanCommonId')) {
    function groupLoanCommonId($branchCode)
    {
        $records = App\Models\Memberloans::select('group_loan_common_id')->where('group_loan_common_id', 'like', '%' . $branchCode . '%')->orderBy('id', 'desc')->first();
        ;
        if ($records) {
            $id = $records->group_loan_common_id + 1;
            return str_pad($id, 11, '0', STR_PAD_LEFT);
        } else {
            $id = $branchCode . '0000001';
            return str_pad($id, 11, '0', STR_PAD_LEFT);
        }
    }
}
/**************************Account Head ***************/
if (!function_exists('getHead')) {
    function getHead($head_id, $lable)
    {
        $data = App\Models\AccountHeads::where('status', '!=', 9)->where('parent_id', $head_id)->where('labels', $lable)->orderBy('head_id', 'ASC')->get();
        return $data;
    }
}
if (!function_exists('headTotal')) {
    function headTotal($head_id, $field)
    {
        $getDr = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('payment_type', 'DR')->sum('amount');
        $getCr = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('payment_type', 'CR')->sum('amount');
        $total = $getCr - $getDr;
        return $total;
    }
}
if (!function_exists('headTotalData')) {
    function headTotalData($head_id, $field, $startDate, $endDate)
    {
        $getDr = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('payment_type', 'DR');
        $getCr = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('payment_type', 'CR');
        if ($startDate != '' && $endDate == "") {
            $getDr = $getDr->whereDate('entry_date', '<=', date("Y-m-d", strtotime(convertDate($startDate))));
            $getCr = $getCr->whereDate('entry_date', '<=', date("Y-m-d", strtotime(convertDate($startDate))));
        }
        if ($startDate != '' && $endDate != "") {
            $startDate = date("Y-m-d", strtotime(convertDate($startDate)));
            $endDate = date("Y-m-d", strtotime(convertDate($endDate)));
            $getDr = $getDr->whereBetween('entry_date', [$startDate, $endDate]);
            $getCr = $getCr->whereBetween('entry_date', [$startDate, $endDate]);
        }
        $getCr = $getCr->sum('amount');
        $getDr = $getDr->sum('amount');
        $total = $getCr - $getDr;
        return $total;
    }
}
if (!function_exists('headTotalFilter')) {
    function headTotalFilter($head_id, $field, $endDate, $branch_id)
    {
        // dd($head_id,$field,$endDate,$branch_id);
        $getDr = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('payment_type', 'DR');
        $getCr = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('payment_type', 'CR');
        if ($endDate != '') {
            $getDr = $getDr->whereDate('entry_date', '<=', date("Y-m-d", strtotime(convertDate($endDate))));
            $getCr = $getCr->whereDate('entry_date', '<=', date("Y-m-d", strtotime(convertDate($endDate))));
        }
        if ($branch_id != '') {
            $getDr = $getDr->where('branch_id', $branch_id);
            $getCr = $getCr->where('branch_id', $branch_id);
        }
        if (!is_null(Auth::user()->branch_ids)) {
            $branch_ids = Auth::user()->branch_ids;
            $getDr = $getDr->whereIn('branch_id', explode(",", $branch_ids));
            $getCr = $getCr->whereIn('branch_id', explode(",", $branch_ids));
        }
        $getDr = $getDr->sum('amount');
        $getCr = $getCr->sum('amount');
        $total = $getCr - $getDr;
        return $total;
    }
}
if (!function_exists('profitLoassTotalFilter')) {
    function profitLoassTotalFilter($head_id, $field, $endDate)
    {
        $getCr = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('payment_type', 'CR');
        if ($endDate != '') {
            $getCr = $getCr->whereDate('entry_date', '<=', $endDate);
        }
        $getCr = $getCr->sum('amount');
        $total = $getCr;
        return $total;
    }
}
if (!function_exists('getCountBranchAccounts')) {
    function getCountBranchAccounts($branch_id)
    {
        $getCount = App\Models\Member::where('branch_id', $branch_id)->count();
        return $getCount;
    }
}
if (!function_exists('branchHeadTotal')) {
    function branchHeadTotal($head_id, $branch_id, $field)
    {
        $getCr = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('payment_type', 'CR')->where('branch_id', $branch_id)->sum('amount');
        $total = $getCr;
        return $total;
    }
}
if (!function_exists('getSamraddhChequeData')) {
    function getSamraddhChequeData($cheque_id)
    {
        $getRecord = App\Models\SamraddhCheque::where('cheque_no', $cheque_id)->first();
        return $getRecord;
    }
}
if (!function_exists('getBankLedgerData')) {
    function getBankLegderOpeningBalance($start_date, $bank_id, $date)
    {
        $getRecord = '';
        if ($start_date == $date) {
            $getRecord = App\Models\SamraddhBankClosing::whereDate('entry_date', $date)->where('bank_id', $bank_id)->first();
        } else if ($start_date == ' ') {
            $getRecord = App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->orderBy('id', 'DESC')->first();
        } else {
            $getRecord = App\Models\SamraddhBankClosing::whereDate('entry_date', $start_date)->where('bank_id', $bank_id)->orderBy('id', 'DESC')->first();
        }
        return $getRecord;
    }
}
if (!function_exists('getBankLegderClosingBalance')) {
    function getBankLegderClosingBalance($end_date, $bank_id, $date)
    {
        $getRecord = '';
        if ($end_date == $date) {
            $getRecord = App\Models\SamraddhBankClosing::whereDate('entry_date', $date)->where('bank_id', $bank_id)->first();
            $getRecord = $getRecord->balance;
        } else if ($end_date == '') {
            $getRecord = App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->orderBy('id', 'DESC')->first();
            $getRecord = $getRecord->closing_balance;
        } else {
            $getRecord = App\Models\SamraddhBankClosing::whereDate('entry_date', $end_date)->where('bank_id', $bank_id)->orderBy('id', 'DESC')->first();
            // dd($getRecord);
            $getRecord = $getRecord->closing_balance;
        }
        return $getRecord;
    }
}
if (!function_exists('getEmployeeData')) {
    function getEmployeeData($emp_id)
    {
        $getEmployee = App\Models\Employee::where('id', $emp_id)->first();
        return $getEmployee;
    }
}
if (!function_exists('getInvestmentMaturity')) {
    function getInvestmentMaturity($investment_id)
    {
        $monthly = array(2, 3, 5, 6, 10, 11);
        $investmentData = App\Models\Memberinvestments::select('plan_id', 'deposite_amount', 'current_balance', 'interest_rate', 'maturity_amount', 'tenure', 'created_at', 'maturity_date')->Where('id', $investment_id)->first();
        $finalAmount = 0;
        if (in_array($investmentData->plan_id, $monthly)) {
            $createdDate = date_create(date("Y-m-d", strtotime(convertDate($investmentData->created_at))));
            $maturityDate = date_create($investmentData->maturity_date);
            $diff = date_diff($createdDate, $maturityDate);
            $mDiff = ($diff->y * 12 + $diff->m) - 3;
            if ($investmentData->plan_id == 2) {
                $principal = $investmentData->deposite_amount;
                if ($investmentData->tenure >= 8 && $investmentData->tenure <= 18) {
                    $rate = 11;
                } else if ($investmentData->tenure >= 6 && $investmentData->tenure <= 7) {
                    $rate = 10.50;
                } else if ($investmentData->tenure < 6) {
                    $rate = 10;
                }
                $ci = 1;
                $time = $investmentData->tenure * 12;
                $irate = $rate / $ci;
                $year = $time / 12;
                $result = ($principal * (pow((1 + $irate / 100), $year * $ci) - 1) / (1 - pow((1 + $irate / 100), -$ci / 12))) . number_format(2);
                if ($investmentData->current_balance < $mDiff * $investmentData->deposite_amount) {
                    $defaulterAmount = 1.50 * $result / 100;
                    $finalAmount = round($result - $defaulterAmount);
                } else {
                    $finalAmount = round($result);
                }
            } elseif ($investmentData->plan_id == 10) {
                $principal = $investmentData->deposite_amount;
                $rate = $investmentData->interest_rate;
                $ci = 1;
                $time = $investmentData->tenure * 12;
                $irate = $rate / $ci;
                $year = $time / 12;
                $result = ($principal * (pow((1 + $irate / 100), $year * $ci) - 1) / (1 - pow((1 + $irate / 100), -$ci / 12))) . number_format(2);
                if ($investmentData->current_balance < $mDiff * $investmentData->deposite_amount) {
                    $defaulterAmount = 1.50 * $result / 100;
                    $finalAmount = round($result - $defaulterAmount);
                } else {
                    $finalAmount = round($result);
                }
            }
        }
        return $finalAmount;
    }
}
/**************************Account Head ***************/
if (!function_exists('getFixedAsset')) {
    function getFixedAsset($id, $companyLists)
    {
        $getRecord = App\Models\AccountHeads::getCompanyRecords("CompanyId", $companyLists)->where('parent_id', $id)->get();
        return $getRecord;
    }
}
if (!function_exists('getsubChildFixedAsset')) {
    function getsubChildFixedAsset($id, $companyLists)
    {
        $getRecord = App\Models\AccountHeads::getCompanyRecords("CompanyId", $companyLists)->where('parent_id', $id)->where('status', 0)->get();
        return $getRecord;
    }
}
if (!function_exists('getsubChildsubAssetFixedAsset')) {
    function getsubChildsubAssetFixedAsset($id, $companyLists)
    {
        $getRecord = App\Models\AccountHeads::getCompanyRecords("CompanyId", $companyLists)->where('parent_id', $id)->where('status', 0)->get();
        return $getRecord;
    }
}
if (!function_exists('getheadlabel5')) {
    function getheadlabel5($id, $companyLists)
    {
        $getRecord = App\Models\AccountHeads::getCompanyRecords("CompanyId", $companyLists)->where('parent_id', $id)->get();
        return $getRecord;
    }
}
if (!function_exists('getAccountHeadParentid')) {
    function getAccountHeadParentid($id)
    {
        $getRecord = App\Models\AccountHeads::whereId($id)->first('parent_id');
        return $getRecord;
    }
}
/**************************Account Head ***************/
if (!function_exists('getSamraddhBankAccountId')) {
    function getSamraddhBankAccountId($id, $company_id = null)
    {
        $query = App\Models\SamraddhBankAccount::whereId($id);
        if (!empty($company_id)) {
            $query->where('company_id', $company_id);
        }
        $account = $query->first();
        return $account;
    }
}
if (!function_exists('getSamraddhBankAccountIdNew')) {
    function getSamraddhBankAccountIdNew($id, $company_id = null)
    {
        // this helper is modifyed by sourab on 11-03-2024 as per feedback by bank leger report feedback.
        $query = App\Models\SamraddhBankAccount::where('bank_id', $id);
        if (!empty($company_id)) {
            $query->where('company_id', $company_id);
        }
        $account = $query->first();
        return $account;
    }
}
if (!function_exists('getAcountHeadNameHeadId')) {
    function getAcountHeadNameHeadId($head_id)
    {
        $data = App\Models\AccountHeads::where('head_id', $head_id)->first('sub_head');
        if (isset($data->sub_head)) {
            return $data->sub_head;
        } else {
            return 'N/A';
        }
    }
}
if (!function_exists('getaccountHead_id')) {
    function getaccountHead_id($head_id)
    {
        $data = App\Models\AccountHeads::where('head_id', $head_id)->first('head_id');
        return $data->head_id;
    }
}
// 16-05 Aman BranchBusiness
if (!function_exists('branchBusinessInvestNewAcCount')) {
    function branchBusinessInvestNewAcCount($start, $end, $branch_id, $plan_id, $company_id = null)
    {
        //dd($start,$end,$branch_id,$plan_id);
        $data = App\Models\Memberinvestments::where('plan_id', $plan_id)->where('account_number', 'Not Like', "%" . 'R-' . "%");
        //$data=App\Models\Memberinvestments::where('plan_id',$plan_id);
        /*$data=App\Models\Daybook::with(['investment'])->whereHas('investment', function ($query) use ($plan_id) {
        $query->where('member_investments.plan_id',$plan_id);
        })->where('is_eli', '!=',1)->where('transaction_type',4);*/
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($company_id != '') {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
/**
 * get no of investment account by plan id.
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('branchBusinessInvestNewDenoSum')) {
    function branchBusinessInvestNewDenoSum($start, $end, $branch_id, $plan_id, $company_id = null)
    {
        //$data=App\Models\Memberinvestments::where('plan_id',$plan_id);
        /*$data=App\Models\Daybook::with(['investment' => function($query){ $query->select('id', 'plan_id','account_number');}])->whereHas('investment', function ($query) use ($plan_id) {
        $query->where('member_investments.plan_id',$plan_id);
        })->where('is_eli', '!=',1)->where('transaction_type',4)->where('payment_type','!=','DR');*/
        $data = App\Models\Memberinvestments::where('plan_id', $plan_id)->where('account_number', 'not like', '%R-%');
        if ($company_id != '') {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $sum = $data->sum('deposite_amount');
        return $sum;
    }
}
/**
 * get account no  of investment rwnew  by plan id.
 * @param  $associate_id,$start,$end,$planIds,$branch_id
 * @return   Response
 */
if (!function_exists('branchBusinessInvestRenewAc')) {
    function branchBusinessInvestRenewAc($start, $end, $planIds, $branch_id, $company_id = null)
    {
        $data = App\Models\Daybook::with([
            'investment' => function ($query) {
                $query->select('id', 'plan_id', 'account_number');
            }
        ])->whereHas('investment', function ($query) use ($planIds) {
            $query->whereIn('member_investments.plan_id', $planIds);
        })->where('is_eli', '!=', 1)->where('transaction_type', 4)->where('payment_type', '!=', 'DR');
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($company_id != '') {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $data = $data->get();
        $c = count($data);
        return $c;
    }
}
if (!function_exists('branchBusinessTotalCaseCollectionCount')) {
    function branchBusinessTotalCaseCollectionCount($start, $end, $branch_id, $planIds)
    {
        $data = App\Models\Daybook::with([
            'investment' => function ($query) {
                $query->select('id', 'plan_id', 'account_number');
            }
        ])->whereHas('investment', function ($query) use ($planIds) {
            $query->whereIn('member_investments.plan_id', $planIds);
        })->where('is_eli', '!=', 1)->where('payment_type', '!=', 'DR')->where('payment_mode', '0');
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $data = $data->get();
        $c = count($data);
        return $c;
    }
}
if (!function_exists('branchBusinessTotalCaseCollectionAmountSum')) {
    function branchBusinessTotalCaseCollectionAmountSum($start, $end, $branch_id, $planIds)
    {
        $data = App\Models\Daybook::with([
            'investment' => function ($query) {
                $query->select('id', 'plan_id', 'account_number');
            }
        ])->whereHas('investment', function ($query) use ($planIds) {
            $query->whereIn('member_investments.plan_id', $planIds);
        })->where('is_eli', '!=', 1)->where('payment_type', '!=', 'DR')->where('payment_mode', '0');
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $data = number_format((float) $data->sum('deposit'), 2, '.', '');
        return $data;
    }
}
/**
 * get account no  of investment rwnew  by plan id.
 * @param  $associate_id,$start,$end,$planIds,$branch_id
 * @return   Response
 */
if (!function_exists('branchBusinessInvestRenewAmountSum')) {
    function branchBusinessInvestRenewAmountSum($start, $end, $planIds, $branch_id, $company_id = null)
    {
        $data = App\Models\Daybook::with([
            'investment' => function ($query) {
                $query->select('id', 'plan_id', 'account_number');
            }
        ])->whereHas('investment', function ($query) use ($planIds) {
            $query->whereIn('member_investments.plan_id', $planIds);
        })->where('is_eli', '!=', 1)->where('transaction_type', 4)->where('payment_type', '!=', 'DR');
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($company_id != '') {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $data = number_format((float) $data->sum('deposit'), 2, '.', '');
        return $data;
    }
}
/**
 * get no of investment account by plan id. Plan type-- monthly, fd
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('branchBusinessInvestNewAcCountType')) {
    function branchBusinessInvestNewAcCountType($start, $end, $planIds, $branch_id, $company_id = null)
    {
        $data = App\Models\Memberinvestments::whereIn('plan_id', $planIds)->where('account_number', 'Not Like', "%" . 'R-' . "%");
        ;
        //$data=App\Models\Memberinvestments::whereIn('plan_id',$planIds);
        /*$data=App\Models\Daybook::with(['investment' => function($query){ $query->select('id', 'plan_id','account_number');}])->whereHas('investment', function ($query) use ($planIds) {
        $query->whereIn('member_investments.plan_id',$planIds);
        })->where('is_eli', '!=',1)->where('transaction_type',4)->where('payment_type','!=','DR');*/
        if ($company_id != '') {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
/**
 *  get renew ammount sum  of investment  by plan id. Plan type-- monthly, fd
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('branchBusinessInvestNewDenoSumType')) {
    function branchBusinessInvestNewDenoSumType($start, $end, $planIds, $branch_id, $company_id = null)
    {
        //$data=App\Models\Memberinvestments::whereIn('plan_id',$planIds);
        /*$data=App\Models\Daybook::with(['investment' => function($query){ $query->select('id', 'plan_id','account_number');}])->whereHas('investment', function ($query) use ($planIds) {
        $query->whereIn('member_investments.plan_id',$planIds);
        })->where('is_eli', '!=',1)->where('transaction_type',4)->where('payment_type','!=','DR');*/
        $data = App\Models\Memberinvestments::whereIn('plan_id', $planIds)->where('account_number', 'Not Like', "%" . 'R-' . "%");
        ;
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($company_id != '') {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $sum = $data->sum('deposite_amount');
        return $sum;
    }
}
/**
 *  get renew ammount sum  of investment  by plan id. Plan type-- monthly, fd
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('branchBusinessTenureInvestNewAcCount')) {
    function branchBusinessTenureInvestNewAcCount($start, $end, $branch_id, $plan_id, $tenure, $company_id = null)
    {
        $tenure = ((float) $tenure / 12);
        $data = App\Models\Memberinvestments::where('tenure', '=', $tenure)->where('plan_id', $plan_id);
        if (!empty($company_id)) {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
/**
 *  get renew ammount sum  of investment  by plan id. Plan type-- monthly, fd
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('branchBusinessInvestTenureNewAcCountType')) {
    function branchBusinessInvestTenureNewAcCountType($start, $end, $branch_id, $plan_id, $tenure, $company_id = null)
    {
        $tenure = ((float) $tenure / 12);
        //dd($start,$end,$branch_id,$plan_id,$tenure);
        //$data=App\Models\Memberinvestments::where('tenure',$tenure/12)->whereIn('plan_id',$plan_id);
        $data = App\Models\Memberinvestments::where('tenure', '=', $tenure)->where('plan_id', $plan_id)->where('account_number', 'Not Like', "%" . 'R-' . "%");
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if (!empty($company_id)) {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
/**
 *  get renew ammount sum  of investment  by plan id. Plan type-- monthly, fd
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('branchBusinessInvestTenureNewDenoSumType')) {
    function branchBusinessInvestTenureNewDenoSumType($start, $end, $branch_id, $planIds, $tenure, $company_id = null)
    {
        //$data=App\Models\Memberinvestments::where('tenure',($tenure/12))->whereIn('plan_id',$planIds);
        $tenure = ((float) $tenure / 12);
        $data = App\Models\Memberinvestments::where('tenure', '=', $tenure)->where('plan_id', $planIds)->where('account_number', 'Not Like', "%" . 'R-' . "%");
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if (!empty($company_id)) {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $sum = $data->sum('deposite_amount');
        return $sum;
    }
}
/**
 *  get renew ammount sum  of investment  by plan id. Plan type-- monthly, fd
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('branchBusinessTenureInvestNewDenoSum')) {
    function branchBusinessTenureInvestNewDenoSum($start, $end, $branch_id, $plan_id, $tenure, $company_id = null)
    {
        $tenure = ((float) $tenure / 12);
        $data = App\Models\Memberinvestments::where('tenure', '=', $tenure)->where('plan_id', $plan_id)->where('account_number', 'Not Like', "%" . 'R-' . "%");
        //$data=App\Models\Memberinvestments::where('tenure',$tenure/12)->where('plan_id',$plan_id);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if (!empty($company_id)) {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $sum = $data->sum('deposite_amount');
        return $sum;
    }
}
/**
 *  get daily mature total number of account
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('totalmatureAccount')) {
    function totalmatureAccount($start, $end, $branch_id, $plan_id, $tenure, $company_id = null)
    {
        $tenure = ((float) $tenure / 12);
        $data = App\Models\Memberinvestments::where('tenure', '=', $tenure)->where('plan_id', $plan_id)->where('is_mature', 0);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if (!empty($company_id)) {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(investment_interest_date)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
/**
 *  get daily mature total amnt of account
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('totalmatureAmount')) {
    function totalmatureAmount($start, $end, $branch_id, $plan_id, $tenure, $company_id = null)
    {
        $tenure = ($tenure / 12);
        $data = App\Models\Memberinvestments::where('tenure', '=', $tenure)->where('plan_id', $plan_id)->where('is_mature', 0);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if (!empty($company_id)) {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(investment_interest_date)'), [$startDate, $endDate]);
        }
        $count = $data->sum('maturity_payable_amount');
        return $count;
    }
}
/**
 *  getmonthly mature account tenure wise. Plan type-- monthly, fd
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('matureInvestTenureNewAcCountType')) {
    function matureInvestTenureNewAcCountType($start, $end, $branch_id, $planIds, $tenure, $company_id = null)
    {
        $data = App\Models\DemandAdvice::with('investment');
        $data = $data->whereHas('investment', function ($query) use ($planIds) {
            $query->where('member_investments.plan_id', $planIds);
        });
        $data = $data->whereHas('investment', function ($query) use ($tenure) {
            $query->where('member_investments.tenure', $tenure / 12);
        });
        $data = $data->whereHas('investment', function ($query) use ($tenure) {
            $query->where('member_investments.is_mature', 0);
        });
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if (!empty($company_id)) {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(date)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
/**
 *  get renew ammount sum  of investment  by plan id. Plan type-- monthly, fd
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('matureInvestTenureNewDenoSumType')) {
    function matureInvestTenureNewDenoSumType($start, $end, $branch_id, $planIds, $tenure, $company_id = null)
    {
        $data = App\Models\DemandAdvice::with('investment');
        $data = $data->whereHas('investment', function ($query) use ($planIds) {
            $query->where('member_investments.plan_id', $planIds);
        });
        $data = $data->whereHas('investment', function ($query) use ($tenure) {
            $query->where('member_investments.tenure', $tenure / 12);
        });
        $data = $data->whereHas('investment', function ($query) use ($tenure) {
            $query->where('member_investments.is_mature', 0);
        });
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if (!empty($company_id)) {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(date)'), [$startDate, $endDate]);
        }
        $sum = number_format((float) $data->sum('final_amount'), 2, '.', '');
        return $sum;
    }
}
/**
 *  get  ammount sum  of expense
 * @param  $associate_id,$start,$end,$branch_id,$label,$headId
 * @return   Response
 */
if (!function_exists('getExpenseHeadAmount')) {
    function getExpenseHeadAmount($head_id, $label, $start, $end, $branch_id)
    {
        $head_ids = array($head_id);
        // Now get child of that head
        $records = App\Models\AccountHeads::where('head_id', $head_id)->where('status', 0)->first();
        $subHeadsIDS = App\Models\AccountHeads::where('head_id', $head_id)->where('status', 0)->pluck('head_id')->toArray();
        if (count($subHeadsIDS) > 0) {
            $head_id = array_merge($head_ids, $subHeadsIDS);
            $return_array = get_change_sub_account_head($head_ids, $subHeadsIDS, true);
        }
        foreach ($return_array as $key => $value) {
            $ids[] = $value;
        }
        $data = App\Models\AllHeadTransaction::whereIn('head_id', $ids);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate]);
        }
        $TotalCrAmnt = $data->where('payment_type', "CR")->sum('amount');
        $TotalDrAmnt = $data->where('payment_type', "DR")->sum('amount');
        //dd($TotalCrAmnt,$TotalDrAmnt);
        $total = $TotalCrAmnt - $TotalDrAmnt;
        return $total;
    }
}
if (!function_exists('getInvestmentStationarychrgAccount')) {
    function getInvestmentStationarychrgAccount($start, $end, $branch_id, $company_id = null)
    {
        $data = App\Models\BranchDaybook::where('type', 3)->where('sub_type', 35);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($company_id != '') {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate]);
        }
        // $TotalCrAmnt=$data->where('payment_type',"CR")->sum('amount');
        // $TotalDrAmnt=$data->where('payment_type',"DR")->sum('amount');
        //dd($TotalCrAmnt,$TotalDrAmnt);
        $total = $data->count();
        return $total;
    }
}
if (!function_exists('getInvestmentStationarychrgAmount')) {
    function getInvestmentStationarychrgAmount($start, $end, $branch_id, $company_id = null)
    {
        $data = App\Models\BranchDaybook::where('type', 3)->where('sub_type', 35);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($company_id != '') {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate]);
        }
        // $TotalCrAmnt=$data->where('payment_type',"CR")->sum('amount');
        // $TotalDrAmnt=$data->where('payment_type',"DR")->sum('amount');
        //dd($TotalCrAmnt,$TotalDrAmnt);
        $total = $data->sum('amount');
        return $total;
    }
}
if (!function_exists('getExpenseHeadaccountCount')) {
    function getExpenseHeadaccountCount($id, $label, $start, $end, $branch_id)
    {
        $head_ids = array($id);
        // Now get child of that head
        $records = App\Models\AccountHeads::where('head_id', $id)->where('status', 0)->first();
        $subHeadsIDS = App\Models\AccountHeads::where('head_id', $id)->where('status', 0)->pluck('head_id')->toArray();
        if (count($subHeadsIDS) > 0) {
            $head_id = array_merge($head_ids, $subHeadsIDS);
            $return_array = get_change_sub_account_head($head_ids, $subHeadsIDS, true);
        }
        foreach ($return_array as $key => $value) {
            $ids[] = $value;
        }
        $data = App\Models\AllHeadTransaction::whereIn('head_id', $ids);
        //     if($id == 4)
        // {
        //   dd($data);
        // }
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        // $TotalCrAmnt=$data->where('payment_type',"CR")->sum('amount');
        // $TotalDrAmnt=$data->where('payment_type',"DR")->sum('amount');
        //dd($TotalCrAmnt,$TotalDrAmnt);
        $total = $data->count();
        return $total;
    }
}
/**
 *  get total kanyadhan account having tenure not equal to (1,3,5,7,10) investment  by plan id. Plan type-- monthly, fd
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
// 17-06 Aman
if (!function_exists('branchBusinessInvestTenureKanyadhan')) {
    function branchBusinessInvestTenureKanyadhan($start, $end, $branch_id, $plan_id, $tenure, $company_id = null)
    {
        $data = App\Models\Memberinvestments::whereNotIn('tenure', $tenure)->whereIn('plan_id', $plan_id);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if (!empty($company_id)) {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
/**
 *  get kanyadhan ammount sum  having tenure not equal to (1,3,5,7,10)  of investment  by plan id. Plan type-- monthly, fd
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('branchBusinessInvestKanyadhanTenureNewDenoSumType')) {
    function branchBusinessInvestKanyadhanTenureNewDenoSumType($start, $end, $branch_id, $plan_id, $tenure, $company_id = null)
    {
        $data = App\Models\Memberinvestments::whereNotIn('tenure', $tenure)->whereIn('plan_id', $plan_id);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if (!empty($company_id)) {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $sum = $data->sum('deposite_amount');
        return $sum;
    }
}
if (!function_exists('adminBusinessInvestNewAcCount')) {
    function adminBusinessInvestNewAcCount($start, $end, $branch_id, $plan_id, $zone, $region, $sector)
    {
        $data = App\Models\Memberinvestments::with('branch')->where('plan_id', $plan_id);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($zone != '') {
            $data = $data->whereHas('branch', function ($query) use ($zone) {
                $query->where('branch.zone', $zone);
            });
        }
        if ($region != '') {
            $data = $data->whereHas('branch', function ($query) use ($region) {
                $query->where('branch.regan', $region);
            });
        }
        if ($sector != '') {
            $data = $data->whereHas('branch', function ($query) use ($sector) {
                $query->where('branch.sector', $sector);
            });
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $data = $data->select('branch_id')->groupBy('branch_id');
        return $data;
    }
}
/**
 *  get total number of loam a/c having sanaction is done .
 * @param  $start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('loanSancationAccount')) {
    function loanSancationAccount($start, $end, $branch_id)
    {
        // dd($start,$end,$branch_id);
        $data = App\Models\Memberloans::whereIn('status', ['1', '3', '4'])->whereIn('loan_type', ['1', '2']);
        $grpdata = App\Models\Grouploans::whereIn('status', ['1', '3', '4'])->where('loan_type', 3);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
            $grpdata = $grpdata->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(approve_date)'), [$startDate, $endDate]);
            $grpdata = $grpdata->whereBetween(\DB::raw('DATE(approve_date)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        $grpCount = $grpdata->count();
        $count = $grpCount + $count;
        return $count;
    }
}
/**
 *  get total  of loam amount having sanaction is done .
 * @param  $start,$end,$branch_id
 * @return   Response
 */
if (!function_exists('loanSancationAmt')) {
    function loanSancationAmt($start, $end, $branch_id)
    {
        $data = App\Models\Memberloans::whereIn('status', ['1', '3', '4'])->whereIn('loan_type', ['1', '2']);
        ;
        $grpdata = App\Models\Grouploans::whereIn('status', ['1', '3', '4'])->where('loan_type', 3);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
            $grpdata = $grpdata->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(approve_date)'), [$startDate, $endDate]);
            $grpdata = $grpdata->whereBetween(\DB::raw('DATE(approve_date)'), [$startDate, $endDate]);
        }
        $sum = $data->sum('amount') + $grpdata->sum('amount');
        return $sum;
    }
}
/**
 *  get total number of loam recover a/c  .
 * @param  $start,$end,,$branch_id
 * @return   Response
 */
if (!function_exists('loanRecoverAccount')) {
    function loanRecoverAccount($start, $end, $branch_id)
    {
        $data = App\Models\LoanDayBooks::where('loan_type', '!=', 4)->whereIN('loan_sub_type', array('0' => 0))->where('is_deleted', 0);
        //$data=App\Models\LoanDayBooks::get();
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
/**
 *  get total  of loam amount having sanaction is done .
 * @param  $start,$end,$branch_id
 * @return   Response
 */
if (!function_exists('loanRecoverAmt')) {
    function loanRecoverAmt($start, $end, $branch_id)
    {
        // $data=App\Models\LoanDayBooks::get();
        $data = App\Models\LoanDayBooks::where('loan_type', '!=', 4)->whereIN('loan_sub_type', array('0' => 0))->where('is_deleted', 0);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $sum = $data->sum('deposit');
        return $sum;
    }
}
/**
 *  get total number of loam a/c having sanaction is done .
 * @param  $start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('loanAgainstSancationAccount')) {
    function loanAgainstSancationAccount($start, $end, $branch_id)
    {
        // dd($start,$end,$branch_id);
        $data = App\Models\Memberloans::whereIn('status', ['1', '3', '4'])->whereIn('loan_type', ['4']);
        //$grpdata=App\Models\Grouploans::whereIn('status',['1','3','4'])->where('loan_type',3);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
            // $grpdata=$grpdata->where('branch_id','=',$branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(approve_date)'), [$startDate, $endDate]);
            // $grpdata=$grpdata->whereBetween(\DB::raw('DATE(approve_date)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        // $grpCount = $grpdata->count();
        $count = $count;
        return $count;
    }
}
/**
 *  get total  of loam amount having sanaction is done .
 * @param  $start,$end,$branch_id
 * @return   Response
 */
if (!function_exists('loanAgainstSancationAmt')) {
    function loanAgainstSancationAmt($start, $end, $branch_id)
    {
        $data = App\Models\Memberloans::whereIn('status', ['1', '3', '4'])->whereIn('loan_type', ['4']);
        ;
        //  $grpdata=App\Models\Grouploans::whereIn('status',['1','3','4'])->where('loan_type',3);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
            //$grpdata=$grpdata->where('branch_id','=',$branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(approve_date)'), [$startDate, $endDate]);
            //$grpdata=$grpdata->whereBetween(\DB::raw('DATE(approve_date)'), [$startDate, $endDate]);
        }
        $sum = $data->sum('amount');
        return $sum;
    }
}
/**
 *  get total number of loam recover a/c  .
 * @param  $start,$end,,$branch_id
 * @return   Response
 */
if (!function_exists('loanAgainstRecoverAccount')) {
    function loanAgainstRecoverAccount($start, $end, $branch_id)
    {
        $data = \App\Models\LoanDayBooks::where('loan_type', 4)->whereIN('loan_sub_type', array('0' => 0))->where('is_deleted', 0);
        //$data=App\Models\LoanDayBooks::get();
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
/**
 *  get total  of loam amount having sanaction is done .
 * @param  $start,$end,$branch_id
 * @return   Response
 */
if (!function_exists('loanAgainstRecoverAmt')) {
    function loanAgainstRecoverAmt($start, $end, $branch_id)
    {
        // $data=App\Models\LoanDayBooks::get();
        $data = \App\Models\LoanDayBooks::where('loan_type', 4)->whereIN('loan_sub_type', array('0' => 0))->where('is_deleted', 0);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $sum = $data->sum('deposit');
        return $sum;
    }
}
/**
 *
 *  get total number of ssb a/c  .
 * @param  $start,$end,,$branch_id
 * @return   Response
 */
if (!function_exists('loanRecoverAccountCashMode')) {
    function loanRecoverAccountCashMode($start, $end, $branch_id)
    {
        $data = App\Models\LoanDayBooks::where('payment_mode', 0)->where('is_deleted', 0)->get();
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
if (!function_exists('loanRecoverAmtCashMode')) {
    function loanRecoverAmtCashMode($start, $end, $branch_id)
    {
        $data = App\Models\LoanDayBooks::where('payment_mode', 0)->where('is_deleted', 0)->get();
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $sum = $data->sum('credit_amount');
        return $sum;
    }
}
if (!function_exists('totalSSbAccountByType')) {
    function totalSSbAccountByType($start, $end, $branch_id, $type, $company_id = null)
    {
        $data = App\Models\SavingAccountTranscation::where('type', $type);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if (!empty($company_id)) {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
/**
 *  get total number of ssb a/c  .
 * @param  $start,$end,,$branch_id
 * @return   Response
 */
if (!function_exists('totalSSbAmtByType')) {
    function totalSSbAmtByType($start, $end, $branch_id, $type, $company_id = null)
    {
        $data = App\Models\SavingAccountTranscation::where('type', $type);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if (!empty($company_id)) {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        if ($type == 5) {
            $count = number_format((float) $data->sum('withdrawal'), 2, '.', '');
        } else {
            $count = number_format((float) $data->sum('deposit'), 2, '.', '');
        }
        return $count;
    }
}
/**
 *  get total other mi by type  .
 * @param  $start,$end,,$branch_id
 * @return   Response
 */
if (!function_exists('totalOtherMiByType')) {
    function totalOtherMiByType($start, $end, $branch_id, $type, $sub_type, $company_id = null)
    {
        $data = App\Models\MemberTransaction::where('type', $type)->where('sub_type', $sub_type);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($company_id != '') {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = number_format((float) $data->sum('amount'), 2, '.', '');
        return $count;
    }
}
// Stationary  Charge 50 rupee ,paymen _mode = DR and type = 21
if (!function_exists('totalOtherStationarySTN')) {
    function totalOtherStatinarySTN($start, $end, $branch_id, $type, $company_id = null)
    {
        $data = App\Models\MemberTransaction::where('type', $type);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($company_id != '') {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = number_format((float) $data->sum('amount'), 2, '.', '');
        return $count;
    }
}
if (!function_exists('totalOtherMiByTypeTotalCount')) {
    function totalOtherMiByTypeTotalCount($start, $end, $branch_id, $type, $sub_type)
    {
        $data = App\Models\MemberTransaction::where('type', $type)->where('sub_type', $sub_type);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
/**
 *  get total number of  mi joining   .
 * @param  $start,$end,,$branch_id
 * @return   Response
 */
if (!function_exists('totalMijoining')) {
    function totalMijoining($start, $end, $branch_id)
    {
        $data = App\Models\Member::where('member_id', '!=', '');
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
/**
 *  get total payment expense   .
 * @param  $start,$end,,$branch_id
 * @return   Response
 */
if (!function_exists('totalPaymentExpense')) {
    function totalPaymentExpense($start, $end, $branch_id, $head)
    {
        $head_ids = array($head);
        // Now get child of that head
        $records = App\Models\AccountHeads::where('head_id', $head)->where('status', 0)->first();
        $subHeadsIDS = App\Models\AccountHeads::where('head_id', $head)->where('status', 0)->pluck('head_id')->toArray();
        if (count($subHeadsIDS) > 0) {
            $head_id = array_merge($head_ids, $subHeadsIDS);
            $return_array = get_change_sub_account_head($head_ids, $subHeadsIDS, true);
        }
        foreach ($return_array as $key => $value) {
            $ids[] = $value;
        }
        $data = App\Models\AllHeadTransaction::whereIn('head_id', $ids)->where('payment_type', '!=', 'DR');
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->get();
        }
        $count = number_format((float) $data->sum('amount'), 2, '.', '');
        return $count;
    }
}
/**
 *  get total other mi by type  .
 * @param  $start,$end,,$branch_id
 * @return   Response
 */
if (!function_exists('investOtherMiByType')) {
    function investOtherMiByType($associate_id, $start, $end, $branch_id, $type, $sub_type)
    {
        $data = App\Models\MemberTransaction::where('associate_id', $associate_id)->where('type', $type)->where('sub_type', $sub_type);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $sum = number_format((float) $data->sum('amount'), 2, '.', '');
        return $sum;
    }
}
/**
 *  get total number of ssb a/c amount  .
 * @param  $start,$end,,$branch_id
 * @return   Response
 */
if (!function_exists('totalInvestSSbAcCountByType')) {
    function totalInvestSSbAcCountByType($associate_id, $start, $end, $branch_id, $type)
    {
        //dd($associate_id,$start,$end,$branch_id,$type);
        $data = App\Models\SavingAccountTranscation::where('associate_id', $associate_id)->where('type', $type);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
/**
 *  get total number of ssb a/c amount  .
 * @param  $start,$end,,$branch_id
 * @return   Response
 */
if (!function_exists('totalInvestSSbAmtByType')) {
    function totalInvestSSbAmtByType($associate_id, $start, $end, $branch_id, $type)
    {
        $data = App\Models\SavingAccountTranscation::where('associate_id', $associate_id)->where('type', $type);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->sum('deposit');
        return $count;
    }
}
if (!function_exists('getBranchWiseBusinessDetail')) {
    function getBranchWiseBusinessD($head_id, $label, $branch_id)
    {
        $info = 'head' . $label;
        $data = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('branch_id', $branch_id);
        $count = $data->count();
        return $count;
    }
}
if (!function_exists('getBranchWiseBusinessDate')) {
    function getBranchWiseBusinessDate($head_id, $label, $branch_id, $date)
    {
        $info = 'head' . $label;
        $data = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('branch_id', $branch_id);
        if ($date != '') {
            $data = $data->whereDate('entry_date', '<=', date("Y-m-d", strtotime(convertDate($date))));
        }
        $count = $data->count();
        return $count;
    }
}
if (!function_exists('getBranchWiseBusinessDateData')) {
    function getBranchWiseBusinessDateData($head_id, $label, $branch_id, $date, $to_date)
    {
        $info = 'head' . $label;
        $data = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('branch_id', $branch_id);
        if ($date != '') {
            $data = $data->whereBetween('entry_date', [date("Y-m-d", strtotime(convertDate($date))), date("Y-m-d", strtotime(convertDate($to_date)))]);
        }
        $count = $data->count();
        return $count;
    }
}
if (!function_exists('getBranchWiseBusinessDateCR')) {
    function getBranchWiseBusinessDateCR($head_id, $label, $branch_id, $date)
    {
        $info = 'head' . $label;
        $data = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('branch_id', $branch_id)->where('payment_type', 'CR');
        if ($date != '') {
            $data = $data->whereDate('entry_date', '<=', date("Y-m-d", strtotime(convertDate($date))));
        }
        $count1 = $data->count();
        $info = 'head' . $label;
        $data1 = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('branch_id', $branch_id)->where('payment_type', 'DR');
        if ($date != '') {
            $data1 = $data1->whereDate('entry_date', '<=', date("Y-m-d", strtotime(convertDate($date))));
        }
        $count2 = $data1->count();
        $count = $count1 - $count2;
        return $count;
    }
}
if (!function_exists('getHeadTotalAmountBranchWise')) {
    function getHeadTotalAmountBranchWise($head_id, $label, $branch_id)
    {
        $info = 'head' . $label;
        $getCR = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('branch_id', $branch_id)->where('payment_type', "CR")->sum('amount');
        $getDR = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('branch_id', $branch_id)->where('payment_type', "DR")->sum('amount');
        $total = $getCR - $getDR;
        return $total;
    }
}
if (!function_exists('get_my_permission')) {
    function get_my_permission($user_id)
    {
        $userPermissionArr = array();
        $user_given_permission = DB::table('user_given_permission')->select('user_permission.id', 'user_permission.name')
            ->join('user_permission', 'user_permission.id', '=', 'user_given_permission.permission_id')->where('user_given_permission.user_id', $user_id)->get()->toArray();
        for ($q = 0; $q < count($user_given_permission); $q++) {
            array_push($userPermissionArr, $user_given_permission[$q]->id);
        }
        //print_r($userPermissionArr);
        //return $userPermissionArr;
    }
}
if (!function_exists('check_my_permission')) {
    function check_my_permission($user_id, $permission_id)
    {
        if ($user_id == "1") {
            return true;
        } else {
            $user_given_permission = DB::table('user_given_permission')->where('user_given_permission.user_id', $user_id)->where('user_given_permission.permission_id', $permission_id)->count();
            if ($user_given_permission > 0) {
                return true;
            } else {
                return false;
            }
        }
    }
}
if (!function_exists('getLoanPlantotalAmount')) {
    function getLoanPlantotalAmount($plan_id, $branch_id, $date)
    {
        $data = App\Models\Memberloans::where('loan_type', $plan_id)->where('branch_id', $branch_id);
        if ($date != '') {
            $date = $data->whereDate('created_at', $date);
        }
        $sum = $data->sum('deposite_amount');
        return $sum;
    }
}
if (!function_exists('getLoanPlantotalrateofinterest')) {
    function getLoanPlantotalrateofinterest($plan_id, $branch_id, $date)
    {
        $data = App\Models\LoanDayBooks::where('loan_type', $plan_id)->where('branch_id', $branch_id)->where('is_deleted', 0);
        if ($date != '') {
            $date = $data->whereDate('created_at', $date);
        }
        $sum = $data->sum('roi_amount');
        return $sum;
    }
}
if (!function_exists('getLoanPlantotalpenalty')) {
    function getLoanPlantotalpenalty($plan_id, $branch_id, $date)
    {
        $data = App\Models\LoanDayBooks::where('loan_type', $plan_id)->where('branch_id', $branch_id)->where('loan_sub_type', 1)->where('is_deleted', 0);
        if ($date != '') {
            $date = $data->whereDate('created_at', $date);
        }
        $sum = $data->sum('deposit');
        return $sum;
    }
}
if (!function_exists('LoanDayBooksAmount')) {
    function LoanDayBooksAmount($loan_type, $account_number)
    {
        $data = App\Models\LoanDayBooks::where('loan_type', $loan_type)
            ->where('account_number', $account_number)
            ->where('is_deleted', 0)
            ->whereIn('loan_sub_type', [0, 1])
            ->sum('deposit');
        return $data;
    }
}
if (!function_exists('getLoanFileChargeTotalAmount')) {
    function getLoanFileChargeTotalAmount($branch_id)
    {
        $getCr = App\Models\Daybook::where('branch_id', $branch_id)->whereIn('transaction_type', [6, 10])->where('payment_type', "CR")->sum('amount');
        $getDr = App\Models\Daybook::where('branch_id', $branch_id)->whereIn('transaction_type', [6, 10])->where('payment_type', "DR")->sum('amount');
        $total = $getCr - $getDr;
        return $total;
    }
}
if (!function_exists('getPlansInterestRate')) {
    function getPlansInterestRate($plan_id, $branch_id)
    {
        $data = App\Models\Memberinvestments::Where('plan_id', $plan_id)->Where('branch_id', $branch_id)->select(['plan_id', 'interest_rate'])->groupBy(['plan_id', 'interest_rate'])->first();
        return $data;
    }
}
if (!function_exists('getemploytotalrentpayment')) {
    function getemploytotalrentpayment($emp_id, $branch_id)
    {
        $data = App\Models\RentPayment::Where('employee_id', $emp_id)->Where('branch_id', $branch_id)->sum('rent_amount');
        return $data;
    }
}
if (!function_exists('commissionledgertotalAmount')) {
    function commissionledgertotalAmount($member_id)
    {
        $data = App\Models\CommissionLeaserDetail::Where('member_id', $member_id)->sum('amount_tds');
        return $data;
    }
}
if (!function_exists('getmemberassociategroupBy')) {
    function getmemberassociategroupBy($associate_id)
    {
        $data = App\Models\Member::Where('id', $associate_id)->select('associate_id')->groupBy('associate_id')->get();
        return $data;
    }
}
// 25-05
if (!function_exists('getmemberinvestementPlanwise')) {
    function getmemberinvestementPlanwise($start, $end, $branch_id, $planIds, $tenure, $company_id = null)
    {
        $tenure = ($tenure / 12);
        $data = App\Models\Memberinvestments::where('plan_id', $planIds)->where('tenure', $tenure);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($company_id != '') {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $data = $data->pluck('id')->toArray();
        return $data;
    }
}
if (!function_exists('getmemberinvestementPlanwiseType')) {
    function getmemberinvestementPlanwiseType($start, $end, $branch_id, $planIds, $tenure, $company_id = null)
    {
        $tenure = ($tenure / 12);
        $data = App\Models\Memberinvestments::whereIn('plan_id', $planIds)->where('tenure', $tenure);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($company_id != '') {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $data = $data->pluck('id')->toArray();
        return $data;
    }
}
// get registered kanyadhan plan account Id
if (!function_exists('getmemberinvestementKanyadhanId')) {
    function getmemberinvestementKanyadhanId($start, $end, $branch_id, $planIds, $tenure, $company_id = null)
    {
        $data = App\Models\Memberinvestments::whereIn('plan_id', $planIds)->whereNotIn('tenure', $tenure);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($company_id != '') {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $data = $data->pluck('id')->toArray();
        return $data;
    }
}
if (!function_exists('getmemberinvestement_emi_recoverKanyadhan')) {
    function getmemberinvestement_emi_recoverKanyadhan($start, $end, $branch_id, $ids, $company_id = null)
    {
        $data = App\Models\Daybook::whereIn('investment_id', $ids)->where('transaction_type', 4);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($company_id != '') {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
if (!function_exists('getmemberinvestement_emi_recoverKanyadhan_sum')) {
    function getmemberinvestement_emi_recoverKanyadhan_sum($start, $end, $branch_id, $ids, $company_id = null)
    {
        $data = App\Models\Daybook::whereIn('investment_id', $ids)->where('transaction_type', 4);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($company_id != '') {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->sum('deposit');
        return $count;
    }
}
if (!function_exists('getrenewemirecovertotalAccount')) {
    function getrenewemirecovertotalAccount($start, $end, $branch_id, $tenure, $plan, $company_id = null)
    {
        //$data = App\Models\Daybook::whereIn('investment_id',$ids)->where('transaction_type',4);
        $tenureInYears = ((float) $tenure / 12);
        $data = App\Models\Daybook::with([
            'investment' => function ($query) {
                $query->select('id', 'plan_id', 'account_number');
            }
        ])
            ->whereHas('investment', function ($query) use ($tenureInYears, $plan) {
                $query->where('member_investments.tenure', $tenureInYears)
                    ->where('member_investments.plan_id', $plan);
            })
            ->where('is_eli', '!=', '1')
            ->where('transaction_type', '=', '4')
            ->where('payment_type', '!=', 'DR');
        if (!empty($branch_id)) {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($company_id != '') {
            $data = $data->where('company_id', '=', $company_id);
        }
        if (!empty($start)) {
            $startDate = $start;
            $endDate = empty($end) ? '' : $end;
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
if (!function_exists('getrenewemirecovertotalAmount')) {
    function getrenewemirecovertotalAmount($start, $end, $branch_id, $tenure, $plan, $company_id = null)
    {
        // dd($start,$end,$branch_id,$ids);
        // $data = App\Models\Daybook::whereIn('investment_id',$ids)->where('transaction_type',4);
        $tenure = ((float) $tenure / 12);
        // dd($start,$end,$branch_id,$ids);
        //$data = App\Models\Daybook::whereIn('investment_id',$ids)->where('transaction_type',4);
        $data = App\Models\Daybook::with([
            'investment' => function ($query) {
                $query->select('id', 'plan_id', 'account_number');
            }
        ])->whereHas('investment', function ($query) use ($tenure, $plan) {
            $query->where('member_investments.tenure', $tenure)->where('member_investments.plan_id', $plan);
            ;
        })->where('is_eli', '!=', 1)->where('transaction_type', 4)->where('payment_type', '!=', 'DR');
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($company_id != '') {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->sum('deposit');
        return $count;
    }
}
if (!function_exists('getbankreceivedBalance')) {
    function getbankreceivedBalance($start, $end, $branch_id, $bank_id)
    {
        $data = App\Models\BranchDaybook::where('payment_type', 'CR')->whereIn('payment_mode', [1, 2])->where(function ($q) use ($bank_id) {
            $q->where('cheque_bank_to', $bank_id)
                ->orwhere('transction_bank_to', $bank_id);
        });
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween('entry_date', [$startDate, $endDate]);
        }
        $count = $data->sum('amount');
        return $count;
    }
}
if (!function_exists('getbankpaymentBalance')) {
    function getbankpaymentBalance($start, $end, $branch_id, $bank_id)
    {
        $data = App\Models\BranchDaybook::where('payment_type', 'DR')->whereIn('payment_mode', [1, 2])->where(function ($q) use ($bank_id) {
            $q->where('cheque_bank_from_id', $bank_id)
                ->orwhere('transction_bank_from_id', $bank_id);
        });
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween('entry_date', [$startDate, $endDate]);
        }
        $count = $data->sum('amount');
        return $count;
    }
}
if (!function_exists('getbankopeningBalance')) {
    function getbankopeningBalance($start, $bank_id)
    {
        if ($start != '') {
            $startDate = $start;
            $exists = App\Models\SamraddhBankClosing::where('entry_date', '=', $startDate)->where('bank_id', $bank_id)->exists();
            if ($exists) {
                $data = App\Models\SamraddhBankClosing::where('entry_date', '=', $startDate)->where('bank_id', $bank_id)->first();
                $data = $data->opening_balance;
            } else {
                $data = App\Models\SamraddhBankClosing::where('entry_date', '<=', $startDate)->where('bank_id', $bank_id)->orderBy('entry_date', 'desc')->first();
                if ($data) {
                    $data = $data->closing_balance;
                } else {
                    $data = 0;
                }
            }
        } else {
            $data = App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->orderBy('entry_date', 'DESC')
                ->first();
            $data = $data->closing_balance;
        }
        return $data;
    }
}
if (!function_exists('getbankclosingBalance')) {
    function getbankclosingBalance($end, $bank_id)
    {
        if ($end != '') {
            $endDate = $end;
            $data = App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('entry_date', '<=', $endDate)->orderBy('entry_date', 'desc')->first();
            $data = 0;
            if ($data) {
                $data = $data->balance;
            }
        } else {
            $data = 0;
        }
        return $data;
    }
}
if (!function_exists('getchequeopeningBalance')) {
    function getchequeopeningBalance($start, $branch_id, $company_id = null)
    {
        $getCr = App\Models\BranchDaybook::where('payment_type', 'CR')->where('company_id', $company_id)->where('payment_mode', 1);
        $getDr = App\Models\BranchDaybook::where('payment_type', 'DR')->where('company_id', $company_id)->where('payment_mode', 1);
        if ($branch_id != '') {
            $getCr = $getCr->where('branch_id', '=', $branch_id);
            $getDr = $getDr->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            $getCr = $getCr->where('entry_date', '<', $startDate);
            $getDr = $getDr->where('entry_date', '<', $startDate);
        }
        $getCr = $getCr->sum('amount');
        $getDr = $getDr->sum('amount');
        $total = $getCr - $getDr;
        return $total;
    }
}
if (!function_exists('getchequeclosingBalance')) {
    function getchequeclosingBalance($end, $branch_id, $company_id = null)
    {
        $getCr = App\Models\BranchDaybook::where('payment_type', 'CR')->where('company_id', $company_id)->where('payment_mode', 1);
        $getDr = App\Models\BranchDaybook::where('payment_type', 'DR')->where('company_id', $company_id)->where('payment_mode', 1);
        if ($branch_id != '') {
            $getCr = $getCr->where('branch_id', '=', $branch_id);
            $getDr = $getDr->where('branch_id', '=', $branch_id);
        }
        if ($end != '') {
            $endDate = $end;
            $getCr = $getCr->where('entry_date', '<=', $endDate);
            $getDr = $getDr->where('entry_date', '<=', $endDate);
        }
        $getCr = $getCr->sum('amount');
        $getDr = $getDr->sum('amount');
        $total = $getCr - $getDr;
        return $total;
    }
}
if (!function_exists('getcashopeningBalance')) {
    function getcashopeningBalance($start, $branch_id)
    {
        $getCr = App\Models\BranchDaybook::where('payment_type', 'CR')->where('payment_mode', 0);
        $getDr = App\Models\BranchDaybook::where('payment_type', 'DR')->where('payment_mode', 0);
        if ($branch_id != '') {
            $getCr = $getCr->where('branch_id', '=', $branch_id);
            $getDr = $getDr->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            $getCr = $getCr->where('entry_date', '<=', $startDate);
            $getDr = $getDr->where('entry_date', '<=', $startDate);
        }
        $getCr = $getCr->sum('amount');
        $getDr = $getDr->sum('amount');
        $total = $getCr - $getDr;
        return $total;
    }
}
if (!function_exists('getcashclosingBalance')) {
    function getcashclosingBalance($end, $branch_id)
    {
        $getCr = App\Models\BranchDaybook::where('payment_type', 'CR')->where('payment_mode', 0);
        $getDr = App\Models\BranchDaybook::where('payment_type', 'DR')->where('payment_mode', 0);
        if ($branch_id != '') {
            $getCr = $getCr->where('branch_id', '=', $branch_id);
            $getDr = $getDr->where('branch_id', '=', $branch_id);
        }
        if ($end != '') {
            $endDate = $end;
            $getCr = $getCr->where('entry_date', '<=', $endDate);
            $getDr = $getDr->where('entry_date', '<=', $endDate);
        }
        $getCr = $getCr->sum('amount');
        $getDr = $getDr->sum('amount');
        $total = $getCr - $getDr;
        return $total;
    }
}
if (!function_exists('getbankopeningBalanceLoanFromBankType')) {
    function getbankopeningBalanceLoanFromBankType($start, $end, $branch_id, $bank_id)
    {
        $getCr = App\Models\BranchDaybook::where('payment_type', 'CR')->where('type', 17)->where(function ($q) use ($bank_id) {
            $q->where('cheque_bank_to', $bank_id)
                ->orwhere('transction_bank_to', $bank_id);
        });
        $getDr = App\Models\BranchDaybook::where('payment_type', 'DR')->where('type', 17)->where(function ($q) use ($bank_id) {
            $q->where('cheque_bank_from_id', $bank_id)
                ->orwhere('transction_bank_from_id', $bank_id);
        });
        if ($branch_id != '') {
            $getCr = $getCr->where('branch_id', '=', $branch_id);
            $getDr = $getDr->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $getCr = $getCr->where('entry_date', '<=', $startDate);
            $getDr = $getDr->where('entry_date', '<=', $startDate);
        }
        $getCr = $getCr->sum('amount');
        $getDr = $getDr->sum('amount');
        $total = $getCr - $getDr;
        return $total;
    }
}
if (!function_exists('getbankclosingBalanceLoanFromBankType')) {
    function getbankclosingBalanceLoanFromBankType($start, $end, $branch_id, $bank_id)
    {
        $getCr = App\Models\BranchDaybook::where('payment_type', 'CR')->where('type', 17)->where(function ($q) use ($bank_id) {
            $q->where('cheque_bank_to', $bank_id)
                ->orwhere('transction_bank_to', $bank_id);
        });
        $getDr = App\Models\BranchDaybook::where('payment_type', 'DR')->where('type', 17)->where(function ($q) use ($bank_id) {
            $q->where('cheque_bank_from_id', $bank_id)
                ->orwhere('transction_bank_from_id', $bank_id);
        });
        if ($branch_id != '') {
            $getCr = $getCr->where('branch_id', '=', $branch_id);
            $getDr = $getDr->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $getCr = $getCr->where('entry_date', '<=', $endDate);
            $getDr = $getDr->where('entry_date', '<=', $endDate);
        }
        $getCr = $getCr->sum('amount');
        $getDr = $getDr->sum('amount');
        $total = $getCr - $getDr;
        return $total;
    }
}
/**************************Report Healper ***********************/
/**
 *  get total number of Loan account .
 * @param  $start,$end,,$branch_id
 * @return   Response
 */
if (!function_exists('totalLoanAc')) {
    function totalLoanAc($associate_id, $start, $end, $branch_id)
    {
        //dd($associate_id,$start,$end,$branch_id,$type);
        $data = App\Models\Memberloans::where('associate_member_id', $associate_id)->whereIn('status', [3, 4]);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(approve_date)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
/**
 *  get total sum of loan amount  .
 * @param  $start,$end,,$branch_id
 * @return   Response
 */
if (!function_exists('totalLoanAmount')) {
    function totalLoanAmount($associate_id, $start, $end, $branch_id)
    {
        $data = App\Models\Memberloans::where('associate_member_id', $associate_id)->whereIn('status', [3, 4]);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(approve_date)'), [$startDate, $endDate]);
        }
        $count = number_format((float) $data->sum('amount'), 2, '.', '');
        return $count;
    }
}
/**
 *  get total number of Loan renew account .
 * @param  $start,$end,,$branch_id
 * @return   Response
 */
if (!function_exists('totalRenewLoanAc')) {
    function totalRenewLoanAc($associate_id, $start, $end, $branch_id)
    {
        //dd($associate_id,$start,$end,$branch_id,$type);
        $data = App\Models\LoanDayBooks::where('associate_id', $associate_id)->whereIN('loan_sub_type', array('0' => 0, '1' => 1))->where('is_deleted', 0);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
/**
 *  get total sum of loan renew amount  .
 * @param  $start,$end,,$branch_id
 * @return   Response
 */
if (!function_exists('totalRenewLoanAmount')) {
    function totalRenewLoanAmount($associate_id, $start, $end, $branch_id)
    {
        $data = App\Models\LoanDayBooks::where('associate_id', $associate_id)->whereIN('loan_sub_type', array('0' => 0, '1' => 1))->where('is_deleted', 0);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = number_format((float) $data->sum('deposit'), 2, '.', '');
        return $count;
    }
}
if (!function_exists('investNewDenoSumTypeBranch')) {
    function investNewDenoSumTypeBranch($start, $end, $planIds, $branch_id, $company_id = null)
    {
        $data = App\Models\Memberinvestments::whereIn('plan_id', $planIds)->where('account_number', 'Not Like', "%" . 'R-' . "%");
        ;
        //$data=App\Models\Memberinvestments::whereIn('plan_id',$planIds);
        /*$data=App\Models\Daybook::with(['investment' => function($query){ $query->select('id', 'plan_id','account_number');}])->whereHas('investment', function ($query) use ($planIds) {
        $query->whereIn('member_investments.plan_id',$planIds);
        })->where('is_eli', '!=',1)->where('transaction_type',4)->where('payment_type','!=','DR');*/
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($company_id != '') {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $sum = $data->sum('deposite_amount');
        return $sum;
    }
}
if (!function_exists('investRenewAmountSumBranch')) {
    function investRenewAmountSumBranch($start, $end, $planIds, $branch_id, $company_id = null)
    {
        $data = App\Models\Daybook::with([
            'investment' => function ($query) {
                $query->select('id', 'plan_id', 'account_number');
            }
        ])->whereHas('investment', function ($query) use ($planIds) {
            $query->whereIn('member_investments.plan_id', $planIds);
        })->whereIn('transaction_type', [2, 4])->where('is_deleted', 0);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($company_id != '') {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $data = $data->sum('deposit');
        return $data;
    }
}
if (!function_exists('totalNiACByBranch')) {
    function totalNiACByBranch($start, $end, $branch_id, $type, $company_id = null)
    {
        $getCr = App\Models\BranchDaybook::where('payment_type', 'CR')->where('type', $type);
        $getDr = App\Models\BranchDaybook::where('payment_type', 'DR')->where('type', $type);
        if ($branch_id != '') {
            $getCr = $getCr->where('branch_id', '=', $branch_id);
            $getDr = $getDr->where('branch_id', '=', $branch_id);
        }
        if ($company_id != '') {
            $getCr = $getCr->where('company_id', '=', $company_id);
            $getDr = $getDr->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            //$getCr=$getCr->where('entry_date','<',$endDate);
            //$getDr=$getDr->where('entry_date','<',$endDate);
            $getCr = $getCr->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate]);
            $getDr = $getDr->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate]);
        }
        $getCr = $getCr->sum('amount');
        $getDr = $getDr->sum('amount');
        $total = $getCr - $getDr;
        return $getDr;
    }
}
if (!function_exists('totalNiACByCount')) {
    function totalNiACByCount($start, $end, $branch_id, $type, $company_id = null)
    {
        $getCr = App\Models\BranchDaybook::where('type', $type);
        if ($branch_id != '') {
            $getCr = $getCr->where('branch_id', '=', $branch_id);
        }
        if ($company_id != '') {
            $data = $getCr->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            //$getCr=$getCr->where('entry_date','<',$endDate);
            $getCr = $getCr->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate]);
        }
        $getCr = $getCr->count();
        return $getCr;
    }
}
if (!function_exists('getMicroEndDate')) {
    function getMicroEndDate($created_at, $end, $branch_id, $type, $company_id = null)
    {
        $getCr = App\Models\BranchCurrentBalance::where('branch_id', $branch_id);
        if ($created_at != '') {
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = $created_at;
            }
            $getCr = $getCr->where('entry_date', '<=', $endDate);
        }
        if ($company_id != '') {
            $getCr = $getCr->where('company_id', '=', $company_id);
        }
        $records = $getCr->orderBy("entry_date", "desc")->first('totalAmount');
        if (!empty($records)) {
            if ($records->totalAmount != '') {
                $total = $records->totalAmount;
            } else {
                $total = 0;
            }
        } else {
            $total = 0;
        }
        return $total;
    }
}
if (!function_exists('getLoadEndDate')) {
    function getLoadEndDate($created_at, $end, $branch_id, $type, $company_id = null)
    {
        $getCr = App\Models\BranchCurrentBalance::where('branch_id', $branch_id);
        if ($created_at != '') {
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = $created_at;
            }
            $getCr = $getCr->where('entry_date', '<=', $endDate);
        }
        if ($company_id != '') {
            $getCr = $getCr->where('company_id', '=', $company_id);
        }
        $records = $getCr->orderBy("entry_date", "desc")->first('totalAmount');
        //dd($records);
        if (!empty($records)) {
            if ($records->totalAmount != '') {
                $total = $records->totalAmount;
            } else {
                $total = 0;
            }
        } else {
            $total = 0;
        }
        return $total;
    }
}
if (!function_exists('totalPaymentWithdrawal')) {
    function totalPaymentWithdrawal($start, $end, $branch_id, $type)
    {
        $data = App\Models\SavingAccountTranscation::where('type', $type);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $total = $data->sum('withdrawal');
        return $total;
    }
}
if (!function_exists('totalPaymentForMaturityAmount')) {
    function totalPaymentForMaturityAmount($start, $end, $branch_id, $type, $company_id = null)
    {
        $data = App\Models\Memberinvestments::where('is_mature', $type);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($company_id != '') {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(investment_interest_date)'), [$startDate, $endDate]);
        }
        $total = $data->sum('maturity_payable_amount');
        return $total;
    }
}
/**
 *  get  mature account of kanyadhan plan tenure wise. Plan type-- monthly, fd
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('matureInvestTenureKanyadhanNewAc')) {
    function matureInvestTenureKanyadhanNewAcCountType($start, $end, $branch_id, $plan_id, $tenure, $company_id = null)
    {
        $data = App\Models\Memberinvestments::whereNotIn('tenure', $tenure)->where('is_mature', 0)->whereIn('plan_id', $plan_id);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if (!empty($company_id)) {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
/**
 *  get mature kanyadhan account. Plan type-- monthly, fd
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('matureInvestTenureKanyadhanAmount')) {
    function matureInvestTenureKanyadhanAmount($start, $end, $branch_id, $planIds, $tenure, $company_id = null)
    {
        $data = App\Models\Memberinvestments::whereNotIn('tenure', $tenure)->where('is_mature', 0)->whereIn('plan_id', $planIds);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($company_id != '') {
            $data = $data->where('company_id', '=', $company_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $sum = $data->sum('maturity_payable_amount');
        return $sum;
    }
}
/**
 *  get interest on deposit amount
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('interestondepositAmount')) {
    function interestondepositAmount($start, $branch_id, $investment_id, $mature_amount)
    {
        //d($start,$branch_id,$investment_id,$mature_amount);
        $data = App\Models\Daybook::whereIn('transaction_type', [2, 4])->where('investment_id', $investment_id);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            $data = $data->where(\DB::raw('DATE(created_at)'), $startDate);
        }
        $data = $data->first();
        // $amount = $mature_amount - $data->deposit;
        if ($data) {
            $amount = $data->deposit;
        } else {
            $amount = 0;
        }
        return $amount;
    }
}
/**
 *  get investment total cash account
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('gettotalcashaccount')) {
    function gettotalcashaccount($start, $end, $branch_id)
    {
        //d($start,$branch_id,$investment_id,$mature_amount);
        $data = App\Models\Daybook::where('transaction_type', 2)->where('payment_mode', 0);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $total = $data->count();
        return $total;
    }
}
/**
 *  get investment total cash amount
 * @param  $associate_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('gettotalcashamount')) {
    function gettotalcashamount($start, $end, $branch_id)
    {
        //d($start,$branch_id,$investment_id,$mature_amount);
        $data = App\Models\Daybook::where('transaction_type', 2)->where('payment_mode', 0);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $total = $data->sum('deposit');
        return $total;
    }
}
/**
 *  get loan total account planwise
 * @param  $type_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('gettotalloanAccountPlanwise')) {
    function gettotalloanAccountPlanwise($start, $end, $branch_id, $plan_id)
    {
        //d($start,$branch_id,$investment_id,$mature_amount);
        $data = App\Models\Memberloans::where('loan_type', $plan_id);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $total = $data->count();
        return $total;
    }
}
/**
 *  get loan total amount planwise
 * @param  $type_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('gettotalloanAmountPlanwise')) {
    function gettotalloanAmountPlanwise($start, $end, $branch_id, $plan_id)
    {
        //d($start,$branch_id,$investment_id,$mature_amount);
        $data = App\Models\Memberloans::where('loan_type', $plan_id);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $total = $data->sum('amount');
        return $total;
    }
}
/**
 *  get loan total amount planwise
 * @param  $type_id,$start,$end,$plan_id,$branch_id
 * @return   Response
 */
if (!function_exists('getfundsendloanandmicro')) {
    function getfundsendloanandmicro($start, $end, $branch_id, $type)
    {
        //d($start,$branch_id,$investment_id,$mature_amount);
        $data = App\Models\FundTransfer::where('transfer_type', 0)->where('transfer_mode', $type);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $data = $data->get();
        return $data;
    }
}
if (!function_exists('getMaturityTransactionRecord')) {
    function getMaturityTransactionRecord($type, $type_id)
    {
        $data = App\Models\AllHeadTransaction::select('id', 'head_id', 'amount')->where('type', $type)->where('type_id', $type_id)->orderBy('id', 'desc')->first();
        return $data;
    }
}
if (!function_exists('getTransactionDetails')) {
    function getDemandTransactionDetails($type, $type_id)
    {
        $data = App\Models\AllHeadTransaction::select('id', 'head_id', 'amount')->where('type', $type)->where('type_id', $type_id)->orderBy('id', 'ASC')->first();
        return $data;
    }
}
if (!function_exists('getMaturityTransactionDetails')) {
    function getMaturityTransactionDetails($type, $sub_type, $type_id)
    {
        $data = App\Models\AllHeadTransaction::select('id', 'head_id', 'amount')->where('type', $type)->where('sub_type', $sub_type)->where('type_id', $type_id)->orderBy('id', 'desc')->first();
        return $data;
    }
}
if (!function_exists('getTotalFWbranchWise')) {
    function getTotalFWbranchWise($start, $end, $branch_id)
    {
        $data = \App\Models\Member::with('associate_branch')->where('member_id', '!=', '9999999')->where('is_associate', 1);
        if ($branch_id != '') {
            $data = $data->where('associate_branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(associate_join_date)'), [$startDate, $endDate]);
        }
        $total = $data->count();
        return $total;
    }
}
if (!function_exists('getTotalFW')) {
    function getTotalFW()
    {
        $data = App\Models\Member::where('is_associate', 1)->count();
        return $data;
    }
}
if (!function_exists('getTotalFinalPaymentBranchDaybook')) {
    function getTotalFinalPaymentBranchDaybook($start, $end, $branch_id)
    {
        $getCr = App\Models\BranchDaybook::where('payment_mode', 0)->where('payment_type', 'CR')->where('type', '!=', '7');
        if ($branch_id != '') {
            $getCr = $getCr->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            //$getCr=$getCr->where('entry_date','<',$endDate);
            $getCr = $getCr->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate]);
        }
        $getCr = $getCr->sum('amount');
        return $getCr;
    }
}
if (!function_exists('getbranchtoHototalAmount')) {
    function getbranchtoHototalAmount($start, $end, $branch_id)
    {
        //d($start,$branch_id,$investment_id,$mature_amount);
        $data = App\Models\FundTransfer::where('transfer_type', 0);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $data = $data->sum('amount');
        return $data;
    }
}
if (!function_exists('file_chrg_total_ac')) {
    function file_chrg_total_ac($startDate, $endDate, $branch_id)
    {
        $file_chrg_total_ac = Daybook::whereIn('transaction_type', ['6,10'])->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->count();
        return $file_chrg_total_ac;
    }
}
if (!function_exists('file_chrg_amount_total')) {
    function file_chrg_amount_total($startDate, $endDate, $branch_id)
    {
        $file_chrg_amount_total = Daybook::whereIn('transaction_type', ['6,10'])->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('amount');
        return $file_chrg_amount_total;
    }
}
if (!function_exists('file_chrg_total_ac_case_mode')) {
    function file_chrg_total_ac_case_mode($startDate, $endDate, $branch_id)
    {
        $file_chrg_total_ac_case_mode = Daybook::whereIn('transaction_type', ['6,10'])->where('branch_id', $branch_id)->where('payment_mode', '0')->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->count();
        return $file_chrg_total_ac_case_mode;
    }
}
if (!function_exists('file_chrg_amount_total_cash_mode')) {
    function file_chrg_amount_total_cash_mode($startDate, $endDate, $branch_id)
    {
        $file_chrg_amount_total_cash_mode = Daybook::whereIn('transaction_type', ['6,10'])->where('branch_id', $branch_id)->where('payment_mode', '0')->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('amount');
        return $file_chrg_amount_total_cash_mode;
    }
}
if (!function_exists('closing_cash_in_hand_samraddh_micro')) {
    function closing_cash_in_hand_samraddh_micro($endDate, $branch_id)
    {
        $getCr = App\Models\BranchCash::where('branch_id', $branch_id)->where('type', '0');
        if ($endDate != '') {
            $getCr = $getCr->where('entry_date', '<=', $endDate);
        }
        $records = $getCr->orderBy("id", "desc")->get();
        if (count($records) > 0) {
            $total = $records[0]->balance;
        } else {
            $total = 0;
        }
        return $total;
    }
}
if (!function_exists('closing_cash_in_hand_samraddh_loan')) {
    function closing_cash_in_hand_samraddh_loan($endDate, $branch_id)
    {
        $getCr = App\Models\BranchCash::where('branch_id', $branch_id)->where('type', '1');
        if ($endDate != '') {
            $getCr = $getCr->where('entry_date', '<=', $endDate);
        }
        $records = $getCr->orderBy("id", "desc")->get();
        if (count($records) > 0) {
            $total = $records[0]->loan_balance;
        } else {
            $total = 0;
        }
        return $total;
    }
}
if (!function_exists('getMoneyBackAmount')) {
    function getMoneyBackAmount($investId)
    {
        $moneyBackAmount = App\Models\InvestmentMonthlyYearlyInterestDeposits::where('investment_id', $investId)->orderBy('id', 'DESC')->first();
        return $moneyBackAmount;
    }
}
if (!function_exists('branchBusinessInvestRenewAmountSumFW')) {
    function branchBusinessInvestRenewAmountSumFW($start, $end, $planIds, $branch_id)
    {
        $memberget = App\Models\Member::where('is_associate', 1)->where('branch_id', $branch_id);
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $memberget = $memberget->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $memberget = $memberget->get(['id']);
        $data = App\Models\Daybook::with([
            'investment' => function ($query) {
                $query->select('id', 'plan_id', 'account_number');
            }
        ])->whereHas('investment', function ($query) use ($planIds) {
            $query->whereIn('member_investments.plan_id', $planIds);
        })->whereIN('associate_id', $memberget)->where('is_eli', '!=', 1)->whereIN('transaction_type', array('0' => 2, '1' => 4))->where('payment_type', '!=', 'DR');
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $data = number_format((float) $data->sum('deposit'), 2, '.', '');
        if ($data <= 0) {
            $data = 0;
        } else {
            $data = $data;
        }
        return $data;
    }
}
if (!function_exists('branchBusinessInvestRenewAmountcountFW')) {
    function branchBusinessInvestRenewAmountcountFW($start, $end, $planIds, $branch_id)
    {
        $memberget = App\Models\Member::where('is_associate', 1)->where('branch_id', $branch_id);
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $memberget = $memberget->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $memberget = $memberget->pluck('id')->toArray();
        $data = App\Models\Daybook::with([
            'investment' => function ($query) {
                $query->select('id', 'plan_id', 'account_number');
            }
        ])->whereHas('investment', function ($query) use ($planIds) {
            $query->whereIn('member_investments.plan_id', $planIds);
        })->whereIN('associate_id', $memberget)->where('is_eli', '!=', 1)->whereIN('transaction_type', array('0' => 2, '1' => 4))->where('payment_type', '!=', 'DR');
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $data = $data->count();
        if ($data <= 0) {
            $data = 0;
        } else {
            $data = $data;
        }
        return $data;
    }
}
if (!function_exists('fw_loan_recovery_count')) {
    function fw_loan_recovery_count($start, $end, $branch_id)
    {
        $memberget = App\Models\Member::where('is_associate', 1)->where('branch_id', $branch_id);
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $memberget = $memberget->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $memberget = $memberget->pluck('id')->toArray();
        if (count($memberget) > 0) {
            $data = App\Models\LoanDayBooks::where('branch_id', $branch_id)->whereIN('associate_id', $memberget)->where('is_deleted', 0);
            if ($start != '') {
                $startDate = $start;
                if ($end != '') {
                    $endDate = $end;
                } else {
                    $endDate = '';
                }
                $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            }
            $data = $data->count();
        } else {
            $data = 0;
        }
        return $data;
    }
}
if (!function_exists('fw_loan_recovery')) {
    function fw_loan_recovery($start, $end, $branch_id)
    {
        $memberget = App\Models\Member::where('is_associate', 1)->where('branch_id', $branch_id);
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $memberget = $memberget->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $memberget = $memberget->pluck('id')->toArray();
        if (count($memberget) > 0) {
            $data = App\Models\LoanDayBooks::where('branch_id', $branch_id)->whereIN('associate_id', $memberget)->where('is_deleted', 0);
            if ($start != '') {
                $startDate = $start;
                if ($end != '') {
                    $endDate = $end;
                } else {
                    $endDate = '';
                }
                $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            }
            $data = $data->sum('deposit');
        } else {
            $data = 0;
        }
        return $data;
    }
}
if (!function_exists('fw_filecharge_sum')) {
    function fw_filecharge_sum($start, $end, $branch_id)
    {
        $memberget = App\Models\Member::where('is_associate', 1)->where('branch_id', $branch_id);
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $memberget = $memberget->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $memberget = $memberget->pluck('id')->toArray();
        if (count($memberget) > 0) {
            $data = App\Models\Daybook::whereIn('transaction_type', ['6,10'])->where('branch_id', $branch_id)->whereIn('associate_id', $memberget);
            if ($start != '') {
                $startDate = $start;
                if ($end != '') {
                    $endDate = $end;
                } else {
                    $endDate = '';
                }
                $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            }
            $data = $data->sum('amount');
        } else {
            $data = 0;
        }
        return number_format((float) $data, 2, '.', '');
        ;
    }
}
if (!function_exists('fw_filecharge')) {
    function fw_filecharge($start, $end, $branch_id)
    {
        $memberget = App\Models\Member::where('is_associate', 1)->where('branch_id', $branch_id);
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $memberget = $memberget->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $memberget = $memberget->pluck('id')->toArray();
        if (count($memberget) > 0) {
            $data = App\Models\Daybook::whereIn('transaction_type', ['6,10'])->where('branch_id', $branch_id)->whereIN('associate_id', $memberget);
            if ($start != '') {
                $startDate = $start;
                if ($end != '') {
                    $endDate = $end;
                } else {
                    $endDate = '';
                }
                $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            }
            $data = $data->count();
        } else {
            $data = 0;
        }
        return $data;
    }
}
if (!function_exists('totalOtherMemberFW')) {
    function totalOtherMemberFW($start, $end, $branch_id)
    {
        // $memberget=App\Models\Member::where('is_associate',1)->where('branch_id',$branch_id);
        // if($start !='')
        // {
        //     $startDate=$start;
        //     if($end !='')
        //     {
        //         $endDate=$end;
        //     }
        //     else
        //     {
        //         $endDate='';
        //     }
        //     $memberget=$memberget->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        // }
        // $memberget=$memberget->get(['id']);
        $data = App\Models\MemberTransaction::where('type', 1)->whereIN('sub_type', ['11,12']);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        if ($count <= 0) {
            $count = 0;
        } else {
            $count = $count;
        }
        return $count;
    }
}
if (!function_exists('totalOtherMemberFWSum')) {
    function totalOtherMemberFWSum($start, $end, $branch_id)
    {
        // $memberget=App\Models\Member::where('is_associate',1)->where('branch_id',$branch_id);
        // if($start !='')
        // {
        //     $startDate=$start;
        //     if($end !='')
        //     {
        //         $endDate=$end;
        //     }
        //     else
        //     {
        //         $endDate='';
        //     }
        //     $memberget=$memberget->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        // }
        // $memberget=$memberget->get(['id']);
        $data = App\Models\MemberTransaction::where('type', 1)->whereIN('sub_type', ['11,12']);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = number_format((float) $data->sum('amount'), 2, '.', '');
        if ($count <= 0) {
            $count = 0;
        } else {
            $count = $count;
        }
        return $count;
    }
}
/****************** Associate Loan record *****************************/
if (!function_exists('associateLoanTypeAC')) {
    function associateLoanTypeAC($associate_id, $start, $end, $branch_id, $type)
    {
        //dd($associate_id,$start,$end,$branch_id,$type);
        $data = App\Models\Memberloans::where('associate_member_id', $associate_id)->where('loan_type', $type)->where('status', 1);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(approve_date)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
if (!function_exists('associateLoanTypeAmount')) {
    function associateLoanTypeAmount($associate_id, $start, $end, $branch_id, $type)
    {
        $data = App\Models\Memberloans::where('associate_member_id', $associate_id)->where('loan_type', $type)->where('status', 1);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(approve_date)'), [$startDate, $endDate]);
        }
        $count = number_format((float) $data->sum('amount'), 2, '.', '');
        return $count;
    }
}
if (!function_exists('associateLoanTypeRecoverAc')) {
    function associateLoanTypeRecoverAc($associate_id, $start, $end, $branch_id, $type)
    {
        $data = App\Models\LoanDayBooks::where('associate_id', $associate_id)->where('loan_type', $type)->whereIN('loan_sub_type', array('0' => 0, '1' => 1))->where('is_deleted', 0);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
if (!function_exists('associateLoanTypeRecoverAmount')) {
    function associateLoanTypeRecoverAmount($associate_id, $start, $end, $branch_id, $type)
    {
        $data = App\Models\LoanDayBooks::where('associate_id', $associate_id)->where('loan_type', $type)->whereIN('loan_sub_type', array('0' => 0, '1' => 1))->where('is_deleted', 0);
        if ($branch_id != '') {
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $count = number_format((float) $data->sum('deposit'), 2, '.', '');
        return $count;
    }
}
if (!function_exists('checkDataExist')) {
    function checkDataExist($date, $bank_id)
    {
        $data = App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('entry_date', $date)->first();
        return $data;
    }
}
if (!function_exists('getDataRow')) {
    function getDataRow($date, $bank_id)
    {
        $data = App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('entry_date', '<', $date)->orderBy('entry_date', 'desc')->first();
        if ($data) {
            return $data;
        }
    }
}
if (!function_exists('eventHoliday')) {
    function eventHoliday($date, $state_id)
    {
        $data = App\Models\Event::where('state_id', $state_id)->where('start_date', $date)->count();
        //$data=App\Models\Event::where('start_date',$date)->count();
        return $data;
    }
}
if (!function_exists('associateTreeid')) {
    function associateTreeid($senior_id, $carder = null)
    {
        $data = App\Models\AssociateTree::with([
            'member'
        ])
            ->where('senior_id', '=', $senior_id)
            ->when($carder != null, function ($q) use ($carder) {
                $q->where('carder', '=', $carder);
            })
            ->get();
        return $data;
    }
}
if (!function_exists('associateTreeCarder')) {
    function associateTreeCarder($id, $cader)
    {
        $data = App\Models\AssociateTree::With('member')->where('senior_id', $id)->where('carder', $cader)->get();
        return $data;
    }
}
if (!function_exists('getMbTrsAmount')) {
    function getMbTrsAmount($investmentId)
    {
        $data = App\Models\EliMoneybackInvestments::where('investment_id', $investmentId)->first();
        return $data;
    }
}
if (!function_exists('getLoanEmiPenaltyRecord')) {
    function getLoanEmiPenaltyRecord($id)
    {
        $data = App\Models\LoanDayBooks::where('id', '>', $id)->where('is_deleted', 0)->first();
        if ($data) {
            if ($data->loan_sub_type == 1) {
                return $data->deposit;
            } else {
                return 'N/A';
            }
        } else {
            return 'N/A';
        }
    }
}
if (!function_exists('getMemberNomineeDetail')) {
    function getMemberNomineeDetail($id)
    {
        $data = App\Models\MemberNominee::where('member_id', $id)->first();
        return $data;
    }
}
if (!function_exists('getMemberNomineeRelation')) {
    function getMemberNomineeRelation($id)
    {
        $data = App\Models\Relations::whereId($id)->first();
        return $data;
    }
}
//----------------duplicate_daybook----------------------
if (!function_exists('getBranchOpeningDetail')) {
    function getBranchOpeningDetail($id)
    {
        $data = App\Models\Branch::when($id != '0', function ($q) use ($id) {
            $q->whereId($id);
        })->first(['id', 'branch_code', 'name', 'date', 'micro_amount', 'loan_amount', 'total_amount', 'transferrabel_amount']);
        return $data;
    }
}
if (!function_exists('getBranchTotalBalanceAllTran')) {
    function getBranchTotalBalanceAllTran($start_date, $branch_date, $branch_balance, $branch_id, $company_id = null)
    {
        $data_DR = DB::table('branch_daybook')
            ->when(($branch_id > 0 && $branch_id != NULL), function ($q) use ($branch_id) {
                $q->where('branch_daybook.branch_id', $branch_id);
            })
            ->where('payment_mode', 0)
            ->where('payment_type', 'DR')
            ->where(\DB::raw('DATE(entry_date)'), '>=', $branch_date)
            ->where(\DB::raw('DATE(entry_date)'), '<', $start_date)
            ->when($company_id > 0, function ($query) use ($company_id) {
                return $query->where('company_id', $company_id);
            })
            ->where('is_deleted', 0)
            ->orderBy('entry_date', 'ASC')
            ->sum('amount');
        $data_CR = DB::table('branch_daybook')
            ->where('description_dr', 'not like', '%Eli Amount%')
            ->when(($branch_id > 0 && $branch_id != NULL), function ($q) use ($branch_id) {
                $q->where('branch_id', $branch_id);
            })->where('payment_mode', 0)
            ->where('payment_type', 'CR')
            ->where(\DB::raw('DATE(entry_date)'), '>=', $branch_date)
            ->when($company_id > 0, function ($query) use ($company_id) {
                return $query->where('company_id', $company_id);
            })
            ->where(\DB::raw('DATE(entry_date)'), '<', $start_date)
            ->where('is_deleted', 0)
            ->orderBy('entry_date', 'ASC')
            ->sum('amount');
        $total = $data_CR - $data_DR;
        if ($company_id == '1' || $company_id == '0') {
            $data = $branch_balance + $total;
        } else {
            $data = $total;
        }
        return $data;
    }
}
if (!function_exists('getTotalFileCharge')) {
    function getTotalFileCharge($start_date, $branch_date, $branch_id)
    {
        $loanFileCharge = App\Models\Memberloans::whereIn('status', [1, 3, 4])->where('file_charge_type', '1')->where('branch_id', $branch_id)->whereBetween('approve_date', [$start_date, $branch_date])->sum('file_charges');
        $grouploanFileCharge = App\Models\Grouploans::whereIn('status', [1, 3, 4])->where('file_charge_type', '1')->where('branch_id', $branch_id)->whereBetween('approve_date', [$start_date, $branch_date])->sum('file_charges');
        $data = $loanFileCharge + $grouploanFileCharge;
        return $data;
    }
}
/** this function last modify by sourab on 03-11-2023 */
if (!function_exists('getBranchTotalBalanceAllTranDR')) {
    function getBranchTotalBalanceAllTranDR($start_date, $end_date, $branch_id, $company_id = null)
    {
        $query = DB::table('branch_daybook')
            ->select('branch_daybook.*', 'branch_daybook.created_at as record_created_date', 'branch_daybook.payment_mode as branch_payment_mode', 'branch_daybook.payment_type as branch_payment_type', 'branch_daybook.member_id as branch_member_id', 'branch_daybook.associate_id as branch_associate_id', 'member_investments.*', 'branch_daybook.id as btid', 'branch_daybook.company_id')
            ->leftjoin('member_investments', 'member_investments.id', 'branch_daybook.type_id', 'branch_daybook.company_id');

        if (($branch_id != '') && ($branch_id > 0)) {
            $query = $query->where('branch_daybook.branch_id', '=', $branch_id);
        }
        $query = $query->when($company_id > '0', function ($q) use ($company_id) {
            $q->where('branch_daybook.company_id', $company_id);
        })->whereBetween('branch_daybook.entry_date', [$start_date, $end_date])->where('branch_daybook.is_deleted', 0)->orderBy('branch_daybook.entry_date', 'ASC');
        $data = $query->get();
        // dd($data);
        $c = 0;
        foreach ($data as $value) {
            if ($value->branch_payment_type == 'DR') {
                if ($value->branch_payment_mode == 0) {
                    $c = $c + $value->amount;
                }
            }
        }
        return $c;
    }
}
if (!function_exists('getBranchTotalBalanceAllTranCR')) {
    function getBranchTotalBalanceAllTranCR($start_date, $end_date, $branch_id, $company_id = null)
    {
        $query = DB::table('branch_daybook')
            ->select('branch_daybook.*', 'branch_daybook.created_at as record_created_date', 'branch_daybook.payment_mode as branch_payment_mode', 'branch_daybook.payment_type as branch_payment_type', 'branch_daybook.member_id as branch_member_id', 'branch_daybook.associate_id as branch_associate_id', 'member_investments.*', 'branch_daybook.id as btid')
            ->leftjoin('member_investments', 'member_investments.id', 'branch_daybook.type_id', 'branch_daybook.company_id');
        if (($branch_id != '') && ($branch_id > 0)) {
            $query = $query->where('branch_daybook.branch_id', '=', $branch_id);
        }
        $query = $query->when($company_id > '0', function ($q) use ($company_id) {
            $q->where('branch_daybook.company_id', $company_id);
        })->whereBetween('branch_daybook.entry_date', [$start_date, $end_date])->where('branch_daybook.is_deleted', 0)->orderBy('branch_daybook.entry_date', 'ASC');
        $data = $query->get();
        $c = 0;
        foreach ($data as $value) {
            $rec = 0;
            if ($value->type == 3 && $value->sub_type == 30) {
                $rec = App\Models\Daybook::where('id', $value->type_transaction_id)->first();
                if (isset($rec->is_eli)) {
                    $rec = $rec->is_eli;
                }
            }
            if ($value->branch_payment_type == 'CR') {
                if ($value->branch_payment_mode == 0 && $rec == 0) {
                    $c = $c + $value->amount;
                }
            }
        }
        return $c;
    }
}
if (!function_exists('getBranchTotalBalanceAllTranDRnew')) {
    function getBranchTotalBalanceAllTranDRnew($start_date, $end_date, $branch_id)
    {
        $data = DB::table('branch_daybook')->select('branch_daybook.*', 'branch_daybook.created_at as record_created_date', 'branch_daybook.payment_mode as branch_payment_mode', 'branch_daybook.payment_type as branch_payment_type', 'branch_daybook.member_id as branch_member_id', 'branch_daybook.associate_id as branch_associate_id', 'member_investments.*', 'branch_daybook.id as btid')->leftjoin('member_investments', 'member_investments.id', 'branch_daybook.type_id')->where('branch_daybook.branch_id', $branch_id)->where('branch_daybook.entry_date', '>=', $start_date)->where('branch_daybook.entry_date', '<', $end_date)->where('branch_daybook.is_deleted', 0)->orderBy('branch_daybook.entry_date', 'ASC')->get();
        $c = 0;
        foreach ($data as $value) {
            if ($value->branch_payment_type == 'DR') {
                if ($value->branch_payment_mode == 0) {
                    $c = $c + $value->amount;
                }
            }
        }
        return $c;
    }
}
if (!function_exists('getBranchTotalBalanceAllTranCRnew')) {
    function getBranchTotalBalanceAllTranCRnew($start_date, $end_date, $branch_id, $company_id = null)
    {
        $data = DB::table('branch_daybook')->select('branch_daybook.*', 'branch_daybook.created_at as record_created_date', 'branch_daybook.payment_mode as branch_payment_mode', 'branch_daybook.payment_type as branch_payment_type', 'branch_daybook.member_id as branch_member_id', 'branch_daybook.associate_id as branch_associate_id', 'member_investments.*', 'branch_daybook.id as btid')->leftjoin('member_investments', 'member_investments.id', 'branch_daybook.type_id', 'branch_daybook.company_id')->where('branch_daybook.branch_id', $branch_id)->where('branch_daybook.entry_date', '>=', $start_date)->where('branch_daybook.company_id', $company_id)->where('branch_daybook.entry_date', '<', $end_date)->where('branch_daybook.is_deleted', 0)->orderBy('branch_daybook.entry_date', 'ASC')->get();
        // $ELI_AMOUNT = App\Models\BranchDaybook::with(['day_book_data'=>function($q) use($start_date,$end_date,$branch_id){
        //     $q->where('is_eli',1);
        // }])->where('branch_id',$branch_id)->where('payment_mode',0)->where('type',3)->where('sub_type',30)->where(\DB::raw('DATE(entry_date)'),'>=',$end_date)->where(\DB::raw('DATE(entry_date)'),'<',$start_date)->sum('amount');
        $c = 0;
        foreach ($data as $value) {
            $rec = 0;
            if ($value->type == 3 && $value->sub_type == 30) {
                $rec = App\Models\Daybook::where('id', $value->type_transaction_id)->first();
                if (isset($rec->is_eli)) {
                    $rec = $rec->is_eli;
                }
            }
            if ($value->branch_payment_type == 'CR') {
                if ($value->branch_payment_mode == 0 && $rec == 0) {
                    $c = $c + $value->amount;
                }
            }
        }
        // if($ELI_AMOUNT)
        // {
        //     $c = $c - $ELI_AMOUNT;
        // }
        return $c;
    }
}
//----------------duplicate_daybook----------------------
if (!function_exists('headTotalFilterData')) {
    function headTotalFilterData($head_id, $field, $startDate, $branch_id, $endDate)
    {
        $getDr = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('payment_type', 'DR');
        $getCr = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('payment_type', 'CR');
        // if($startDate!='' && $endDate == "")
        // {
        //     $getDr=$getDr->whereDate('entry_date','<=',date("Y-m-d", strtotime(convertDate($startDate))));
        //     $getCr=$getCr->whereDate('entry_date','<=',date("Y-m-d", strtotime(convertDate($startDate))));
        // }
        if ($startDate != '' && $endDate != "") {
            $startDate = date("Y-m-d", strtotime(convertDate($startDate)));
            $endDate = date("Y-m-d", strtotime(convertDate($endDate)));
            $getDr = $getDr->whereBetween('entry_date', [$startDate, $endDate]);
            $getCr = $getCr->whereBetween('entry_date', [$startDate, $endDate]);
            //dd($startDate,$endDate, $getCr);
        }
        if ($branch_id != '') {
            $getDr = $getDr->where('branch_id', $branch_id);
            $getCr = $getCr->where('branch_id', $branch_id);
        }
        if (!is_null(Auth::user()->branch_ids)) {
            $branch_ids = Auth::user()->branch_ids;
            $getDr = $getDr->whereIn('branch_id', explode(",", $branch_ids));
            $getCr = $getCr->whereIn('branch_id', explode(",", $branch_ids));
        }
        $getDr = $getDr->sum('amount');
        $getCr = $getCr->sum('amount');
        $total = $getCr - $getDr;
        return $total;
    }
}
if (!function_exists('headTotalFilterDataNew')) {
    function headTotalFilterDataNew($head_id, $field, $startDate, $branch_id, $endDate)
    {
        $getDr = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('payment_type', 'DR');
        $getCr = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('payment_type', 'CR');
        // if($startDate!='' && $endDate == "")
        // {
        //     $getDr=$getDr->whereDate('entry_date','<=',date("Y-m-d", strtotime(convertDate($startDate))));
        //     $getCr=$getCr->whereDate('entry_date','<=',date("Y-m-d", strtotime(convertDate($startDate))));
        // }
        if ($startDate != '' && $endDate != "") {
            $startDate = date("Y-m-d", strtotime(convertDate($startDate)));
            $endDate = date("Y-m-d", strtotime(convertDate($endDate)));
            $getDr = $getDr->whereBetween('entry_date', [$startDate, $endDate]);
            $getCr = $getCr->whereBetween('entry_date', [$startDate, $endDate]);
            //dd($startDate,$endDate, $getCr);
        }
        if ($branch_id != '') {
            $getDr = $getDr->where('branch_id', $branch_id);
            $getCr = $getCr->where('branch_id', $branch_id);
        }
        if (!is_null(Auth::user()->branch_ids)) {
            $branch_ids = Auth::user()->branch_ids;
            $getDr = $getDr->whereIn('branch_id', explode(",", $branch_ids));
            $getCr = $getCr->whereIn('branch_id', explode(",", $branch_ids));
        }
        $getDr = $getDr->sum('amount');
        $getCr = $getCr->sum('amount');
        $total = $getDr - $getCr;
        return $total;
    }
}
if (!function_exists('getBranchWiseBalanceSheetCR')) {
    function getBranchWiseBalanceSheetCR($head_id, $label, $branch_id, $date, $end_date)
    {
        $info = 'head' . $label;
        $data = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('branch_id', $branch_id)->where('payment_type', 'CR');
        if ($date != '' && $end_date == "") {
            $data = $data->whereDate('entry_date', '<=', date("Y-m-d", strtotime(convertDate($date))));
        }
        if ($date != '' && $end_date != "") {
            $startDate = date("Y-m-d", strtotime(convertDate($date)));
            $endDate = date("Y-m-d", strtotime(convertDate($end_date)));
            $data = $data->whereBetween('entry_date', [$startDate, $endDate]);
        }
        $count1 = $data->count();
        $info = 'head' . $label;
        $data1 = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('branch_id', $branch_id)->where('payment_type', 'DR');
        if ($date != '') {
            $data1 = $data1->whereDate('entry_date', '<=', date("Y-m-d", strtotime(convertDate($date))));
        }
        $count2 = $data1->count();
        $count = $count1 - $count2;
        return $count;
    }
}
if (!function_exists('getBranchWiseBalanceSheetCRData')) {
    function getBranchWiseBalanceSheetCRData($head_id, $branch_id, $date, $end_date)
    {
        // $info='head'.$label;
        $data = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('branch_id', $branch_id)->where('payment_type', 'CR');
        if ($date != '' && $end_date == "") {
            $data = $data->whereDate('entry_date', '<=', date("Y-m-d", strtotime(convertDate($date))));
        }
        if ($date != '' && $end_date != "") {
            $startDate = date("Y-m-d", strtotime(convertDate($date)));
            $endDate = date("Y-m-d", strtotime(convertDate($end_date)));
            $data = $data->whereBetween('entry_date', [$startDate, $endDate]);
        }
        $count1 = $data->count();
        $data1 = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('branch_id', $branch_id)->where('payment_type', 'DR');
        if ($date != '') {
            $data1 = $data1->whereDate('entry_date', '<=', date("Y-m-d", strtotime(convertDate($date))));
        }
        $count2 = $data1->count();
        $count = $count1 - $count2;
        return $count;
    }
}
if (!function_exists('getBranchWiseBalanceSheetDateData')) {
    function getBranchWiseBalanceSheetDateData($head_id, $branch_id, $date, $end_date)
    {
        $data = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('branch_id', $branch_id);
        if ($date != '' && $end_date == "") {
            $data = $data->whereDate('entry_date', '<=', date("Y-m-d", strtotime(convertDate($date))));
        }
        if ($date != '' && $end_date != "") {
            $startDate = date("Y-m-d", strtotime(convertDate($date)));
            $endDate = date("Y-m-d", strtotime(convertDate($end_date)));
            $data = $data->whereBetween('entry_date', [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
if (!function_exists('getBranchWiseBalanceSheetDate')) {
    function getBranchWiseBalanceSheetDate($head_id, $label, $branch_id, $date, $end_date)
    {
        $info = 'head' . $label;
        $data = App\Models\AllHeadTransaction::where('head_id', $head_id)->where('branch_id', $branch_id);
        if ($date != '' && $end_date == "") {
            $data = $data->whereDate('entry_date', '<=', date("Y-m-d", strtotime(convertDate($date))));
        }
        if ($date != '' && $end_date != "") {
            $startDate = date("Y-m-d", strtotime(convertDate($date)));
            $endDate = date("Y-m-d", strtotime(convertDate($end_date)));
            $data = $data->whereBetween('entry_date', [$startDate, $endDate]);
        }
        $count = $data->count();
        return $count;
    }
}
if (!function_exists('updateSavingAccountTransaction')) {
    function updateSavingAccountTransaction($savingAccountId, $accountNo)
    {
        $entryTime = date("H:i:s");
        $dayBookRecords = App\Models\SavingAccountTranscation::select('id', 'opening_balance', 'deposit', 'withdrawal', 'created_at')->where('saving_account_id', $savingAccountId)->where('account_no', $accountNo)->orderBy('created_at', 'asc')->get();
        $arraydayBookRecords = $dayBookRecords->toArray();
        foreach ($arraydayBookRecords as $key => $value1) {
            $addmiute = $key + 1;
            $lastRecord = App\Models\SavingAccountTranscation::select('id', 'opening_balance', 'deposit')->where('saving_account_id', $savingAccountId)->where('account_no', $accountNo)->where('created_at', '<', $value1['created_at'])->orderBy('created_at', 'desc')->first();
            $endTime = strtotime("+" . $addmiute . " minutes", strtotime($entryTime));
            $newTime = date('H:i:s', $endTime);
            if ($lastRecord) {
                $updateAssociateAmount = App\Models\SavingAccountTranscation::where('id', $value1['id'])->update(array('created_at' => date("Y-m-d " . $newTime . "", strtotime(convertDate($value1['created_at'])))));
            } else {
                $updateAssociateAmount = App\Models\SavingAccountTranscation::where('id', $value1['id'])->update(array('created_at' => date("Y-m-d " . $newTime . "", strtotime(convertDate($value1['created_at'])))));
            }
        }
        $dayBookRecords = App\Models\SavingAccountTranscation::select('id', 'opening_balance', 'deposit', 'withdrawal', 'created_at')->where('saving_account_id', $savingAccountId)->where('account_no', $accountNo)->orderBy('created_at', 'asc')->get();
        $arraydayBookRecords = $dayBookRecords->toArray();
        foreach ($arraydayBookRecords as $key => $value) {
            $lastRecord = App\Models\SavingAccountTranscation::select('id', 'opening_balance', 'deposit', 'withdrawal')->where('saving_account_id', $savingAccountId)->where('account_no', $accountNo)->where('created_at', '<', $value['created_at'])->orderBy('created_at', 'desc')->first();
            if ($lastRecord) {
                if ($value['deposit'] > 0) {
                    $updateAssociateAmount = App\Models\SavingAccountTranscation::where('id', $value['id'])->update(array('opening_balance' => ($value['deposit'] + $lastRecord->opening_balance)));
                } elseif ($value['withdrawal'] > 0) {
                    $updateAssociateAmount = App\Models\SavingAccountTranscation::where('id', $value['id'])->update(array('opening_balance' => ($lastRecord->opening_balance - $value['withdrawal'])));
                }
            } else {
                $updateAssociateAmount = App\Models\SavingAccountTranscation::where('id', $value['id'])->update(array('opening_balance' => $value['deposit']));
            }
        }
        return true;
    }
}
if (!function_exists('getTdsDrAmount')) {
    function getTdsDrAmount($daybookRefid, $headid, $company_id = null)
    {
        $getDr = App\Models\AllHeadTransaction::where('daybook_ref_id', $daybookRefid)
            ->where('head_id', $headid)
            ->where('payment_type', 'DR')
            ->where('is_deleted', 0)
            ->when($company_id, function ($q) use ($company_id) {
                $q->where('company_id', $company_id);
            })
            ->first('amount');
        if ($getDr) {
            return $getDr->amount;
        } else {
            return 0;
        }
    }
}
if (!function_exists('getTdsDrAmountNew')) {
    function getTdsDrAmountNew($daybookRefid, $headid, $company_id = null)
    {
        $getDr = App\Models\AllHeadTransaction::where('head_id', $headid)
            ->where('payment_type', 'DR')
            ->where('is_deleted', 0)
            ->when($company_id, function ($q) use ($company_id) {
                $q->where('company_id', $company_id);
            })
            ->first('amount');
        if ($getDr) {
            return $getDr->amount;
        } else {
            return 0;
        }
    }
}
if (!function_exists('updateRenewalTransaction')) {
    function updateRenewalTransaction($accountNo)
    {
        $entryTime = date("H:i:s");
        $dayBookRecords = Daybook::select('id', 'opening_balance', 'deposit', 'created_at')->where('account_no', $accountNo)->whereIN('transaction_type', [2, 4, 16, 17, 18, 26])->orderBy(\DB::raw('date(created_at)'), 'asc')->get();
        ;
        $arraydayBookRecords = $dayBookRecords->toArray();
        foreach ($arraydayBookRecords as $key => $value1) {
            $addmiute = $key + 1;
            $lastRecord = Daybook::select('id', 'opening_balance', 'deposit')->where('account_no', $accountNo)->whereIN('transaction_type', [2, 4, 16, 17, 18, 26])->where('created_at', '<', $value1['created_at'])->orderBy('created_at', 'desc')->first();
            $endTime = strtotime("+" . $addmiute . " minutes", strtotime($entryTime));
            $newTime = date('H:i:s', $endTime);
            if ($lastRecord) {
                $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => ($value1['deposit'] + $lastRecord->opening_balance), 'created_at' => date("Y-m-d " . $newTime . "", strtotime(convertDate($value1['created_at'])))));
            } else {
                $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => $value1['deposit'], 'created_at' => date("Y-m-d " . $newTime . "", strtotime(convertDate($value1['created_at'])))));
            }
        }
        $dayBookRecords = Daybook::select('id', 'opening_balance', 'deposit', 'withdrawal', 'created_at')->where('account_no', $accountNo)->whereIN('transaction_type', [2, 4, 16, 17, 18, 26])->orderBy(\DB::raw('date(created_at)'), 'asc')->get();
        ;
        $arraydayBookRecords = $dayBookRecords->toArray();
        foreach ($arraydayBookRecords as $key => $value1) {
            $lastRecord = Daybook::select('id', 'opening_balance', 'deposit')->where('account_no', $accountNo)->whereIN('transaction_type', [2, 4, 16, 17, 18, 26])->where('created_at', '<', $value1['created_at'])->orderBy('created_at', 'desc')->first();
            if ($lastRecord) {
                //$updateAssociateAmount = Daybook::where('id',$value1['id'])->update(array('opening_balance' => ($value1['deposit']+$lastRecord->opening_balance)));
                if ($value1['deposit'] > 0) {
                    $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => ($value1['deposit'] + $lastRecord->opening_balance)));
                } elseif ($value1['withdrawal'] > 0) {
                    $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => ($lastRecord->opening_balance - $value1['withdrawal'])));
                }
            } else {
                $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => $value1['deposit']));
            }
        }
        return true;
    }
}
if (!function_exists('getLastOpeingBalance')) {
    function getLastOpeingBalance($investmentId)
    {
        $data = App\Models\Daybook::where('investment_id', $investmentId)->orderBy('id', 'desc')->first();
        return $data->opening_balance;
    }
}
if (!function_exists('headTotalMember')) {
    function headTotalMember($id, $startDate, $endDate, $branch_id)
    {
        $head_ids = array($id);
        // Now get child of that head
        $records = App\Models\AccountHeads::where('head_id', $id)->where('status', 0)->first();
        $subHeadsIDS = App\Models\AccountHeads::where('head_id', $id)->where('status', 0)->pluck('head_id')->toArray();
        if (count($subHeadsIDS) > 0) {
            $head_id = array_merge($head_ids, $subHeadsIDS);
            $return_array = get_change_sub_account_head($head_ids, $subHeadsIDS, true);
        }
        foreach ($return_array as $key => $value) {
            $ids[] = $value;
        }
        $getDr = App\Models\AllHeadTransaction::whereIn('head_id', $ids);
        // $getCr=App\Models\AllHeadTransaction::whereIn('head_id',$ids)->where('payment_type','CR');
        if ($startDate != '' && $endDate != "") {
            $startDate = date("Y-m-d", strtotime(convertDate($startDate)));
            $endDate = date("Y-m-d", strtotime(convertDate($endDate)));
            $getDr = $getDr->whereBetween('entry_date', [$startDate, $endDate]);
            // $getCr=$getCr->whereBetween('entry_date', [$startDate, $endDate]);
            //dd($startDate,$endDate, $getCr);
        }
        if ($branch_id != '') {
            $getDr = $getDr->where('branch_id', $branch_id);
            // $getCr=$getCr->where('branch_id',$branch_id);
        }
        if (!is_null(Auth::user()->branch_ids)) {
            $branch_ids = Auth::user()->branch_ids;
            $getDr = $getDr->whereIn('branch_id', explode(",", $branch_ids));
            // $getCr=$getCr->whereIn('branch_id',explode(",",$branch_ids));
        }
        $getDr = $getDr->count();
        return $getDr;
    }
}
/**
 *  head sum get through new table
 *
 */
if (!function_exists('headTotalNew')) {
    function headTotalNew($id, $startDate, $endDate, $branch_id, $company_id)
    {
        $head_ids = array($id);
        $return_array = [];
        $ids = [];
        $AccountHeads = App\Models\AccountHeads::where('head_id', $id)->whereIn('status', [0, 1]);
        // Now get child of that head
        $records = $AccountHeads->first();
        $subHeadsIDS = $AccountHeads->pluck('head_id')->toArray();
        if (count($subHeadsIDS) > 0) {
            $head_id = array_merge($head_ids, $subHeadsIDS);
            $return_array = get_change_sub_account_head($head_ids, $subHeadsIDS, true);
        }
        foreach ($return_array as $key => $value) {
            $ids[] = $value;
            $seralizedArray = json_encode($ids);
            $AccountHeads->update(['child_head' => $seralizedArray]);
        }
        $getCr = App\Models\AllHeadTransaction::whereIn('head_id', $ids)
            ->where('payment_type', 'CR')
            ->where('is_deleted', 0)
        ;
        $getDr = App\Models\AllHeadTransaction::whereIn('head_id', $ids)
            ->where('payment_type', 'DR')
            ->where('is_deleted', 0)
        ;
        if ($startDate != '' && $endDate != "") {
            $startDate = date("Y-m-d", strtotime(convertDate($startDate)));
            $endDate = date("Y-m-d", strtotime(convertDate($endDate)));
            $getDr = $getDr->whereBetween('entry_date', [$startDate, $endDate]);
            $getCr = $getCr->whereBetween('entry_date', [$startDate, $endDate]);
        }
        if (isset($branch_id) && ($branch_id != '')) {
            $getDr = $getDr->where('branch_id', $branch_id);
            $getCr = $getCr->where('branch_id', $branch_id);
        }
        if (isset($company_id) && ($company_id != '')) {
            $getDr = $getDr->where('company_id', (int) $company_id);
            $getCr = $getCr->where('company_id', (int) $company_id);
        }
        if (!is_null(Auth::user()->branch_ids)) {
            $branch_ids = Auth::user()->branch_ids;
            $getDr = $getDr->whereIn('branch_id', explode(",", $branch_ids));
            $getCr = $getCr->whereIn('branch_id', explode(",", $branch_ids));
        }
        $getDr = $getDr->sum('amount');
        $getCr = $getCr->sum('amount');
        if (isset($records) && ($records->cr_nature == '1')) {
            $total = $getCr - $getDr;
        } else {
            $total = $getDr - $getCr;
        }
        /*
        if (isset($records) && ($records->previous_parent_id == $records->current_parent_id)) {
        return $total;
        } else {
        $total = -$total;
        return $total;
        }
        */
        return $total;
    }
}
if (!function_exists('headTreeid')) {
    function headTreeid($id)
    {
        $data = App\Models\AccountHeads::where('parent_id', $id);
        $data = $data->pluck('id');
        //print_r($data);die;
        return $data;
    }
}
if (!function_exists('get_change_sub_account_head')) {
    function get_change_sub_account_head($head_ids, $subHeadsIDS, $is_level)
    {
        if ($is_level == false) {
            $return_array = App\Models\AccountHeads::whereIn('head_id', $head_ids)->where('status', 0)->pluck('head_id')->toArray();
        } else {
            $subHeadsIDS2 = App\Models\AccountHeads::whereIn('parent_id', $subHeadsIDS)->pluck('head_id')->toArray();
            if (count($subHeadsIDS2) > 0) {
                $head_ids = array_merge($head_ids, $subHeadsIDS2);
                $return_array = get_change_sub_account_head($head_ids, $subHeadsIDS2, true);
            } else {
                $return_array = get_change_sub_account_head($head_ids, $subHeadsIDS, false);
                // return $return_array;
            }
        }
        return $return_array;
    }
}
if (!function_exists('getInvestmentCurrentBalance')) {
    function getInvestmentCurrentBalance($investmentId, $accountNumber)
    {
        $deposit = Daybook::where('investment_id', $investmentId)->where('account_no', $accountNumber)->where('transaction_type', '>', 1)->whereNotIn('transaction_type', [3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 19])->where('is_deleted', 0)->sum('deposit');
        $withdrawal = Daybook::where('investment_id', $investmentId)->where('account_no', $accountNumber)->where('transaction_type', '>', 1)->whereNotIn('transaction_type', [3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 19])->where('is_deleted', 0)->sum('withdrawal');
        $total = $deposit - $withdrawal;
        return $total;
    }
}
if (!function_exists('getSavingCurrentBalance')) {
    function getSavingCurrentBalance($sId, $accountNumber)
    {
        $deposit = App\Models\SavingAccountTranscation::where('saving_account_id', $sId)->where('account_no', $accountNumber)->sum('deposit');
        $withdrawal = App\Models\SavingAccountTranscation::where('saving_account_id', $sId)->where('account_no', $accountNumber)->sum('withdrawal');
        $total = $deposit - $withdrawal;
        return $total;
    }
}
if (!function_exists('getHeadParentId')) {
    function getHeadParentId($ahId)
    {
        $data = App\Models\AccountHeads::where('head_id', $ahId)->first('parent_id');
        return $data->parent_id;
    }
}
if (!function_exists('get_account_head_ids')) {
    function get_account_head_ids($head_ids, $subHeadsIDS, $is_level)
    {
        if ($is_level == false) {
            $record = App\Models\AccountHeads::whereIn('head_id', $head_ids)->where('status', 0)->pluck('head_id')->toArray();
        } else {
            $subHeadsIDS2 = App\Models\AccountHeads::whereIn('head_id', $subHeadsIDS)->pluck('parent_id')->toArray();
            if (count($subHeadsIDS2) > 0) {
                $head_ids = array_merge($head_ids, $subHeadsIDS2);
                $record = get_account_head_ids($head_ids, $subHeadsIDS2, true);
            } else {
                $record = get_account_head_ids($head_ids, $subHeadsIDS, false);
            }
        }
        return $record;
    }
}
if (!function_exists('getAdvancedEntry')) {
    function getAdvancedEntry($type, $banking_transaction_id, $vendor_type_id)
    {
        $getAdvanced = App\Models\BankingAdvancedLedger::where('type', $type)->where('banking_transaction_id', $banking_transaction_id)->where('vendor_type_id', $vendor_type_id)->count();
        if ($getAdvanced > 0) {
            return $getAdvanced;
        } else {
            $getAdvanced = App\Models\BankingAdvancedLedger::where('type', $type)->where('banking_transaction_id', '>', $banking_transaction_id)->where('vendor_type_id', $vendor_type_id)->count();
            if ($getAdvanced > 0) {
                return $getAdvanced;
            } else {
                return 0;
            }
        }
    }
}
if (!function_exists('getNextAdvancedEntry')) {
    function getNextAdvancedEntry($type, $banking_id, $vendor_type_id)
    {
        $getAdvanced = \App\Models\BankingAdvancedLedger::where('type', $type)->where('banking_id', '>', $banking_id)->where('vendor_type_id', $vendor_type_id)->count();
        if ($getAdvanced > 0) {
            return $getAdvanced;
        } else {
            return 0;
        }
    }
}
/**
 * get branch code by branch id.
 * @param   $id(table column name)
 * @return   Response (return column value -- branch code)
 */
if (!function_exists('getBranchNameByBrachAuto')) {
    function getBranchNameByBrachAuto($id)
    {
        $data = App\Models\Branch::whereId($id)->first('name');
        return $data;
    }
}
/**
 * get branch name by branch code.
 * @param   $id(table column name)
 * @return   Response (return column value -- branch code)
 */
if (!function_exists('getBranchNameByBrachCode')) {
    function getBranchNameByBrachCode($id)
    {
        $data = App\Models\Branch::whereId($id)->first('name');
        return $data;
    }
}
/**
 * insert the expense logs
 * @param $expense_id, $head_id, $branch_id, $type, $created_by
 * @return Response
 */
if (!function_exists('expenses_logs')) {
    function expenses_logs($expense_id, $branch_id, $type, $created_by)
    {
        switch ($type) {
            case "add":
                $title = "Create";
                $desc = "Expense created";
                break;
            case "delete":
                $title = "Delete";
                $desc = "Expense deleted";
                break;
            case "update":
                $title = "Update";
                $desc = "Expense updated";
                break;
            case "approve":
                $title = "Approve";
                $desc = "Expense approved";
                break;
            case "bill_delete":
                $title = "Bill Delete";
                $desc = "Bill Deleted";
                break;
        }
        $data['bill_no'] = $expense_id;
        // $data['head_id'] = $head_id;
        $data['branch_id'] = $branch_id;
        $data['title'] = $title;
        $data['description'] = $desc;
        $data['created_by'] = $created_by;
        $data['created_by_id'] = Auth::user()->id;
        $data['created_by_name'] = Auth::user()->username;
        $create = App\Models\ExpenseLogs::create($data);
    }
}
if (!function_exists('associateTdsDeductGet')) {
    function associateTdsDeductGet($id, $date, $company_id)
    {
        $date = getFinacialYearDate($date);
        $data = App\Models\AssociateTdsDeduct::where('company_id', $company_id)->where('member_id', $id)->where(\DB::raw('DATE(start_date)'), '>=', $date['dateStart'])->where(\DB::raw('DATE(end_date)'), '<=', $date['dateEnd'])->count('id');
        return $data;
    }
}
if (!function_exists('getVendorDetail')) {
    function getVendorDetail($id)
    {
        $data = App\Models\Vendor::whereId($id)->first(['name', 'mobile_no']);
        return $data;
    }
}
if (!function_exists('calculateAssocaiteTotalCommission')) {
    function calculateAssocaiteTotalCommission($id)
    {
        $date = getFinacialYear();
        $date1 = '2021-04-01';
        $date2 = '2022-03-31';
        //$data=App\Models\CommissionLeaserDetail::where('member_id',$id)->whereBetween(\DB::raw('DATE(created_at)'), [$date1, $date2])->sum('amount_tds');
        $data = App\Models\CommissionLeaserDetail::where('member_id', $id)->whereBetween(\DB::raw('DATE(created_at)'), [$date['dateStart'], $date['dateEnd']])->sum('amount_tds');
        return $data;
    }
}
if (!function_exists('getBranchTotalBalance')) {
    function getBranchTotalBalance($start_date, $end_date, $branch_id)
    {
        $data = DB::table('branch_daybook')->where('branch_id', $branch_id)->whereBetween('entry_date', [$start_date, $end_date])->where('is_deleted', 0)->orderBy('entry_date', 'ASC')->get();
        $datas = array();
        $c = 0;
        foreach ($data as $value) {
            if ($value->payment_mode == 'CR') {
                if ($value->payment_mode == 0) {
                    $c = $c + $value->amount;
                    $datas['CR'] = $c;
                }
            }
            if ($value->payment_mode == 'DR') {
                if ($value->payment_mode == 0) {
                    $c = $c + $value->amount;
                    $datas['DR'] = $c;
                }
            }
        }
        return $datas;
    }
}
if (!function_exists('getCustomIdProofName')) {
    function getCustomIdProofName($id, $flag)
    {
        $name = '';
        if ($flag == 'single') {
            $data1 = App\Models\IdType::whereId($id)->first('name');
            if ($data1) {
                $name = $data1->name;
            }
        } else {
            $data = App\Models\IdType::whereIn('id', $id)->get(['id', 'name']);
            if ($data) {
                $newArray = array();
                foreach ($data as $row) {
                    $newArray[$row->id] = $row->name;
                }
                $name = $newArray;
            }
        }
        return $name;
    }
}
if (!function_exists('customBranchName')) {
    function customBranchName()
    {
        return App\Models\Branch::where('manager_id', Auth::user()->id)->first(['id', 'cash_in_hand', 'date', 'name', 'branch_code', 'first_login', 'day_closing_amount', 'transferrabel_amount', 'state_id']);
    }
}
if (!function_exists('investNewAcCountCustom')) {
    function investNewAcCountCustom($start, $end, $plan_id, $branch_id)
    {
        $where = '';
        $results = array();
        if ($branch_id != '' || $start != '') {
            $where = "WHERE";
        }
        if ($branch_id != '') {
            $where .= " branch_id = " . $branch_id . " ";
        }
        if ($branch_id != '' && $start != '') {
            $AND = "AND";
        } else {
            $AND = "";
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $where .= $AND . " created_at BETWEEN date('" . $startDate . "') AND date('" . $endDate . "')";
        }
        $dd = DB::select("SELECT associate_id, plan_id, COUNT(associate_id) As Total FROM member_investments " . $where . " GROUP BY associate_id, plan_id");
        foreach ($dd as $row) {
            if (!empty($row->associate_id) && !empty($row->plan_id)) {
                $results[$row->associate_id . '_' . $row->plan_id] = $row->Total;
            }
        }
        return $results;
    }
}
if (!function_exists('investNewDenoSumCustom')) {
    function investNewDenoSumCustom($start, $end, $plan_id, $branch_id)
    {
        $where = '';
        $results = array();
        if ($branch_id != '' || $start != '') {
            $where = "WHERE";
        }
        if ($branch_id != '') {
            $where .= " branch_id = " . $branch_id . " ";
        }
        if ($branch_id != '' && $start != '') {
            $AND = "AND";
        } else {
            $AND = "";
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $where .= $AND . " created_at BETWEEN date('" . $startDate . "') AND date('" . $endDate . "')";
        }
        $dd = DB::select("SELECT SUM(deposite_amount) AS Total_Ammount, associate_id, plan_id, COUNT(associate_id) As Total FROM member_investments " . $where . " GROUP BY associate_id, plan_id");
        foreach ($dd as $row) {
            if (!empty($row->associate_id) && !empty($row->plan_id)) {
                $results[$row->associate_id . '_' . $row->plan_id] = $row->Total_Ammount;
            }
        }
        return $results;
    }
}
if (!function_exists('debit_card_logs')) {
    function debit_card_logs($debit_card_id, $trans_id, $member_id, $ssb_id, $card_no, $type, $emp_id, $created_by)
    {
        switch ($type) {
            case "add":
                $title = "Create";
                $desc = "Debit card " . $card_no . " created";
                break;
            case "delete":
                $title = "Delete";
                $desc = "Debit card " . $card_no . " deleted";
                break;
            case "update":
                $title = "Update";
                $desc = "Debit card " . $card_no . " updated";
                break;
            case "approve":
                $title = "Approve";
                $desc = "Debit card " . $card_no . " approved";
                break;
            case "reject":
                $title = "Reject";
                $desc = "Debit card " . $card_no . " rejected";
                break;
            case "block":
                $title = "Block";
                $desc = "Debit card " . $card_no . " blocked";
                break;
            case "unblock":
                $title = "Unblock";
                $desc = "Debit card " . $card_no . " unblocked";
                break;
        }
        $data['debit_card_id'] = $debit_card_id;
        $data['transaction_id'] = $trans_id;
        $data['member_id'] = $member_id;
        $data['ssb_id'] = $ssb_id;
        $data['employee_id'] = $emp_id;
        $data['title'] = $title;
        $data['description'] = $desc;
        $data['created_by'] = $created_by;
        $data['created_by_id'] = Auth::user()->id;
        $data['created_by_name'] = Auth::user()->username;
        $create = App\Models\DebitCardLog::create($data);
    }
}
/** this function is last modify by sourab on 05-03-24*/
if (!function_exists('BranchDaybookAmount')) {
    function BranchDaybookAmount($start, $end, $branch_id, $company_id = null)
    {
        $where = '';
        $results = array();
        if ($branch_id != '' || $start != '') {
            $where = "WHERE";
        }
        if ($branch_id != '') {
            $where .= " branch_id = " . $branch_id . " ";
        }
        if ($branch_id != '' && $start != '') {
            $AND = "AND";
        } else {
            $AND = "";
        }
        if (($company_id > '0') && ($company_id != '')) {
            $where .= $AND . " company_id = " . $company_id . " ";
        }
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $where .= $AND . " entry_date BETWEEN date('" . $startDate . "') AND date('" . $endDate . "') AND is_deleted = 0 AND description_dr not like '%Eli Amount%' ";
        }
        $dd = DB::select("SELECT SUM(amount) AS Total_Ammount ,payment_type,payment_mode from branch_daybook " . $where . " GROUP BY payment_mode, payment_type ");
        foreach ($dd as $row) {
            if (!empty($row->payment_type)) {
                $results[$row->payment_mode . '_' . $row->payment_type] = $row->Total_Ammount;
            }
        }
        return $results;
    }
}
if (!function_exists('getTransactionTypeCustom')) {
    function getTransactionTypeCustom()
    {
        $dd = \App\Models\TransactionType::get();
        $results = array();
        foreach ($dd as $row) {
            if (!empty($row->type)) {
                $results[$row->type . '_' . $row->sub_type] = $row->title;
            }
        }
        return $results;
    }
}
if (!function_exists('getMemberCustom')) {
    function getMemberCustom($id)
    {
        $dd = \App\Models\Member::whereId($id)->first();
        return $dd;
    }
}
///------------ 26 March 2022 Alpana-----------
// updated by Shahid on 26-12-2023
if (!function_exists('getMemberCustomData')) {
    function getMemberCustomData($id, $company_id = NULL)
    {
        $query = \App\Models\MemberCompany::whereId($id)->with(['member']);
        if ($company_id != NULL) {
            $query->where('company_id', $company_id);
        }
        $query = $query->first();
        return $query;
    }
}
//
if (!function_exists('getInvestmentPlanName ')) {
    function getInvestmentPlanName($transaction_type_id)
    {
        $data = App\Models\Plans::where('id', $transaction_type_id)->first('name');
        return $data->name;
    }
}
/**
 * get loan detail.
 * @return   Response (return column value)
 */
if (!function_exists('getLoanDetailNew')) {
    function getLoanDetailNew($id)
    {
        $data = App\Models\Memberloans::whereId($id)->first('account_number', 'loan_type');
        return $data;
    }
}
/**
 * get loan detail.
 * @return   Response (return column value)
 */
if (!function_exists('getGroupLoanDetailNew')) {
    function getGroupLoanDetailNew($id)
    {
        $data = App\Models\Grouploans::Where('member_loan_id', $id)->first('account_number');
        return $data;
    }
}
if (!function_exists('getGroupLoanDetailById')) {
    function getGroupLoanDetailNew($id)
    {
        $data = App\Models\Grouploans::whereId($id)->first('account_number');
        return $data;
    }
}
if (!function_exists('customGetMemberData')) {
    function customGetMemberExportData($member_id)
    {
        $memberData = App\Models\Memberloans::select('customer_id')->where('id', $member_id)->first();
        $data = App\Models\Member::select('id', 'first_name', 'last_name', 'mobile_no', 'member_id', 'address')->where('id', $memberData->customer_id)->first();
        return $data;
    }
}
if (!function_exists('customGetMemberData')) {
    function customGetMemberData($member_id)
    {
        $data = App\Models\Member::select('id', 'first_name', 'last_name', 'mobile_no', 'member_id', 'address')->where('id', $member_id)->first();
        return $data;
    }
}
if (!function_exists('customGetBranchDetail')) {
    function customGetBranchDetail($id)
    {
        $data = App\Models\Branch::select('id', 'name', 'sector')->whereId($id)->first();
        return $data;
    }
}
/**
 * get all active relations.
 * @param
 * @return  array()  Response
 */
if (!function_exists('get_member_id_proofNew')) {
    function get_member_id_proofNew($member_id, $type)
    {
        $no = '';
        $doc = \App\Models\MemberIdProof::where('member_id', $member_id)->where(function ($query) use ($type) {
            $query->where('first_id_type_id', $type)
                ->orWhere('second_id_type_id', $type);
        })->first(['first_id_type_id', 'second_id_type_id', 'id', 'member_id', 'first_id_no', 'second_id_no']);
        if ($doc) {
            if ($doc->first_id_type_id == $type) {
                $no = $doc->first_id_no;
            }
            if ($doc->second_id_type_id == $type) {
                $no = $doc->second_id_no;
            }
        } else {
            $no = '';
        }
        return $no;
    }
}
if (!function_exists('getMemberSsbAccountDetailNew')) {
    function getMemberSsbAccountDetailNew($memberId)
    {
        $data = App\Models\SavingAccount::where('member_id', $memberId)->first(['id', 'account_no']);
        return $data;
    }
}
if (!function_exists('getUserDetail')) {
    function getUserDetail($username)
    {
        $data = \App\Models\Admin::where('username', $username)->first();
        return $data;
    }
}
if (!function_exists('getUserRoleDetail')) {
    function getUserRoleDetail($id)
    {
        $data = \App\Models\Admin::select('id', 'mobile_number', 'role_id', 'email')->whereId($id)->first();
        return $data;
    }
}
if (!function_exists('getAssociateOneYear')) {
    function getAssociateOneYear($date, $id)
    {
        $date = date("Y-m-d", strtotime(convertDate($date)));
        $data = App\Models\Member::select('id', 'associate_join_date')
            ->whereId($id)->where('associate_join_date', '>=', DB::raw('date("' . $date . '")- interval 1 year'))->first();
        // print_r($date);die;
        if ($data) {
            $return = 1;
        } else {
            $return = 0;
        }
        return $return;
    }
}
/**
 * get finacial year by Date .
 * @param
 * @return   Response (return start date or end date
 */
if (!function_exists('getFinacialYearDate')) {
    function getFinacialYearDate($dateget)
    {
        if (date('m', strtotime($dateget)) > 3 && date('Y', strtotime($dateget)) == date('Y')) {
            $syear = date('Y');
            $eyear = (date('Y') + 1);
        } else {
            $syear = (date('Y') - 1);
            $eyear = date('Y');
        }
        $startDate = $syear . '-04-01';
        $endDate = $eyear . '-03-31';
        $dateStart = date('Y-m-d', strtotime($startDate));
        $dateEnd = date('Y-m-d', strtotime($endDate));
        $return_array = compact('dateStart', 'dateEnd');
        return $return_array;
    }
}
if (!function_exists('calculateAssocaiteTotalCommissionDate')) {
    function calculateAssocaiteTotalCommissionDate($id, $dateget)
    {
        $date = getFinacialYearDate($dateget);
        $dataId = App\Models\CommissionLeaser::where(\DB::raw('DATE(start_date)'), '>=', $date['dateStart'])->where(\DB::raw('DATE(end_date)'), '<=', $date['dateEnd'])->where('status', '!=', 0)->where('is_deleted', '!=', 1)->pluck('id')->toArray();
        $data = App\Models\CommissionLeaserDetail::where('member_id', $id)->whereIN('commission_leaser_id', $dataId)->sum('amount_tds');
        return $data;
    }
}
/**
 * get associate commission .
 * @param   $member_id startdate,enddate
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getTotalCollection_all')) {
    function getTotalCollection_all($associate_id, $start_date, $end_date)
    {
        $startDateDb = date("Y-m-d", strtotime(convertDate($start_date)));
        $endDateDb = date("Y-m-d", strtotime(convertDate($end_date)));
        if ($start_date != '') {
            $total_collection = App\Models\Daybook::join('member_investments', 'member_investments.id', '=', 'day_books.investment_id')
                ->select(DB::raw('sum(day_books.deposit) as total'), DB::raw('day_books.associate_id as associate_id'))->where('day_books.is_eli', '!=', 1)->where('day_books.transaction_type', '=', 4)->where('day_books.associate_id', '=', $associate_id) /*->whereNotIn('member_investments.plan_id', [4,9])*/ ->whereBetween(\DB::raw('DATE(day_books.created_at)'), [$startDateDb, $endDateDb])->groupBy(DB::raw('day_books.associate_id'))->get();
        } else {
            $total_collection = App\Models\Daybook::join('member_investments', 'member_investments.id', '=', 'day_books.investment_id')
                ->select(DB::raw('sum(day_books.deposit) as total'), DB::raw('day_books.associate_id as associate_id'))->where('day_books.is_eli', '!=', 1)->where('day_books.transaction_type', '=', 4)->where('day_books.associate_id', '=', $associate_id)->where('day_books.is_deleted', 0) /*->whereNotIn('member_investments.plan_id', [4,9])*/ ->groupBy(DB::raw('day_books.associate_id'))->get();
        }
        //print_r($total_collection);die;
        if (count($total_collection) > 0) {
            return $total_collection[0]->total;
        } else {
            return 0;
        }
    }
}
/**
 * get monthly wise renewal .
 * @param   $investmentId,$renewAmount
 * @return   Response (return array) 3june 2022
 */
if (!function_exists('getMonthlyWiseRenewalNewChanges')) {
    function getMonthlyWiseRenewalNewChanges($investmentId, $renewAmount, $dateForRenew, $daybook_id)
    {
        //echo $renewAmount;
        $resultArray = array();
        $renewMonth = 0;
        $invesmentData = investmentDepositeAmount($investmentId);
        $currentBalance = $invesmentData->current_balance;
        $invesmentDepositeAmount = $invesmentData->deposite_amount;
        $currentMonth = date("m", strtotime($dateForRenew));
        $currentDay = date("d", strtotime($dateForRenew));
        $lastRenewalDate = Daybook::select('created_at')->where('investment_id', $investmentId)->orderBy('opening_balance', 'DESC')->limit(1)->first();
        $curgetDepo = Daybook::where('id', '<', $daybook_id)->where('investment_id', $investmentId)->where('is_deleted', 0)->where('status', 1)->sum('deposit');
        $curget = Daybook::where('id', '<', $daybook_id)->where('investment_id', $investmentId)->where('is_deleted', 0)->where('status', 1)->sum('withdrawal');
        $getcurrentbal = $curgetDepo - $curget;
        $currentBalance = $getcurrentbal;
        $lastRenewalTime = strtotime($lastRenewalDate['created_at']);
        $getLastMonth = date("m", $lastRenewalTime);
        $monthDiff = $currentMonth - $getLastMonth;
        $renewAmountMonthsNumber = $renewAmount / $invesmentDepositeAmount;
        $getEmiMonth = $currentBalance / $invesmentDepositeAmount;
        $b = strtotime($invesmentData->created_at);
        $y = date("m", $b);
        $emiDay = date("d", $b);
        $emiMont = ($currentMonth - $y) + 1;
        //  echo 'new'.$getEmiMonth.'c='.$currentBalance;
        $totalEmi = $getEmiMonth + $renewAmountMonthsNumber;
        $am = 0;
        for ($i = 1; $i <= ceil($renewAmountMonthsNumber); $i++) {
            $renewMonth = $getEmiMonth + $i;
            if ($renewMonth <= $totalEmi) {
                $renewMonth = $getEmiMonth + $i;
                $invesmentDepositeAmount = $invesmentDepositeAmount;
                $am = $am + $invesmentDepositeAmount;
            } else {
                $renewMonth = $totalEmi;
                $invesmentDepositeAmount = $renewAmount - $am;
            }
            //echo $am;
            // echo 'new'.$renewMonth;
            if ($invesmentData->plan_id == 7) {
                $j = $renewMonth + Date("t", strtotime($dateForRenew . " last month"));
                $month = ' + ' . $j . ' day';
                $createDateInvest = date('Y-m-d', strtotime($invesmentData->created_at . $month));
                // echo $createDateInvest;
                $j1 = $renewMonth;
                $month1 = ' + ' . $j1 . ' day';
                $createDateInvest1 = date('Y-m-d', strtotime($invesmentData->created_at . $month1));
                $emiDateInvest1 = strtotime($createDateInvest1);
                $emiDateInvest = strtotime($createDateInvest);
                $renewDateinvest = strtotime($dateForRenew);
                if ($renewDateinvest > $emiDateInvest) {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    //$resultArray[$i]['type'] = 1;
                    $resultArray[$i]['type'] = 2;
                } else if ($renewDateinvest == $emiDateInvest1) {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    $resultArray[$i]['type'] = 2;
                } else {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    if ($renewDateinvest >= $emiDateInvest1 && $renewDateinvest <= $emiDateInvest) {
                        $resultArray[$i]['type'] = 2;
                    } else {
                        //$resultArray[$i]['type'] = 3;
                        $resultArray[$i]['type'] = 2;
                    }
                }
            } else {
                $renewMonth1 = $renewMonth - 1;
                $month = ' + ' . $renewMonth . ' month';
                $month1 = ' + ' . $renewMonth1 . ' month';
                $createDateInvest = date('Y-m', strtotime($invesmentData->created_at . $month));
                $renewM = date("Y-m", strtotime($createDateInvest));
                $emiDateInvest = strtotime($renewM);
                $createDateInvest1 = date('Y-m', strtotime($invesmentData->created_at . $month1));
                $renewM1 = date("Y-m", strtotime($createDateInvest1));
                $emiDateInvest1 = strtotime($renewM1);
                $dateForRenewM = date("Y-m", strtotime($dateForRenew));
                $renewDateinvest = strtotime($dateForRenewM);
                if ($renewDateinvest > $emiDateInvest) {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    //$resultArray[$i]['type'] = 1;
                    $resultArray[$i]['type'] = 2;
                } elseif ($renewDateinvest == $emiDateInvest1) {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    $resultArray[$i]['type'] = 2;
                } else {
                    $resultArray[$i]['amount'] = $invesmentDepositeAmount;
                    $resultArray[$i]['month'] = $renewMonth;
                    $resultArray[$i]['type'] = 3;
                    if ($renewDateinvest >= $emiDateInvest1 && $renewDateinvest <= $emiDateInvest) {
                        $resultArray[$i]['type'] = 2;
                    } else {
                        //$resultArray[$i]['type'] = 3;
                        $resultArray[$i]['type'] = 2;
                    }
                }
            }
        }
        return $resultArray;
    }
}
/**
 * get branch detail .
 * @param   $planId
 * @return   Response
 */
if (!function_exists('getBranchDetailManagerId')) {
    function getBranchDetailManagerId($id)
    {
        $data = App\Models\Branch::Where('manager_id', $id)->first();
        return $data;
    }
}
if (!function_exists('getBranchTotalBalanceAllTran2')) {
    function getBranchTotalBalanceAllTran2($start_date, $branch_date, $branch_balance, $branch_id, $company_id = null)
    {
        $data_DR = DB::table('branch_daybook')->where('branch_id', $branch_id)->where('payment_mode', 0)->where('company_id', $company_id)->where('payment_type', 'DR')->where(\DB::raw('DATE(entry_date)'), '>=', $branch_date)->where(\DB::raw('DATE(entry_date)'), '<=', $start_date)->where('is_deleted', 0)->orderBy('entry_date', 'ASC')->sum('amount');
        $data_CR = DB::table('branch_daybook')->where('description_dr', 'not like', '%Eli Amount%')->where('branch_id', $branch_id)->where('company_id', $company_id)->where('payment_mode', 0)->where('payment_type', 'CR')->where(\DB::raw('DATE(entry_date)'), '>=', $branch_date)->where(\DB::raw('DATE(entry_date)'), '<=', $start_date)->where('is_deleted', 0)->orderBy('entry_date', 'ASC')->sum('amount');
        $total = $data_CR - $data_DR;
        $data = $branch_balance + $total;
        return $data;
    }
}
/**
 * get associate commission .
 * @param   $member_id startdate,enddate
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getAssociateTotalCommissionAdmin')) {
    function getAssociateTotalCommissionAdmin($member_id, $startDate, $endDate, $type)
    {
        $amount = App\Models\AssociateCommission::where('member_id', $member_id)->where('type', '>', 2)->where('is_deleted', '0');
        if ($startDate != '') {
            $total_amount = $amount /*->where('is_distribute',0)*/ ->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum($type);
        } else {
            $total_amount = $amount /*->where('is_distribute',0)*/ ->sum($type);
        }
        return number_format($total_amount, 2, '.', '');
    }
}
/**
 * get financial year .
 * @return   Response
 */
if (!function_exists('getFinancialYear')) {
    function getFinancialYear()
    {
        $year = 2020;
        $currentYear = date('Y');
        $financialYear = array();
        for ($i = 0; $i <= $currentYear - $year; $i++) {
            $start = $year + $i;
            $end = $year + $i + 1;
            $financialYear[] = $start . ' - ' . $end;
        }
        return $financialYear;
    }
}
if (!function_exists('getHeadClosing')) {
    function getHeadClosing($head_id, $s_date)
    {
        $date = (int) date('Y', strtotime(convertDate($s_date)));
        $month = (int) date("m", strtotime(convertDate($s_date)));
        if ($month >= 01 && $month <= 03) {
            $date = $date - 1;
        }
        $start_date = $date - 1;
        $end_date = $date;
        // dd( $start_date, $end_date, $date );
        $totalAmount = 0.00;
        $head_ids = array($head_id);
        // Now get child of that head
        $records = App\Models\AccountHeads::where('head_id', $head_id)->where('status', 0)->first();
        $subHeadsIDS = App\Models\AccountHeads::where('head_id', $head_id)->where('status', 0)->pluck('head_id')->toArray();
        $return_array = '';
        if (count($subHeadsIDS) > 0) {
            $head_id = array_merge($head_ids, $subHeadsIDS);
            $return_array = get_change_sub_account_head($head_ids, $subHeadsIDS, true);
        }
        foreach ($return_array as $key => $value) {
            $ids[] = $value;
        }
        $closingDetail = \App\Models\HeadClosing::whereIN('head_id', $ids)->where('start_year', $start_date)->where('end_year', $end_date)->sum('amount');
        // $closingDetail  = \App\Models\HeadClosing::whereIN('head_id',$ids)->where('start_year', $start_date)->where('end_year', $end_date)->sum('amount');
        if ($closingDetail > 0) {
            $totalAmount = $closingDetail;
        }
        return $totalAmount;
    }
}
/**
 * get head  tree .
 * @param   $id,
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('headTree')) {
    function headTree($id)
    {
        $getRecord = App\Models\AccountHeads::where('parent_id', $id)->where('status', 0)->get();
        return $getRecord;
    }
}
/**
 * get head  tree .
 * @param   $id,
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getheadClosingValue')) {
    function getheadClosingValue($start_date, $end_date, $head_id)
    {
        $closingDetail = \App\Models\HeadClosing::where('head_id', $head_id)->where('start_year', $start_date)->where('end_year', $end_date)->first();
        return $closingDetail;
    }
}
if (!function_exists('getHeadClosingNew')) {
    function getHeadClosingNew($head_id, $s_date)
    {
        $date = (int) date('Y', strtotime(convertDate($s_date)));
        $month = (int) date("m", strtotime(convertDate($s_date)));
        $start_date = $date;
        $end_date = $date + 1;
        // dd( $start_date, $end_date, $date );
        $totalAmount = 0.00;
        $head_ids = array($head_id);
        // Now get child of that head
        $records = App\Models\AccountHeads::where('head_id', $head_id)->where('status', 0)->first();
        $subHeadsIDS = App\Models\AccountHeads::where('head_id', $head_id)->where('status', 0)->pluck('head_id')->toArray();
        $return_array = '';
        if (count($subHeadsIDS) > 0) {
            $head_id = array_merge($head_ids, $subHeadsIDS);
            $return_array = get_change_sub_account_head($head_ids, $subHeadsIDS, true);
        }
        foreach ($return_array as $key => $value) {
            $ids[] = $value;
        }
        $childHead = ($records->child_head);
        $count = \App\Models\HeadClosing::whereIN('head_id', $ids)->where('start_year', $start_date)->where('end_year', $end_date)->count();
        if ($count == 0) {
            return null;
        }
        $closingDetail = \App\Models\HeadClosing::whereIN('head_id', $ids)->where('start_year', $start_date)->where('end_year', $end_date)->sum('amount');
        if ($count != 0) {
            $totalAmount = $closingDetail;
        }
        return $totalAmount;
    }
}
if (!function_exists('getBranchTotalBalanceAllTranDRnew')) {
    function getBranchTotalBalanceAllTranDRnew($start_date, $end_date, $branch_id, $company_id = null)
    {
        $data = DB::table('branch_daybook')->select('branch_daybook.*', 'branch_daybook.created_at as record_created_date', 'branch_daybook.payment_mode as branch_payment_mode', 'branch_daybook.payment_type as branch_payment_type', 'branch_daybook.member_id as branch_member_id', 'branch_daybook.associate_id as branch_associate_id', 'member_investments.*', 'branch_daybook.id as btid')->leftjoin('member_investments', 'member_investments.id', 'branch_daybook.type_id')->where('branch_daybook.branch_id', $branch_id)->where('branch_daybook.entry_date', '>=', $start_date)->where('branch_daybook.entry_date', '<', $end_date)->where('branch_daybook.is_deleted', 0)->where('company_id', $company_id)->orderBy('branch_daybook.entry_date', 'ASC')->get();
        $c = 0;
        foreach ($data as $value) {
            if ($value->branch_payment_type == 'DR') {
                if ($value->branch_payment_mode == 0) {
                    $c = $c + $value->amount;
                }
            }
        }
        return $c;
    }
}
if (!function_exists('getBranchTotalBalanceAllTranCRnew')) {
    function getBranchTotalBalanceAllTranCRnew($start_date, $end_date, $branch_id, $company_id = null)
    {
        $data = DB::table('branch_daybook')->select('branch_daybook.*', 'branch_daybook.created_at as record_created_date', 'branch_daybook.payment_mode as branch_payment_mode', 'branch_daybook.payment_type as branch_payment_type', 'branch_daybook.member_id as branch_member_id', 'branch_daybook.associate_id as branch_associate_id', 'member_investments.*', 'branch_daybook.id as btid')->leftjoin('member_investments', 'member_investments.id', 'branch_daybook.type_id')->where('branch_daybook.branch_id', $branch_id)->where('company_id', $company_id)->where('branch_daybook.entry_date', '>=', $start_date)->where('branch_daybook.entry_date', '<', $end_date)->where('branch_daybook.is_deleted', 0)->orderBy('branch_daybook.entry_date', 'ASC')->get();
        // $ELI_AMOUNT = App\Models\BranchDaybook::with(['day_book_data'=>function($q) use($start_date,$end_date,$branch_id){
        //     $q->where('is_eli',1);
        // }])->where('branch_id',$branch_id)->where('payment_mode',0)->where('type',3)->where('sub_type',30)->where(\DB::raw('DATE(entry_date)'),'>=',$end_date)->where(\DB::raw('DATE(entry_date)'),'<',$start_date)->sum('amount');
        $c = 0;
        foreach ($data as $value) {
            $rec = 0;
            if ($value->type == 3 && $value->sub_type == 30) {
                $rec = App\Models\Daybook::where('id', $value->type_transaction_id)->first();
                if (isset($rec->is_eli)) {
                    $rec = $rec->is_eli;
                }
            }
            if ($value->branch_payment_type == 'CR') {
                if ($value->branch_payment_mode == 0 && $rec == 0) {
                    $c = $c + $value->amount;
                }
            }
        }
        // if($ELI_AMOUNT)
        // {
        //     $c = $c - $ELI_AMOUNT;
        // }
        return $c;
    }
}
/**
 * get account no.
 * @param   $id
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getSsbBalance')) {
    function getSsbBalance($id)
    {
        $data = App\Models\SavingAccount::where('member_investments_id', $id)->first(['balance']);
        return $data->balance;
    }
}
/**
 *Loan applicatant bank detail.
 * @param $stateId
 * @return  array()  Response
 */
if (!function_exists('loanApplicatBankDetail')) {
    function loanApplicatBankDetail($id)
    {
        $loan = App\Models\Loanapplicantdetails::where('member_loan_id', $id)->first(['id', 'bank_name', 'bank_account_number', 'ifsc_code', 'member_loan_id']);
        //print_r($loan);die;
        return $loan;
    }
}
if (!function_exists('headSumType')) {
    function headSumType($id, $childID, $branch_id, $start_year, $end_year, $type)
    {
        $startDate = $start_year . '-04-01';
        $endDate = $end_year . '-03-31';
        $allid = $childID . ',' . $id;
        $head_ids = explode(',', $allid);
        if ($branch_id == 'all') {
            $branch = '';
        } else {
            $branch = $branch_id;
        }
        $getDr = App\Models\AllHeadTransaction::whereIn('head_id', $head_ids)->where('payment_type', $type)->where('is_deleted', 0);
        if ($startDate != '' && $endDate != "") {
            $startDate = date("Y-m-d", strtotime(convertDate($startDate)));
            $endDate = date("Y-m-d", strtotime(convertDate($endDate)));
            $getDr = $getDr->whereBetween('entry_date', [$startDate, $endDate]);
            //dd($startDate,$endDate, $getCr);
        }
        if ($branch_id > 0) {
            $getDr = $getDr->where('branch_id', $branch);
        }
        $getDr = $getDr->sum('amount');
        // echo  $getDr;die;
        return $getDr;
    }
}
/*********************************** Account branch transfer start  Alpana(21-10-22)*******************/
/**
 * get last mi code for  investments through old_branch_id because add new functionality (account branch transfer )
 * @param   $planId,$branch_id
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getInvesmentMiCodeNew')) {
    function getInvesmentMiCodeNew($planId, $branch_id)
    {
        $data = App\Models\Memberinvestments::Where('plan_id', $planId)->Where('old_branch_id', $branch_id)->orderBy('mi_code', 'desc')->first('mi_code');
        return $data;
    }
}
/**
 * get associate  mi code for member by through old_branch_id because add new functionality (account branch transfer
 * @param   $member_id,$branch_id
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getAssociateMiCodeNew')) {
    function getAssociateMiCodeNew($member_id, $branch_id)
    {
        $data = App\Models\Member::where([['associate_branch_id_old', '=', $branch_id]])->orderBy('associate_micode', 'desc')->first('associate_micode');
        return $data;
    }
}
/**
 * get last mi code for loan.
 * @param   $planId,$branch_id
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getLoanMiCodeNew')) {
    function getLoanMiCodeNew($loantypeId, $branch_id)
    {
        $data = App\Models\Memberloans::Where('loan_type', $loantypeId)->Where('old_branch_id', $branch_id)->orderBy('mi_code', 'desc')->first('mi_code');
        return $data;
    }
}
// if (! function_exists('getplanroi'))
// {
//     function getplanroi($planId)
//     {
//         $data=App\Models\MaturityCalculation::Where('plan_id',$planId)->get()->groupBy(['type']);
//         return $data;
//     }
// }
/**
 * get roi of ffd and fd plan.
 * @param   $planId,$monthmin,$monthmax
 * @return   Response (return column value -- data)
 */
// if (!function_exists('getplanroi')) {
//     function getplanroi($planId)
//     {
//         $data = App\Models\PlanTenures::Where('plan_id', $planId)->get();
//         return $data;
//     }
// }
/**Updated by mahesh on 11 january 2024 */
if (!function_exists('getplanroi')) {
    function getplanroi($planId, $date = null, $tenure = null)
    {
        $data = App\Models\PlanTenures::Where('plan_id', $planId);
        if ($date !== null) {
            $data = $data->where('effective_from', '<=', $date)->where(function ($q) use ($date) {
                $q->where('effective_to', '>=', $date)->orWhereNull('effective_to');
            });
        }
        if ($tenure !== null) {
            $data = $data->where('tenure', $tenure * 12);
        }
        $data = $data->get();
        return $data;
    }
}
/**
 * get Account Number from Demand Advice
 * @param   $demandId
 * @return   Response (return column value -- data)
 */
if (!function_exists('getdemandAccountNumber')) {
    function getdemandAccountNumber($id)
    {
        $data = App\Models\DemandAdvice::select('id', 'account_number')->whereId($id)->first();
        return $data;
    }
}
/**
 * get last mi code for loan.
 * @param   $planId,$old_branch_id
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getGroupLoanMiCodeNew')) {
    function getGroupLoanMiCodeNew($old_branch_id)
    {
        $data = App\Models\Grouploans::Where('old_branch_id', $old_branch_id)->orderBy('mi_code', 'desc')->first('mi_code');
        return $data;
    }
}
/*********************************** Account branch transfer End  Alpana(21-10-22)*******************/
/****    get admin user name 7-11-2022  Alpana   *************/
if (!function_exists('getAdminUsername')) {
    function getAdminUsername($id)
    {
        $data = \App\Models\Admin::select('id', 'username')->whereId($id)->first();
        return $data->username;
    }
}
/**
 * get  member investment.
 * @param   $id
 * @return   Response (return name)
 */
if (!function_exists('getMemberInvestmentDetailById')) {
    function getMemberInvestmentDetailById($id)
    {
        $data = App\Models\Memberinvestments::leftJoin('plans', 'member_investments.plan_id', '=', 'plans.id')->where('member_investments.id', $id)
            ->select('member_investments.*', 'plans.id', 'plans.name')
            ->first();
        return $data;
    }
}
/**
 * get associate commission .
 * @param   $member_id startdate,enddate
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getTotalCollectionNew')) {
    function getTotalCollectionNew($associate_id, $start_date, $end_date)
    {
        $monthget = date("m", strtotime(convertDate($start_date)));
        $yearget = date("Y", strtotime(convertDate($end_date)));
        $total_collection = App\Models\CommissionFuleCollection::where('month', $monthget)->where('year', $yearget)->where('associate_id', '=', $associate_id)->sum('qualifying_amount');
        if ($total_collection > 0) {
            return $total_collection;
        } else {
            return 0;
        }
    }
}
/*************** Correction Request ************* */
if (!function_exists('getCorrectionDetail')) {
    function getCorrectionDetail($correctionType, $correctionTypeId)
    {
        $data = App\Models\CorrectionRequests::select('id', 'status', 'print_type')
            ->where('correction_type', $correctionType)
            ->where('correction_type_id', $correctionTypeId)
            ->orderBy('id', 'desc')
            ->first();
        return $data;
    }
}
/**
 * get total amount .
 * @param
 * @return   Response (return column value --  mi code)
 */
// CHANGES BY ALPANA MAM ON 20-12-23

// if (!function_exists('getAllDeposit')) {
//     function getAllDeposit($id, $dateSys)
//     {
//         $getSSBAccounttotal = $SSBbalance = 0;
//         $Accounttotal = 0;
//         $getAccount = \App\Models\Memberinvestments::where('customer_id', $id)->whereHas('plan', function ($q) {
//             $q->where('plan_category_code', '<>', 'S');
//         })->where('is_mature', 1)->pluck('id');
//         $getSSBAccount = \App\Models\Memberinvestments::where('customer_id', $id)->whereHas('plan', function ($q) {
//             $q->where('plan_category_code', 'S');
//         })->where('is_mature', 1)->pluck('account_number');
//         // $getSSBAccount = \App\Models\SavingAccount::where('member_id',$id)->first('id');
//         // if($getSSBAccount)
//         // {
//         //     $getSSBAccounttotalCR = \App\Models\SavingAccountTranscation::where('saving_account_id',$getSSBAccount->id)->where('is_deleted',0)->value(DB::raw("SUM(deposit)"));
//         //     $getSSBAccounttotalDR = \App\Models\SavingAccountTranscation::where('saving_account_id',$getSSBAccount->id)->where('is_deleted',0)->value(DB::raw("SUM(withdrawal)"));
//         //     $getSSBAccounttotal=$getSSBAccounttotalCR-$getSSBAccounttotalDR ;
//         // }
//         if ($getAccount) {
//             $AccounttotalCR = \App\Models\InvestmentBalance::whereIn('investment_id', $getAccount)->sum('totalBalance');
//             // $AccounttotalDR = \App\Models\Daybook::whereIn('investment_id',$getAccount)->whereIn('transaction_type',[2,4,15,16,17,18,20,21,22,23,24,25,26,27,28,29,30])->where('is_deleted',0)->value(DB::raw("SUM(withdrawal)"));
//             $Accounttotal = $AccounttotalCR;
//         }
//         if ($getSSBAccount) {
//             $ssbBalance = \App\Models\SavingAccountBalannce::whereIn('account_no', $getSSBAccount)->first('totalBalance');
//         }
//         $ssbBalance = isset($ssbBalance->totalBalance) ? $ssbBalance->totalBalance - 500 : 0;
//         // echo $Accounttotal.'------'.$getSSBAccounttotal.'---'.$id;die;
//         //    if($getSSBAccounttotal>0)
//         //    {
//         //     $SSBbalance=$getSSBAccounttotal-500;
//         //    }
//         $totalDeposit = $Accounttotal + $ssbBalance;
//         $totalDeposit = ($totalDeposit < 0) ? 0 : $totalDeposit;
//         //echo $Accounttotal.'-----'.$getSSBAccounttotal.'---'.$id.'--'.$dateSys;die;
//         return number_format((float) $totalDeposit, 2, '.', '');
//     }

// }
/**
 * Collector Account Store Loan & Investment .
 * @param
 * @return   Response
 */
if (!function_exists('CollectorAccountStoreLI')) {
    function CollectorAccountStoreLI($collector_type, $type_id, $associate_id, $globaldate, $is_app = NULL)
    {
        if (isset($is_app) && $is_app > 1) {
            $created_by_id = $is_app;
        } else {
            $created_by_id = Auth::user()->id;
        }
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        if ($collector_type == 'investmentcollector') {
            $type = 1;
        } else if ($collector_type == 'investmentsavingcollector') {
            $type = 4;
        } else if ($collector_type == 'loancollector') {
            $type = 2;
        } else if ($collector_type == 'grouploancollector') {
            $type = 3;
        }
        $type_id = $type_id;
        $associate_id = $associate_id;
        $status = 1;
        $created_by_id = $created_by_id;
        $created_by = 1;
        $created_at = $created_at;
        $updated_at = $created_at;
        // dd($type,$type_id,$associate_id,$status,$created_by_id,$created_by,$created_at,$updated_at);
        DB::beginTransaction();
        try {
            $data = [
                'type' => $type,
                'type_id' => $type_id,
                'associate_id' => $associate_id,
                'status' => $status,
                'created_id' => $created_by_id ?? Auth::user()->id,
                'created_by' => $created_by,
                'created_at' => $created_at,
                'updated_at' => $created_at,
            ];
            \App\Models\CollectorAccount::create($data);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
    }
}



// neW updated helper
if (!function_exists('getAllDeposit')) {
    function getAllDeposit($id, $dateSys)
    {
        $getSSBAccounttotal = $SSBbalance = 0;
        $Accounttotal = 0;
        $getAccount = \App\Models\Memberinvestments::where('customer_id', $id)->whereHas('plan', function ($q) {
            $q->where('plan_category_code', '<>', 'S');
        })->where('is_mature', 1)->pluck('id');

        $getSSBAccount = \App\Models\SavingAccount::where('customer_id', $id)->pluck('id');
        if ($getSSBAccount) {
            // $getSSBAccounttotalCR = \App\Models\SavingAccountTranscation::where('saving_account_id',$getSSBAccount->id)->where('is_deleted',0)->value(DB::raw("SUM(deposit)"));
            // $getSSBAccounttotalDR = \App\Models\SavingAccountTranscation::where('saving_account_id',$getSSBAccount->id)->where('is_deleted',0)->value(DB::raw("SUM(withdrawal)"));
            $getSSBAccounttotal = \App\Models\SavingAccountBalannce::whereIn('saving_account_id', $getSSBAccount)->sum('totalBalance');
        }
        if ($getAccount) {
            $AccounttotalCR = \App\Models\InvestmentBalance::whereIn('investment_id', $getAccount)->sum('totalBalance');
            // $AccounttotalDR = \App\Models\Daybook::whereIn('investment_id',$getAccount)->whereIn('transaction_type',[2,4,15,16,17,18,20,21,22,23,24,25,26,27,28,29,30])->where('is_deleted',0)->value(DB::raw("SUM(withdrawal)"));
            $Accounttotal = $AccounttotalCR;
        }
        if ($getSSBAccount) {
            $ssbBalance = \App\Models\SavingAccountBalannce::whereIn('account_no', $getSSBAccount)->first('totalBalance');
        }
        $ssbBalance = isset($ssbBalance->totalBalance) ? $ssbBalance->totalBalance - 500 : 0;
        // echo $Accounttotal.'------'.$getSSBAccounttotal.'---'.$id;die;
        if ($getSSBAccounttotal > 0) {
            $SSBbalance = $getSSBAccounttotal - 500;
        }
        $totalDeposit = $Accounttotal + $ssbBalance;
        $totalDeposit = ($totalDeposit < 0) ? 0 : $totalDeposit;
        //echo $Accounttotal.'-----'.$getSSBAccounttotal.'---'.$id.'--'.$dateSys;die;
        return number_format((float) $totalDeposit, 2, '.', '');
    }
}

/**
 * get associate commission .
 * @param   $member_id startdate,enddate
 * @return   Response (return column value --  mi code)
 */
if (!function_exists('getTotalCollectionNewTotal')) {
    function getTotalCollectionNewTotal($associate_id, $start_date, $end_date)
    {
        $monthget = date("m", strtotime(convertDate($start_date)));
        $yearget = date("Y", strtotime(convertDate($end_date)));
        $total_collection = App\Models\CommissionFuleCollection::where('month', $monthget)->where('year', $yearget)->where('associate_id', '=', $associate_id)->sum('total_amount');
        if ($total_collection > 0) {
            return $total_collection;
        } else {
            return 0;
        }
    }
}
if (!function_exists('calculateAssocaiteTotalCommissionDateNew')) {
    function calculateAssocaiteTotalCommissionDateNew($id, $dateget)
    {
        $date = getFinacialYearDate($dateget);
        $dataId = App\Models\CommissionLeaser::where(\DB::raw('DATE(start_date)'), '>=', $date['dateStart'])->where(\DB::raw('DATE(end_date)'), '<=', $date['dateEnd'])->where('status', '!=', 0)->where('is_deleted', '!=', 1)->pluck('id')->toArray();
        $dataold = App\Models\CommissionLeaserDetail::where('member_id', $id)->whereIN('commission_leaser_id', $dataId)->sum('amount_tds');
        $dataIdNew = App\Models\CommissionLeaserMonthly::where(\DB::raw('DATE(start_date)'), '>=', $date['dateStart'])->where(\DB::raw('DATE(end_date)'), '<=', $date['dateEnd'])->where('status', '!=', 0)->where('is_deleted', '!=', 1)->where('id', '>', 4)->pluck('id')->toArray();
        $datanew = App\Models\CommissionLeaserDetailMonthly::where('member_id', $id)->whereIN('commission_leaser_id', $dataIdNew)->sum('amount_tds');
        $data = $dataold + $datanew;
        // die($data);
        return $data;
    }
}
/**
 * Generate Member Id
 */
if (!function_exists('generateCode')) {
    function generateCode($request, $type = NULL, $faCodeType = NULL, $miCodeType, $company_id)
    {
        $getfaCode = getFaCodeNew($company_id);
        $memberfaCode = $getfaCode[$company_id]['member_id'][0]->code;
        $passbookfaCode = $getfaCode[$company_id]['passbook'][0]->code;
        $certificatefaCode = $getfaCode[$company_id]['certificate'][0]->code;
        $branch_id = $request['branchid'];
        // pass role_id(5 for member),branch_id,fa_code
        $getMiCode = getLastMiCode($miCodeType, $branch_id, $memberfaCode, $company_id);
        $miCodeAdd = !empty($getMiCode)
            ? ($getMiCode->mi_code == 9999998 ? $getMiCode->mi_code + 2 : $getMiCode->mi_code + 1)
            : 1;
        $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
        $getBranchCode = getBranchCode($branch_id);
        $branchCode = $getBranchCode->branch_code;
        // genarate Member id
        $code = $branchCode . $memberfaCode . $miCode;
        $data = ['memberCode' => $code, 'faCode' => $memberfaCode, 'miCode' => $miCode, 'branchCode' => $branchCode, 'passbookCode' => $passbookfaCode, 'certificateCode' => $certificatefaCode];
        return $data;
        // save member details
    }
}
if (!function_exists('getFaCodeNew')) {
    function getFaCodeNew($companyId)
    {
        $facode = App\Models\FaCode::where([['status', '=', '1'], ['is_deleted', '=', '0'], ['company_id', '=', $companyId]])->get()->groupBy(['company_id', 'slug']);
        return $facode;
    }
}
if (!function_exists('getSavingAccountDetails')) {
    function getSavingAccountDetails($mId)
    {
        $getDetails = \App\Models\SavingAccount::where('member_id', $mId)->get();
        return $getDetails;
    }
}
if (!function_exists('p')) {
    function p($data)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}
if (!function_exists('pd')) {
    function pd($data)
    {
        if (Auth::user()->id == 14) {
            echo '<pre>';
            print_r($data);
            echo '</pre>';
            die();
        } else {
            return null;
        }
    }
}
if (!function_exists('u')) {
    function u($data)
    {
        return ucwords(strtolower($data));
    }
}
if (!function_exists('pdw')) {
    function pdw($data)
    {
        if (Auth::user()->id == 14) {
            echo '<pre>';
            print_r($data);
            echo '</pre>';
        } else {
            return null;
        }
    }
}
if (!function_exists('getMemberDatalatest')) {
    function getMemberDatalatest($member_id)
    {
        $data = App\Models\Member::with('memberIdProofs', 'savingAccount', 'memberBankDetails')->where('id', $member_id)->first();
        return $data;
    }
}
if (!function_exists('getCompanyBranchWise')) {
    function getCompanyBranchWise($branchId)
    {
        $data = \App\Models\CompanyBranch::where('branch_id', $branchId)->pluck('company_id');
        return $data;
    }
}
if (!function_exists('getCompanyBranch')) {
    function getCompanyBranch($company_id)
    {
        $data = \App\Models\CompanyBranch::where('company_id', $company_id)->with('branch:id,name')->get();
        return $data;
    }
}
if (!function_exists('getAccountHeadsDetails')) {
    function getAccountHeadsDetails($head_id, $company_id = null)
    {
        $companyList = $company_id;
        $arrayCompanyList = explode(' ', $companyList);
        $companyList = array_map(function ($value) {
            return intval($value);
        }, $arrayCompanyList);
        $data = App\Models\AccountHeads::where('head_id', (int) $head_id)
            ->when($company_id, function ($q) use ($companyList) {
                $q->getCompanyRecords("CompanyId", $companyList);
            })
            ->first();
        return $data;
    }
}
if (!function_exists('getMemberDataNew')) {
    function getMemberDataNew($member_id, $company_id = null)
    {
        $query = App\Models\MemberCompany::where('member_id', $member_id);
        if (!empty($company_id)) {
            $query->where('company_id', $company_id);
        }
        $data = $query->first();
        return $data;
    }
}
if (!function_exists('getMemberCompanyDataNew')) {
    function getMemberCompanyDataNew($customerId, $companyId = null)
    {
        $query = App\Models\MemberCompany::with('member')->where('customer_id', $customerId)->orWhereHas('memberBankDetails');
        if (!empty($companyId)) {
            $query->where('company_id', $companyId);
        }
        $data = $query->first();
        return $data;
    }
}
/**
 * get  state code by id.
 * @param   $id
 * @return   Response (return name)
 */
if (!function_exists('getStateCode')) {
    function getStateCode($id)
    {
        $name = '';
        $data = App\Models\State::whereId($id)->first('state_code');
        if ($data) {
            $name = $data->state_code;
        }
        return $name;
    }
}
/**
 * get  state code by id.
 * @param   $id
 * @return   Response (return name)
 */
if (!function_exists('getSavingAccountId')) {
    function getSavingAccountId($id)
    {
        $name = '';
        $data = App\Models\SavingAccountTranscation::whereId($id)->count('saving_account_id');
        return $name;
    }
}
/**
 * Get Plan Tanure Data
 */
if (!function_exists('getTanureDetail')) {
    function getTanureDetail($company_id)
    {
        $results = DB::table('plan_tenures as pt')
            ->join('plans as p', 'p.id', '=', 'pt.plan_id')
            ->select('pt.tenure', 'p.plan_category_code', 'p.id')
            ->where('p.company_id', '=', $company_id)
            ->where(function ($query) {
                $query->whereNotNull('p.plan_sub_category_code')
                    ->where('p.plan_sub_category_code', '<>', 'K')
                    ->orWhereNull('p.plan_sub_category_code');
            })
            ->groupBy('p.plan_category_code', 'pt.tenure', 'p.id')
            ->orderBy('p.plan_category_code', 'ASC')->get();
        return $results;
    }
}
if (!function_exists('getPlanCodeByCategory')) {
    function getPlanCodeByCategory($code)
    {
        $results = \App\Models\Plans::Where('plan_category_code', $code)->pluck('id');
        //  dd($results);
        return $results;
    }
}
if (!function_exists('getmemberIdfromautoId')) {
    function getmemberIdfromautoId($id)
    {
        $results = \App\Models\MemberCompany::select('id', 'member_id', 'customer_id')->whereId($id)->first();
        return $results;
    }
}
//New Function Create Of Brnach Bussness Report
if (!function_exists('branchBusinessMonthlyNewAccount')) {
    function branchBusinessMonthlyNewAccount($start, $end, $plancat, $branch_id, $company_id = null, $type = null)
    {
        $query = DB::table('branch AS b')
            ->leftJoin(DB::raw('(SELECT d.branch_id,
            SUM(IF(p.plan_category_code = "' . $plancat . '" AND d.transaction_type = 2, 1, 0)) AS mnccac,
            SUM(IF(p.plan_category_code = "' . $plancat . '" AND d.transaction_type = 2, d.deposit, 0)) AS mnccamt,
            SUM(IF(p.plan_category_code = "' . $plancat . '" AND d.transaction_type = 4, 1, 0)) AS mrenac,
            SUM(IF(p.plan_category_code = "' . $plancat . '" AND d.transaction_type = 4, d.deposit, 0)) AS mrenamt,
            SUM(IF(p.plan_category_code = "' . $plancat . '" AND d.transaction_type = 2, 1, 0)) AS fnccac,
            SUM(IF(p.plan_category_code = "' . $plancat . '" AND d.transaction_type = 2, d.deposit, 0)) AS fnccamt,
            SUM(IF(d.transaction_type = 2, d.deposit, 0)) AS ncc_m,
            SUM(d.deposit) AS tcc_m
            FROM day_books AS d
            JOIN member_investments AS a ON a.account_number = d.account_no
            JOIN plans AS p ON p.id = a.plan_id
            JOIN plan_categories AS c ON c.code = p.plan_category_code
            WHERE d.transaction_type IN (2, 4)
            AND d.is_deleted = 0
            AND DATE(d.created_at) BETWEEN "' . $start . '" AND "' . $end . '"
            AND p.plan_category_code IN ("D", "M", "F")
            AND d.company_id = ' . $company_id . '
            GROUP BY d.branch_id) AS t'), 't.branch_id', '=', 'b.id');
        $query->where('b.id', $branch_id);
        $query->select(
            DB::raw('IFNULL(t.mnccac, 0) AS mnccac'),
            DB::raw('IFNULL(t.mnccamt, 0) AS mnccamt'),
            DB::raw('IFNULL(t.mrenac, 0) AS mrenac'),
            DB::raw('IFNULL(t.mrenamt, 0) AS mrenamt'),
            DB::raw('IFNULL(t.fnccac, 0) AS fnccac'),
            DB::raw('IFNULL(t.fnccamt, 0) AS fnccamt'),
            DB::raw('IFNULL(t.ncc_m, 0) AS ncc_m'),
            DB::raw('IFNULL(t.tcc_m, 0) AS tcc_m')
        );
        $data = $query->get()->toArray();
        $mnaccac = 0;
        $mnccamt = 0;
        $mrenac = 0;
        $mrenamt = 0;
        $fnccac = 0;
        $fnccamt = 0;
        $ncc_m = 0;
        $tcc_m = 0;
        foreach ($data as $res) {
            $mnaccac += $res->mnccac;
            $mnccamt += $res->mnccamt;
            $mrenac += $res->mrenac;
            $mrenamt += $res->mrenamt;
            $fnccac += $res->fnccac;
            $fnccamt += $res->fnccamt;
            $ncc_m += $res->ncc_m;
            $tcc_m += $res->tcc_m;
        }
        if ($type == 'MonthlyNewAccount') {
            $data = $mnaccac;
        } elseif ($type == 'MonthlyNewAccountAmount') {
            $data = $mnccamt;
        } elseif ($type == 'MonthlyRenewAccount') {
            $data = $mrenac;
        } elseif ($type == 'MonthlyRenewAccountAmount') {
            $data = $mrenamt;
        } elseif ($type == 'FDNewAccount') {
            $data = $fnccac;
        } elseif ($type == 'FDNewAccountAmount') {
            $data = $fnccamt;
        } elseif ($type == 'NCCM') {
            $data = $ncc_m;
        } elseif ($type == 'TCCM') {
            $data = $tcc_m;
        }
        return $data;
    }
}
//New SSB Acount Data
if (!function_exists('branchBusinessSsbAccount')) {
    function branchBusinessSsbAccount($start, $end, $plancat, $branch_id, $company_id = null, $type = null)
    {
        $query = DB::table('branch AS b')
            ->leftJoin(DB::raw('(SELECT d.branch_id,
                SUM(IF(d.type = 1, 1, 0)) AS snccac,
                SUM(IF(d.type = 1, d.deposit, 0)) AS snccamt,
                SUM(IF(d.type = 2, 1, 0)) AS srenac,
                SUM(IF(d.type = 2, d.deposit, 0)) AS srenamt
                FROM saving_account_transctions AS d
                WHERE d.type IN (1, 2)
                AND d.is_deleted = 0
                AND DATE(d.created_at) BETWEEN "' . $start . '" AND "' . $end . '"
                AND d.company_id = ' . $company_id . '
                GROUP BY d.branch_id) AS t'), 't.branch_id', '=', 'b.id')
            ->where('b.id', $branch_id)
            ->select(
                DB::raw('IFNULL(t.snccac, 0) AS snccac'),
                DB::raw('IFNULL(t.snccamt, 0) AS snccamt'),
                DB::raw('IFNULL(t.srenac, 0) AS srenac'),
                DB::raw('IFNULL(t.srenamt, 0) AS srenamt')
            );
        $data = $query->get()->toArray();
        $snccac = 0;
        $snccamt = 0;
        $srenac = 0;
        $srenamt = 0;
        foreach ($data as $res) {
            $snccac += $res->snccac;
            $snccamt += $res->snccamt;
            $srenac += $res->srenac;
            $srenamt += $res->srenamt;
        }
        if ($type == 'SSBNewAccount') {
            $data = $snccac;
        } elseif ($type == 'SSBNewAccountAmount') {
            $data = $snccamt;
        } elseif ($type == 'SSBrenewAccount') {
            $data = $srenac;
        } elseif ($type == 'SSBrenewAmount') {
            $data = $srenamt;
        }
        return $data;
    }
}
//Other Mi And NEW MI Accout
if (!function_exists('otherMiAccount')) {
    function otherMiAccount($start, $end, $branch_id, $company_id = null)
    {
        $query = DB::table('branch AS b')
            ->leftJoin(DB::raw('(SELECT m.branch_id, COUNT(m.id) AS new_mi
                        FROM members AS m
                        JOIN member_companies AS c ON m.id = c.customer_id
                        WHERE m.created_at BETWEEN "' . $start . '" AND "' . $end . '"
                        AND c.company_id = ' . $company_id . '
                        GROUP BY m.branch_id) AS t'), 't.branch_id', '=', 'b.id')
            ->where('b.id', $branch_id)
            ->select(
                DB::raw('IFNULL(t.new_mi, 0) AS new_mi')
            );
        $data = $query->get()->toArray();
        $new_mi = 0;
        foreach ($data as $res) {
            $new_mi += $res->new_mi;
        }
        $data = $new_mi;
        return $data;
    }
}
if (!function_exists('branchBusineOtherMI')) {
    function branchBusineOtherMI($start, $end, $branch_id, $company_id = null)
    {
        $query = DB::table('branch AS b')
            ->leftJoin(DB::raw('(SELECT m.branch_id, COUNT(m.id) AS new_mi
                        FROM members AS m
                        JOIN member_companies AS c ON m.id = c.customer_id
                        WHERE m.created_at BETWEEN "' . $start . '" AND "' . $end . '"
                        AND c.company_id = ' . $company_id . '
                        GROUP BY m.branch_id) AS t'), 't.branch_id', '=', 'b.id')
            ->where('b.id', $branch_id)
            ->select(
                DB::raw('IFNULL(t.new_mi, 0) AS new_mi')
            );
        $data = $query->get()->toArray();
        $new_mi = 0;
        foreach ($data as $res) {
            $new_mi += $res->new_mi;
        }
        $data = $new_mi;
        return $data;
    }
}
if (!function_exists('NewmiJoining')) {
    function NewmiJoining($start, $end, $branch_id, $company_id = null)
    {
        $count = DB::table('members as a')
            ->join('member_companies as b', 'a.id', '=', 'b.customer_id')
            ->where('a.is_deleted', 0)
            ->whereBetween(DB::raw('DATE(a.created_at)'), [$start, $end])
            ->where('a.branch_id', $branch_id)
            ->where('b.company_id', $company_id)
            ->count();
        return $count;
    }
}
if (!function_exists('TotalOtherMI')) {
    function TotalOtherMI($start, $end, $branch_id, $company_id = null)
    {
        $sum = DB::table('branch_daybook as a')
            ->where('a.type', 1)
            ->where('a.sub_type', 11)
            ->where('a.is_deleted', 0)
            ->whereBetween('a.entry_date', [$start, $end])
            ->where('a.branch_id', $branch_id)
            ->where('a.company_id', $company_id)
            ->sum('a.amount');
        return $sum;
    }
}
if (!function_exists('TotalOtherSTN')) {
    function TotalOtherSTN($start, $end, $branch_id, $company_id = null)
    {
        $sum = DB::table('branch_daybook')
            ->select(DB::raw('SUM(amount) as total'))
            ->where('type', 1)
            ->where('sub_type', 12)
            ->where('is_deleted', 0)
            ->whereBetween('entry_date', [$start, $end])
            ->where('branch_id', $branch_id)
            ->where('company_id', $company_id)
            ->get();
        $totalSum = $sum[0]->total;
        return $totalSum;
    }
}
if (!function_exists('TotalCommissionCurrentYear')) {
    function TotalCommissionCurrentYear($id, $dateget, $company_id, $currentAmount, $isTdsDeduct, $getPan)
    {
        $date = getFinacialYearDate($dateget);
        $dataIdNew = App\Models\CommissionLeaserMonthly::where(\DB::raw('DATE(start_date)'), '>=', $date['dateStart'])->where(\DB::raw('DATE(end_date)'), '<=', $date['dateEnd'])->where('status', '!=', 0)->where('is_deleted', '!=', 1)->where('id', '>', 4)->where('company_id', $company_id)->pluck('id')->toArray();
        $datanew = App\Models\CommissionLeaserDetailMonthly::where('member_id', $id)->where('is_deleted', '!=', 1)->where('company_id', $company_id)->whereIN('commission_leaser_id', $dataIdNew)->sum('amount_tds');
        $total_amount = $currentAmount + $datanew;
        $tdsPer = App\Models\TdsDeductionSetting::where('type', 2)->where('beneficiary_type', 1)->whereRaw("'" . $dateget . "' BETWEEN effective_from_date AND IFNULL(effective_to_date, '" . date("Y-m-d") . "')")->whereRaw("'" . $total_amount . "' BETWEEN minlimit AND  maxlimit")->first(['id', 'type', 'beneficiary_type', 'effective_from_date', 'effective_to_date', 'minlimit', 'maxlimit', 'tds_pan', 'tds_no_pan']);
        //  pd($tdsPer);
        $perAmount = 0.00;
        $per = 0.00;
        if ($isTdsDeduct == 1) {
            if ($getPan != '') {
                $perAmount = ($currentAmount * $tdsPer->tds_pan) / 100;
                $per = $tdsPer->tds_pan;
            } else {
                $perAmount = ($currentAmount * $tdsPer->tds_no_pan) / 100;
                $per = $tdsPer->tds_no_pan;
            }
        } else {
            if ($getPan != '') {
                $perAmount = ($total_amount * $tdsPer->tds_pan) / 100;
                $per = $tdsPer->tds_pan;
            } else {
                $perAmount = ($total_amount * $tdsPer->tds_no_pan) / 100;
                $per = $tdsPer->tds_no_pan;
            }
        }
        // die($perdata);
        $data['total_amount'] = $total_amount;
        $data['tds_pan'] = $tdsPer->tds_pan;
        $data['tds_no_pan'] = $tdsPer->tds_no_pan;
        $data['perAmount'] = $perAmount;
        $data['per'] = $per;
        return $data;
    }
}
if (!function_exists('trimData')) {
    function trimData($data)
    {
        $data = trim($data);
        $data = preg_replace('/\s+/', ' ', $data);
        return $data;
    }
}
if (!function_exists('getMonthName')) {
    function getMonthName($monthIndex)
    {
        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
        return $months[$monthIndex];
    }
}
if (!function_exists('datemiddlemonth')) {
    function datemiddlemonth($openingDate, $currentDate)
    {
        $currentStartDay = date('d', strtotime($openingDate));
        $currentStartMonth = date('m', strtotime($currentDate));
        $currentStartYear = date('Y', strtotime($currentDate));
        $start = $currentStartYear . '-' . $currentStartMonth . '-' . $currentStartDay;
        $end = date('Y-m-d', strtotime($start . ' +1 months'));
        $response = [
            'start' => $start,
            'end' => $end,
        ];
        return $response;
    }
}
if (!function_exists('allHeadTransactionbyType')) {
    function allHeadTransactionbyType($head_id, $type_id)
    {
        $data = \App\Models\AllHeadTransaction::where('head_id', $head_id)->where('type_id', $type_id)->first(['id', 'amount']);
        return $data;
    }
}
if (!function_exists('getGstTransation')) {
    function getGstTransation($received_id)
    {
        $data = \App\Models\GstTransaction::where('received_id', $received_id)->first();
        return $data;
    }
}
if (!function_exists('getfundtransferPandingAmount')) {
    function getfundtransferPandingAmount($branch_id)
    {
        $data = App\Models\FundTransfer::where('status', 0)->when($branch_id, function ($q) use ($branch_id) {
            $q->where('branch_id', $branch_id);
        });
        $data = $data
            ->sum('amount')
        ;
        return $data;
    }
}
if (!function_exists('getbranchbankbalanceamounthelper')) {
    function getbranchbankbalanceamounthelper($branch_id, $company_id = null)
    {
        $globaldate = date('Y-m-d');
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $getBranchAmount = \App\Models\Branch::whereId($branch_id)->first();
        $Amount = $company_id == 1 ? $getBranchAmount->total_amount : 0;
        $startDate = ($company_id == 1) ? date("Y-m-d", strtotime(convertDate('2021-08-05'))) : '';
        $balance = \App\Models\BranchCurrentBalance::where('branch_id', $branch_id)->when($startDate != '', function ($q) use ($startDate) {
            $q->whereDate('entry_date', '>=', $startDate);
        })->where('entry_date', '<=', $entry_date);
        if (isset($company_id)) {
            $balance = $balance->where('company_id', $company_id);
        }
        $balance = $balance->sum('totalAmount');
        $day_closing_amount = ($balance + $Amount);
        return $day_closing_amount;
    }
}
if (!function_exists('getTotalCollection_allNew')) {
    function getTotalCollection_allNew($member_id, $year, $month, $company_id)
    {
        if ($company_id > 0) {
            $total_amount = App\Models\CommissionFuleCollection::where('associate_id', $member_id)
                ->where('company_id', $company_id)
                ->where('month', $month)
                ->where('year', $year)->select('total_amount')
                ->get();
        } else {
            $total_amount = App\Models\CommissionFuleCollection::where('associate_id', $member_id)
                ->where('month', $month)
                ->where('year', $year)->select('total_amount')
                ->get();
        }
        return number_format($total_amount->sum('total_amount'), 2, '.', '');
    }
}
if (!function_exists('getAssociateTotalCommissionAdminNew')) {
    function getAssociateTotalCommissionAdminNew($member_id, $year, $month, $company_id)
    {
        if ($company_id > 0) {
            $total_amount = App\Models\AssociateCommissionTotalMonthly::where('member_id', $member_id)
                ->where('company_id', $company_id)
                ->where('month', $month)
                ->where('year', $year)->select('commission_amount')
                ->first();
        } else {
            $total_amount = App\Models\AssociateCommissionTotalMonthly::where('member_id', $member_id)
                ->where('month', $month)
                ->where('year', $year)->select('commission_amount')
                ->first();
        }
        return (($total_amount->commission_amount) ?? 0);
    }
}
if (!function_exists('getTotalCollectionNew2')) {
    function getTotalCollectionNew2($member_id, $year, $month, $company_id)
    {
        if ($company_id > 0) {
            $total_amount = App\Models\CommissionFuleCollection::where('associate_id', $member_id)
                ->where('company_id', $company_id)
                ->where('month', $month)
                ->where('year', $year)->select('qualifying_amount')
                ->get();
        } else {
            $total_amount = App\Models\CommissionFuleCollection::where('associate_id', $member_id)
                ->where('month', $month)
                ->where('year', $year)->select('qualifying_amount')
                ->get();
        }
        return number_format($total_amount->sum('qualifying_amount'), 2, '.', '');
    }
}
if (!function_exists('getheadClosingValue2')) {
    function getheadClosingValue2($start_date, $end_date, $head_id, $company_id, $branchId)
    {
        $data = \App\Models\HeadClosing::where('head_id', $head_id)
            ->where('start_year', $start_date)->where('branch_id', $branchId)
            ->where('status', 1)
            ->where('company_id', $company_id)
            ->where('end_year', $end_date)
            ->first();
        return $data;
    }
}
if (!function_exists('SsbAccountSettingAmount')) {
    function SsbAccountSettingAmount()
    {
        $amount = App\Models\SsbAccountSetting::where('user_type', 2)->where('is_delete', 0)->where('plan_type', 1)->first('amount');
        return $amount;
    }
}
if (!function_exists('getBranchStateByManagerId')) {
    function getBranchStateByManagerId($id)
    {
        $data = App\Models\Branch::where('manager_id', $id)->first('state_id');
        if ($data) {
            return $data->state_id;
        }
    }
}
if (!function_exists('genralsettings')) {
    function genralsettings()
    {
        $charges = App\Models\SystemDefaultSettings::where('delete', '0')->where('status', '1')->pluck('amount', 'head_id');
        return $charges;
    }
}
// created by shahid
if (!function_exists('getMemberCompanySsbAccountDetailNew')) {
    function getMemberCompanySsbAccountDetailNew($memberId, $companyId)
    {
        $data = App\Models\SavingAccount::where('member_id', $memberId)->whereCompanyId($companyId)->first();
        return $data;
    }
}
if (!function_exists('getLoanCodeByCompany')) {
    function getLoanCodeByCompany($type, $companyId)
    {
        $data = App\Models\Loans::select('code')->where('loan_type', $type)->whereStatus('1')->whereCompanyId($companyId)->first();
        return $data->code;
    }
}
/** this function is created by sourab on 31-10-2023 for makeing global daybook */
if (!function_exists('getdaybookreportamounts')) {
    function getdaybookreportamounts($daybookreportglobal)
    {
        $cashInhand = $daybookreportglobal['cash_in_hand'];
        $cashInhandOpening = $daybookreportglobal['cashInhandOpening'];
        $cashInhandclosing = $daybookreportglobal['cashInhandclosing'];
        $cheque = $daybookreportglobal['cheque'];
        $bank = $daybookreportglobal['bank'];
        $data = $daybookreportglobal['data'];
        $end_date = $daybookreportglobal['endDate'];
        $branch_id = $daybookreportglobal['branch_id'];
        $start_date = $daybookreportglobal['startDate'];
        $company_id = $daybookreportglobal['company_id'] ?? 0;
        $getBranchOpening_cash = getBranchOpeningDetail($branch_id);
        $balance_cash = 0;
        $C_balance_cash = 0;
        if ($getBranchOpening_cash->date == $start_date) {
            $balance_cash = $getBranchOpening_cash->total_amount;
        }
        if ($getBranchOpening_cash->date < $start_date) {
            $getBranchTotalBalance_cash = getBranchTotalBalanceAllTran($start_date, $getBranchOpening_cash->date, $getBranchOpening_cash->total_amount, $branch_id, $company_id);
            $new_date = date('Y-m-d', strtotime('-1 day', strtotime(str_replace('/', '-', $start_date))));
            $new_end_date = date('Y-m-d', strtotime('-1 day', strtotime(str_replace('/', '-', $end_date))));
            $getTotal_FileCharge1 = getTotalFileCharge($new_date, $new_end_date, $branch_id);
            $balance_cash = $getBranchTotalBalance_cash;
        }
        $getTotal_DR = getBranchTotalBalanceAllTranDR($start_date, $end_date, $branch_id, $company_id);
        $getTotal_CR = getBranchTotalBalanceAllTranCR($start_date, $end_date, $branch_id, $company_id);
        $getTotal_FileCharge = getTotalFileCharge($start_date, $end_date, $branch_id);
        $totalBalance = $getTotal_CR - $getTotal_DR;
        $C_balance_cash = $balance_cash + $totalBalance;
        $data = [
            'cashInhand' => $cashInhand,
            'balance_cash' => $balance_cash,
            'C_balance_cash' => $C_balance_cash,
        ];
        return $data;
    }
}
if (!function_exists('get_plan_type_money_back')) {
    function get_plan_type_money_back($id, $companyId)
    {
        $data = \App\Models\Plans::whereId($id)
            ->whereCompanyId($companyId)
            ->where('plan_sub_category_code', 'B')
            ->exists();
        return $data;
    }
}
if (!function_exists('get_plan_type_monthly_income')) {
    function get_plan_type_monthly_income($id, $companyId)
    {
        $data = \App\Models\Plans::whereId($id)
            ->whereCompanyId($companyId)
            ->where('plan_sub_category_code', 'I')
            ->exists();
        return $data;
    }
}
if (!function_exists('getPlanDetailCheck')) {
    function getPlanDetailCheck($planId, $companyId = NULL)
    {
        $data = App\Models\Plans::withoutGlobalScope(ActiveScope::class)->Where('id', $planId)
            ->when($companyId != null, function ($q) use ($companyId) {
                $q->Where('company_id', $companyId);
            })->first();
        return $data;
    }
}
if (!function_exists('getUpcomingYears')) {
    function getUpcomingYears($numYears = 5)
    {
        $currentYear = date('Y');
        $years = [];

        for ($i = 0; $i < $numYears; $i++) {
            $years[] = $currentYear + $i;
        }

        return $years;
    }
}
/** this helper created by sourab on 16-11-2023 for getting upcomming year */
if (!function_exists('nextyear')) {
    function nextyear()
    {
        $date = date('Y');
        return date('Y', strtotime('+1 year', strtotime($date)));
    }
}
/** this helper function created by sourab on 17-11-2023 for  getting sum amount of  */
if (!function_exists('last12monthdeposit')) {
    function last12monthdeposit($account_no, $start, $end)
    {
        $start = date('Y-m-d', strtotime($start));
        $end = date('Y-m-d', strtotime($end));
        $amount = Daybook::where('account_no', $account_no)
            ->whereBetween('created_at', [$start, $end])
            ->sum('deposit');
        // dd($account_no,$start,$end,$amount);
        return $amount;
    }
}
if (!function_exists('money_back_count')) {
    function money_back_count($id)
    {
        $data = InvestmentMonthlyYearlyInterestDeposits::whereInvestmentId($id)
            ->get('date', 'id')
            ->groupBy('date')
            ->count('id'); // count of id
        return $data;
    }
}
/** to get member investment details by using account number */
if (!function_exists('getMemberInvestmentDetails')) {
    function getMemberInvestmentDetails($account_number, $company_id = null)
    {
        $data = App\Models\Memberinvestments::with('plan')
            ->where('account_number', $account_number)
            ->when(!empty($company_id), function ($query) use ($company_id) {
                $query->where('company_id', $company_id);
            })
            ->first();
        return $data;
    }
}
/**By mahesh on 22-dec-2023 */
/**
 * Check the user is exist or not in a company
 * @param array $requestedData
 * @return boolean
 */
if (!function_exists('checkNewUser')) {
    function checkNewUser(array $requestedData)
    {
        $newUser = MemberCompany::where('customer_id', $requestedData['customer_id'])->whereCompanyId($requestedData['company_id'])->exists();
        return $newUser;
    }
}
/**By mahesh on 22-dec-2023 */
/**
 * Convert to 2 decimal format
 * @param $amount
 * @return string
 */
if (!function_exists('convertToDecimal')) {
    function convertToDecimal($amount)
    {
        return number_format((float) $amount, 2, '.', '');
    }
}
if (!function_exists('getplanheaddyanmic')) {
    function getplanheaddyanmic($ssbId, $companyId)
    {
        $ssbaccountdetails = App\Models\SavingAccount::where('id', $ssbId)
            ->orWhere('account_no', $ssbId)
            ->first();

        $getPlans = App\Models\Memberinvestments::whereId($ssbaccountdetails->member_investments_id)->first()->plan_id;
        $planDetailDR = getPlanDetail($getPlans, $companyId);
        $head4InvestDR = $planDetailDR->deposit_head_id;
        return $head4InvestDR;
    }
}
if (!function_exists('calculateAge')) {
    function calculateAge($dob)
    {
        $dobObj = new DateTime($dob);
        $today = new DateTime();
        $interval = $dobObj->diff($today);
        return $interval->y;
    }
}
if (!function_exists('get_cron_name')) {
    function get_cron_name($uuid)
    {
        $cronName = App\Models\CronLog::where('uuid', $uuid)->value('cron_name');
        return $cronName;
    }
}

if (!function_exists('get_cron_tag')) {
    function get_cron_tag($uuid)
    {
        $cron = App\Models\CronLog::where('uuid', $uuid)->value('status');
        // dd($cron); // 1=>'start',2=>'inprogress',3=>'completed',4=>'Failed'
        return in_array($cron, [3, 4]) ? 1 : 0;
    }
}

if (!function_exists('head_create_logs')) {
    function head_create_logs($headId, $companyId, $parentID, $type, $headName, $parentHeadname, $selectedCompanies = null, $new = null, $parentheadid = null, $newhead = null, $systemdate = null)
    {


        //    $systemdate = Carbon::parse($systemdate)->setTime(now()->hour, now()->minute, now()->second);

        // Fetch the AccountHead once
        $value = App\Models\AccountHeads::where('head_id', $headId)->first();
        $globaldate = Session::get('created_at');
        $currentDateTime = Carbon::now();
        $systemdate = Carbon::createFromFormat('d/m/Y', $systemdate)->format('Y-m-d H:i:s');

        // Create the log data
        $logData = [
            'head_id' => $headId,
            'company_id' => $companyId,
            'parent_id' => $parentID,
            'type' => $type,
            'old_value' => json_encode($value->toArray()),
            'new_value' => json_encode($value->toArray()),
            'created_by' => Auth::user()->username,
            'created_at' => $systemdate,
            'updated_at' => $systemdate,


        ];
        $data = json_encode($value->toArray());
        $data = json_decode($data, true);
        // dd($data['parent_id']);
        // Extract company names
        $companyIds = explode(',', $selectedCompanies);
        $companies = App\Models\Companies::whereIn('id', $companyIds)->get();
        $companyNames = $companies->pluck('name')->implode(', ');

        // Set description based on the type
        if ($type == 1) {
            $logData['description'] = "$headName created by " . Auth::user()->username . " under $parentHeadname head";
        } elseif ($type == 2) {
            $logData['description'] = "$headName assigned in companies [ $companyNames ] by " . Auth::user()->username . " under $parentHeadname head";
        } elseif ($type == 3) {
            $logData['description'] = "$headName Grouped with $parentHeadname by " . Auth::user()->username;
            // $logData['old_value'] = json_encode($value->toArray());
            // $logData['new_value'] = json_encode($value->toArray());
        } elseif ($type == 4) {

            $logData['description'] = "$headName edited by " . Auth::user()->username . " to " . $newhead;

        }

        // Check if a log entry already exists
        $logDetail = App\Models\HeadLog::where('head_id', $headId)->where('type', $type)->latest()->get();

        // Update or create the log entry

        // Create a new log entry


        App\Models\HeadLog::create($logData);

    }
}


function calculateAge($dob)
{
    // Create DateTime objects for the date of birth and today
    $dobObj = new DateTime($dob);
    $today = new DateTime();

    // Calculate the interval between the dates
    $interval = $dobObj->diff($today);

    // Return the years from the interval
    return $interval->y;
}

// Created by shahid for loan listing

if (!function_exists('getLoanTypeByCompany')) {
    function getLoanTypeByCompany($type, $companyId)
    {
        $data = App\Models\Loans::select('code', 'loan_category')->where('loan_type', $type)->whereStatus('1')->whereCompanyId($companyId)->first();
        return $data;
    }
}

if (!function_exists('cheque_logs')) {
    function cheque_logs($type, $receivedChequeTableId, $title, $description, $new_value = null, $old_value = null, $status, $day_ref_id, $created_by, $created_by_id, $created_at)
    {
        $n = [];
        $o = [];
        $nv = json_decode($new_value);
        $ov = json_decode($old_value);
        if ($nv) {
            foreach ($nv as $key => $value) {
                if ($value !== $ov->$key) {
                    $n[$key] = $value;
                }
            }
        }
        if ($nv) {
            foreach ($nv as $key => $value) {
                if ($value !== $ov->$key) {
                    $o[$key] = $ov->$key;
                }
            }
        }
        $data = [
            'type' => $type,
            'type_id' => $receivedChequeTableId,
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'new_value' => json_encode($n),
            'old_value' => json_encode($o),
            'day_ref_id' => $day_ref_id,
            'created_by' => Auth::user()->role_id == 3 ? '2' : '1',
            'created_by_id' => Auth::user()->id,
            'created_at' => date('Y-m-d H:i:s', strtotime(convertdate($created_at))),
        ];
        return App\Models\ChequeLog::insertGetId($data);
    }
}
if (!function_exists('get_cron_name')) {
    function get_cron_name($uuid)
    {
        $cronName = App\Models\CronLog::where('uuid', $uuid)->value('cron_name');
        return $cronName;
    }
}

if (!function_exists('get_cron_tag')) {
    function get_cron_tag($uuid)
    {
        $cron = App\Models\CronLog::where('uuid', $uuid)->value('status');
        // dd($cron); // 1=>'start',2=>'inprogress',3=>'completed',4=>'Failed'
        return in_array($cron, [3, 4]) ? 1 : 0;
    }
}
if (!function_exists('getModelInstance')) {
    function getModelInstance($model)
    {
        $modelClass = '\App\Models\\' . $model;
        return new $modelClass;
    }
}

if (!function_exists('planLogDetails')) {
    function planLogDetails($type, $type_id, $tenure_id, $title, $description, $old_data, $new_data, $created_by, $created_by_id)
    {
        $data['type'] = $type;
        $data['type_id'] = $type_id;
        $data['tenure_id'] = $tenure_id;
        $data['title'] = $title;
        $data['description'] = $description;
        $data['old_data'] = ($old_data != '') ? $old_data : null;
        $data['new_data'] = ($new_data != '') ? $new_data : null;
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        return App\Models\PlanLogDetails::create($data);
    }
}
if (!function_exists('getMemberFullName')) {
    function getMemberFullName($id)
    {
        $data = App\Models\Member::whereId($id)->first();
        return ($data->first_name . ' ' . ($data->last_name ?? ''));
    }
}

if (!function_exists('getMemberCurrentRunningLoan')) {
    function getMemberCurrentRunningLoan($customer_id, $type = true, $aNumber)
    {
        if (getMemberLoanCount($customer_id, $type) >= 2) {
            // $type is true for member_loan and false means group_loan
            $model = $type == true ? \App\Models\Memberloans::query() : \App\Models\Grouploans::query();

            $data = $model->whereCustomerId($customer_id)
                ->when($aNumber != null, function ($query) use ($aNumber) {
                    $query->where('account_number', '!=', $aNumber);
                })
                ->where(function ($query) use ($type) {
                    $query
                        ->where('status', '=', 4);
                    if ($type) {
                        $query->where('loan_type', '!=', 3);
                    }
                })->orderByDesc('id')
                ->value('account_number');
            $account_number = $data;
        } else {
            $account_number = 'N/A';
        }
        return $account_number;
    }
}
if (!function_exists('getMemberCurrentRunningClosingAmount')) {
    function getMemberCurrentRunningClosingAmount($customer_id, $type = true, $aNumber)
    {
        // $type is true for member_loan and false means group_loan
        $model = $type == true ? \App\Models\Memberloans::query() : \App\Models\Grouploans::query();
        $data = $model->whereCustomerId($customer_id)
            ->when($aNumber != null, function ($query) use ($aNumber) {
                $query->where('account_number', '!=', $aNumber);
            })
            ->where(function ($query) use ($type) {
                $query->where('status', '=', 4);
                if ($type) {
                    $query->where('loan_type', '!=', 3);
                }
            })->orderByDesc('id')
            ->first();
        if ($data && (getMemberLoanCount($customer_id, $type) >= 2)) {
            $stateId = getBranchStateByManagerId($data->branch_id);
            $d = loan_closing_amount($data->id, $data->account_number, $data->approve_date, $stateId, $data->emi_option, $data->emi_amount, $data->closing_date) ?? 0;
        } else {
            $d = 'N/A';
        }
        return $d;
    }
}
if (!function_exists('getMemberLoanCount')) {
    function getMemberLoanCount($customer_id, $type = true)
    {
        $model = $type == true ? \App\Models\Memberloans::query() : \App\Models\Grouploans::query();
        $data = $model->whereCustomerId($customer_id)->where(function ($query) use ($type) {
            $query->where('status', '=', 4);
            if ($type) {
                $query->where('loan_type', '!=', 3);
            }
        })->orderByDesc('id')->get();
        return $data->count('id');
    }
}
// if (!function_exists('smsStatus')) {
//     function smsStatus()
//     {
//         $smsStatus = SmsSetting::latest('id')->value('status');
//         return $smsStatus;
//     }
// }
if (!function_exists('accountNumberToMember')) {
    function accountNumberToMember($accountNUmber, $type)
    {
        if ($type == 'L') {
            $data = \App\Models\Memberloans::select('applicant_id');
        } else {
            $data = \App\Models\Grouploans::select('member_id');
        }
        $data->where('account_number', $accountNUmber)->first();
    }
}
if (!function_exists('getPlanCategoryCodeById')) {
    function getPlanCategoryCodeById($id)
    {
        $data = \App\Models\Plans::whereId($id)->value('plan_category_code');
        return $data;
    }
}
if (!function_exists('AllHeadTransaction_delete')) {
    function AllHeadTransaction_delete($daybook_ref_id, $type_id = null, $type_transaction_id = null)
    {
        $all_head_delete = App\Models\AllHeadTransaction::whereIn('daybook_ref_id', $daybook_ref_id);
        if (isset($type_id) && $type_id != null) {
            $all_head_delete->where('type_id', $type_id);
        }
        if (isset($type_transaction_id) && $type_transaction_id != null) {
            $all_head_delete->where('type_transaction_id', $type_transaction_id);
        }
        $all_head_delete->update(['is_deleted' => 1]);
        return $all_head_delete;
    }
}
if (!function_exists('BranchDaybook_delete')) {
    function BranchDaybook_delete($daybook_ref_id, $type_id = null, $type_transaction_id = null)
    {
        $BranchDaybook = App\Models\BranchDaybook::whereIn('daybook_ref_id', $daybook_ref_id);
        if (isset($type_id) && $type_id != null) {
            $BranchDaybook->where('type_id', $type_id);
        }
        if (isset($type_transaction_id) && $type_transaction_id != null) {
            $BranchDaybook->where('type_transaction_id', $type_transaction_id);
        }
        $BranchDaybook->update(['is_deleted' => 1]);
        return $BranchDaybook;
    }
}
if (!function_exists('SamraddhBankDaybook_delete')) {
    function SamraddhBankDaybook_delete($daybook_ref_id, $type_id = null, $type_transaction_id = null)
    {
        $SamraddhBankDaybook = App\Models\SamraddhBankDaybook::whereIn('daybook_ref_id', $daybook_ref_id);

        if (isset($type_id) && $type_id != null) {
            $SamraddhBankDaybook = $SamraddhBankDaybook->where('type_id', $type_id);
        }

        if (isset($type_transaction_id) && $type_transaction_id != null) {
            $SamraddhBankDaybook = $SamraddhBankDaybook->where('type_transaction_id', $type_transaction_id);
        }

        $cheque = $SamraddhBankDaybook->pluck('cheque_id');

        $SamraddhBankDaybook->update(['is_deleted' => 1]);

        if (!empty($cheque)) {
            foreach ($cheque as $chq) {
                $getRecord = App\Models\SamraddhCheque::whereId($chq)->update(['is_use' => 0, 'status' => 1]);
            }
        }
        return $SamraddhBankDaybook;
    }
}
if (!function_exists('SavingAccountTranscation_delete')) {
    function SavingAccountTranscation_delete($daybook_ref_id)
    {
        $SavingAccountTranscation = App\Models\SavingAccountTranscation::whereIn('daybook_ref_id', $daybook_ref_id);
        $SavingAccountTranscation->update(['is_deleted' => 1]);
        return $SavingAccountTranscation;
    }
}
if (!function_exists('getSamraddhBankDaybook')) {
    function getSamraddhBankDaybook($daybook_ref_id)
    {
        $samraddhBankDaybook = App\Models\SamraddhBankDaybook::where('daybook_ref_id', $daybook_ref_id)->first();
        return $samraddhBankDaybook;
    }
}
if (!function_exists('getBranchdaybookData')) {
    function getBranchdaybookData($daybook_ref_id)
    {
        $branchDaybook = App\Models\BranchDaybook::where('daybook_ref_id', $daybook_ref_id)->where('sub_type', 51)->value('transction_no');
        return $branchDaybook;
    }
}
if (!function_exists('jsonArrayConverion')) {
    function jsonArrayConverion($old_value, $new_value)
    {
        $nc = json_encode($new_value);
        $oc = json_encode($old_value);
        $n = [];
        $o = [];
        $nv = json_decode($nc);
        $ov = json_decode($oc);
        if ($nv) {
            foreach ($nv as $key => $value) {
                if ($value !== $ov->$key) {
                    $n[$key] = $value;
                }
            }
        }
        if ($nv) {
            foreach ($nv as $key => $value) {
                if ($value !== $ov->$key) {
                    $o[$key] = $ov->$key;
                }
            }
        }
        $return = ['n' => json_encode($n), 'o' => json_encode($o)];
        return $return;
    }
}
if (!function_exists('accountListAllBank')) {
    function accountListAllBank($bank_id)
    {
        $account = App\Models\SamraddhBankAccount::whereBankId($bank_id)->get(['id', 'account_no', 'ifsc_code', 'bank_id', 'branch_name']);
        return $account;
    }
}
if (!function_exists('getMemberNameByMemberInvestmentAutoId')) {
    function getMemberNameByMemberInvestmentAutoId($id)
    {
        $Memberinvestments = App\Models\Memberinvestments::whereId($id)->with('member')->first();
        $m = $Memberinvestments->member;
        $f = $m->first_name;
        $l = $m->last_name??'';
      return $m ? trim("$f $l") : '';
    }
}