<script type="text/javascript">
$(document).ready(function() {
	var date = new Date();
	
	//const end = new Date(date.getFullYear(), date.getMonth(), (date.getDate() + 1));

	
	$('#effective_from_date').hover(function(){
		const ddd = $('#adm_report_currentdate').val();
		$('#effective_from_date').datepicker({
		format: "dd/mm/yyyy",
		todayHighlight: true,
		startDate: ddd,
		
		autoclose: true,
		orientation:'bottom'
	}).datepicker('setDate', ddd);
	})
    var loantable;
    $.validator.addMethod("lessThanEquals",
    function (value, element, param) {
		var $otherElement = $(param);
		return parseInt(value, 10) <= parseInt($otherElement.val(), 10);
		return value > target.val();
    }, "Amount should be less than OR equals closer amount.");

	$.validator.addMethod('inActivePlan',function(value,element,param){
		const status = $('option:selected',element).data('status');
		if(status == 0)
		{
			return false;
		}
		else{
			return true;
		}
		
	},'Selected Plan is Inactive');
	
	$.validator.addMethod('inActiveTenure', function (value,element,param){
		const tstatus = $('option:selected',element).data('tstatus');
		if(tstatus == 0)
		{
			return false;
		}
		else{
			return true;
		}
	},'Selected Tenure is Inactive,Please select another one');	
	
    $('#loanchargeform').validate({ // initialize the plugin
		
        rules: {
            'type' : 'required',
            'loan_type' : 'required',
            'charge_type' : 'required',
            'charge' : 'required',
			plan_name:{
				required:true,
				inActivePlan:true
			},
			tenure:{
				required:true,
				inActiveTenure:true
			},
			
			'effective_from_date': 'required',
			
           	max_amount:{
                required:true,

            },
            min_amount:{
                required:true,

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
	

	$("#charge").keypress(function (e){
	  var charCode = (e.which) ? e.which : e.keyCode;
	  if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46) {
		return false;
	  }

	  
	});
	
	$('#max_amount,#min_amount').on('keyup',function(){
        const MinAmount = $('#min_amount').val();
        const maxAmount =  $('#max_amount').val();

        if(parseInt(MinAmount) > parseInt(maxAmount))
        {
            $('#warning-msg').html('Max amount should be greater than Minimum amount');
        }
        else{
            $('#warning-msg').html('');
        }

    })
	
	

    var loanchargetable = $('#loanchargetable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
		bFilter : false,
        ordering: false,
		lengthMenu: [10, 20, 40, 50, 100],
        
		"fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
                var oSettings = this.fnSettings ();
                $('html, body').stop().animate({
                    scrollTop: ($('#loanchargetable').offset().top)
                }, 1000);
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                return nRow;
            },
        ajax: {
            "url": "{!! route('admin.loan.loansettings.loanchargelist') !!}",
            "type": "POST",
            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'type', name: 'type'},
            {data: 'loan_type', name: 'loan_type'},
            {data: 'plan_name', name: 'plan_name'},
            {data: 'tenure', name: 'tenure'},
            
            {data: 'min_amount', name: 'min_amount'},
            {data: 'max_amount', name: 'max_amount'},
            {data: 'charge', name: 'charge'},
            {data: 'charge_type', name: 'charge_type'},
			{data: 'status', name: 'status', searchable: false ,
                "render":function(data, type, row){
                    if(row.status==0){
                        return "<span class='badge badge-danger'>Inactive</span>";
                    }else{
                        return "<span class='badge badge-success'>Active</span>";
                    }
                }
            },
			{data: 'effective_from', name: 'effective_from'},
			{data: 'effective_to', name: 'effective_to'},
			{data: 'created_by', name: 'created_by'},
			{data: 'created_by_username', name: 'created_by_username'},
			{data: 'created_at', name: 'created_at'},
			{data: 'updated_at', name: 'updated_at'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false
    });
    $(loanchargetable.table().container()).removeClass( 'form-inline' ); 


	
	$('.export').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
		//$('#report_export').val(extension);
		var formData = {}
		
		var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
		doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit,1);
		$("#cover").fadeIn(100);
		
	});


	// function to trigger the ajax bit
	function doChunkedExport(start,limit,formData,chunkSize,page){
		formData['start']  = start;
		formData['limit']  = limit;
		formData['page']  = page;
		
		jQuery.ajax({
			type : "post",
			dataType : "json",
			url :  "{!! route('admin.loan.loansettings.loanchargelistexport') !!}",
			data : formData,
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			success: function(response) {
				console.log(response);
				if(response.result=='next'){
					start = start + chunkSize;
					page = page + 1;
					doChunkedExport(start,limit,formData,chunkSize,page);
					
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

	$( document ).ajaxStart(function() {
		$( ".loader" ).show();
	});
	$( document ).ajaxComplete(function() {
		$( ".loader" ).hide();
	});

	
	$('.charge_type').change(function (e) {
		
		var chargetype	=	jQuery(".charge_type").val();
		
		if(chargetype == 0){
			$('.charge').attr({min:0, max:100});	
		}else{
			
			$('.charge').removeAttr('max');
		}
	});	

	$('.submitloancharge').removeAttr('disabled');




});  
$(document).on('change','#loan_type',function(){ 
	var loan_type=$('#loan_type').val();
		$.ajax({
		type: "POST",  
		url: "{!! route('admin.planByLoanType') !!}",
		dataType: 'JSON',
		data: {'loan_type':loan_type},
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		success: function(response) { 
		$('#plan_name').find('option').remove();
		$('#plan_name').append('<option value="">----Select Plan Name----</option>');
		
			$.each(response.data, function (index, value) { 
				$("#plan_name").append("<option value='"+value.id+"'>"+value.name+"</option>");
				
			}); 

		}
	});
});

$(document).on('change','#plan_name',function(){ 
	var plan_name=$('#plan_name').val();
	
		$.ajax({
		type: "POST",  
		url: "{!! route('admin.tenureByPlanName') !!}",
		dataType: 'JSON',
		data: {'plan_name':plan_name},
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		success: function(response) { 
			console.log(response)
		$('#tenure').find('option').remove();
		$('#tenure').append('<option value="">----Select Tenure----</option>');
		
			$.each(response.data, function (index, value) { 
				$("#tenure").append("<option value='"+value.id+"'>"+value.name+"</option>");
				
			}); 

		}
	});
});

$(document).on('change','#tenure',function(){ 
	
	var type = $(".type").val();
	var loan_type = $("#loan_type").val();
	var plan_name = $("#plan_name").val();
	var tenure=$('#tenure').val();

		$.ajax({
		type: "POST",  
		url: "{!! route('admin.loanChargeCheckExistingTenure') !!}",
		dataType: 'JSON',
		data: {'tenure':tenure,'loan_type':loan_type,'plan_name':plan_name,'type':type},
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		success: function(response) { 

		if(response.msg_type=="exist")
		{
			$('.tenurewarning').html('Loan Charge For this Tenure Already Exist!');
			$('.submitloancharge').prop("disabled", true);
		}
		if(response.msg_type=="not_exist")
		{
			$('.tenurewarning').html('');
			$('.submitloancharge').removeAttr('disabled');
		}
	}
	});
});


	

</script>
