<script type="text/javascript">
var renewaldetails;
$(document).ready(function() {

  jQuery.validator.addMethod("lettersonly", function(value, element) {
  return this.optional(element) || /^[a-z]+$/i.test(value);
}, "Letters only please"); 

    
    $('#filter').validate({
      rules: {
        member_id :{ 
            number : true,
        },
        associate_code :{ 
            number : true,
        },  
        scheme_account_number :{ 
        //    number : true,
        }, 
      },
      messages: { 
          member_id:{ 
            number: 'Please enter valid member id.'
          },
          associate_code:{ 
            number: 'Please enter valid associate code.'
          },
          scheme_account_number:{ 
            number: 'Please enter valid account number.'
          },
      },
      errorElement: 'span',
      errorPlacement: function (error, element) {
        error.addClass(' ');
        element.closest('.error-msg').append(error);
      },
      highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
        if($(element).attr('type') == 'radio'){
          $(element.form).find("input[type=radio]").each(function(which){
            $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
            $(this).addClass('is-invalid');
          });
        }
      },
      unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
        if($(element).attr('type') == 'radio'){
          $(element.form).find("input[type=radio]").each(function(which){
            $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
            $(this).removeClass('is-invalid');
          });
        }
      }
  });

    $(document).on('change','#branchid',function(){
          var bId = $('option:selected', this).attr('data-val');
          var sbId = $( "#hbranchid option:selected" ).val();  
          if(bId != sbId){
            $('#branchid').val('');
            swal("Warning!", "Branch does not match from top dropdown state", "warning");
          }  
    });
    




    // AJAX call for autocomplete 


    $(document).on('click','.selectmember',function(){
        var val = $(this).attr('data-val');
        var account = $(this).attr('data-account');
        var id = $(this).attr('value');
        $("#member_name").val(val+' - ('+account+')');
        $("#member_id").val(id);
        $("#suggesstion-box").hide();
    });

 
 

    var date = new Date();
    $('#start_date').datepicker({
        format: "dd/mm/yyyy",
        orientation: "top",
        autoclose: true
    });

    $('#end_date').datepicker({
        format: "dd/mm/yyyy",
        orientation: "top",
        autoclose: true
    });

    $('input[name="start_date"]').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    });

    $('input[name="start_date"]').on('cancel.daterangepicker', function(ev, picker) {
      $(this).val('');
    });

    // Datatables
    renewaldetails = $('#usermanagement-listing').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.usermanagementdetails.listing') !!}",
            "type": "POST",
            "data":function(d) {
                d.searchform=$('form#filter').serializeArray(),
                d.start_date=$('#start_date').val(),
                d.end_date=$('#end_date').val(),
                d.is_search=$('#is_search').val(),
                d.investments_export=$('#investments_export').val()
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
             {data: 'created_at', name: 'created_at'},
            {data: 'username', name: 'username'},
			      {data: 'employee_code', name: 'employee_code'},
            {data: 'employee_name', name: 'employee_name'},
            {data: 'mobile_number', name: 'mobile_number'},
            {data: 'user_id', name: 'user_id'}, 
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false,
    });
    $(renewaldetails.table().container()).removeClass( 'form-inline' );

    // Show loading image
    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    // Hide loading image
    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });




});

function printDiv(elem) {
    printJS({
    printable: elem,
    type: 'html',
    targetStyles: ['*'], 
  })
}

function searchForm()
{ 
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        renewaldetails.draw();
    }
}

function resetForm()
{
    $('#is_search').val("yes");
    $('#start_date').val('');
    $('#end_date').val('');
    $('#branch_id').val('');
    $('#plan_id').val('');
    $('#scheme_account_number').val('');
    $('#name').val('');
    $('#member_id').val('');
    $('#associate_code').val('');
    $('#amount_status').val('');
    renewaldetails.draw();
}

var currentUserID = $("#id").val();

