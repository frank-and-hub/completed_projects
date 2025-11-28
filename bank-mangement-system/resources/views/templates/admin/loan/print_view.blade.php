@extends('templates.admin.master')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">

                <div class="">
                    <table class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Print Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>  
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Print Loan Form</td>
                                <td><a href="{{ $formPrintUrl }}" target="_blank"><i class="fa fa-print" aria-hidden="true"></i></a></a></td>
                            </tr>
                            <!--<tr>
                                <td>2</td>
                                <td>Loan Term Condition</td>
                                <td><a href="{{ $formTermConditionUrl }}"><i class="fa fa-print" aria-hidden="true"></i></a></a></td>
                            </tr>-->
                        </tbody>                  
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- <div class="modal fade" id="loan-rejected" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
  <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document" style="max-width: 600px !important; ">
    <div class="modal-content">
      <div class="modal-body p-0">
        <div class="card bg-white border-0 mb-0">
          <div class="card-header bg-transparent pb-2ß">
            <div class="text-dark text-center mt-2 mb-3">Reject Loan Request</div>
          </div>
          <div class="card-body px-lg-5 py-lg-5">
            <form action="" method="post" id="loan-reject-form" name="loan-reject-form">
              @csrf
              <input type="hidden" name="loan_id" id="loan_id" value="">
              <div class="form-group row">
                <div class="col-lg-12">
                  <textarea name="rejection" name="rejection" rows="6" cols="50" class="form-control" placeholder="Remark"></textarea>
                </div>
              </div>  

              <div class="text-right">
                <input type="submit" name="submitform" value="Submit" class="btn btn-primary">
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div> -->
@stop

@section('script')
<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>
@include('templates.admin.loan.partials.script')
@endsection