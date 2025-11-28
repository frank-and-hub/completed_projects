<?php
namespace App\Http\Controllers\Api\AssociateRegistration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use DB;
use App\Services\ImageUpload;
class RegistrationDetailController extends Controller
{
    /**
     * Summary of __construct
     */
    public function __construct(Request $request)
    {
        $this->associateNo = $request->associate_no; 
    }

    /**
     * Summary of getOccupation and other details
     * 
     * get all occupation list
     * @param Request $req
     * Occupation,Categories,Relationship,ID Proof Document Type,State
     * @return void
     */

    public function getOccupation(Request $req)
    {
        $associateNo = $this->associateNo ;
        try{
            $memberDetail =  \App\Models\Member::select('id','associate_app_status','branch_id')->where('associate_no',$associateNo)->where('associate_status',1)->where('is_block',0)->first();
          
            if($memberDetail)
            {
                $token = md5($associateNo);
                $data = array();
               
                if($token == $req->token) 
                {
                    
                    $data['occupations']  = \App\Models\Occupation::data()->get(['id','name']);
                    $data['religions'] = \App\Models\Religion::data()->get(['id','name']);
                    $data['categories'] = \App\Models\SpecialCategory::data()->get(['id','name']);
                    $data['relations'] = \App\Models\Relations::get(['id','name']);
                    $data['idProoffs'] = \App\Models\IdType::data()->get(['id','name']);
                    $data['states'] = \App\Models\State::data()->get(['id','name']);
                    $data['plans'] = \App\Models\Plans::data()->get(['id','name','slug']);
                    $branchName = $memberDetail->branch->name;
                    $branchId = $memberDetail->branch->id;
                    $globalDate = headerMonthAvailability(date('d'), date('m'), date('Y'), $memberDetail->branch->state_id);
                    $date = date('Y-m-d', strtotime(convertDate($globalDate)));
                    $formNo = random_int(0000, 9999);

                    $status   = "Success";

                    $code     = 200;

                    $message = 'Details listing!';

                    $result   =$data;

                    $associate_status=$memberDetail->associate_app_status;
                }
                else{
                    $status = "Error";

                    $code = 201;

                    $message = 'API token mismatch!';

                    $result = '';

                    $branchName = '';
                    $branchId ='';

                    $associate_status=$memberDetail->associate_app_status;
                }
                   
            }
            else{
                $status = "Error";

                $code = 201;

                $message = 'Something went wrong!';

                $result = '';

                $associate_status=9;

                $branchName = '';
                
                $branchId ='';
            }
            DB::Commit();

        }
        catch(Exception $ex)
        {
            $status = "Error";
            $code = 500;
            $message = $ex->getMessage();
            $result = '';
            $associate_status = 9;
            $branchName = '';
            $branchId ='';

        }

        return response()->json(compact('status', 'code', 'message', 'result', 'associate_status','branchId','branchName','date','formNo'), $code);
    }

    /**
     * Summary of getStateDetail
     * get state and district details based on id
     * @param Request $req
     * @return mixed
     */

    public function getStateDetail(Request $req)
    {
        $associateNo = $this->associateNo ;
        try{
            $memberDetail =  \App\Models\Member::select('id','associate_app_status')->where('associate_no',$associateNo)->where('associate_status',1)->where('is_block',0)->first();
          
            if($memberDetail)
            {
                $token = md5($associateNo);
                $data = array();
               
                if($token == $req->token) 
                {
                    $record = (isset($req->state_id)) ? \App\Models\District::data($req->state_id) : \App\Models\City::data($req->district_id);

                    $data= $record->get(['id','name']);
                    $status   = "Success";

                    $code     = 200;

                    $message = 'Branch listing!';

                    $result   =  $data;

                    $associate_status=$memberDetail->associate_app_status;
                }
                else{
                    $status = "Error";

                    $code = 201;

                    $message = 'API token mismatch!';

                    $result = '';

                    $associate_status=$memberDetail->associate_app_status;
                }
                   
            }
            else{
                $status = "Error";

                $code = 201;

                $message = 'Something went wrong!';

                $result = '';

                $associate_status=9;
            }
            DB::Commit();

        }
        catch(Exception $ex)
        {
            $status = "Error";
            $code = 500;
            $message = $ex->getMessage();
            $result = '';
            $associate_status = 9;

        }

        return response()->json(compact('status', 'code', 'message', 'result', 'associate_status'), $code);
    }

