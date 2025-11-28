<?php
$member = $data ? $data->member : '';
$ssb = $data ? $data->ssb : '';
$deposit_amount = $data->current_balance??0 ;
// last money back date on member investment table
$last_deposit_to_ssb_date = date('d-m-Y',strtotime($data->last_deposit_to_ssb_date??$data->created_at)); 
// investment created date
$created_at = date('d/m/Y',strtotime($data->created_at));
// last money back date on member investment table
if($type != 'mbat'){
    $last_money_back_date = date('d-m-Y',strtotime($last_deposit_to_ssb_date . '+ 1 months'));
}else{
    $last_money_back_date = date('d-m-Y',strtotime($last_deposit_to_ssb_date . '+ 1 years'));
}
// get investment deposit balance
if (isset($data->plan) && $data->plan->plan_category_code == "S") {
    $amount = $data->ssbBalanceView ? $data->ssbBalanceView->totalBalance??0:0;
    $current_balance = number_format((float)  $amount, 2, '.', '');
} else {
    $current_balance = ($data['InvestmentBalance']) ? $data['InvestmentBalance']->totalBalance : 0;
}
$money_back_count = money_back_count($data->id)??0;
$last12monthdeposit = last12monthdeposit($data->account_number,$last_deposit_to_ssb_date,$last_money_back_date);
?>
<div class="row col-md-12">
    <div class="col-md-4">
        <div class="form-group row">
        <label class="col-form-label col-lg-12">Next {{$type=='mbat' ? 'Money Back' : ' MIS'}} Due Date</label>
        <div class="col-lg-12 error-msg">
                <div class="">
                    {{Form::text('view_date',date('d/m/Y',strtotime($last_money_back_date)),['id'=>'date','class'=>'form-control','autocomplete'=>'off','readonly'=>true])}}
                    {{Form::hidden('date',$last_money_back_date,['id'=>'date','class'=>'form-control','autocomplete'=>'off'])}}
                </div>
            </div>
        </div> 
    </div>
    <div class="col-md-4">
        <div class="form-group row">
        <label class="col-form-label col-lg-12">Member Name</label>
        <div class="col-lg-12 error-msg">
                <div class="">
                    {{Form::text('membername',(($member) ? (($member->first_name) . ' ' . ($member->last_name??'')) : ''),['id'=>'membername','class'=>'form-control','autocomplete'=>'off','readonly'=>true])}}
                </div>
            </div>
        </div> 
    </div>
    <div class="col-md-4"> 
        <div class="form-group row"> 
            <label class="col-form-label col-lg-12">Tenure</label>
            <div class="col-lg-12 error-msg">
                <div class="">
                    {{Form::text('tenure',($data->tenure??0) * 12 . ' Months',['id'=>'tenure','class'=>'form-control','autocomplete'=>'off','readonly'=>true])}}
                </div>
            </div>
        </div>
    </div>
    @if($type=='mbat')
    <div class="col-md-4"> 
        <div class="form-group row"> 
            <label class="col-form-label col-lg-12">Deposit in Last 12 Month</label>
            <div class="col-lg-12 error-msg">
                <div class="">
                    {{Form::text('tenure',$last12monthdeposit??0,['id'=>'tenure','class'=>'form-control','autocomplete'=>'off','readonly'=>true])}}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4"> 
        <div class="form-group row"> 
            <label class="col-form-label col-lg-12">Previous Carry Forword Amount</label>
            <div class="col-lg-12 error-msg">
                <div class="">
                    {{Form::text('tenure',($data->carry_forward_amount??0),['id'=>'tenure','class'=>'form-control','autocomplete'=>'off','readonly'=>true])}}
                </div>
            </div>
        </div>
    </div>
    @endif
    <div class="col-md-4">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">Total Deposit Amount</label>
            <div class="col-lg-12 error-msg">
                <div class="">
                    {{Form::text('deposit_amount',$current_balance,['id'=>'deposit_amount','class'=>'form-control','autocomplete'=>'off','readonly'=>true])}}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">Opening Date</label>
            <div class="col-lg-12 error-msg">
                <div class="">
                    {{Form::text('created_at',$created_at,['id'=>'created_at','class'=>'form-control','autocomplete'=>'off','readonly'=>true])}}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">No. of {{$type=='mbat' ? 'Money Back' : 'MIS'}} Released</label>
            <div class="col-lg-12 error-msg">
                <div class="">
                    {{Form::text('money_back_count',$money_back_count,['id'=>'money_back_count','class'=>'form-control','autocomplete'=>'off','readonly'=>true])}}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">Last {{$type=='mbat' ? 'Money Back' : 'MIS'}} Released On</label>
            <div class="col-lg-12 error-msg">
                <div class="">                    
                    {{Form::text('last_deposit_to_ssb_date',date('d/m/Y',strtotime($last_deposit_to_ssb_date)),['id'=>'last_deposit_to_ssb_date','class'=>'form-control','autocomplete'=>'off','readonly'=>true])}}
                </div>
            </div>
        </div>
    </div>
    {{Form::hidden('last_money_back_date',$last_money_back_date,['id'=>'last_money_back_date'])}}
    {{Form::hidden('plan_id',$data->plan_id,['id'=>'plan_id'])}}
    {{Form::hidden('company_id',$data->company_id,['id'=>'company_id'])}}
    <div class="col-md-12">
        <div class="form-group text-center">
            <div class="col-lg-12 page">
                {{Form::hidden('is_search','no',['id'=>'is_search','class'=>'form-control'])}}
                {{Form::hidden('miscit_cron_export',null,['id'=>'miscit_cron_export','class'=>'form-control'])}}
                {{Form::submit('Submit',['class'=>'btn btn-primary'])}}
                <!-- <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset </button> -->
            </div>
        </div>
    </div>
</div>