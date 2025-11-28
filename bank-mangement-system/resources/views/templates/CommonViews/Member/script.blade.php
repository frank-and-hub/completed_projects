<script type="text/javascript">
    var memberTable;
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
   memberTable = $('#member_blacklist_on_loan_listing').DataTable({
        processing: true,
        serverSide: true,
         pageLength: 20,
         lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#member_blacklist_on_loan_listing').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.member_blacklist_on_loan_listing') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter').serializeArray()}, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},           
            {data: 'join_date', name: 'join_date'},
            {data: 'branch', name: 'branch'},           
            {data: 'member_id', name: 'member_id',
                "render":function(data, type, row) {
                    var accountNumber = row.reinvest_old_account_number;
                    if ( accountNumber ) {
                        return '<a href="showdata/id" target=_blank>' + 'R-' + row.member_id +'</a>';
                    } else {
                        return  row.member_id;
                    }
                }
            },
            {data: 'name', name: 'name'},
			 {data: 'dob', name: 'dob'},
            {data: 'block_reason', name: 'block_reason',orderable: true, searchable: true},
            {data: 'mobile_no', name: 'mobile_no'},
            {data: 'associate_code', name: 'associate_code'},
            {data: 'associate_name', name: 'associate_name'},		
			{data: 'is_blacklist_on_loan', name: 'is_blacklist_on_loan'},   
      
      {data: 'address', name: 'address'},     
      
      {data: 'status', name: 'status'},
      {data: 'action', name: 'action',orderable: false, searchable: false},
        ]
    });
    $(memberTable.table().container()).removeClass( 'form-inline' );
    memberInvestmentPaymentTable = $('#member_investment_payment_listing').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#chequefilter').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.member.investmentchequepaymentlisting') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#chequefilter').serializeArray()},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
             {data: 's-branch', name: 's-branch'},
             {data: 'branch_code', name: 'branch_code'},
            {data: 'sector', name: 'sector'},
            {data: 'regan', name: 'regan'},
            {data: 'zone', name: 'zone'},
            {data: 'amount', name: 'amount'},
            {data: 'transaction_date', name: 'transaction_date'},
            {data: 'cheque_date', name: 'cheque_date'},
            {data: 'cheque_number', name: 'cheque_number'},
            {data: 'bank_name', name: 'bank_name'},
            {data: 'branch_name', name: 'branch_name'},
            {data: 'status', name: 'status'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ]
    });
    $(memberInvestmentPaymentTable.table().container()).removeClass( 'form-inline' );
    
 /*
	$(document).on('click','.export_blacklist_member',function(){
        var extension = $(this).attr('data-extension');
        $('#member_export').val(extension);
        $('form#member_filter').attr('action',"{!! route('admin.member.blacklist_on_loan_export') !!}");
        $('form#member_filter').submit();
        return true;
    });
	*/
	$('.export_blacklist_member').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#member_export').val(extension);
		if(extension == 0)
		{
        var formData = jQuery('#member_filter').serializeObject();
        var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
		}
		else{
			
			
		   
			$('#member_export').val(extension);

			$('form#member_filter').attr('action',"{!! route('admin.member_blacklist_on_loan_listing_export') !!}");

			$('form#member_filter').submit();
		}
	});
	
	
	// function to trigger the ajax bit
    function doChunkedExport(start,limit,formData,chunkSize){
        formData['start']  = start;
        formData['limit']  = limit;
        formData['member_id']  = $("#customer_id").val();
        formData['branch_id']  = $("#branch_id").val();
        formData['status']  = $("#status").val();
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url :  "{!! route('admin.member_blacklist_on_loan_listing_export') !!}",
            data : formData,
            success: function(response) {
                console.log(response);
                if(response.result=='next'){
                    start = start + chunkSize;
                    doChunkedExport(start,limit,formData,chunkSize);
					$(".loaders").text(response.percentage+"%");
                }else{
					var csv = response.fileName;
                    console.log('DOWNLOAD');
					$(".spiners").css("display","none");
					$("#cover").fadeOut(100); 
					window.open(csv, '_blank');
                }
            }
        });
    }
	
	// A function to turn all form data into a jquery object
    jQuery.fn.serializeObject = function(){
        var o = {};
        var a = this.serializeArray();
        jQuery.each(a, function() {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };

    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });


  $.validator.addMethod("dateDdMm", function(value, element,p) {
     
      if(this.optional(element) || /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g.test(value)==true)
      {
        $.validator.messages.dateDdMm = "";
        result = true;
      }else{
        $.validator.messages.dateDdMm = "Please enter valid date";
        result = false;  
      }
    
    return result;
  }, "");
  $('#associate_register').validate({
    rules: {
      block_reason:{ 
        required : true,
          },
        }
  }); //
  $('#filter').validate({
      rules: {
        start_date:{ 
            dateDdMm : true,
          },
          end_date:{
            dateDdMm : true,
          },
          member_id :{ 
            number : false,
          },
          associate_code :{ 
            number : true,
          },  

      },
      messages: { 
          member_id:{ 
            number: 'Please enter valid Customer id.'
          },
          associate_code:{ 
            number: 'Please enter valid associate code.'
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

  

  
});

  
function searchForm()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        memberTable.draw();
    }
}
function resetForm()
{
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    $('#is_search').val("no");
    $('#name').val('');
    $('#member_id').val('');
    $('#customer_id').val('');
    $('#status').val('');
    $('#branch_id').val('');
    $('#end_date').val('');
    $('#end_date').val('');
    $('#start_date').val('');
    $('#associate_code').val('');
    memberTable.draw();
}

