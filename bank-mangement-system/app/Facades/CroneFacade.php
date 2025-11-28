<?php
namespace App\Facades;
use Illuminate\Support\Facades\Facade;
use App\Models\BranchCash;
use App\Models\BranchCurrentBalance;
use App\Models\Branch;
use Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission; 

class CroneFacade extends Facade{
	
	public function show_module(){	
		$branch_name = customBranchName();
		$getBranchId = $branch_name->id;
		$branch_id = $getBranchId;
		$globaldate = date('Y-m-d');
		$entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
	  
		$getBranchAmount = Branch::whereId($branch_id)->first();
		$company_id = '';
		$Amount = $company_id == 1 ? $getBranchAmount->total_amount : 0;  
		$startDate = ($company_id==1)? date("Y-m-d", strtotime(convertDate('2021-08-05'))) : '';
		$balance = BranchCurrentBalance::where('branch_id', $branch_id)->when($startDate != '',function($q) use($startDate){
			$q->whereDate('entry_date','>=',$startDate);
		})->where('entry_date', '<=', $entry_date);
		
		if ($company_id != '') {
			$balance = $balance->where('company_id', $company_id);
		}
		$balance = $balance->sum('totalAmount'); 
		  // 'SELECT sum(totalAmount) FROM `branch_current_balance` WHERE branch_id = 1 and entry_date > '2023-06-20' ORDER BY `branch_current_balance`.`entry_date` DESC'
		$day_closing_amount = $balance + $Amount;
		$show_module = true;
		$cash_in_hand = $branch_name->cash_in_hand;
		$diff_balance = $cash_in_hand - $day_closing_amount;

		// $show_module = ($diff_balance > 0) ? true : false;

		// if (isset($branch_name->first_login) && $branch_name->first_login == '0') {			
		// 	if ($balance) {
		// 		$show_module = ($diff_balance > 0) ? false : true;				
		// 	}
		// 	else
		// 	{
		// 		$show_module = ($diff_balance > 0) ? false : true;
		// 	}
		// 	if ($cash_in_hand == 0) {
		// 		$show_module = true;
		// 	}
		// } else {
		// 	$show_module = true;
		// 	if ($cash_in_hand == 0) {
		// 		$show_module = true;
		// 	}
		// }

		// $authUser = Auth::user();

			// if($show_module){
			// 	$permissions = Permission::all();
			// 	$authUser->syncPermissions($permissions);
			// }else{
			// 	$permissions = $authUser->permissions;
			// 	foreach ($permissions as $permission) {
			// 		if(!in_array($permission->name,['Branch To Ho','SSB Withdraw','Branch to Bank Fund Transfer'])){
			// 			$authUser->revokePermissionTo($permission);
			// 		}
			// 	}
			// }
			
		return $show_module;
	}
}