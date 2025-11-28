@extends('templates.admin.master')

@section('content')
<div class="content"> 
    <div class="row">
        <div class="col-md-12">
            <div class="card account_box">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Account information</h6>
                </div>
                <div class="card-body">
					<div class="">
						<form action="{{route('admin.account.viewotp')}}" method="post" id="account_form">
							@csrf
							<div class="form-group row">
								<label class="col-form-label col-lg-2">Username:</label>
								<div class="col-lg-10">
									<input type="hidden" name="id" value="{{ $val->id }}" class="id">
									<input type="text" name="username" value="{{$val->username}}" class="form-control username">
								</div>
							</div>                         
							<div class="form-group row">
								<label class="col-form-label col-lg-2">Password:</label>
								<div class="col-lg-10">
									<input type="tpassword" name="password"  class="form-control password" required>
								</div>
							</div>          
							<div class="text-right">
								<button type="submit" class="btn bg-dark">Submit<i class="icon-paperplane ml-2"></i></button>
							</div>
						</form>
					</div>
            	</div>
			</div>
			<div class="card otp_box" style="display: none;">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">OTP information</h6>
                </div>
                <div class="card-body">
					<div class="">
						<form action="{{route('otp_account_varified')}}" method="post" id="otp_form">
							@csrf
							<input type="hidden" name="id" id="id" />
							<input type="hidden" name="username" id="username" />
							<input type="hidden" name="password" id="password" />
							<div class="form-group row">
								<label class="col-form-label col-lg-2">OTP:</label>
								<div class="col-lg-10">
									<input type="text" name="otp" class="form-control otp" maxlength="4" required />
								</div>
							</div>          
							<div class="text-right">
								<button type="submit" class="btn bg-dark">Verify</button>
							</div>
						</form>
					</div>
            	</div>
			</div>
        </div>    
    </div>
</div>


<script type="text/javascript">
$(document).ready(function() {
    $('#account_form').validate({ // initialize the plugin
        
		rules: {
            'username' : 'required',
            'password' : 'required',
        },
        submitHandler: function(form) {
            var post_url = $('#account_form').attr("action"); //get form action url
            var request_method = $('#account_form').attr("method"); //get form GET/POST method
            var form_data = $('#account_form').serialize(); //Encode form elements for submission
			
            $.ajax({
                url : post_url,
                type: request_method,
                data : form_data
            }).done(function(response){
				var json_data = JSON.parse(response);
                if(json_data['msg_type']=='success'){
					//alert(json_data['otp']);
					$('.account_box').hide();
                    $('.otp_box').show();
                    var user = $('.username').val();
                    var password = $('.password').val();
					var id = $('.id').val();
					
                    $('#username').val(user);
                    $('#password').val(password);
					$('#id').val(id);

                    $('#otp_form').attr('action',"{!! route('otp_account_varified') !!}");
                }else if(json_data['msg_type']=='success' && json_data['otp'] == 1){
                    var base_url = window.location.origin;
                    window.location.href = "/admin/account";
                }else{
                    swal("Error!", ""+response.view+"", "error");
                }
            });
        }
    });


    $('#otp_form').validate({ // initialize the plugin
        rules: {
            'otp' : {number: true,required: true,minlength: 4,maxlength:4},
        },
        submitHandler: function(form) {
            var post_url = $('#otp_form').attr("action"); //get form action url
            var request_method = $('#otp_form').attr("method"); //get form GET/POST method
            var form_data = $('#otp_form').serialize(); //Encode form elements for submission
            $.ajax({
                url : post_url,
                type: request_method,
                data : form_data
            }).done(function(response){
				var json_data = JSON.parse(response);

                if(json_data['msg_type']=='success'){
					swal("Success!", ""+json_data.view+"", "success");
                    location.reload();
                }else{
                    swal("Error!", ""+json_data.view+"", "error");
                }
            });
        }
    });

    /*$(document).on('click','#resendotp',function(){
        var uId = $('.uId').val();
        var pNumber = $('.pNumber').val();

        $.ajax({
            type: "POST",  
            url: "{!! route('resendAdminotp') !!}",
            data: {'uId':uId,'pNumber':pNumber},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.msg_type=='success'){
                    swal("Success!", ""+response.view+"", "success");
                }else{
                    swal("Error!", ""+response.view+"", "error");
                }
            }
        });       
        
    });*/
});
</script>
@stop