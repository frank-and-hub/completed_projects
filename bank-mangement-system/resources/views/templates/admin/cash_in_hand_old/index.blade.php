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
                        {{Form::open(['url'=>'#','method'=>'POST','enctype'=>'multipart/form-data','id'=>'filter','name'=>'filter'])}}
                          <div class="row">
                              <div class="col-md-4">
                                  <div class="form-group row">
                                      <label class="col-form-label col-lg-12">From Date </label>
                                      <div class="col-lg-12 error-msg">
                                           <div class="">
                                           {{Form::text('start_date','',['id'=>'start_date','class'=>'form-control','autocomplete'=>'off'])}}
                                             </div>
                                      </div>
                                  </div>
                              </div>
                              <div class="col-md-4">
                                  <div class="form-group row">
                                      <label class="col-form-label col-lg-12">To Date </label>
                                      <div class="col-lg-12 error-msg">
                                           <div class=""> 
                                            {{Form::text('end_date','',['id'=>'end_date','class'=>'form-control','autocomplete'=>'off'])}}
                                             </div>
                                      </div>
                                  </div>
                              </div>
                            @php
                                $dropDown = $company;
                                $filedTitle = 'Company';
                                $name = 'company_id';
                            @endphp
                            @include('templates.GlobalTempletes.new_role_type',['dropDown'=>$dropDown,'filedTitle'=>$filedTitle,'name'=>$name,'value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch_id','apply_col_md'=>true,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch'])

                              <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            {{Form::hidden('is_search','no',['id'=>'is_search','class'=>'form-control'])}}    
                                            {{Form::hidden('fund_transfer_export','',['id'=>'fund_transfer_export','class'=>'form-control'])}}
                                            <button type="button" class=" btn bg-dark legitRipple searchform" id="submitt" onClick="searchForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {{Form::close()}}
                    </div>
                </div>
            </div>

      <!-- Table -->
            <div class="col-md-12">
                <div class="card">
                    <div class="">
                        <table id="cashInHand" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Company Name</th>
                                    <th>BR Name</th>
                                    <th>BR Code</th>
                                    <th>Opening Balance</th>
                                    <th>Closing Balance</th>
                                    <th>Date</th>    
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
  </div>
@include('templates.admin.cash_in_hand.partials.script')
@stop