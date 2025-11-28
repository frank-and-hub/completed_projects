<?php 
namespace App\Http\Controllers\Admin; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use Mail;
/*
    |---------------------------------------------------------------------------
    | Admin Panel -- CommanController
    |--------------------------------------------------------------------------
    |
    | This controller handles all functions which call multiple times .
*/
class CommanCommissionController extends Controller
{
	/**
     * Create a new controller instance.
     * @return void
     */
	public function __construct()
    {
    	// check user login or not
    //	$this->middleware('auth');
    }
    private static $commissionDistributeForInvestmentRenew = '';
    private static $associateParent = ''; 
    private static $associateParentInvestment = ''; 
    private static $commissionDistributeInvestmentRenew = ''; 
/*---------------- Investment Renew Commission Start----------------------------*/  

    
     
   /**
     *  associte detail get or distribute commission 
     *
     * @param   (associate_id,type_id,type,total_amount,month,plan_id,branch_id)
     * @return \Illuminate\Http\Response
     */
    public static  function commissionDistributeInvestmentRenew($company_id,$associate_id,$month,$plan_id,$tenure,$branchId,$investmentId,$total_amount,$leadgerMonth,$leadgerYear,$final_total_amount)
    {
        $array = array();
        $firtsAssociate=associateTreeChain($associate_id); 
        $associate_carder=$firtsAssociate->carder; 
        if($associate_carder>0)
        {
            $x=$associate_carder;
            $associateId = $associate_id;
            $carderFrom = 1;
            $carderTo = $associate_carder;
            $monthcarder=$month;
            if($month>$tenure && $plan_id==7)
            {
                if(($tenure+1)==$month)
                {
                    $monthcarder=$tenure;
                }
            }

            $data=\App\Models\CommissionDetail::where('plan_id',$plan_id)->where('tenure',$tenure)->where('carder_id','>',0)->where('carder_id','<=',$associate_carder)->whereRaw('? between tenure_to and tenure_from', [$monthcarder])->where('status',1)->sum('associate_per');
            $commissionPer = $data;
            static::commissionDistributeForInvestmentRenew($company_id,$associateId,$branchId,$investmentId,$total_amount,$month,$commissionPer,$carderFrom,$carderTo,$leadgerMonth,$leadgerYear,$final_total_amount);
            
        }
        if($associate_carder<16)
        {
         static::associateParentInvestmentRenew($company_id,$firtsAssociate->senior_id,$month,$plan_id,$tenure,$associate_carder,$branchId,$investmentId,$total_amount,$leadgerMonth,$leadgerYear,$final_total_amount,$array);
        }
    }


     /**
     *  get associate parent list or distribute  commission to all
     * @param   
     * @return \Illuminate\Http\Response
     */
    public static function associateParentInvestmentRenew($company_id,$member_id,$month,$plan_id,$tenure,$carder,$branchId,$investmentId,$total_amount,$leadgerMonth,$leadgerYear,$final_total_amount,$array,$c='') 
    {
        $parent=associateTreeChainActiveGet($member_id); 
        if($parent->carder>$carder && $parent->carder<16)
        {            $c.=$parent->carder.',';     
            $aso_carder=$parent->carder;
            $x=explode(",",$c); 
            $total_carder=count($x);
            $y=$x[$total_carder-2]-$carder; 
            if($y>0)
            {                
                $associateId = $parent->member_id;
                $carderFrom= $carder+1;
                $carderTo = $parent->carder;
                $monthcarder=$month;
                if($month>$tenure && $plan_id==7)
                {
                    if(($tenure+1)==$month)
                    {
                        $monthcarder=$tenure;
                    }
                }
                $data=\App\Models\CommissionDetail::where('plan_id',$plan_id)->where('tenure',$tenure)->where('carder_id','>',$carder)->where('carder_id','<=',$aso_carder)->whereRaw('? between tenure_to and tenure_from', [$monthcarder])->where('status',1)->sum('associate_per');
                $commissionPer= $data;               
                
                static::commissionDistributeForInvestmentRenew($company_id,$associateId,$branchId,$investmentId,$total_amount,$month,$commissionPer,$carderFrom,$carderTo,$leadgerMonth,$leadgerYear,$final_total_amount);
                
                
                
                
                 

            }
        }
        

        
        if($parent->senior_id>0)
        {
            //static::associateParentInvestmentRenew($parent->senior_id,$month,$plan_id,$tenure,$parent->carder,$array,$c);
            static::associateParentInvestmentRenew($company_id,$parent->senior_id,$month,$plan_id,$tenure,$parent->carder,$branchId,$investmentId,$total_amount,$leadgerMonth,$leadgerYear,$final_total_amount,$array);
        }
        
      
    }


