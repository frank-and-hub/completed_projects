<form id="company_register_branch" name="company_register_branch" class="row" style="display: none;" method="post">
<div class="col-12">
	<h3 class="text-center">Step - 4 Branch </h3>
	<hr>
</div>
    @csrf
    <div class="col-md-12">
        @include('templates.admin.company.drag_and_drop_branch', [
            'valueArray'=>isset($companybranch)?'companybranch':'branch',
            'labelOne' => 'Included Branch',
            'labelTwo'=>'Excluded Branch',
            'searchOne'=>'branchSearchOne',
            'searchTwo'=>'branchSearchTwo',
            'dataOne'=>'name',
            'dataTwo'=>'branch_code',
            'valueArray_opposite'=>isset($companybranch)?'companybranch_not':'',
            'value'=>'id',
        ])
    </div>
	
	<div class="col-md-12">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">Primary Branch<sup class="text-danger" >*</sup></label>
            <div class="col-lg-12 error-msg">
                <select type="text" {{ $title == "Company | View Company Details" ? 'disabled' : 'required' }} name="p_branch" class="form-control" id="p_branch" value="{{$company->p_branch ?? old('p_branch')}}">
					@foreach($branch as $value)
					<option value="{{$value->id}}" {{ isset($selectedIsPrimary) ? ($selectedIsPrimary->branch_id == $value->id) ? 'selected':'':''}}>{{$value->name}}</option>
					@endforeach
				<select>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
	
    <div class="row col-md-12">
        <div class="col-md-6">
            <div class="text-start">
                <button class="btn btn-primary " id="prev_four">Previous</button>
            </div>
        </div>
        @if($title != "Company | View Company Details")
            @if(isset($account_head_up))
            <div class="col-md-6">
                <div class="text-right">
                    <button type="submit" name="update" class="btn btn-primary input_dis">Update</button>
                </div>
            </div>
            @else
            <div class="col-md-6">
                <div class="text-right">
                    <button type="submit" name="submit" class="btn btn-primary input_dis">Submit</button>
                </div>
            </div>
            @endif
        @endif
    </div>
</form>
@if($title == "Company | View Company Details")
    <script>
    $(document).ready(function(){
        $('input').attr('disabled','disabled'); 
        $('input').prop('disabled', true).css('color', '#333');
        $('select').css({'-webkit-appearance': 'none','-moz-appearance': 'none','appearance': 'none','color' :'#333'}).prop('disabled', true);
        $('sup').html('');
        $('label').html(function(index, currentText) {
            return currentText.toUpperCase();
        });
        $("#company_image").addClass("d-none");
    });
    </script>
@endif