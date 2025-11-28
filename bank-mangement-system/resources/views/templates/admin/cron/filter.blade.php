<div class="col-md-12">@php $stateid = getBranchState(Auth::user()->username); @endphp
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold">Search Filter</h6>
        </div>
        <div class="card-body">
            {{Form::open(['url'=>'#','method'=>'post','id'=>'cron_filter','class'=>'','name'=>'cron_filter','enctype'=>'multipart/form-data'])}}            
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-12">Cron Name</label>
                            <div class="col-lg-12 error-msg">
                                <div class="">
                                    {{Form::text('name',null,['id'=>'name','class'=>'form-control','autocomplete'=>'off'])}}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-12">Cron Date</label>
                            <div class="col-lg-12 error-msg">
                                <div class="">
                                    {{Form::text('date',null,['id'=>'date','class'=>'form-control','autocomplete'=>'off'])}}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-12">Cron Status</label>
                            <div class="col-lg-12 error-msg">
                                <div class="">
                                    {{Form::select('status',['0'=>'please select cron status','1'=>'Start','2'=>'In Progress','3'=>'Success','4'=>'Failed'],'0',['id'=>'status','class'=>'form-control'])}}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group text-right">
                            <div class="col-lg-12 page">
                                {{Form::hidden('is_search','no',['id'=>'is_search','class'=>'form-control'])}}
                                {{Form::hidden('created_at','',['id'=>'created_at','class'=>'form-control created_at'])}}
                                {{Form::hidden('cron_export',null,['id'=>'filter_report','class'=>'form-control'])}}
                                <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()">Submit</button>
                                <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset </button>
                            </div>
                        </div>
                    </div>
                </div>
            {{Form::close()}}
        </div>
    </div>
</div> 