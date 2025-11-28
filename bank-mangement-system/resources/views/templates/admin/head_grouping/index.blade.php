@extends('templates.admin.master')



@section('content')

<style>
        .search-table-outter { overflow-x: scroll; }
        .frm{ min-width: 200px; }
       
    </style>

    <div class="loader" style="display: none;"></div>



    <div class="content">

        <div class="row">

            <div class="col-md-12">

                <!-- Basic layout-->

                <div class="card">

                    <div class="">

                        <div class="card-body" >

                            <form method="post" id="account_head_form">

                                @csrf
                               <!--
                               <div class="form-group row">
                                   <label class="col-form-label col-lg-2">Account Head <sup>*</sup></label>
                                   
                                   <div class="col-lg-4 error-msg">
                                       <select name="account_head" id="account_head" class="form-control frm">
                                       <option value="">Select Account Head</option>
                                           @foreach( $account_heads as $heads)
                                               <option value="{{ $heads->head_id}}" >{{ $heads->sub_head }}</option>
                                           @endforeach
                                       </select>
                                   </div>
                               </div>
                               
                               <div class="form-group row">
                                   <label class="col-form-label col-lg-2">Change Account Head <sup>*</sup></label>
                                   
                                   <div class="col-lg-4 error-msg">
                                       <select name="change_account_head" id="change_account_head" class="form-control frm">
                                       <option value="">Select Account Head</option>
                                       </select>
                                   </div>
                               </div>  -->
                               <input type="hidden" class="create_application_date" name="create_application_date" id="create_application_date">
                               
                               <div class="form-group row">
                                   <label class="col-form-label col-lg-2">Chlid Head Type</label>
                                   
                                   <div class="col-lg-4 error-msg">
                                       <select name="head_type" id="head_type" class="form-control frm">
                                           <option value="0">Select Head Type</option>
                                           <option value="4">EXPENSES</option>
                                           <option value="3">INCOMES</option>
                                           <option value="2">ASSETS</option>
                                           <option value="1">LIABILITY</option>
                                       </select>
                                   </div>

                                   <div class="col-lg-4 error-msg" id="headDetails" style="display: none;">
                                       <p><b>Head Details</b>: - </p>
                                       
                                   </div>

                               </div>
                               
                               
                               <div class="form-group row">
                                   <label class="col-form-label col-lg-2">Child Account Head </label>
                                   
                                   <div class="col-lg-4 error-msg">
                                       <select name="account_head" id="account_head" class="form-control frm">
                                       <option value="">Select Account Head</option>
                                       </select>
                                   </div>

                                  
                                   
                                   <div class="col-lg-4 error-msg" id="details" style="display: none;">
                                       <p>Comapies: - </p>
                                       
                                   </div>
                               </div>
                               
                               <div class="form-group row">
                                   <label class="col-form-label col-lg-2">Parent Account Type</label>
                                   
                                   <div class="col-lg-4 error-msg">
                                       <select name="parent_account_type" id="parent_account_type" class="form-control frm">
                                           <option value="0">Select Head Type</option>
                                           <option value="4">EXPENSES</option>
                                           <option value="3">INCOMES</option>
                                           <option value="2">ASSETS</option>
                                           <option value="1">LIABILITY</option>
                                       </select>
                                   </div>
                                   
                               </div>
                               <div class="form-group row">
                                   <label class="col-form-label col-lg-2">Parent Account Head</label>
                                   
                                   <div class="col-lg-4 error-msg">
                                       <select name="change_account_head" id="change_account_head" class="form-control frm">
                                       <option value="">Select Account Head</option>
                                       </select>
                                   </div>
                                   <div class="col-lg-4 error-msg" id="detailss" style="display: none;">
                                       
                                       
                                   </div>
                               </div>

                               <div class="text-right mt-4">
                                   <input type="button" name="submitAccountHead" id="submitAccountHead" value="Submit" class="btn btn-primary submit">
                                   <input type="reset" name="resetform" value="Reset" class="btn btn-gray legitRipple reset">
                               </div>

                           </form>

                        </div>

                    </div>

                    <!-- /basic layout -->

                </div>

            </div>

        </div>

    </div>

@stop

@section('script')

    @include('templates.admin.head_grouping.partials.script')

@stop