    public function registerMember(Request $request)
    {
        $associateNo = $this->associateNo ;

        try{
            $memberDetail =  \App\Models\Member::select('id','associate_app_status')->where('associate_no',$associateNo)->where('associate_status',1)->where('is_block',0)->first();
          
            if($memberDetail)
            {
                $token = md5($associateNo);
                $data = array();
               
                if($token == $request->token) 
                {
                    $getfaCode=getFaCode(1);
                    $faCode=$getfaCode->code;
                    $branch_id=$request->branch_id;
                    $getMiCode=getLastMiCode(5,$branch_id,$faCode);
                    if(!empty($getMiCode))
                    {
                        if($getMiCode->mi_code==9999998)
                        {
                            $miCodeAdd=$getMiCode->mi_code+2;
                        }
                        else
                        {
                           $miCodeAdd=$getMiCode->mi_code+1; 
                        }
                    }
                    else
                    {
                       $miCodeAdd=1; 
                    }
                    $miCode=str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                    $getBranchCode=getBranchCode($branch_id);
                    $branchCode=$getBranchCode->branch_code;
                    // genarate Member id 
                    $getmemberID=$branchCode.$faCode.$miCode;
                    $branchMi=$branchCode.$miCode;
                   
                   
                    $stepOneData = [
                        'form_no' => $request->form_no,
                        'entry_date' => $request->application_date,
                        'branch_id' => $branch_id,
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'email' => $request->email,
                        'mobile_no' => $request->mobile_no,
                        'dob' => $request->dob,
                        'gender' => $request->gender,
                        'occupation_id' => $request->occupation_id,
                        'annual_income' => $request->annual_income,
                        'mother_name' => $request->mother_name,
                        'father_husband' => $request->father_husband,
                        'marital_status' => $request->marital_status,
                        'anniversary_date' => $request->anniversary_date,
                        'religion_id' => $request->religion_id,
                        'special_category_id' => $request->special_category_id,
                        'role_id' => 5,
                        'member_id' =>$getmemberID,
                        'mi_code'=> $miCode,
                        'fa_code'=> $faCode,
                        'branch_code'=> $branchCode,
                        'branch_mi'=> $branchMi,
                        'created_at'=> $request->created_at,

                     
                    ];

                  
                    //Step Two Start
                    $bankDetails = [
                        'bank_name' => $request->bank_name,
                        'branch_name' => $request->bank_branch_name,
                        'account_no' => $request->bank_account_no,
                        'ifsc_code' => $request->bank_ifsc,
                        'address' => $request->bank_branch_address,
                    ];

                    $bankInfoSave=\App\Models\MemberBankDetail::create($bankDetails);

                    $NomineeDetails = [
                        'name' => $request->nominee_first_name,
                        'relation' => $request->nominee_relation,
                        'gender' => $request->nominee_gender,
                        'dob' => $request->nominee_dob,
                        'age' => $request->nominee_age,
                        'mobile_no' => $request->nominee_mobile_no,
                        'is_minor' => (isset($request->is_minor) && !empty($request->is_minor)) ? $request->is_minor : 0,
                        'parent_name' => (isset( $request->parent_nominee_name)) ?  $request->parent_nominee_name : NULL,
                        'parent_no' =>  (isset( $request->parent_nominee_mobile_no)) ?  $request->parent_nominee_mobile_no : NULL,
                    ];

                    $nomineeInfoSave= \App\Models\MemberNominee::create($NomineeDetails);
                    //Step Two End
                    //Step Three Start
                    $stepOneData = [
                        'address' => $request->address,
                        'state_id' => $request->state_id,
                        'district_id' => $request->district_id,
                        'city_id' => $request->city_id,
                        'village' => $request->village,
                        'pin_code' => $request->pin_code,
                    ];
                    $member = Member::create($stepOneData);

                    $idProffDetail = [
                        'first_id_type_id' => $request->first_id_type_id,
                        'first_id_no' => $request->first_id_no,
                        'first_address' => $request->first_address, 
                    ];

                   

                    $idProffDetail = [
                        'second_id_type_id' => $request->second_id_type_id,
                        'second_id_no' => $request->second_id_no,
                        'second_address' => $request->second_address,
                    ];

                    $idProofInfoSave= \App\Models\MemberIdProof::create($idProffDetail); 
                     //Step Three End 

                    //Step Four Start 
                  
                    if ($request->hasFile('signature')) {
                        
                        $signature_image = $request->file('signature');
                        $signature_filename = $member->id.'_'.time().'.'.$signature_image->getClientOriginalExtension();
                        $signature_location = 'asset/profile/member_signature/' . $signature_filename;
                       $return = \App\Models\Image::make($signature_image)->resize(300,300)->save($signature_location);
                       if ( $return ) {
                        $action = 'success';
                        $message ='Signature uploaded Successfully!';
                       } else {
                           $action = 'alert';
                           $message ='Signature not uploaded Successfully!';
                       }
                    }
                    if ($request->hasFile('photo')) {
                       
                        $photo_image = $request->file('photo');
                        $photo_filename = $member->id.'_'.time().'.'.$photo_image->getClientOriginalExtension();
                        $photo_location = 'asset/profile/member_avatar/' . $photo_filename;
                        // $return = \App\Models\Image::make($photo_image)->resize(300,300)->save($photo_location);
                        $mainFolderPhoto = '/profile/member_avatar/';
                        $return = ImageUpload::upload($photo_image, $mainFolderPhoto, $photo_filename);
                        if ( $return ) {
                            $action = 'success';
                            $message ='Photo uploaded Successfully!';
                        } else {
                            $action = 'alert';
                            $message ='Photo not uploaded Successfully!';
                        }
                    }
                    $memberUpdate = \App\Models\Member::find($member->id);
                    if ( $signature_filename && $signature_filename != '' ) {
                        $memberUpdate->signature=$signature_filename;
                    }
                    if ( $photo_filename && $photo_filename != '') {
                        $memberUpdate->photo=$photo_filename;
                    }
                    if ( $memberUpdate->signature != '' && $memberUpdate->photo != '' ){
                        $memberUpdate->is_block=0;
                    }
                    $memberUpdate->created_at=$request->created_at;
                    $memberUpdate->save();
                    //Step Four End

                    
                    $status   = "Success";

                    $code     = 200;

                    $message = 'Member Registered!';

                    $result   = ['data' => $data];

                    $associate_status=$memberDetail->associate_app_status;
                }
                else{
                    $status = "Error";

                    $code = 201;

                    $message = 'API token mismatch!';

                    $result = '';

                    $associate_status=$memberDetail->associate_app_status;
                }
                   
            }
            else{
                $status = "Error";

                $code = 201;

                $message = 'Something went wrong!';

                $result = '';

                $associate_status=9;
            }
            DB::Commit();

        }
        catch(Exception $ex)
        {
            $status = "Error";
            $code = 500;
            $message = $ex->getMessage();
            $result = '';
            $associate_status = 9;

        }

        return response()->json(compact('status', 'code', 'message', 'result', 'associate_status'), $code);

    }

