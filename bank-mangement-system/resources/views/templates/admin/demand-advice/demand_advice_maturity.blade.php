@extends('templates.admin.master')
@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
                    </div>
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="filter_maturity" name="filter_maturity">
                        <input type="hidden" class="create_application_date" name="create_application_date" id="create_application_date">
                           @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Date From</label>

                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="date_from" id="date" class="form-control date-from" autocomplete="off">
                                        </div>

                                    </div>

                                </div>



                                <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Date To</label>

                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="date_to" id="to-date" class="form-control date-to" autocomplete="off">
                                        </div>

                                    </div>

                                </div>
<!-- 
                                @if(Auth::user()->branch_id>0)

                                <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Select Branch </label>

                                        <div class="col-lg-12 error-msg">



                                                <select class="form-control" id="filter_branch" name="filter_branch">

                                                    <option value=""  >----Select----</option>

                                                    @foreach( App\Models\Branch::where('id','=',Auth::user()->branch_id)->pluck('name', 'id') as $key => $val )

                                                    <option value="{{ $key }}"  >{{ $val }}</option>

                                                    @endforeach

                                                </select>



                                        </div>

                                    </div>

                                </div>

                                @else

                                <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Select Branch </label>

                                        <div class="col-lg-12 error-msg">



                                                <select class="form-control" id="filter_branch" name="filter_branch">

                                                    <option value=""  >----Select----</option>

                                                    @foreach( App\Models\Branch::pluck('name', 'id') as $key => $val )

                                                    <option value="{{ $key }}"  >{{ $val }}</option>

                                                    @endforeach

                                                </select>



                                        </div>

                                    </div>

                                </div> -->

                                <!-- @endif -->
                                @include('templates.GlobalTempletes.new_role_type',['dropDown'=>$company,'filedTitle'=>"Company",'name'=>'company_id','value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch_id','apply_col_md'=>false,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch'])

                                <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Select Advice Type </label>

                                        <div class="col-lg-12 error-msg">



                                                <select class="form-control" id="advice_type" name="advice_type">

                                                    <option value=""  >----Select----</option>

                                                    <option value="1"  >Maturity</option>

                                                    <option value="2"  >Prematurity</option>

                                                    <option value="3"  >Death Help </option>

                                                    <option value="4"  >Death Claim </option>

                                                </select>



                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Account Number </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" class="form-control  " name="account_number" id="account_number"  >
                                        </div>
                                    </div>
                                </div>   

                                <div class="col-md-12">

                                    <div class="form-group row">

                                        <div class="col-lg-12 text-right" >

                                            <input type="hidden" name="is_search" id="is_search" value="no">

                                            <input type="hidden" name="demand_advice_report_export" id="demand_advice_report_export" value="">

                                            <button type="button" class=" btn bg-dark legitRipple" onClick="searchMaturityForm()" >Submit</button>

                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetMaturityForm()" >Reset </button>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

            <div class="col-md-12" id="table" >

                <div class="card">

                    <form action="{{route('admin.demand.saveInvestmentMaturityAmount')}}" method="post" enctype="multipart/form-data" id="investment-maturity-form" name="investment-maturity-form">

                        @csrf

                        <div class="card-header header-elements-inline">

                            <div class="">

                                <input type="submit" name="submitform" value="Submit" class="btn bg-dark legitRipple submit-maturity ml-2">

                            </div>

                        </div>

                        <table class="table datatable-show-all" id="demand-advice-maturity-table" >

                            <thead>

                                <tr>

                                    <th width="5%">S/N</th>

                                    <th width="10%">Company Name</th>
                                    <th width="10%">BR Name</th>

                                    <!-- <th width="10%">BR Code</th> -->

                                    <!--<th width="10%">SO Code</th>

                                    <th width="10%">RO Code</th>

                                    <th width="10%">ZO Code</th> -->

                                    <th width="5%">Date</th>

                                    <th width="5%">Member Name</th>


                                    <th width="5%">TDS Amount</th>

                                    <th width="5%">Maturity Amount Till Date</th>

                                    <th width="5%">Maturity Amount Payable</th>

                                    <th width="5%">Voucher No</th>

                                    <th width="10%">Mobile</th>

                                    <th width="10%">Account Number</th>

                                    <th width="10%">Passbook / Bond Photo </th>
									
									<th width="10%">Requested Payment Mode</th>

                                    <th width="10%">Status</th>

                                    <th width="10%">Action</th>



                                </tr>

                            </thead>

                        </table>

                    </form>

                </div>

            </div>

        </div>

    </div>



    <div class="modal fade" id="maturity-calculation-form" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">

      <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document" style="max-width: 1000px !important; ">

        <div class="modal-content">

          <div class="modal-body p-0">

            <div class="card bg-white border-0 mb-0">

              <div class="card-header bg-transparent pb-2ß">

                <div class="text-dark text-center mt-2 mb-3">Maturity Calculation</div>

              </div>

              <div class="card-body px-lg-5 py-lg-5">
                <div class="d-flex pb-2" style="justify-content: space-between;">
                    <div class=" acc_number"></div>
                    <div class=" tenure"></div>
                    <div class=" created_at"></div>
                    <div class=" deno"></div>
                </div>
                    <table class="table datatable-show-all" id="maturity-calculation-table" >

                        <thead>

                            <tr>

                                <th width="5%">S/N</th>

                                <th width="10%">Deposit</th>

                                <th width="10%">Compound Interest</th>

                                <th width="10%">Total</th>

                                <th width="10%">Interest Rate</th>

                                <th width="10%">Interest Rate Amount</th>

                                <th width="10%">Deposit Date</th>

                            </tr>

                        </thead>

                        <tbody class="maturity-calculation-list">

                            <td colspan="9"><span style="padding-left:48% "><b class="amount_diffrence"></b></span></td>

                        </tbody>

                    </table>

              </div>

            </div>

          </div>

          <div class="modal-footer">

            <button type="button" class="btn btn-primary ok" data-dismiss="modal" style="margin-right: 477px; margin-top: 10px;">OK</button>

          </div>

        </div>

      </div>

    </div>

@stop



@section('script')

<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>

@include('templates.admin.demand-advice.partials.demand_maturity_script')

@endsection 



