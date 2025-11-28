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
                        <form action="{{URL::to("admin/member/interest-tds/$id")}}" method="post" enctype="multipart/form-data" id="membertdsfileter" name="membertdsfileter">
                        @csrf
                        <input type="hidden" name="m_id" id="m_id" value="{{ $id }}">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date </label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" class="form-control" name="start_date" id="start_date" @if($startDate) value="{{ date("d/m/Y", strtotime(convertDate($startDate))) }}" @endif> 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                 <input type="text" class="form-control  " name="end_date" id="end_date" @if($endDate) value="{{ date("d/m/Y", strtotime(convertDate($endDate))) }}" @endif>
                                               </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Plan </label>
                                        <div class="col-lg-12  error-msg">
                                            <select class="form-control" id="plan_id" name="plan_id">
                                                <option value="">Select Plan</option> 
                                                @foreach( $plans as $k =>$val )
                                                    @if($val->id != 1)
                                                        <option @if($val->id == $planId) selected @endif value="{{ $val->id }}">{{ $val->name }}</option> 
                                                    @endif    
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

								<!-- <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch </label>
                                        <div class="col-lg-12  error-msg">
                                            <select class="form-control" id="branch_id" name="branch_id">
												<option value=""  >All</option>
												@foreach( $branch as $k =>$val )
												    <option value="{{ $val->id }}" @if($val->id == $branch_id) selected @endif>{{ $val->name }}</option> 
												@endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div> -->

                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="yes">
                                            <input type="hidden" name="m_investment_tds_export" id="m_investment_tds_export" value="">
                                            <input type="submit" class="btn bg-dark legitRipple" name="submitform" id="submitform" value="Submit">
                                            <input type="submit" class="btn btn-gray legitRipple" id="reset_form" value="Reset">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Transactions List</h6>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn bg-dark legitRipple export-tds ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        <button type="button" class="btn bg-dark legitRipple export-tds" data-extension="1" style="float: right;">Export PDF</button>

                        <form action="{!! route('admin.member.printinvestmenttds') !!}" method="post" enctype="multipart/form-data" id="print_tds" name="print_tds">
                            @csrf
                            <input type="hidden" name="start_date" id="s_date" value="{{ $startDate }}">
                            <input type="hidden" name="end_date" id="e_date" value="{{ $endDate }}">
                            <input type="hidden" name="plan_id" id="p_id" value="{{ $planId }}">
                            <input type="hidden" name="branch_id" id="b_id" value="{{ $branch_id }}">
                            <input type="hidden" name="m_id" id="m_id" value="{{ $id }}">
                            <input type="hidden" name="is_search" id="isserach" value="{{ $is_search }}">

                            <input type="submit" class="btn bg-dark legitRipple ml-2" data-extension="2" style="float: right;margin-right: 10px" value="Print">

                            <!-- <a href="javascript:void(0)" data-href="{{URL::to('/')}}/admin/print-tds-payable" target="_blank"><button type="button" class="btn bg-dark legitRipple ml-2" data-extension="2" style="float: right;">Print</button></a> -->
                        </form>

                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card">
                    
                    @foreach($plans as $val)
                    @php
                        //$records = App\Models\MemberInvestmentInterestTds::where('member_id',$id)->where('plan_type',$val->id)->orderby('id','DESC')->get();

                        $records = App\Models\Memberinvestments::where('member_id',$id)->where('plan_id','!=',1)->where('plan_id',$val->id);

                        if($planId !=''){
                            $records = $records->where('plan_id','=',$planId);
                        }

                        $records = $records->orderby('id','DESC')->get();
                        
                    @endphp
                    @if(count($records) > 0)

                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">{{ $val->name }}</h6>
                    </div>

                    <div class="">
                        <table id="m_interest_tds_listin" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Date</th>
                                    <th>Financial Year</th>
                                    <th>Payment Date</th>
                                    <th>Plan Name</th>
                                    <th>Account Number</th>
                                    <th>Interest Amount</th>
                                    <th>TDS Deduction</th>
                                    <th>Cr.</th>
                                    <th>Dr.</th>
                                    <th>Balance</th>   
                                </tr>
                            </thead>  
                            <tbody>
                                
                                @foreach($records as $key => $rval)

                                @php
                                    $interestAmount = App\Models\MemberInvestmentInterest::where('investment_id',$rval->id);

                                    if($startDate !=''){
                                        $interestAmount = $interestAmount->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]); 
                                    }

                                    if($planId !=''){
                                        $interestAmount = $interestAmount->where('plan_type','=',$planId);
                                    }

                                    if($branch_id !=''){
                                        $interestAmount = $interestAmount->where('branch_id','=',$branch_id);
                                    }

                                    $interestAmount = $interestAmount->sum('interest_amount');



                                    $interestTdsAmount = App\Models\MemberInvestmentInterestTds::where('investment_id',$rval->id);

                                    if($startDate !=''){
                                        $interestTdsAmount = $interestTdsAmount->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]); 
                                    }

                                    if($planId !=''){
                                        $interestTdsAmount = $interestTdsAmount->where('plan_type','=',$planId);
                                    }

                                    if($branch_id !=''){
                                        $interestTdsAmount = $interestTdsAmount->where('branch_id','=',$branch_id);
                                    }
                                    $fYear = 'N/A';
                                    $pDate = 'N/A';
                                    if(isset($rval->demandadvice))
                                    {
                                        $fYear = date('Y',strtotime($rval->demandadvice->date)); 
                                        $pDate = date('d/m/Y',strtotime($rval->demandadvice->date)); 
                                    }
                                    $interestTdsAmount = $interestTdsAmount->sum('tdsamount_on_interest');

                                @endphp

                                <tr>

                                    <td>{{ $key+1 }}</td>

                                    <td>{{ date("d/m/Y", strtotime(convertDate($rval->created_at))) }}</td>
                                    <td>{{ $fYear }}</td>
                                    <td>{{ $pDate }}</td>
                                    <td>{{ getPlanDetail($rval->plan_id)->name }}</td>
                                    <td>{{ $rval->account_number }}</td>
                                    <td>{{ number_format($interestAmount,2) }}</td>
                                    <td>{{ number_format($interestTdsAmount,2) }}</td>
                                    @php
                                    $tdsAmount = $interestAmount-$interestTdsAmount;
                                    @endphp
                                    <td>{{ number_format($tdsAmount,2) }}</td>
                                    @if($rval->is_mature == 0)
                                    <td>{{ number_format($tdsAmount,2) }}</td>
                                    <td>0</td>
                                    @else
                                    <td>0</td>
                                    <td>{{ number_format($tdsAmount,2) }}</td>
                                    @endif
                                    
                                </tr>
                                @endforeach
                            </tbody>                  
                        </table>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.member.partials.listing_script')
@stop