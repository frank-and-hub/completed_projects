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
                        <form action="{{route('admin.ssbaccount.ssbaccountdetailssearch')}}" method="post" enctype="multipart/form-data" id="filter" name="filter">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="col-form-label col-lg-12">Select Plan </label>
                                    <select class="form-control" id="plan_type" name="plan_type">
                                        <option value="1">Ssb</option>                                    
                                        <option value="2" >Ssb Child</option>                                    
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">User type </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="user_type" name="user_type">
                                                    <option value="1" >Member</option>
                                                    <option value="2" >Associate</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <button type="button" class=" btn bg-dark legitRipple" name="associate_code" id="search"  >Submit</button>
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
                    <div class=""> 
                    <form action="{{route('admin.ssbaccount.activatesetting')}}" method="post" enctype="multipart/form-data" id="filter" name="filter">
                            @csrf                       
                        <table class="table datatable-show-all" id="ssbac_detail">
                            <thead>
                                <tr>
                                    <th width="5%">S/N</th>
                                    <th width="15%">Created Date</th>
                                    <th width="15%">Plan</th>
                                    <th width="15%">User Type</th>
                                    <th width="15%">Amount</th>                    
                                    <th width="15%">Action</th>    
                                </tr>
                            </thead>  
                            <tbody id="data"></tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6">
                                        <div class="col-md-12">
                                            <div class="form-group row"> 
                                                <div class="col-lg-12 text-right" >
                                                    <input type="submit" class=" btn bg-dark legitRipple" name="update" id="update" value="Update" >
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>                         
                        </table>                        
                    </form>
                    </div>
					
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.ssb_account.partials.ssbaccount_script')
@stop
