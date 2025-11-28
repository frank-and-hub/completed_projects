<div class="card-body">
    {{ Form::open(['url' => '#', 'method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'payable_filter', 'class' => '', 'name' => 'payable_filter']) }}
    <div class="row">
    @php
        $dropDown = $AllCompany;
        $filedTitle = 'Company';
        $name = 'company_id';
        @endphp
        <div class="col-md-4">
            <div class="form-group row">
                <label class="col-form-label col-lg-12">Company Name <span class="text-danger">*</span></label>
                <div class="col-lg-12 error-msg">
                    <div class="">
                        <select class="form-control " id="company_id" name="company_id">
                            <option value="">---- Please Select Company----</option>
                            <option value="0">All Company</option>
                            @foreach($AllCompany as $k => $v)
                            <option value="{{$k}}">{{$v}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group row">
                <label class="col-form-label col-lg-12">From Date <span class="text-danger">*</span> </label>
                <div class="col-lg-12 error-msg">
                    <div class="">
                        {{Form::text('start_date','',['id'=>'start_date','class'=>'form-control','readonly'=>true])}}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group row">
                <label class="col-form-label col-lg-12">To Date <span class="text-danger">*</span> </label>
                <div class="col-lg-12 error-msg">
                    <div class="">
                        {{Form::text('end_date','',['id'=>'end_date','class'=>'form-control','readonly'=>true])}}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group row">
                <label class="col-form-label col-lg-12">Select Head Type</label>
                <div class="col-lg-12  error-msg">
                    <select class="form-control " id="head_type" name="head_type">
                        <option value="">---- Please Select Head Type----</option>
                        @foreach($head_type as $k => $v)
                        <option value="{{$k}}">{{$v}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group row">
                <label class="col-form-label col-lg-12">Select Head </label>
                <div class="col-lg-12  error-msg">
                    <select class="form-control " id="head_id" name="head_id">
                        <option value="">---- Please Select ----</option>
                    </select>
                </div>
            </div>
        </div>
      
        <div class="col-md-12">
            <div class="form-group row">
                <div class="col-lg-12 text-right">
                    {{Form::hidden('payable_export','0',['id'=>'payable_export','class'=>''])}}
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