<div class="card-body">
    {{ Form::open(['url' => '#', 'method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'tds_payable_filter', 'class' => '', 'name' => 'tds_payable_filter']) }}
    <div class="row">
        <div class="col-md-4">
            <div class="form-group row">
                <label class="col-form-label col-lg-12">From Date </label>
                <div class="col-lg-12 error-msg">
                    <div class="input-group">
                        {{Form::text('start_date','',['id'=>'start_date','class'=>'form-control','autocomplete'=>'off'])}}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group row">
                <label class="col-form-label col-lg-12">To Date </label>
                <div class="col-lg-12 error-msg">
                    <div class="input-group">
                        {{Form::text('end_date','',['id'=>'end_date','class'=>'form-control','autocomplete'=>'off'])}}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group row">
                <label class="col-form-label col-lg-12">Select TDS Head </label>
                <div class="col-lg-12  error-msg">
                    <select class="form-control " id="head_id" name="head_id">
                        <option value="">---- Please Select ----</option>
                        @foreach ($tdsHeads as $key=>$val)
                         <option value="{{ $key }}">{{ ucwords($val) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        @include('templates.GlobalTempletes.new_role_type',['dropDown'=>$AllCompany,'filedTitle'=>'Company','name'=>'company_id','value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch_id','apply_col_md'=>true,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch'])
        <div class="col-md-12">
            <div class="form-group row">
                <div class="col-lg-12 text-right">
                    {{Form::hidden('tds_payable_export','',['id'=>'tds_payable_export','class'=>''])}}
                    {{Form::hidden('is_search','no',['id'=>'is_search','class'=>''])}}
                    {{Form::hidden('created_at','',['id'=>'created_at','class'=>'created_at'])}}
                    <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()">Submit</button>
                    <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{ Form::close() }}
</div>