    /**
     * Summary of getNomineesDetails
     * @param Request $request
     * @return mixed
     */

    public function getNomineesDetails(Request $request)
    {
        $associateNo = $this->associateNo ;
        $memberId = $request->member_id;
        try{
            $memberDetail =  \App\Models\Member::select('id','associate_app_status')->where('associate_no',$associateNo)->where('associate_status',1)->where('is_block',0)->first();
          
            if($memberDetail)
            {
                $token = md5($associateNo);
                $data = array();
               
                if($token == $req->token) 
                {
                    $data['nomineeDetail'] = \App\Models\MemberNominee::where('member_id', $memberId)->first();

                    $status   = "Success";

                    $code     = 200;

                    $message = 'Nominee Detail!';

                    $result   = ['data' => $data];

                    $associate_status=$memberDetail->associate_app_status;
                }
                else{
                    $status = "Error";

                    $code = 201;

                    $message = 'API token mismatch!';

                    $result = '';

                    $associate_status=$memberDetail->associate_app_status;
                }
                   
            }
            else{
                $status = "Error";

                $code = 201;

                $message = 'Something went wrong!';

                $result = '';

                $associate_status=9;
            }
            DB::Commit();

        }
        catch(Exception $ex)
        {
            $status = "Error";
            $code = 500;
            $message = $ex->getMessage();
            $result = '';
            $associate_status = 9;

        }

        return response()->json(compact('status', 'code', 'message', 'result', 'associate_status'), $code);     
    }


}