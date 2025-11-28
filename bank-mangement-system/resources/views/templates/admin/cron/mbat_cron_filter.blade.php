<div class="col-md-12">@php $stateid = getBranchState(Auth::user()->username); @endphp
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold">Transfer Cron Form</h6>
        </div>
        <div class="card-body">
            {{Form::open(['url'=>'#','method'=>'post','id'=>'filter','class'=>'','name'=>'filter','enctype'=>'multipart/form-data'])}}            
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-12">Money Back Account Number</label>
                            <div class="col-lg-12 error-msg">
                                <div class="">
                                    {{Form::text('account_no',null,['id'=>'account_no','class'=>'form-control','autocomplete'=>'off'])}}
                                </div>
                            </div>
                        </div>
                    </div>
                    {{Form::hidden('cron_type',$cron_type,['id'=>'cron_type','class'=>'form-control'])}}
                    {{Form::hidden('created_at','',['id'=>'created_at','class'=>'form-control created_at'])}}
                    <div class="col-md-4"></div>
                    <div id="account_details" class="col-md-12 row"></div>
                </div>
            {{Form::close()}}
        </div>
    </div>
</div> 