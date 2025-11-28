<div class="card-body">
    {{ Form::open(['url' => '#', 'method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'filter', 'class' => '', 'name' => 'filter']) }}
    <div class="row">
        <div class="col-md-4">
            <div class="form-group row">
                <label class="col-form-label col-lg-12">From Date </label>
                <div class="col-lg-12 error-msg">
                    <div class="input-group">
                        {{Form::text('start_date','',['id'=>'start_date','class'=>'form-control','readonly'=>true])}}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group row">
                <label class="col-form-label col-lg-12">To Date </label>
                <div class="col-lg-12 error-msg">
                    <div class="input-group">
                        {{Form::text('end_date','',['id'=>'end_date','class'=>'form-control','readonly'=>true])}}
                    </div>
                </div>
            </div>
        </div>
        @php
            $dropDown = $company;
            $filedTitle = 'Company';
            $name = 'company_id';
        @endphp
        @include('templates.GlobalTempletes.both_company_filter',['all'=>true])
        {{--@include('templates.GlobalTempletes.new_role_type',['dropDown'=>$AllCompany,'filedTitle'=>'Company','name'=>'company_id','value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch_id','apply_col_md'=>true,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch'])--}}
        <div class="col-md-12">
            <div class="form-group row">
                <div class="col-lg-12 text-right">
                    {{Form::hidden('gst_payable_export','',['id'=>'gst_payable_export','class'=>''])}}
                    {{Form::hidden('is_search','no',['id'=>'is_search','class'=>''])}}
                    {{Form::hidden('created_at','',['id'=>'created_at','class'=>'created_at'])}}
                    <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()">Submit</button>
                    <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset</button>
                </div>
            </div>
        </div>
    </div>
    {{ Form::close() }}
</div>
