@extends('templates.admin.master')

@section('content')
@section('css')
<style>
    .hideTableData {
        display: none;
    }
</style>
@endsection
<div class="content">
    <div class="row">  
        <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
                    </div>
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="kotabusinessFilter" name="kotabusinessFilter">
                        @csrf
                            <div class="row">
                                <?php
                                    // $finacialYear=getFinacialYear();
                                    // $startDate=date("d/m/Y", strtotime($finacialYear['dateStart']));
                                    // $endDate=date("d/m/Y");
                                ?>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Start Date</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                    <input type="text" class="form-control" name="start_date" id="start_date" value=""> 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">End Date</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                <input type="text" class="form-control" name="end_date" id="end_date" value=""> 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                 @if(Auth::user()->branch_id<1)
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="branch_id" name="branch_id">
                                                <option value="">All</option>
                                                @foreach( App\Models\Branch::pluck('name', 'id') as $key => $val )
                                                    <option value="{{ $key }}" >{{ $val }}</option> 
                                                    <!-- <option value="{{ $key }}" @if($key==1) selected @endif >{{ $val }}</option>  -->
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                               @else
                                  <input type="hidden" name="branch_id" id="branch_id" value="{{Auth::user()->branch_id}}">                         
                                @endif
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Associate Code </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="associate_code" id="associate_code" class="form-control"  > 
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Associate Name  </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="associate_name" id="associate_name" class="form-control"  > 
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Cader </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="cader_id" name="cader_id">
                                                <option value="">Select Cader</option>
                                                @foreach( App\Models\Carder::pluck('name', 'id') as $key => $val )
                                                    <option value="{{ $key }}"  >{{ $val }}</option> 
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Business  Mode  </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="business_mode" name="business_mode">
                                                <option value="">Select Business Mode</option>
                                                <option value="0">Self</option>
                                                <option value="1">Team</option>
                                                <option value="2">All</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!--<div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Plan  </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="select_plan" name="select_plan">
                                                <option value="">Select Plan</option>
                                                @foreach( App\Models\Plans::pluck('name', 'id') as $key => $val )
                                                    <option value="{{ $key }}"  >{{ $val }}</option> 
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>-->

                                <div class="col-md-12">
                                    <div class="form-group text-right"> 
                                        <div class="col-lg-12 page">
                                            <input type="hidden" name="is_search" id="is_search" value="yes">
                                            <input type="hidden" name="kotareport_export" id="kotareport_export" value="">
                                            <button type="button" class=" btn bg-dark legitRipple" onClick="searchKotaBusinessForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetKotaBusinessForm()">Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
        </div>
        <div class="col-lg-12 table-section hideTableData">                
            <div class="card bg-white shadow">
                <div class="card-header bg-transparent header-elements-inline">
                    <h3 class="mb-0 text-dark">Quota Business Report</h3>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple exportkotabusiness ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        <button type="button" class="btn bg-dark legitRipple exportkotabusiness" data-extension="1">Export PDF</button>
                    </div>
                </div>

                <div class="table-responsive" id='kota-business-report_div'>
                    <table id="kota-business-report" class="table table-flush">
                       <thead class="">
                            <tr>
                                <th>S/N</th>                                  
                                <th>BR Name</th>
                                <th>BR Code</th>
                                <th>SO Name</th>
                                <th>RO Name</th>
                                <th>ZO Name</th>
                                <th>Associate Code</th>
                                <th>Associate Name</th>
                                <th>Associate Carder</th>                               
                                <th class="self" >Quota Business Target (Self Business) Amt</th>
                                <th class="self" >Achieved Target (Self Business) Amt</th>
                                <th class="self" >Quota Business Target (Self Business) %</th>
                                <th class="self">Achieved Target (Self Business) %</th>
                                 <th>Senior Code</th>
                                <th>Senior Name</th>
                                <th>Senior Carder</th> 
                                 <th class="team">Quota Business Target (Team Business) Amt</th>
                                <th class="team">Achieved Target (Team Business) Amt</th>
                                <th class="team">Quota Business Target (Team Business) %</th>
                                <th class="team">Achieved Target (Team Business) %</th>     
                                <th>Joining Date</th>
                                <th>Mobile Number</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="table-responsive" id='kota-business-report_div1' style="display: none">

                    <table id="kota-business-report1" class="table table-flush" >
                       <thead class="">
                            <tr>
                                <th>S/N</th>   
                                <th>BR Name</th>
                                <th>BR Code</th>
                                <th>SO Name</th>
                                <th>RO Name</th>
                                <th>ZO Name</th>                                  
                                <th>Associate Code</th>
                                <th>Associate Name</th>
                                <th>Associate Carder</th>                            
                                <th class="self" >Quota Business Target (Self Business) Amt</th>
                                <th class="self" >Achieved Target (Self Business) Amt</th>
                                <th class="self" >Quota Business Target (Self Business) %</th>
                                <th class="self" >Achieved Target (Self Business) %</th>
                                 <th>Senior Code</th>
                                <th>Senior Name</th>
                                <th>Senior Carder</th>
                                <th>Joining Date</th>
                                <th>Mobile Number</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="table-responsive" id='kota-business-report_div2' style="display: none">
                    <table id="kota-business-report2" class="table table-flush">
                       <thead class="">
                            <tr>
                                <th>S/N</th> 
                                <th>BR Name</th>
                                <th>BR Code</th>
                                <th>SO Name</th>
                                <th>RO Name</th>
                                <th>ZO Name</th> 
                                <th>Associate Code</th>
                                <th>Associate Name</th>
                                <th>Associate Carder</th>                                
                                 <th class="team">Quota Business Target (Team Business) Amt</th>
                                <th class="team" >Achieved Target (Team Business) Amt</th>
                                <th class="team" >Quota Business Target (Team Business) %</th>
                                <th class="team">Achieved Target (Team Business) %</th>
                                 <th>Senior Code</th>
                                <th>Senior Name</th>
                                <th>Senior Carder</th>    
                                <th>Joining Date</th>
                                <th>Mobile Number</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('script')
@include('templates.admin.associate.partials.kota_script')
@stop