function searchCheckForm()
{  
    if($('#chequefilter').valid())
    {
        $('#is_search').val("yes");
        memberInvestmentPaymentTable.draw();
    }
}

function resetCheckForm()
{
    $('#is_search').val("no");
    $('#start_date').val('');
    $('#branch_id').val('');
    $('#status').val(''); 
    memberInvestmentPaymentTable.draw();
}


$(document).on('keyup','#member_id',function(){
	$('#show_mwmber_detail').html('');
	var code = $(this).val();
	if (code!='') {
	$.ajax({
		  type: "POST",  
		  url: "{!! route('admin.member_blacklist_member_data') !!}",
		  dataType: 'JSON',
		  data: {'code':code},
		  headers: {
			  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		  },
		  success: function(response) { 
			if(response.msg_type=="error2")
			{
			  $('#show_mwmber_detail').html('<div class="alert alert-danger alert-block">  <strong>Customer blocked!</strong> </div>');
			}
			else
			{
				if(response.msg_type=="success")
				{
				  $('#show_mwmber_detail').html(response.view);
				  //$('#id').val(response.id); 
				}
				else if(response.msg_type=="error1")
				{
				  $('#show_mwmber_detail').html('<div class="alert alert-danger alert-block">  <strong>Customer already in Black List !</strong> </div>');
				}
				else
				{
				  $('#show_mwmber_detail').html('<div class="alert alert-danger alert-block">  <strong>Customer not found!</strong> </div>');
				}
			}
		  }
	  });
	} 

});


$(document).on('click','.blockMemberOnLoan',function(){
  console.log('eeeeeeee');
	var memberID = $("#memberID").val();
	var is_block = "1";
	var block_reason = $("#block_reason").val();
	var urls = $("#urls").val();
  var gdate = $('.gdate').text();
	if (memberID!='') {
		if($('#associate_register').valid()){
		swal({
			title: "Are you sure?",
			text: "Do you want to blacklist this user for loan?",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-primary delete_cheque",
			confirmButtonText: "Yes",
			cancelButtonText: "No",
			cancelButtonClass: "btn-danger delete_cancel",
			closeOnConfirm: false,
			closeOnCancel: true
		  },
		  function(isConfirm) {
			if (isConfirm) {
				$.ajax({
					  type: "POST",  
					  url: "{!! route('admin.common_controller.action_blacklist_member_for_loan') !!}",
					  dataType: 'JSON',
					  data: {'memberID':memberID, 'is_block':is_block,'block_reason':block_reason,'date':gdate},
					  headers: {
						  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					  },
					  success: function(response) { 
              alert('you test outside');
              if(response.msg_type =='success'){
              alert('you test INSIDE');
							/*
							swal("Success!", ""+response.view+"", "success")
							.then((value) => {
							  window.location.href = urls+'/admin/blacklist-members-on-loan';
							});
							*/
              swal({
                  title: "Success!",
                  text: response.view, // Assuming response.view contains the message
                  icon: "success",
                  button: "OK",
              }).then(function(isConfirm){
                if (isConfirm) {
                  var redirectUrl = '/admin/blacklist-members-on-loan';
                  window.location.href = redirectUrl;
                }
              });
              

							// swal("Success!", ""+response.view+"", "success"),isConfirm(function(true){
              //   var redirectUrl = '/admin/blacklist-members-on-loan';
              //   window.location.href = redirectUrl;
              // });
						} else{
						   swal("Error!", ""+response.view+"", "error");
						}
					  }
				});
			}
		});
  }
		
		
	} 
})



$(document).on('click','.unblockUser',function(){
	var memberID = $(this).attr("data-row-id");
	var is_block = "0";
	var urls = $("#urls").val();
	if (memberID!='') {
		
		swal({
			title: "Are you sure?",
			text: "Do you want to active this user for loan?",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-primary delete_cheque",
			confirmButtonText: "Yes",
			cancelButtonText: "No",
			cancelButtonClass: "btn-danger delete_cancel",
			closeOnConfirm: false,
			closeOnCancel: true
		  },
		  function(isConfirm) {
			if (isConfirm) {
				$.ajax({
					  type: "POST",  
					  url: "{!! route('admin.common_controller.action_blacklist_member_for_loan') !!}",
					  dataType: 'JSON',
					  data: {'memberID':memberID, 'is_block':is_block},
					  headers: {
						  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					  },
					  success: function(response) { 
              alert('unblock test OUSTIDE');
              if(response.msg_type =='success'){
              alert('unblock test insidE');
							swal("Success!", ""+response.view+"", "success");
							location.reload();
						} else{
						   swal("Error!", ""+response.view+"", "error");
						}
					  }
				});
			}
		});
		
		
	} 
});

$(document).on('click','#details',function(){
    var customer_id = $(this).data('customer_id');
    $.ajax({
      url:"{{ route('admin.block_details') }}",
      type: "POST",
      data:{
        customer_id:customer_id
      },
      success:function(e)
      {
        console.log(e);
        $('#blockedDetail').addClass('d-none');
        if(e.msg != 0)
        {
          $('#blockedDetail').removeClass('d-none');
          var created_by = (e.memberLogData.created_by == 1)?'Admin':'Branch';
          var reasonDate = moment(e.memberLogData.created_at).format("DD-MM-YYYY");
          $('#reasonDate').val(reasonDate);
          $('#reason').val(e.memberLogData.reason);
          $('#created_by').val(created_by);
          $('#created_by_name').val(e.UserData.username);
        }
        else
        {
          $('.close').click();
          swal('Data Not Found','User is not Blocked','warning');
        }
      }
    });
  });

 
</script>