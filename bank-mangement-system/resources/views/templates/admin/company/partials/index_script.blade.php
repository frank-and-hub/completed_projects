<script>
$(document).ready(function() {
    $(document).ready((e)=>{
        // window.addEventListener('load', function() {
        //     var myButton = document.getElementById('disablebtn');
        //     myButton.disabled = false;
        // });
        $('#preview').on('click',function(e){
            e.preventDefault();
            $('#company_image').click();
        });
        $('#company_image').change(function(){
            const file = this.files[0];
            // console.log(file);   
            if (file){
            let reader = new FileReader();
            reader.onload = function(event){
                // console.log(event.target.result);
                $('#preview').attr('src', event.target.result);
            }
            reader.readAsDataURL(file);
            }
        });
    });
    $(window).on('load', function() {
        $('.loader').hide();
    });
    $(window).on('ready', function() {
        $('.loader').show();
    });
	var result = false;
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
    $('.input_dis').mouseover(function(){
        $('#sortable2 li div div .disable').attr('disabled','disabled');
        $('#sortable1 li div div .disable').removeAttr('disabled','disabled');
    });
    
    $("#fa_code_from").blur(function() {
        var num = $(this).val();
        if (num % 100 != 0) {
            swal({
                title: "Number should be multiple of 100",
                // text: "Do you want to delete this money back setting?",
                type: "warning",
                // showCancelButton: true,
            }),
            $("#fa_code_from").val("");
            $("#fa_code_to").val("");
            return false;
        } 
    });
    
    $('#update_company').on('click', e => {
        e.preventDefault();
        if ($('#company_register_form').valid()) {
            var companyRegisterForm = new FormData(document.forms['company_register_form']);
            $('.loader').show();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.companies.companyRegisterForm_update') !!}",
                dataType: 'JSON',
                data: companyRegisterForm,
                cache: false,
                contentType: false,
                processData: false,
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},                
                success: function(e) {
                    $('.loader').hide();
				JSON.parse(JSON.stringify(e)).data > 0 ? swal({title:'Successfully!',text:'Company Details Updated Successfully',type:'success' },function(isConfirm){ window.location.href = "{{route('admin.companies.show')}}"; }) : swal('error !','Company Details have some error ','error');
                }
            });
        }
    });
    // $(document).on({"contextmenu": (e) => {e.preventDefault()},"keydown": (e) => {if(event.keyCode >= 112 && event.keyCode <= 123){e.preventDefault();        }}});
    @if($title != 'Company | Edit Company Details')
    $('#company_register_form').submit(function() {
        if ($('#company_register_form').valid()) {
            $('#company_register_account_head').show();
            $('#company_register_form').hide();
            $('#company_register_fa_code').hide();
            $('#company_register_branch').hide();
            // $('#company_default_settings').hide();
            var fa_code = +$('#fa_code_from').val();
            $('#fa_code').val(fa_code);
            @if($title == "Company | New Company Register" || $title == "Company | Edit Company Details")
                var fa_code_from = fa_code + 1;
                $('#passbook_fa_code').val(fa_code);
                $('#member_id_fa_code').val(fa_code_from);
                $('#associate_code_fa_code').val(fa_code_from + 1);
                $('#SSb_code_fa_code').val(fa_code_from + 2);
                $('#certificate_fa_code').val(fa_code_from + 3);
            @endif
        }
        return false;
    });
    $('#company_register_account_head').submit(function() {
        if ($('#company_register_account_head').valid()) {
            $('#company_register_fa_code').show();
            $('#company_register_account_head').hide();
            $('#company_register_form').hide();
            $('#company_register_branch').hide();
            // $('#company_default_settings').hide();
            var fa_code = +$('#fa_code_from').val();
            $('#fa_code').val(fa_code);
            <?php if($title == "Company | New Company Register" || $title == "Company | Edit Company Details"){ ?>
                var fa_code_from = fa_code + 1;
                $('#passbook_fa_code').val(fa_code);
                $('#member_id_fa_code').val(fa_code_from);
                $('#associate_code_fa_code').val(fa_code_from + 1);
                $('#SSb_code_fa_code').val(fa_code_from + 2);
                $('#certificate_fa_code').val(fa_code_from + 3);
                // $('#member_id_fa_code').val(fa_code);
                // $('#associate_code_fa_code').val(fa_code_from);
                // $('#passbook_fa_code').val(fa_code_from + 1);
                // $('#certificate_fa_code').val(fa_code_from + 2);
            <?php } ?>
        }
        return false;
    });
    $('#company_register_fa_code').submit(function() {
        if ($('#company_register_fa_code').valid()) {
            $('#company_register_account_head').hide();
            $('#company_register_form').hide();
            $('#company_register_fa_code').hide();
            $('#company_register_branch').show();
            // $('#company_default_settings').show();
        }
        return false;
    });
    // $('#company_default_settings').submit(function() {
        // if ($('#company_default_settings').valid()) {
            // $('#company_register_account_head').hide();
            // $('#company_register_form').hide();
            // $('#company_register_fa_code').hide();
            // $('#company_default_settings').hide();
            // $('#company_register_branch').show();
        // }
        // return false;
    // });
    $(document).on('click', '#prev_one', function() {
        $('#company_register_account_head').hide();
        $('#company_register_form').show();
        $('#company_register_fa_code').hide();
        $('#company_register_branch').hide();
        // $('#company_default_settings').hide();
        return false;
    });
    $(document).on('click', '#prev_two', function() {
        $('#company_register_account_head').show();
        $('#company_register_form').hide();
        $('#company_register_fa_code').hide();
        $('#company_register_branch').hide();
        // $('#company_default_settings').hide();
        return false;
    });
    $(document).on('click', '#prev_three', function() {
        $('#company_register_account_head').hide();
        $('#company_register_form').hide();
        $('#company_register_fa_code').show();
        $('#company_register_branch').hide();
        // $('#company_default_settings').hide();
        return false;
    });
    $(document).on('click', '#prev_four', function() {
        $('#company_register_account_head').hide();
        $('#company_register_form').hide();
        $('#company_register_fa_code').show();
        $('#company_register_branch').hide();
        // $('#company_default_settings').show();
        return false;
    });
    @endif
    @if(isset($account_head_up))
    // $('#company_register_branch').submit(function() {
    //     if ($('#company_register_branch').valid()) {
    //         var companyRegisterForm = new FormData(document.forms['company_register_form']);
    //         var data = '';
    //         $('.loader').show();
    //         $.ajax({
    //             type: "POST",
    //             url: "{!! route('admin.companies.companyRegisterForm_update') !!}",
    //             dataType: 'JSON',
    //             data: companyRegisterForm,
    //             cache: false,
    //             contentType: false,
    //             processData: false,
    //             headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                
    //             success: function(response) {
    //             //     var data = JSON.parse(JSON.stringify(response));
    //             //     var Company_id = data.data;
    //             //     var companyAccountHead = new FormData(document.forms['company_register_account_head']);
    //             //     if (data.data > 0) {
    //             //         $.ajax({
    //             //             type: "POST",
    //             //             url: "{!! route('admin.companies.companyAccountHead_update') !!}",
    //             //             dataType: 'JSON',
    //             //             data: companyAccountHead,
    //             //             cache: false,
    //             //             contentType: false,
    //             //             processData: false,
    //             //             headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    //             //             success: function(response) {
    //             //                 var data = JSON.parse(JSON.stringify(response));
    //             //                 var Company_id = data.data;
    //             //                 var companyFaCode = new FormData(document.forms['company_register_fa_code']);
    //             //                 if (Company_id > 0) {
    //             //                     $.ajax({
    //             //                         type: "POST",
    //             //                         url: "{!! route('admin.companies.companyFaCode_update') !!}",
    //             //                         dataType: 'JSON',
    //             //                         data: companyFaCode,
    //             //                         cache: false,
    //             //                         contentType: false,
    //             //                         processData: false,
    //             //                         headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    //             //                         success: function(response) {
    //             //                             var data = JSON.parse(JSON.stringify(response));
    //             //                             var Company_id = data.data;
    //             //                             var companyDefaultSettings = new FormData(document.forms['company_default_settings']);
    //             //                             if (Company_id > 0) {
    //             //                                 $.ajax({
    //             //                                     url: "{!! route('admin.companies.company_default_settings_update') !!}",
    //             //                                     type: "POST",
    //             //                                     dataType: 'JSON',
    //             //                                     data: companyDefaultSettings,
    //             //                                     cache: false,
    //             //                                     contentType: false,
    //             //                                     processData: false,
    //             //                                     headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    //             //                                     success: function(response) {
    //             //                                         $('.loader').hide();
    //             //                                         var data = JSON.parse(JSON.stringify(response));
    //             //                                         var Company_id = data.data;
    //             //                                         var companyBranch = new FormData(document.forms['company_register_branch']);
    //             //                                         /*
    //             //                                         if (data.data > 0) {
    //             //                                             $.ajax({
    //             //                                                 type: "POST",
    //             //                                                 url: "{!! route('admin.companies.companyBranch_update') !!}",
    //             //                                                 dataType: 'JSON',
    //             //                                                 data: companyBranch,
    //             //                                                 cache: false,
    //             //                                                 contentType: false,
    //             //                                                 processData: false,
    //             //                                                 headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    //             //                                                 success: function(response) {
    //             //                                                     var data = JSON.parse(JSON.stringify(response));
    //             //                                                     console.log(data.data);
    //             //                                                     return false;
    //             //                                                     if (data.data >0) {
    //             //                                                     }
    //             //                                                 },
    //             //                                                 error: function(response) {
    //             //                                                     var data = JSON.parse(JSON.stringify(response));
    //             //                                                     console.log(data.data);
    //             //                                                 }
    //             //                                             });
    //             //                                         }
    //             //                                         */
    //             //                                         swal('Company Details Updated Successfully');
    //             //                                         window.location.href = "{{route('admin.companies.show')}}";
    //             //                                     },
    //             //                                     error: function(response) {
    //             //                                         var data = JSON.parse(JSON.stringify(response));
    //             //                                         console.log(data.data);
    //             //                                     }                                                    
    //             //                                 });
    //             //                             }
    //             //                         },
    //             //                         error: function(response) {
    //             //                             var data = JSON.parse(JSON.stringify(response));
    //             //                             console.log(data.data);
    //             //                         }
    //             //                     });
    //             //                 }
    //             //             },
    //             //             error: function(response) {
    //             //                 var data = JSON.parse(JSON.stringify(response));
    //             //                 console.log(data.data);
    //             //             }
    //             //         });
    //             //     }
    //             },

    //             error: function(response) {
    //                 var data = JSON.parse(JSON.stringify(response));
    //                 console.log(data.data);
    //             }
    //         });
    //     }
    //     return false;
    // });
    @else
    $('#company_register_branch').submit(function() {
        if ($('#company_register_branch').valid()) {
			var primarybranch = parseInt($('#p_branch').val());
			var branchIds = [];
			$('#company_register_branch input[name="branch[]"]:not(:disabled)').prop('checked', true).each(function() {
				branchIds.push(parseInt($(this).val()));
			});
			console.log({'Primary branch:': primarybranch,'Branch IDs:': branchIds},branchIds.includes(primarybranch));
			// Compare primary branch with selected branch IDs
			if (branchIds.includes(primarybranch)) {
				var companyRegisterForm = new FormData(document.forms['company_register_form']);
				var data = '';
				$('.loader').show();
				$.ajax({
					type: "POST",
					url: "{!! route('admin.companies.companyRegisterForm') !!}",
					dataType: 'JSON',
					data: companyRegisterForm,
					cache: false,
					contentType: false,
					processData: false,
					headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
					success: function(response) {
						var data = JSON.parse(JSON.stringify(response));
						var Company_id = data.data;
						var companyAccountHead = new FormData(document.forms['company_register_account_head']);
						if (data.data > 0) {
							$.ajax({
								type: "POST",
								url: "{!! route('admin.companies.companyAccountHead') !!}",
								dataType: 'JSON',
								data: companyAccountHead,
								cache: false,
								contentType: false,
								processData: false,
								headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
								success: function(response) {
									var data = JSON.parse(JSON.stringify(response));
									var Company_id = data.data;
									var companyFaCode = new FormData(document.forms['company_register_fa_code']);
									if (data.data > 0) {
										$.ajax({
											type: "POST",
											url: "{!! route('admin.companies.companyFaCode') !!}",
											dataType: 'JSON',
											data: companyFaCode,
											cache: false,
											contentType: false,
											processData: false,
											headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
					// 						/*
					// 						 success: function(response) {
					// 							 var data = JSON.parse(JSON.stringify(response));
					// 							 var Company_id = data.data;
					// 							 var companyDefaultSettings = new FormData(document.forms['company_default_settings']);
					// 							 if (data.data > 0) {
					// 								 $.ajax({
					// 									 url: "{!! route('admin.companies.company_default_settings') !!}",
					// 									 type: "POST",
					// 									 dataType: 'JSON',
					// 									 data: companyDefaultSettings,
					// 									 cache: false,
					// 									 contentType: false,
					// 									 processData: false,
					// 									 headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
					// 						*/        
														success: function(response) {
															var data = JSON.parse(JSON.stringify(response));
															var Company_id = data.data;
															var companyBranch = new FormData(document.forms['company_register_branch']);
															if (data.data > 0) {                                                            
																$.ajax({
																	type: "POST",
																	url: "{!! route('admin.companies.companyBranch') !!}",
																	dataType: 'JSON',
																	data: companyBranch,
																	cache: false,
																	contentType: false,
																	processData: false,
																	headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
																	success: function(response) {
																		var data = JSON.parse(JSON.stringify(response));
																		$('.loader').hide();
																		if (response.data >0) {
																			swal({
																				title:'Success!',
																				text:'New Company Registered Successfully',
																				type:'success'
																			},function(isConfirm){
																				window.location.href = "{{route('admin.companies.show')}}";
																			});
																		}
																	},
																	error: function(response) {
																		var data = JSON.parse(JSON.stringify(response));
																		console.log(data.data);
																	}
																});                                                            
															}
														},
					// 						/*
					// 									 error: function(response) {
					// 										 var data = JSON.parse(JSON.stringify(response));
					// 										 console.log(data.data);
					// 									 }
					// 								 });
					// 							 }
					// 						 },
					// 						*/
											error: function(response) {
												var data = JSON.parse(JSON.stringify(response));
												console.log(data.data);
											}
										});
									}
								},
								error: function(response) {
									var data = JSON.parse(JSON.stringify(response));
									console.log(data.data);
								}
							});
						}
					},
					error: function(response) {
						var data = JSON.parse(JSON.stringify(response));
						console.log(data.data);
					}
				});
			} if (!branchIds.includes(primarybranch)) {
			  swal('Warning','Primary branch is Added in Excluded branches List.','warning');
			  return false;
			}
        }
        return false;
    });
