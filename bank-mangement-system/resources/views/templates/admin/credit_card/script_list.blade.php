<script type="text/javascript">
var employeeTable;
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
  
   $("#card_name").on("keypress", function(e) {
    if (e.which === 32 && !this.value.length)
        e.preventDefault(); 
  });
   $("#card_holder_name").on("keypress", function(e) {
    if (e.which === 32 && !this.value.length)
        e.preventDefault(); 
  });
   $("#credit_card_number").on("keypress", function(e) {
    if (e.which === 32 && !this.value.length)
        e.preventDefault(); 
  });
   $("#credit_card_account_number").on("keypress", function(e) {
    if (e.which === 32 && !this.value.length)
        e.preventDefault(); 
  });
   $("#credit_card_bank").on("keypress", function(e) {
    if (e.which === 32 && !this.value.length)
        e.preventDefault(); 
  });
  
  
 employeeTable = $('#credit_card_listing').DataTable({
	processing: true,
	serverSide: true,
	 pageLength: 20,
	 lengthMenu: [10, 20, 40, 50, 100],
	"fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
		var oSettings = this.fnSettings ();
		$('html, body').stop().animate({
		scrollTop: ($('#credit_card_listing').offset().top)
	}, 1000);
		$("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
		return nRow;
	},
	ajax: {
		"url": "{!! route('admin.credit-card.credit_card_listing') !!}",
		"type": "POST",
		"data":function(d) {d.searchform=$('form#filter').serializeArray()}, 
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
	},
	columns: [
		{data: 'DT_RowIndex', name: 'DT_RowIndex'},
		{data: 'card_name', name: 'card_name'},
		{data: 'card_holder_name', name: 'card_holder_name'},
		{data: 'credit_card_number', name: 'credit_card_number'},
		{data: 'credit_card_account_number', name: 'credit_card_account_number'},
		{data: 'credit_card_bank', name: 'credit_card_bank'},
		{data: 'action', name: 'action',orderable: false, searchable: false},
	],"ordering": false
});
$(employeeTable.table().container()).removeClass( 'form-inline' );




$(document).on('click','.deleteCreditCard',function(){ 
	var credit_card_id = $(this).attr("data-row-id");
	
	
	swal({
		title: "Are you sure?",
		text: "Do you want to delete this credit card?",  
		type: "warning",
		showCancelButton: true,
		confirmButtonClass: "btn-primary",
		confirmButtonText: "Yes",
		cancelButtonText: "No",
		cancelButtonClass: "btn-danger",
		closeOnConfirm: false,
		closeOnCancel: true
	  },
	  function(isConfirm) {
		if (isConfirm) {
			  $.ajax({
			  type: "POST",  
			  url: "{!! route('admin.credit-card.delete-credit-card') !!}",
			  dataType: 'JSON',
			  data: {'credit_card_id':credit_card_id},
			  headers: {
				  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			  },
			  success: function(response) {
			   
					if(response.status == "1"){
						swal("Good job!", response.message, "success");
						location.reload();
					} else {
						swal("Warning!", response.message, "warning");
						return false;
					}

				}
			  });
		}
	  });
	  
})


$('#credit_card_register').validate({ 
  rules: {
	  card_name: "required", 
	  card_holder_name: "required",
	  credit_card_account_number: {
		required: true,
		number: true,
		minlength: 12,
		maxlength: 12 
	  },
	  credit_card_bank: "required", 
	  credit_card_number: {
		required: true,
		number: true,
		minlength: 16,
		maxlength: 16 
	  },
  },
  messages: {		  
	   card_name: "Please enter card name.",
	   card_holder_name: "Please enter card holder name.",
	   credit_card_number: {
			required: "Please enter credit card number",
			number: "Please enter valid number.",
			minlength: "Please enter valid credit card number",
			maxlength: "Please enter valid credit card number" 
		},
		credit_card_account_number: {
			required: "Please enter credit card account number",
			number: "Please enter valid number.",
			minlength: "Please enter valid account number",
			maxlength: "Please enter valid account number" 
		},
	   credit_card_bank: "Please enter credit card bank.",
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

 
    
$('.export').on('click',function(){
	var extension = $(this).attr('data-extension');
	$('#emp_export').val(extension);
	$('form#filter').attr('action',"{!! route('admin.hr.employee_export') !!}");
	$('form#filter').submit();
	return true;
}); 

$( document ).ajaxStart(function() {
	$( ".loader" ).show();
});

$( document ).ajaxComplete(function() {
	$( ".loader" ).hide();
});


$('#filter').validate({
  rules: {
  //  status:"required",  

  },
   messages: {  
 //     status: "Please select status",
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

$(document).on('change','#category',function(){ 
    var category=$('#category').val();

          $.ajax({
              type: "POST",  
              url: "{!! route('admin.designationByCategory') !!}",
              dataType: 'JSON',
              data: {'category':category},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                $('#designation').find('option').remove();
                $('#designation').append('<option value="">Select Designation</option>');
                 $.each(response.data, function (index, value) { 
                        $("#designation").append("<option value='"+value.id+"'>"+value.designation_name+"</option>");
                    }); 

              }
          });

  });



var credit_card_head_id = $("#credit_card_head_id").val();

ledgerListingTable = $('#credit_card_transaction_listing').DataTable({
		processing: true,
		serverSide: true,
		pageLength: 20,
		lengthMenu: [10, 20, 40, 50, 100],
		"fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
			var oSettings = this.fnSettings ();
			$('html, body').stop().animate({
			scrollTop: ($('#credit_card_transaction_listing').offset().top)
			}, 1000);
			$("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
			return nRow;
		},
		ajax: {
			"url": "{!! route('admin.credit-card.credit_card_transaction_listing') !!}",
			"type": "POST",
			"data":function(d,oSettings) {
				   if(oSettings.json != null)
				   {
					var total_amount = oSettings.json.total;
				   }
				   else{
					 var total_amount = 0;
				   }
				   
					var page = ($('#credit_card_transaction_listing').DataTable().page.info());
					var currentPage  = page.page+1;
					d.pages = currentPage,
					d.credit_card_head_id = credit_card_head_id,	
					d.total=total_amount 
			},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
		},
		columns: [
			{data: 'DT_RowIndex', name: 'DT_RowIndex'},
			{data: 'created_date', name: 'created_date'},
			{data: 'branch_name', name: 'branch_name'},
			{data: 'head_name', name: 'head_name'},
			{data: 'description', name: 'description'},
			{data: 'payment_mode', name: 'payment_mode'},
			{data: 'debit', name: 'debit'},
			{data: 'credit', name: 'credit'},
			{data: 'balance', name: 'balance'},
		],"ordering": false
});

$(ledgerListingTable.table().container()).removeClass( 'form-inline' );



 
});





function searchForm()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        employeeTable.draw();
    }
}
function resetForm()
{
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    $('#is_search').val("yes");

    $('#start_date').val('');
    $('#end_date').val('');
    $('#branch').val('');
    $('#category').val('');
    $('#designation').val('');
    $('#employee_name').val('');
    $('#employee_code').val('');
    $('#reco_employee_name').val('');
    $('#status').val('active');

    employeeTable.draw();
}

</script>