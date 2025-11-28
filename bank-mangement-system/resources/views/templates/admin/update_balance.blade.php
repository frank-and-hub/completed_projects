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
                        <h6 class="card-title font-weight-semibold">{{$title}}</h6>
                    </div> 
                    <div class="card-body">
                        @if($title == 'Update cash Balance')
                        <form action="{!! route('admin.update_branch_cash_daywise') !!}" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @else
                        <form action="{!! route('admin.update_bank_balance_daywise') !!}" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @endif
                        @csrf
                        <input type="hidden" name="created_at" class="created_at">
                            <div class="row">
                                
                                
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">Select Bank</label>
                                        <div class="col-lg-5 error-msg">
                                        @if($title == 'Update cash Balance')
                                        <select class="form-control" id="branch_id" name="branch_id">

                                          

                                            @foreach($banks as $bank)

                                            <option value="{{$bank->id}}">{{$bank->name}}</option>

                                            @endforeach

                                            </select>
                                        @else
                                        <select class="form-control" id="bank_name" name="bank_name">

                                            <option value="">Select Bank</option>

                                            @foreach($banks as $bank)
                                            
                                            <option value="{{$bank->id}}">{{$bank->bank_name}}</option>

                                            @endforeach

                                            </select>
                                            @endif
                                        </div>
                                    </div>
                                </div>
								<div class="col-md-12">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">Select Start Date</label>
                                        <div class="col-lg-5 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control create_application_date " name="start_date" id="start_date"  autocomplete="off" > 

                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">Select End Date</label>
                                        <div class="col-lg-5 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control create_application_date " name="end_date" id="end_date"  autocomplete="off" > 

                                        </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-12 text-center">
                                     <button type="Submit" class=" btn bg-dark legitRipple" >Update</button>
                                    
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script type="text/javascript">
$(document).ready(function () {
   
  var date = new Date();
	  $('#start_date').datepicker({
	    format: "dd/mm/yyyy",
	    todayHighlight: true,  
	    endDate: date, 
	    autoclose: true
	  });
	  $('#end_date').datepicker({
	    format: "dd/mm/yyyy",
	    todayHighlight: true,  
	    endDate: date, 
	    autoclose: true
	  });
	  
	  $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    	});
		$( document ).ajaxComplete(function() {
			$( ".loader" ).hide();
		});
		$('#load_button_save').submit(function() {
		$( ".loader" ).show();
    	});
$( ".loader" ).fadeOut();
});
$(window).load(function() {
	$( ".loader" ).fadeOut();
  });
</script>
@stop
