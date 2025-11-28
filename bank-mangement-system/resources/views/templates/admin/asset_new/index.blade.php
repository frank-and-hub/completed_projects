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
                    <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                    @csrf
                      <div class="row">
                            @if(Auth::user()->branch_id < 1)
                            @php
                            $dropDown = $company;
                            $filedTitle = 'Company';
                            $name = 'company_id';
                            @endphp
                            @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true])
                            {{--
                           @include('templates.GlobalTempletes.new_role_type',['dropDown'=>$dropDown,'filedTitle'=>$filedTitle,'name'=>$name,'value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch_id','apply_col_md'=>true,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch'])
                           --}}
                            @else
                            <input type="hidden" name="branch_name" id="branch" value="{{Auth::user()->branch_id}}">                         
                            @endif
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Fixed Assets</label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="category" name="category">
                                            <option value="">Select categories</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                  <div class="form-group row">
                                      <label class="col-form-label col-lg-12">Assets </label>
                                      <div class="col-lg-12 error-msg">
                                          <select class="form-control" id="status" name="status">
                                              <option value="">Select Asset</option>
                                             
                                              <option value="0">Working</option>
                                              <option value="1">Damage</option>
                                              
                                          </select>
                                      </div>
                                  </div>
                              </div>
                            <div class="col-md-12">
                                <div class="form-group row"> 
                                    <div class="col-lg-12 text-right" >
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="export" id="export" value="">
                                        <button type="button" class=" btn bg-dark legitRipple submit" onClick="searchForm()" >Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple reset" id="reset_form" onClick="resetForm()" >Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                </div>
            </div>  
                
            <div class="col-md-12 table-section hideTableData">
                <div class="card">
                    <div class="card-header bg-transparent header-elements-inline">
                        <h3 class="mb-0 text-dark">Assets</h3>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        </div>
                    </div>
                    <div class="">
                        <table id="assets_list" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Company Name</th>
                                    <th>Branch Name</th>
                                    <th>Account Head</th>
                                    <th>Sub-Account Head Name</th>
                                    <th>Demand Date</th>
                                    <th>Advice Date</th>
                                    <th>Amount</th>
                                    <th>Party Name</th>
                                    <th>Mobile no.</th>
                                    <th>Bill no.</th>
                                    <th>Bill copy</th>
                                    <th>Status </th>
                                    <th>Action</th>
                                            
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div> 
                 
        </div> 
    </div>
</div> 

@stop

@section('script')
    @include('templates.admin.asset_new.partials.script_list')
@stop