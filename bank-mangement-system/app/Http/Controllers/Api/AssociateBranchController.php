<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;

Class AssociateBranchController extends Controller
{
    public function allBranch(Request $request)
    {
        $branches = Branch::get();
        dd($branches);
    }
}
?>