<?php
$admin = Auth::user()->role_id != 3 ? true : false;
$path = $admin == true ? 'templates.admin.master' : 'layouts.branch.dashboard';
$path2 = $admin == true ? 'templates/CommonViews/Associate/admin/associate_business' : 'templates/CommonViews/Associate/branch/associate_business';
$path3 = $admin == true ? 'templates/CommonViews/Associate/admin/partial/associate_business' : 'templates/CommonViews/Associate/branch/partial/associate_business';
?>
@extends($path)
@section('content')
    @if($admin == 1 || $admin == true)
        <?php
        $companyView = view('templates.GlobalTempletes.both_company_filter_new',['all'=>true])->render();
        $btn = 'btn bg-dark legitRipple';
        ?>
        @include($path2)
        @section('script')
            @include($path3)
        @stop
    @else
        <?
        $companyView = view('templates.GlobalTempletes.new_role_type',[
            'dropDown'=>$allCompany,
            'filedTitle'=>'Company',
            'name'=>'company_id',
            'value'=>'',
            'multiselect'=>'false',
            'design_type'=>4,
            'branchShow'=>false,
            'branchName'=>'branch_id',
            'apply_col_md'=>true,
            'multiselect'=>false,
            'placeHolder1'=>'Please Select Company',
            'placeHolder2'=>'Please Select Branch'
            ])->render();
        $btn = 'btn btn-primary legitRipple';
        ?>
        @include($path2)
        @section('script')
            @include($path3)
        @stop
    @endif
@stop