if(currentUserID == ""){
	$('#usermanagement_register').validate({ 
      rules: {
          username: {
            required:true,
          }, 
		  employee_code: "required", 
		  employee_name: "required",
		  bank_id: "required",
          mobile_number: {
            required: true,
            number: true,
            minlength: 10,
            maxlength: 12 
          },
		  user_id:{
        required:true,
       // number:true
      }, 
		  password: {
            required: true,
            minlength: 4
          },
		   password_confirmation: {
            required: true,
            minlength: 4,
			equalTo: "#password"
          },
		  
		  
      },
      messages: {		  
		   username: {
            required:"Please enter user name",
            lettersonly:"Letters only please"
          },
		   employee_code: "Please enter employee code.",
		   employee_name: "Please enter employee name.",
		   bank_id: "Please select a branch.",
		   mobile_number: {
            required: "Please enter mobile number.",
            number: "Please enter valid number.",
            minlength: "Please enter minimum  10 or maximum 12 digit.",
            maxlength: "Please enter minimum  10 or maximum 12 digit." 
          },
         user_id:{
          required:"Please enter user ID",
          number:"Please enter valid number"
         },
		 password: {
            required: "Please enter password.",
            minlength: "Please enter minimum  4 characters."
          },
		 password_confirmation: {
            required: "Please enter confirm password.",
            minlength: "Please enter minimum  4 characters.",
			equalTo : "Password and confirm password does not matched"
          },
        
      },
      errorElement: 'label',
      errorPlacement: function (error, element) {
        error.addClass(' ');
        element.closest('.error-msg').append(error);
      },
      highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
      },
      unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
      }
  });
} else {
	$('#usermanagement_register').validate({ 
		  rules: {
			  username: "required", 
			  employee_code: "required", 
			  employee_name: "required",
			  bank_id: "required",
			  mobile_number: {
				required: true,
				number: true,
				minlength: 10,
				maxlength: 12 
			  },
			  user_id: "required", 
			  password: {
				minlength: 4
			  },
			   password_confirmation: {
				minlength: 4,
				equalTo: "#password"
			  },
			  
			  
		  },
		  messages: {		  
			   username: "Please enter user name.",
			   employee_code: "Please enter employee code.",
			   employee_name: "Please enter employee name.",
			   bank_id: "Please select a branch.",
			   username: "Please enter username.",
			   mobile_number: {
				required: "Please enter mobile number.",
				number: "Please enter valid number.",
				minlength: "Please enter minimum  10 or maximum 12 digit.",
				maxlength: "Please enter minimum  10 or maximum 12 digit." 
			  },
			 user_id: "Please enter user id.",
			 password: {
				minlength: "Please enter minimum  4 characters."
			  },
			 password_confirmation: {
				minlength: "Please enter minimum  4 characters.",
				equalTo : "Password and confirm password does not matched"
			  },
			
		  },
		  errorElement: 'label',
		  errorPlacement: function (error, element) {
			error.addClass(' ');
			element.closest('.error-msg').append(error);
		  },
		  highlight: function (element, errorClass, validClass) {
			$(element).addClass('is-invalid');
		  },
		  unhighlight: function (element, errorClass, validClass) {
			$(element).removeClass('is-invalid');
		  }
	  });
}



	$(document).on('click','.activedeactiveUser',function(){
		var user_id = $(this).attr("data-row-id");
		var status = $(this).attr("data-row-status");
		if(status == "1"){
			swal({
			  title: "Are you sure?",
			  text: "Do you want to deactive this user!",
			  icon: "warning",
			  buttons: true,
			  dangerMode: true,
			})
			.then((willDelete) => {
			  if (willDelete) {
				$.ajax({
					  type: "POST",  
					  url: "{!! route('active_deactive_admin_user') !!}",
					  dataType: 'JSON',
					  data: {'user_id':user_id, 'status':status},
					  headers: {
						  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					  },
					  success: function(data) {
							swal("Deactive!", data["message"], "success");
							renewaldetails.ajax.reload();
					  }
				});
			  }
			});
		} else {
			swal({
			  title: "Are you sure?",
			  text: "Do you want to active this user!",
			  icon: "warning",
			  buttons: true,
			  dangerMode: true,
			})
			.then((willDelete) => {
			  if (willDelete) {
				$.ajax({
					  type: "POST",  
					  url: "{!! route('active_deactive_admin_user') !!}",
					  dataType: 'JSON',
					  data: {'user_id':user_id, 'status':status},
					  headers: {
						  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					  },
					  success: function(data) {
							swal("Active!", data["message"], "success");
							renewaldetails.ajax.reload();
					  }
				});
			  }
			});
		}
		
	});


	
	
	// Permission
	$('.main-permission').on('click', function() {
		$(this).toggleClass('open');
		if ( $(this).parent().parent().next('tr').attr('style') ) {
			$(this).parent().parent().next('tr').removeAttr('style');
		} else if ( typeof ( $(this).parent().parent().next('tr').attr('style') ) == 'undefined' ) {
			$(this).parent().parent().next('tr').attr('style','display:none');
		}
	});

	$('.all-per').on('click', function() {
		var className = $(this).attr('data-id');
		if ( $(this).is(':checked')  ) {
			$("input:checkbox."+className).prop('checked',this.checked);
		} else {
			$("input:checkbox."+className).prop('checked',false);
		}

	});
	$("input[type='checkbox']").on('click', function() {

		var className = $(this).attr("class");
		var classArray = className.split(" ");
		var classIndex = classArray[0].split('-');
		var upDateClass = 'main-per-'+classIndex[1];

		$("input:checkbox."+upDateClass).prop('checked',false);

		console.log("CCC", className, classArray, classIndex);
		/*if ( $(this).is(':checked')  ) {
			$("input:checkbox."+className).prop('checked',this.checked);
		} else {
			$("input:checkbox."+className).prop('checked',false);
		}*/

	});
	
	$('#per-update').on('click',function(e){
			var form_data = new FormData($('#permissions')[0]);
			$.ajax({
                url: "{!! route('save_user_permission_data') !!}",
                data: form_data,
                cache: false,
                contentType: false,
                processData: false,
                type: 'POST',
				headers: {
					  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				  },
                success: function (getResult) {
					swal("Saved!", "Permission Updated Successfully", "success");
                }
            });
	});
	
	
	$('#employee_code').on('keyup', function() {
		var employee_code = $(this).val();
		if(employee_code!= ""){
			$.ajax({
				  type: "POST",  
				  url: "{!! route('get_employee_name_to_code') !!}",
				  dataType: 'JSON',
				  data: {'employee_code':employee_code},
				  headers: {
					  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				  },
				  success: function(data) {
						if(data["status"] == "1"){
							$("#employee_name").val(data["employee_name"]);
						} else {
							$("#employee_name").val("");
						}
				  }
			});
		}
	});


</script>

