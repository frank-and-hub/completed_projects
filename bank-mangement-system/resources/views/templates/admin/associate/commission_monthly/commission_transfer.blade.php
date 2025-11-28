@extends('templates.admin.master')

@section('content')
<div class="content">
    <div class="row">  
        @if($errors->any())
            <div class="col-md-12">
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
            </div>
        @endif
        <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Commission Calculate </h6>
                    </div>
                    <div class="card-body">
                        <form action="{!! route('admin.associate.commissionCreate') !!}" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                        <input type="hidden" name="created_at" class="created_at">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Start  Date</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" class="form-control  " name="start_date" id="start_date"  value="@isset($start_date) {{ $start_date }} @endisset"> 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">End  Date</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input readonly="" type="text" class="form-control  " name="end_date" id="end_date"  value="@isset($end_date) {{ $end_date}} @endisset"> 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                               

                                <div class="col-md-12">
                                    <div class="form-group text-right"> 
                                        <div class="col-lg-12 page">

                                            <button type="submit" class=" btn bg-dark legitRipple"   >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
        </div>
        @isset($code)
        <div class="col-md-12" id='hide_div'>
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Associate Listing (Total Commission Amount)</h6>
                    </div>
                    <form action="{!! route('admin.associate.commissionSave') !!}" method="post" enctype="multipart/form-data" id="transfer" name="transfer">
                        @csrf 
                        <input type="hidden" name="created_at" class="created_at">
                    <div class="card-body">
                        <div class="row">
                           
                            <div class="col-md-12">
                                <div class="table-responsive">
                                <table id="member_listing" class="table table-flush">
                                    <thead>
                                        <tr>                                    
                                        <th>S.No</th>
                                        <th>Associate code</th> 
                                        <th>Associate Name</th> 
                                        <th>Associate Carder </th> 
                                        <th>Total Amount</th> 
                                        <th>Collection Amount</th> 
                                        <th class="text-center">Action</th>    
                                        </tr>
                                    </thead> 
                                    <tbody>
                                    @if(count($total_commission)>0)
                                    <?php 
                                        $total=0;
                                        $totalFinalAmount=0;
                                        $totalCollection=0;
                                        $totalFuleAmount=0;
                                    ?>
                                         @foreach($total_commission as $index =>  $val) 
                                         <?php  $total=$total+$val->total;

                                         $memData=memberDetailGetByID($val->member_id);?> 
                                         <tr>
                                             <td>{{ $index+1 }}</td>
                                             <td>{{ $memData->associate_no }}</td>
                                             <td>{{ $memData->first_name }} {{$memData->last_name  }}</td>
                                             <td>{{ getCarderNameFull($memData->current_carder_id) }}</td> 
                                             <td>{{ $val->total}}</td> 
                                             <td>
                                                <?php 
                                                $reCollection=getTotalCollection($val->member_id,$start_date,$end_date) 
                                                ?>
                                                 {{ $reCollection }} 
                                             </td> 

                                             <td><a target="_blank" href='{{ URL::to("admin/associate-commission-detail/".$val->member_id."?start=$start_date&end=$end_date")}}' title="Commission Detail"><i class="fa fa-percent  mr-2"></i></a>

                                                <a target="_blank" href='{{ URL::to("admin/associate-loan-commission-detail/".$val->member_id."?start=$start_date&end=$end_date")}}' title="Commission Detail"><i class="fa fa-percent  mr-2"></i></a>
                                             </td>
                                             <input type="hidden" name="member_id[]" id="member_id" value="{{ $val->member_id }}">
                                             <input type="hidden" name="start_date_time" id="start_date_time" value="{{ $start_date_time }}">
                                             <input type="hidden" name="end_date_time" id="end_date_time" value="{{ $end_date_time }}">                                              
                                             <input type="hidden" name="amount[]" id="amount" value="{{ $val->total }}"> 
                                             <input type="hidden" name="collection[]" id="collection" value="{{ $reCollection }}">                                     

                                         </tr>                                      
                                         @endforeach
                                         </tbody>
                                         
                                    @else

                                        <tr>
                                            <td colspan="6" align="center">No record </td>
                                        </tr>
                                        </tbody>
                                    @endif
                                    
                                </table>
                            </div>
                            </div>
                             
                 
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                @if(count($total_commission)>0)
                                <button type="button" class=" btn bg-dark legitRipple"  id="submit_transfer">Calculate Total Commission</button>
                                @endif
                             </div>
                         </div>
                     </div>
                </form> 
                </div>           
        </div>
        @endisset
    </div>
</div>
@stop

@section('script')
@include('templates.admin.associate.commission.transfer_script')
@stop
