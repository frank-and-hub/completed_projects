<div class="content">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h5 class="card-title font-weight-semibold">Search Filter</h5>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                        <input type="hidden" id='slug' name='slug' value="{{ $slug }}">
                        <div class="row">

                            {{-- @include('templates.GlobalTempletes.new_role_type',['dropDown'=>$dropDown,'filedTitle'=>$filedTitle,'name'=>$name,'value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch','apply_col_md'=>true,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch']) --}}
                            @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true])

                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12"> Date</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control " name="start_date" id="start_date" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Scheme Account Number </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="scheme_account_number" id="scheme_account_number" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Associate Code </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="associate_code" id="associate_code" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Customer Id</label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="customer_id" id="customer_id" class="form-control">
                                    </div>
                                </div>
                            </div>

                            {{-- <input type="hidden" name="customer_id"> --}}
                            @if ($slug == 'monthly')
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Plan Name</label>
                                    <div class="col-lg-12 error-msg">
                                        <select name="plan" id="plan" class="form-control">
                                            <option value="">---Please Select Plan --- </option>
                                            @foreach ($plan as $item)
                                            <option value="{{$item->id}}">{{$item->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            @endif
                            <div class="col-md-12">
                                <div class="form-group text-right">
                                    <div class="col-lg-12 page">
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="investments_export" id="investments_export" value="">
                                        <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()">Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-12 table-section hideTableData" >
            <div class="card bg-white shadow">
                <div class="card-header bg-transparent header-elements-inline">
                    <h3 class="mb-0 text-dark">Investments</h3>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="investment_report_isting" class="table table-flush">
                        <thead class="">
                            <tr>
                                <th>S/N</th>
                                <th>Company Name</th>
                                <th>Branch Name</th>
                                <th>Opening Date</th>
                                <th>Current Date</th>
                                <th>Member</th>
                                <th>Member Id</th>
                                <th>Customer Id</th>
                                <th>Mobile No</th>
                                <th>Associate Code</th>
                                <th>Associate Name</th>
                                <th>Account Number</th>
                                <th>Plan Name</th>
                                <th>Tenure</th>
                                <th>Balance</th>
                                <th>Deno Amount</th>
                                <th>Due Emi</th>
                                <th>Due Emi Amount</th>

                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>