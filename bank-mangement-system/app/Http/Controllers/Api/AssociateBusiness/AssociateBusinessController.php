<?php
namespace App\Http\Controllers\Api\AssociateBusiness;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use DB;

class AssociateBusinessController extends controller
{

   public function report(request $request)
   {
        $check = $request['check'];
        dd($check);
   }


}