@endif
    $.validator.addMethod("decimal", function(value, element, p) {
        if (this.optional(element) || /^\d*\.?\d*$/g.test(value) == true) {
            $.validator.messages.decimal = "";
            result = true;
        } else {
            $.validator.messages.decimal = "Please enter valid numeric number.";
            result = false;
        }
        return result;
    }, "");
    $.validator.addMethod("number", function(value, element, p) {
        if (this.optional(element) || /\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/g.test(
                value) == true) {
            $.validator.messages.number = "";
            result = true;
        } else {
            $.validator.messages.number = "Please enter valid mobile number.";
            result = false;
        }
        return result;
    }, "");
    /** tin number have to be 4 digit alphabet 5 digit number after 1 digit alphabet format. */
    $.validator.addMethod("tin_number", function(value, element, p) {
        if (this.optional(element) || /([A-Z]){4}([0-9]){5}([A-Z]){1}$/g.test(
                value) == true) {
            $.validator.messages.tin_number = "";
            result = true;
        } else {
            $.validator.messages.tin_number = "Please enter valid 10 digit Alphanumeric Characters TAN number.";
            result = false;
        }
        return result;
    }, "");
    /** pan number have to be 5 digit alphabet 4 digit number after 1 digit alphabet format. */
    $.validator.addMethod("pan_number", function(value, element, p) {
        if (this.optional(element) || /([A-Z]){5}([0-9]){4}([A-Z]){1}$/g.test(
                value) == true) {
            $.validator.messages.pan_number = "";
            result = true;
        } else {
            $.validator.messages.pan_number = "Please enter valid 10 digit Alphanumeric Characters PAN number.";
            result = false;
        }
        return result;
    }, "");
    /** cin number have to start with U/L then 5 digit number 2 digit alphabet 2 digit number after 3 digit alphabet and 6 digit number's format. */
    $.validator.addMethod("cin_number", function(value, element, p) {
        if (this.optional(element) || /^([LUu]{1})([0-9]{5})([A-Z]{2})([0-9]{4})([A-Z]{3})([0-9]{6})$/g
            .test(
                value) == true) {
            $.validator.messages.cin_number = "";
            result = true;
        } else {
            $.validator.messages.cin_number = "Please enter valid 21 alpha numeric CIN number.";
            result = false;
        }
        return result;
    }, "");
    $.validator.addMethod("fa_code", function(value, element, p) {
        const fa_code_from = $('#fa_code_from').val();
        const fa_code_to = $('#fa_code_to').val();
        if (parseInt(fa_code_from) >= parseInt(fa_code_to)) {
            $.validator.messages.fa_code = "FA Code From Should be Greater Than FA Code To";
            result = false;
        } else {
            $.validator.messages.fa_code = "";
            result = true;
        }
        return result;
    }, "");
    $.validator.addMethod("passbook_min_fa_code", function(value, element, p) {
        const fa_code_from = +$('#fa_code_from').val() + 1;
        const fa_code_to = +$('#fa_code_to').val() + 2;
        const passbook_fa_code = $('#passbook_fa_code').val();
        if (parseInt(passbook_fa_code) > parseInt(fa_code_from) && parseInt(passbook_fa_code) <
            parseInt(fa_code_to)) {
            $.validator.messages.passbook_min_fa_code = "";
            result = true;
        } else {
            $.validator.messages.passbook_min_fa_code =
                "Passbook FA Code From must be in FA Code Range";
            result = false;
        }
        return result;
    }, "");
    $.validator.addMethod("certificate_min_fa_code", function(value, element, p) {
        const fa_code_from = +$('#fa_code_from').val() + 1;
        const fa_code_to = +$('#fa_code_to').val() + 2;
        const certificate_fa_code = $('#certificate_fa_code').val();
        if (parseInt(certificate_fa_code) > parseInt(fa_code_from) && parseInt(certificate_fa_code) <
            parseInt(fa_code_to)) {
            $.validator.messages.certificate_min_fa_code = "";
            result = true;
        } else {
            $.validator.messages.certificate_min_fa_code =
                "Certificate FA Code must be in FA Code Range";
            result = false;
        }
        return result;
    }, "");
    
	@if($title != 'Company | Edit Company Details')
        $(document).on('change','#name',function () {
            var name = $(this).val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.companies.name_unique') !!}" ,
                data: {'name':name},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(e) {			
                    if ( e.data == 0 ) {
                        $(this).addClass('error');
                        if ($('#name-error').length) {
                            $('#name-error').remove();
                        }
                        $('#name').after('<label id="name-error" class="error" for="name">Company Name already exist.</label>');
                        $('#name').val('');
                    }else {
                        $(this).removeClass('error');
                        $('#name-error').remove();
                    }
                }
            });
        });
        $(document).on('change','#fa_code_from',function () {
            var fa_code_from = $(this).val();
            $.post("{!! route('admin.companies.fa_code_from_check') !!}",
                {'fa_code_from': fa_code_from, '_token': $('meta[name="csrf-token"]').attr('content')},
                function (e){
                    if(e.data > 0) {
                        $('#fa_code_to').val(parseInt(fa_code_from) + 99);
                        $(this).removeClass('error'),
                        $('#fa_code_from-error').remove();
                    }else{
                        $('#fa_code_from-error').remove();
                        $('#fa_code_to').val('');
                        $('#fa_code_from').after('<label id="fa_code_from-error" class="error" for="fa_code_from">Company fa code from in existing company fa code from.</label>');
                    }
                }
            ).fail(function(){
                $('#fa_code_to').val('');
            });
            $.ajax({
                type: "POST",
                url: "{!! route('admin.companies.fa_code_from_unique') !!}" ,
                data: {'fa_code_from':fa_code_from},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(e) {
                    if ( e.data == 0 ) {
                        $(this).addClass('error');
                        $('#fa_code_from-error').remove();
                        if ($('#fa_code_from-error').length) {
                            $('#fa_code_from-error').remove();
                        }
                        $('#fa_code_from').after('<label id="fa_code_from-error" class="error" for="fa_code_from">Company fa code from already exist.</label>');
                        $('#fa_code_from').val('');
                    }else {
                        $(this).removeClass('error');
                        $('#fa_code_from-error').remove();
                    }
                }
            });
        });
        $(document).on('change','#fa_code_to',function () {
            var fa_code_to = $(this).val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.companies.fa_code_to_unique') !!}" ,
                data: {'fa_code_to':fa_code_to},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(e) {
                    if ( e.data == 0 ) {
                        $(this).addClass('error');
                        if ($('#fa_code_to-error').length) {
                            $('#fa_code_to-error').remove();
                        }
                        $('#fa_code_to').after('<label id="fa_code_to-error" class="error" for="fa_code_to">Company Fa Code To already exist.</label>');
                        $('#fa_code_to').val('');
                    }else {
                        $(this).removeClass('error');
                        $('#fa_code_to-error').remove();
                    }
                }
            });
        });
        $(document).on('change','#tin_no',function () {
            var tin_no = $(this).val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.companies.tin_unique') !!}" ,
                data: {'tin_no':tin_no},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(e) {
                    if ( e.data == 0 ) {
                        if ($('#tin_no-error').length) {
                            $('#tin_no-error').remove();
                        }					
                        $(this).addClass('error');
                        $('#tin_no').after('<label id="tin_no-error" class="error" for="tin_no">Company Tin No already exist.</label>');
                        $('#tin_no').val('');
                        
                    }else {
                        $(this).removeClass('error');
                        $('#tin_no-error').remove();
                    }
                }
            });
        });
        $(document).on('change','#pan_no',function () {
            var pan_no = $(this).val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.companies.pan_unique') !!}" ,
                data: {'pan_no':pan_no},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(e) {
                    if ( e.data == 0 ) {
                        $(this).addClass('error');
                        if ($('#pan_no-error').length) {
                            $('#pan_no-error').remove();
                        }
                        $('#pan_no').after('<label id="pan_no-error" class="error" for="pan_no">Company Pan No already exist.</label>');
                        $('#pan_no').val('');
                    }else {
                        $(this).removeClass('error');
                        $('#pan_no-error').remove();
                    }
                }
            });
        });
        $(document).on('change','#cin_no',function () {
            var cin_no = $(this).val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.companies.cin_unique') !!}" ,
                data: {'cin_no':cin_no},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(e) {
                    if ( e.data == 0 ) {
                        $(this).addClass('error');
                        if ($('#cin_no-error').length) {
                            $('#cin_no-error').remove();
                        }
                        $('#cin_no').after('<label id="cin_no-error" class="error" for="cin_no">Company Cin No already exist.</label>');
                        $('#cin_no').val('');
                    }else {
                        $(this).removeClass('error');
                        $('#cin_no-error').remove();
                    }
                }
            });
        });
	@endif
	$.validator.addMethod("company_image", function(value, element, p) {
        var result = true;

        // File input validation
        var inputFile = $(element);
        var file = inputFile[0].files[0];

        if (!file) {
            $.validator.messages.company_image = "Please select a file";
            result = false;
        } else {
            // Check file type and size if needed
            // Example: Check if the file is an image
            var allowedExtensions = ["jpg", "jpeg", "png", "svg"];
            var extension = file.name.split('.').pop().toLowerCase();

            if (allowedExtensions.indexOf(extension) === -1) {
                $.validator.messages.company_image = "Invalid file type. Allowed: jpg, jpeg, png, svg";
                result = false;
            } else if (file.size > 1048576) { // 1 MB
                $.validator.messages.company_image = "File size exceeds the limit (1MB)";
                result = false;
            }
        }

        return result;
    }, "");
    $('#company_register_form').validate({
        rules: {
            name: {
                required: true,
                maxlength: 100,
                
            },
            short_name: {
                required: true,
            },
            company_image: {
                required: true,
                // company_image: true,
            },
            mobile_no: {
                required: true,
                decimal: true,
                minlength: 10,
                maxlength: 12,
				number: true,
            },
            email: {
                required: true,
            },
            address: {
                required: true,
            },
            fa_code_from: {
                required: true,
                decimal: true,
                maxlength: 4,
            },
            fa_code_to: {
                required: true,
                decimal: true,
                maxlength: 4,
                fa_code: false,
            },
            tin_no: { 
                tin_number: true,
                
            },            
            pan_no: {
                required: true,
                pan_number: true,
                
            },
            cin_no: {
                required: true,
                cin_number: true,
                
            },
        },
        messages: {
            name: {
                required: "Please enter Name",
            },
            short_name: {
                required: "Please enter Short Name",
            },
            company_image: {
                required: "Please enter Image for company",
            },
            email: {
                required: "Please enter Email",
            },
            address: {
                required: "Please enter Address",
            },
            mobile_no: {
                required: "Please enter Mobile No",
                decimal: "Please enter Numerical value",
                minlength: "Please enter minimum 10 digits",
            },
            fa_code_from: {
                required: "Please enter Fa Code From",
                decimal: "Please enter Numerical value",
            },
            fa_code_to: {
                required: "Please enter Fa Code To",
                decimal: "Please enter Numerical value",
            },
            // tin_no: {
            //     required: "Please enter TIN No",
            //     decimal: "Please enter Numerical value",
            // },
            pan_no: {
                required: "Please enter PAN No",
            },
            cin_no: {
                required: "Please enter CIN No",
            },
        },
        errorElement: 'span',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            element.closest('.error-msg').append(error);
        },
        highlight: function(element, errorClass, validClass) {
            $(element).addClass('is-invalid');
            if ($(element).attr('type') == 'radio') {
                $(element.form).find("input[type=radio]").each(function(which) {
                    $(this).removeClass('is-invalid');
                });
            }
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        }
    });
    $('#company_register_account_head').validate({
        rules: {
        },
        messages: {
        },
        errorElement: 'span',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            element.closest('.error-msg').append(error);
        },
        highlight: function(element, errorClass, validClass) {
            $(element).addClass('is-invalid');
            if ($(element).attr('type') == 'radio') {
                $(element.form).find("input[type=radio]").each(function(which) {
                    $(this).removeClass('is-invalid');
                });
            }
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        }
    });
	$('#company_register_branch').validate({
        rules: {
			p_branch:{required:true},
        },
        messages: {
			p_branch:{
				required:"Please Select Any Primary Branch",
			}
        },
        errorElement: 'span',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            element.closest('.error-msg').append(error);
        },
        highlight: function(element, errorClass, validClass) {
            $(element).addClass('is-invalid');
            if ($(element).attr('type') == 'radio') {
                $(element.form).find("input[type=radio]").each(function(which) {
                    $(this).removeClass('is-invalid');
                });
            }
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        }
    });
    $.validator.addMethod("readonly", function(value, element) {
        return $(element).prop("readonly");
    }, "This field must be read-only");
    $('#company_default_settings').validate({
        rules: {
            settings_name:{required:true},
            settings_short_name:{required:true,readonly:true},
            settings_effective_from:{required:true,readonly:true},
            settings_amount:{required:true},
        },
        messages: {
            settings_name:{
                required:"Please Enter Default Settings Name",
            },
            settings_short_name:{
                required:"Default Settings Short name Required",
                readonly:"Default Settings Short name Must Readonly",
            },
            settings_effective_from:{
                required:"Default Settings Effective From Date is required",
                readonly:"Default Settings Effective From Date Must Readonly",
            },
            settings_amount:{
                required:"Please Enter Default Amount Name",
            },
        },
        errorElement: 'span',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            element.closest('.error-msg').append(error);
        },
        highlight: function(element, errorClass, validClass) {
            $(element).addClass('is-invalid');
            if ($(element).attr('type') == 'radio') {
                $(element.form).find("input[type=radio]").each(function(which) {
                    $(this).removeClass('is-invalid');
                });
            }
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        }
    });
    $('#company_register_fa_code').validate({
        rules: {
            passbook_fa_code: {
                required: true,
                decimal: true,
                // passbook_min_fa_code: true,
            },
            certificate_fa_code: {
                required: true,
                decimal: true,
                // certificate_min_fa_code: true,
            }
        },
        messages: {
            passbook_fa_code: {
                required: "Please enter Passbook Fa Code",
                decimal: "Please enter Numerical value",
            },
            certificate_fa_code: {
                required: "Please enter Certificate Fa Code",
                decimal: "Please enter Numerical value",
            }
        },
        errorElement: 'span',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            element.closest('.error-msg').append(error);
        },
        highlight: function(element, errorClass, validClass) {
            $(element).addClass('is-invalid');
            if ($(element).attr('type') == 'radio') {
                $(element.form).find("input[type=radio]").each(function(which) {
                    $(this).removeClass('is-invalid');
                });
            }
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        }
    });
	@if($title=='Company | New Company Register')
    $(function() {
        $("#sortable1, #sortable2").sortable({
            connectWith: ".connectedSortable"
        }).disableSelection();
    });
	@endif
    // var system_date = new Date($('#system_date').val());
    // $('#settings_effective_from').on('click',function(){
    //     $(this).datepicker({
    //         format: "dd/mm/yyyy",
    //         todayHighlight: true,
    //         autoclose: true, 
    //         orientation: "bottom",
    //         setStartDate: system_date,
    //     });
    // });
    $('#settings_name').on('change',function(){
        var settings_name = $(this).val();
        var words = settings_name.split(" ");
        var firstLetters = "";
        for (var i = 0; i < words.length; i++) {
            var word = words[i];
            if (word.trim() !== "") {
                firstLetters += word.charAt(0).toUpperCase();
            }
        }
        console.log(firstLetters);
        $('#settings_short_name').val(firstLetters);

    });
	$('.numberonly').bind('keyup blur',function(){ 
		$(this).val($(this).val().replace(/[^0-9]/g, ''));
	});
});
</script>