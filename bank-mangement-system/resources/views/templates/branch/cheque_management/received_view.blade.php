@extends('layouts/branch.dashboard')

@section('content')

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title"> 
                        <h3 class="">Received Cheque Management</h3>                    
                </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-header header-elements-inline">
                    <h3 class="mb-0 text-dark">Cheque/UTR  Details</h3>
                </div>
                <div class="card-body">
                         
                            <div class="row">
                                
                              <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-lg-5">Company Name </label>
                                    <div class="col-lg-7 ">
                                        {{$cheque['receivedCompany']->name}}
                                    </div>
                                </div>
                          </div>

                              <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-lg-5">Branch Name </label>
                                        <div class="col-lg-7 ">
                                            {{$cheque['receivedBranch']->name}}
                                        </div>
                                    </div>
                              </div>
                              <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-lg-5">Cheque/UTR  Bank Name</label>
                                        <div class="col-lg-7 ">
                                            {{$cheque->bank_name}}
                                        </div>
                                    </div>
                              </div>
                              <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-lg-5">Cheque/UTR  Branch Name</label>
                                        <div class="col-lg-7 ">
                                            {{$cheque->branch_name}}
                                        </div>
                                    </div>
                              </div>  
                              <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-lg-5">Cheque/UTR  Number</label>
                                        <div class="col-lg-7 ">
                                            {{$cheque->cheque_no}}
                                        </div>
                                    </div>
                              </div>
                              <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-lg-5">Cheque/UTR  Account No </label>
                                        <div class="col-lg-7 ">
                                            {{$cheque->cheque_account_no}}
                                        </div>
                                    </div>
                              </div>
                              <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-lg-5">Account Holder Name </label>
                                        <div class="col-lg-7">
                                            {{$cheque->account_holder_name}}
                                        </div>
                                    </div>
                              </div>
                              <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-lg-5">Cheque/UTR  Date </label>
                                        <div class="col-lg-7">
                                            {{date("d/m/Y", strtotime($cheque->cheque_create_date))}}
                                        </div>
                                    </div>
                              </div>
                              <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-lg-5">Amount</label>
                                        <div class="col-lg-7">
                                        {{ number_format((float)$cheque->amount, 2, '.', '')}}<img src='{{url('/')}}/asset/images/rs.png' width='7'>
                                        </div>
                                    </div>
                              </div>
                              <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-lg-5">Remark </label>
                                        <div class="col-lg-7">
                                            {{$cheque->remark}}
                                        </div>
                                    </div>
                              </div>
                              <div class="col-md-12"><h4 class="  ">Deposit Bank Details</h4></div>
                              <div class="col-md-12">
                              </div>
                              <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-lg-5">Cheque/UTR  Deposit Date </label>
                                        <div class="col-lg-7">
                                            {{date("d/m/Y", strtotime($cheque->cheque_deposit_date))}}
                                        </div>
                                    </div>
                              </div>
                              <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-lg-5">Deposit Bank Name </label>
                                        <div class="col-lg-7">
                                            {{$cheque['receivedBank']->bank_name}}
                                        </div>
                                    </div>
                              </div>
                              <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-lg-5">Deposit Account Number </label>
                                        <div class="col-lg-7">
                                            {{$cheque['receivedAccount']->account_no}}
                                        </div>
                                    </div>
                              </div>
                              <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-lg-5">Clearing Date </label>
                                        <div class="col-lg-7">
                                           <?php 
                                            if($cheque->clearing_date){
                                                $clearing_date = date("d/m/Y", strtotime($cheque->clearing_date));
                                            }else{
                                                $clearing_date = 'N/A';
                                            }
                                            ?>
                                            {{$clearing_date}}
                                        </div>
                                    </div>
                              </div>

                              <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-lg-5">Status</label>
                                        <div class="col-lg-7">
                                          <?php
                                          $status = 'New';
                                          if($cheque->status==1)
                                          {
                                              $status = 'Pending';
                                          }                
                                          if($cheque->status==2)
                                          {
                                              $status = 'Apporved';
                                          }
                                          if($cheque->status==3)
                                          {
                                              $status = 'cleared';
                                          }
                                          if($cheque->status==0)
                                          {
                                              $status = 'Deleted';
                                          }
                                          ?>
                                            {{$status}}
                                        </div>
                                    </div>
                              </div>
                            </div>
                        
                    </div>
            </div>
            </div>
        </div> 
</div>


@stop
