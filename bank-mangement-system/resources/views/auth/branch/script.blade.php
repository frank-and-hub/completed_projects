<script type="text/javascript">

$(document).ready(function() {

    $('#login-form').validate({ // initialize the plugin

        rules: {

            'branch' : {number: true,required: true},

            'password' : 'required',

        },

        submitHandler: function(form) {

            var post_url = $('#login-form').attr("action"); //get form action url

            var request_method = $('#login-form').attr("method"); //get form GET/POST method

            var form_data = $('#login-form').serialize(); //Encode form elements for submission

            $.ajax({

                url : post_url,

                type: request_method,

                data : form_data

            }).done(function(response){ //

                if(response.msg_type=='exist' && response.otp == 3){

                    swal({
                        title: "Are you sure?",
                        text: "Anyone already loggedIn with same details",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: '#DD6B55',
                        confirmButtonText: 'Yes, I am sure!',
                        cancelButtonText: "No, cancel it!",
                        closeOnConfirm: false,
                        closeOnCancel: true
                     },
                     function(isConfirm){

                       if (isConfirm == true){

                            var post_url = $('#login-form').attr("action"); 
                            var request_method = $('#login-form').attr("method"); 
                            $("#branchstatus").val("loginTrue");
                            var form_data = $('#login-form').serialize();

                            $.ajax({
                                    url : post_url,
                                    type: request_method,
                                    data : form_data
                                }).done(function(response){ 
                                    if(response.msg_type=='success' && response.otp == 0){
                                        var user = $('.login_branch').val();
                                        var password = $('.login_password').val();
                                        
                                        $('.branch').attr('readonly',true);
                                        $('.password').attr('readonly',true);
                                        $('.n_login_branch').val(user);
                                        $('.n_login_password').val(password);
                                        $('.pNumber').val(response.pnumber);
                                        $('.uId').val(response.uId);
                                        $('.sign-in-box').hide();
                                        $('.varification-box').show();
                                        $('#varification-form').attr('action',"{!! route('otpvarified') !!}");
                                        swal.close();
                                    }else if(response.msg_type=='success' && response.otp == 1){
                                            var base_url = window.location.origin;
                                            window.location.href = "/branch/noticeboard";
                                        }else{
                                        swal("Error!", ""+response.view+"", "error");
                                    }
                            });
                        }else{
                            //return false;
                            
                             window.location.href = "/login";
                        }
                     });
                }
                else

                if(response.msg_type=='success' && response.otp == 0){

                    var user = $('.login_branch').val();

                    var password = $('.login_password').val();

                    

                    $('.branch').attr('readonly',true);

                    $('.password').attr('readonly',true);

                    $('.n_login_branch').val(user);

                    $('.n_login_password').val(password);

                    $('.pNumber').val(response.pnumber);

                    $('.uId').val(response.uId);

                    $('.sign-in-box').hide();

                    $('.varification-box').show();

                    $('#varification-form').attr('action',"{!! route('otpvarified') !!}");

                    //window.location.href = "branch/dashboard";

                }else if(response.msg_type=='success' && response.otp == 1){

                    var base_url = window.location.origin;

                    window.location.href = "/branch/noticeboard";

                }else{

                    swal("Error!", ""+response.view+"", "error");

                }

            });

        }

    });





    $('#varification-form').validate({ // initialize the plugin

        rules: {

            'otp' : {number: true,required: true,minlength: 4,maxlength:4},

        },

        submitHandler: function(form) {



            var post_url = $('#varification-form').attr("action"); //get form action url

            var request_method = $('#varification-form').attr("method"); //get form GET/POST method

            var form_data = $('#varification-form').serialize(); //Encode form elements for submission

            $.ajax({

                url : post_url,

                type: request_method,

                data : form_data

            }).done(function(response){ //

                if(response.msg_type=='success'){

                    var base_url = window.location.origin;

                    window.location.href = "/branch/dashboard";

                }else{

                    swal("Error!", ""+response.view+"", "error");

                }

            });

        }

    });



    $(document).on('click','#resendotp',function(){

        var uId = $('.uId').val();

        var pNumber = $('.pNumber').val();



        $.ajax({

            type: "POST",  

            url: "{!! route('resendotp') !!}",

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

        

    });

});

    

console.log("Test");

function showMacAddress(){



    var obj = new ActiveXObject("WbemScripting.SWbemLocator");

    var s = obj.ConnectServer(".");

    var properties = s.ExecQuery("SELECT * FROM Win32_NetworkAdapterConfiguration");

    var e = new Enumerator (properties);





    var output;

    output='<table border="0" cellPadding="5px" cellSpacing="1px" bgColor="#CCCCCC">';

    output=output + '<tr bgColor="#EAEAEA"><td>Caption</td><td>MACAddress</td></tr>';

    while(!e.atEnd())



    {

        e.moveNext();

        var p = e.item ();

        if(!p) continue;

        output=output + '<tr bgColor="#FFFFFF">';

        output=output + '<td>' + p.Caption; + '</td>';

        output=output + '<td>' + p.MACAddress + '</td>';

        output=output + '</tr>';

    }



    output=output + '</table>';

    document.getElementById("box").innerHTML=output;

}

</script>