    public static function  commissionDistributeForInvestmentRenew($company_id,$associateId,$branchId,$investmentId,$total_amount,$month,$percentage,$cadre_from,$cadre_to,$leadgerMonth,$leadgerYear,$final_total_amount)
    {
        $percentage = $percentage;
             
        if($percentage>0)
        {
            $cadre_from = $cadre_from;
            $cadre_to = $cadre_to;
            $total_amount = $total_amount;
            $associate_id = $associateId;
            $branch_id = $branchId;
            $type_id=$investmentId;
            $percentInDecimal = $percentage / 100;
            $commission_amount = round($percentInDecimal * $total_amount, 4); 
            $month=$month;
            $type=1;        
            $sub_type=1;

            $associateCommission['assocaite_id'] = $associate_id;
            $associateCommission['type'] = $type;
            $associateCommission['sub_type'] = $sub_type;
            $associateCommission['type_id'] = $type_id;
            $associateCommission['month'] = $month;
            $associateCommission['total_amount'] = $final_total_amount;
            $associateCommission['qualifying_amount'] = $total_amount;
            $associateCommission['commission_amount'] = $commission_amount;
            $associateCommission['percentage'] = $percentage;
            $associateCommission['cadre_from'] = $cadre_from;
            $associateCommission['cadre_to'] = $cadre_to;
            $associateCommission['commission_for_month'] = $leadgerMonth;
            $associateCommission['commission_for_year'] = $leadgerYear;
            $associateCommission['type_id_branch'] = $branch_id;
            $associateCommission['created_by'] = 1;                               
            $associateCommission['created_at'] = date("Y-m-d h:i:s");
            $associateCommission['created_by_id'] = 1; 
            $associateCommission['company_id'] = $company_id; 
        // print_r($associateCommission);die; 
        
            $associateCommissionInsert = \App\Models\AssociateMonthlyCommission::create($associateCommission); 
        }
        else{
            \Log::channel('commissioninvestment')->info('MemberId = '.$associateId.' InvestmentID = '.$investmentId.' totalAmount = '.$total_amount.' monthQualifying = '.$month.' percentage = '.$percentage.' msg= commission not genarate '); 
        }
        

    }








    /*************************************************** */
    /**
     *  create branch day book refresh transaction
     *
     * @param  $amount,$entry_date,$entry_time,$created_at
     * @return \Illuminate\Http\Response
     */
    public static function createBranchDayBookReferenceNew($amount, $created_at)
    {
        $data['amount'] = $amount;
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($created_at)));
        $data['entry_time'] = date("H:i:s", strtotime(convertDate($created_at)));
        $data['created_at'] = date("Y-m-d", strtotime(convertDate($created_at)));
        $data['updated_at'] = date("Y-m-d", strtotime(convertDate($created_at)));
        $transcation = \App\Models\BranchDaybookReference::create($data);
        return $transcation->id;
    }




    /**
     *  Head New table entry
     *
     * @param
     * @return \Illuminate\Http\Response
     */
    public static function headTransactionCreate($daybook_ref_id, $branch_id,  $head_id, $type, $sub_type, $type_id, $associate_id, $member_id,  $amount,  $description, $payment_type, $payment_mode, $currency_code, $v_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id,$company_id)
    {     

        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['branch_id'] = $branch_id;  
        $data['head_id'] = $head_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;  
        $data['amount'] = $amount; 
        $data['description'] = $description;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;  
        $data['v_no'] = $v_no; 
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        $data['company_id'] = $company_id;
        $transcation = \App\Models\AllHeadTransaction::create($data);
        return true;
    }


        /**
     *  Head New table entry
     *
     * @param
     * @return \Illuminate\Http\Response
     */
    public static function headTransactionCreateSSB($daybook_ref_id, $branch_id, $head_id, $type, $sub_type, $type_id, $associate_id, $member_id,$amount, $description, $payment_type, $payment_mode, $currency_code,$v_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $ssb_account_tran_id_to,$company_id)
    {
        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['branch_id'] = $branch_id; 
        $data['head_id'] = $head_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id; 
        $data['amount'] = $amount; 
        $data['description'] = $description;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code; 
        $data['v_no'] = $v_no;  
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;  
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        $data['company_id'] = $company_id;
        $transcation = \App\Models\AllHeadTransaction::create($data);
        return true;
    }


    /**
     *  create branch day book  ------------ New Field Add
     *
     * @param  $amount,$entry_date,$entry_time,$created_at
     * @return \Illuminate\Http\Response
     */
    public static function NewFieldBranchDaybookCreate($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $associate_id, $member_id,$amount, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code,$v_no, $entry_date, $entry_time, $created_by, $created_by_id,$created_at, $updated_at, $type_transaction_id, $ssb_account_id_to,  $ssb_account_tran_id_to,$company_id)
    {   
        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['branch_id'] = $branch_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id; 
        $data['amount'] = $amount; 
        $data['description'] = $description;
        $data['description_dr'] = $description_dr;
        $data['description_cr'] = $description_cr;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;        
        $data['v_no'] = $v_no;         
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id; 
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        /*----------------*/
        $data['ssb_account_id_to'] = $ssb_account_id_to; 
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to; 
        $data['company_id'] = $company_id; 
        $transcation = \App\Models\BranchDaybook::create($data);
        return $transcation->id;
    }



}