<?php 

namespace App\Http\Controllers\Admin; 



use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Request as Request1;

use Validator;

use App\Models\Member; 
use App\Models\Admin; 
use App\Models\AccountBranchTransfer; 

use App\Models\Memberinvestments;



use App\Http\Controllers\Admin\CommanController;

use Yajra\DataTables\DataTables;

use Carbon\Carbon;

use Session;

use Image;

use Redirect;

use URL;

use DB;

use App\Services\Sms;

use App\Models\SamraddhBank;



/*

    |---------------------------------------------------------------------------

    | Admin Panel -- Associate Management AssociateController

    |--------------------------------------------------------------------------

    |

    | This controller handles associate all functionlity.

*/

class AccountLogController extends Controller

{

    /**

     * Create a new controller instance.

     * @return void

     */



    public function __construct()

    {

        // check user login or not

        $this->middleware('auth');

    }



    /**

     * Show associate list.

     * Route: /admin/associate 

     * Method: get 

     * @return  array()  Response

     */

    public function loglist($type,$id)

    {
		if($type==1)
		{
			$data['title']='Associate Log Detail'; 
		 
            $data['accountbrabnch'] =  AccountBranchTransfer::where('type',$type)->where('type_id',$id)->orderBy('id','DESC')->get();  
		  //dd($data['accountbrabnch']);
		} 
       if($type==2)
		{		
			$data['title']='Investment Log Detail';  
            $data['accountbrabnch'] =  AccountBranchTransfer::whereIn('type',[2,4])->where('type_id',$id)->orderBy('id','DESC')->get(); 
		}
	
            
		 
		
        return view('templates.admin.log.logindex',$data);

    }
	
}