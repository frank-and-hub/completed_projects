@if($urlDynimic != 1)
<div class="container-fluid ">
    <div class="content-wrapper">
        @endif
        <div class="content">
            <div class="row">
                @if($urlDynimic != 1)
                <div class="col-lg-12">
                    <div class="card bg-white">
                        <div class="card-body page-title">
                            <h3 class="">ECS Transaction Listing</h3>
                            <a href="{{ redirect()->back()->getTargetUrl() }}" style="float:right" class="btn btn-secondary">Back</a>
                        </div>
                    </div>
                </div>
                @endif
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            @if($urlDynimic == 1)
                            <h6 class="card-title font-weight-semibold">Search Filter</h6>
                            @else
                            <h3 class="card-title font-weight-semibold">Search Filter</h3>
                            @endif
                        </div>
                        <div class="card-body">
                            <form action="#" method="post" enctype="multipart/form-data" id="ecs_filter" name="ecs_filter">
                                <input type="hidden" name="url" id="url" class="form-control" value="{{$urlDynimic}}">
                                <input type="hidden" name="create_application_date" id="create_application_date" class="form-control create_application_date">
                                @csrf
                                <div class="row">
                                    @if($urlDynimic == 1)
                                    @include('templates.GlobalTempletes.both_company_filter',['all'=>true])
                                    @else
                                    @include('templates.GlobalTempletes.role_type',[
                                    'dropDown'=> $branchCompany[Auth::user()->branches->id],
                                    'name'=>'company_id',
                                    'apply_col_md'=>false,
                                    'filedTitle' => 'Company Name'
                                    ])
                                    @endif
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">From Date</label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" class="form-control " name="from_date" id="from_date" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">To Date</label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" class="form-control" name="to_date" id="to_date" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">ECS Type</label>
                                            <div class="col-lg-12 error-msg">
                                                <select class="form-control" name="ecs_type" id="ecs_type">
                                                    <option value="">Select ECS Option</option>
                                                    <option value="1">Is Bank</option>
                                                    <option value="2">Is SSB </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">ECS Status</label>
                                            <div class="col-lg-12 error-msg">
                                                <select class="form-control" name="ecs_status" id="ecs_status">
                                                    <option value="">Select ECS Option</option>
                                                    <option value="1">Success</option>
                                                    <option value="0">Failed </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Customer's Id</label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" class="form-control " name="customer_id" id="customer_id" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Account No</label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" class="form-control " name="account_no" id="account_no" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group text-right">
                                            <div class="col-lg-12 page">
                                                <input type="hidden" name="is_search" id="is_search" value="no">
                                                <input type="hidden" name="ecs_export" id="ecs_export" value="">
                                                <button type="button" class=" btn btn-primary legitRipple" onClick="searchForm()">Submit</button>
                                                <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card bg-white d-none" id="data_div">
                        <div class="card-header bg-transparent header-elements-inline">
                            <h3 class="mb-0 text-dark">Ecs Transaction Listing</h3>
                            <div class="">
                                @if($urlDynimic == 1)
                                <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export Excel</button>
                                @else
                                <button type="button" class="btn btn-primary legitRipple export ml-2" data-extension="0" style="float: right;">Export Excel</button>
                                @endif
                            </div>
                        </div>
                        <div class="table-responsive ">
                            <table id="loan_ecs_listing" class="table datatable-show-all">
                                <thead>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Date</th>
                                        <th>Branch name</th>
                                        <th>Account no</th>
                                        <th>Plan </th>
                                        <th>Customer id </th>
                                        <th>Customer name</th>
                                        <th>Collector code</th>
                                        <th>Collector name</th>
                                        <th>Amount </th>
                                        <th>ECS Mode</th>
                                        <th>ECS Status</th>
                                        <th>Bounce charge </th>
                                        <th>SGST</th>
                                        <th>CGST</th>
                                        <th>IGST</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>