<form id="company_register_account_head" name="company_register_account_head" class="row" style="display: none;" method="post">
<div class="col-12">
	<h3 class="text-center">Step - 2 Account Heads </h3>
	<hr>
</div>    
    @csrf
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <div class="col-md-12">
        @php
        @include('templates.admin.company.drag_and_drop', [
			'valueArray'=> isset($account_head_up) ? 'account_head_up' : 'account_head',
			'labelOne' => 'Included Account Head',
			'labelTwo'=>'Excluded Account Head',
			'searchOne'=>'accountHeadSearchOne',
			'searchTwo'=>'accountHeadSearchTwo',
			'dataOne'=>'sub_head',
			'dataTwo'=>'',
			'value'=>'head_id', 
			'valueArray_opposite'=>isset($account_head_up) ? 'account_head_up_opposite' : '',
		])
    </div>
    @if($company->id)
    <input type="hidden" value="{{$company->id}}" name="company_id_ah"/>
    @endif
    <div class="row col-md-12">
        <div class="col-md-6">
            <div class="text-start">
                <button class="btn btn-primary " id="prev_one">Previous</button>
            </div>
        </div>
        <div class="col-md-6">
            <div class="text-right">
                <button class="btn btn-primary input_dis" type="submit" >Next</button>
            </div>
        </div>
    </div>
</form>