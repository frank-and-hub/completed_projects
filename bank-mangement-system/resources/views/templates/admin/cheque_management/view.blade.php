@extends('templates.admin.master')

@section('content')

<div class="content"> 
  <div class="row"> 
        @if ($errors->any())
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
          <h4 class="card-title mb-3">Cheque Details</h4>
        </div>
        <div class="card-body">
          <div class="row">
                              <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-lg-5">Bank Name </label>
                                        <div class="col-lg-7 ">
                                            {{$cheque['samrddhBank']->bank_name}}
                                        </div>
                                    </div>
                              </div>
                              <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-lg-5">Account No.</label>
                                        <div class="col-lg-7 ">
                                            {{$cheque['samrddhAccount']->account_no}}
                                        </div>
                                    </div>
                              </div>
                              <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-lg-5">Cheque No.</label>
                                        <div class="col-lg-7 ">
                                            {{$cheque->cheque_no}}
                                        </div>
                                    </div>
                              </div> 
                              <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-lg-5">Cheque Created Date</label>
                                        <div class="col-lg-7 ">
                                            {{date("d/m/Y", strtotime($cheque->cheque_create_date))}}
                                        </div>
                                    </div>
                              </div>
                              <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-lg-5">Is Used </label>
                                        <div class="col-lg-7 ">
                                            <?php
                                            if($cheque->is_use==1)
                                            {
                                                $use = 'Yes';
                                            }
                                            else
                                            {
                                                $use = 'No';     
                                            } 

                                            ?>
                                            {{  $use }}
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
                                              $status = 'New';
                                          }                
                                          if($cheque->status==2)
                                          {
                                              $status = 'Pending';
                                          }
                                          if($cheque->status==3)
                                          {
                                              $status = 'cleared';
                                          }
                                          if($cheque->status==4)
                                          {
                                              $status = 'Canceled & Re-issued';
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
                              @if($cheque->status==0)

                              <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-lg-5">Cheque Deleted Date</label>
                                        <div class="col-lg-7 ">
                                            {{date("d/m/Y", strtotime($cheque->cheque_delete_date))}}
                                        </div>
                                    </div>
                              </div>
                              @endif
                              @if($cheque->status==4)

                              <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-lg-5">Cheque Cancel & Re-issue Date</label>
                                        <div class="col-lg-7 ">
                                            {{date("d/m/Y", strtotime($cheque->cheque_cancel_date))}}
                                        </div>
                                    </div>
                              </div>
                              <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-lg-5">Cheque Cancel & Re-issue Remark</label>
                                        <div class="col-lg-7 ">
                                            {{$cheque->remark_cancel}}
                                        </div>
                                    </div>
                              </div>
                              @endif
                            </div>
        </div>
      </div>
    </div>

        

  </div>
</div>
@stop