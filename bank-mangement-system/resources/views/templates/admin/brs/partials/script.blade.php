<script type="text/javascript">
$(document).ready(function(){

    $('#date').datepicker({
          format: 'dd-mm-yyyy',
          autoclose: true,
    });
	
	
	$(document).on("focusin",".datePikars", function () {
        $('.datePikars').datepicker({
            format: 'dd-mm-yyyy',
			autoclose: true,
        });
    });
	  
	$.validator.addMethod("zero", function(value, element,p) {     
		  if(value>=0)
		  {
			$.validator.messages.zero = "";
			result = true;
		  }else{
			$.validator.messages.zero = "Amount must be greater than or equal to 0.";
			result = false;  
		  }
		
		return result;
	}, "");

	
    $.validator.addMethod("decimal", function(value, element,p) {     
      if(this.optional(element) || $.isNumeric(value)==true)
      {
        $.validator.messages.decimal = "";
        result = true;
      }else{
        $.validator.messages.decimal = "Please Enter valid numeric number.";
        result = false;  
      }
    
      return result;
    }, "");
	
	//  Viswajeet BRS Changes 22-06-2023 Start 

	$('.findBranh').change(function(e) {
        e.preventDefault();
        var companyId = $(this).val();
        $('#bank_account').html('<option value="">Select Bank Account</option>');
        $.ajax({
            type: "POST",
            url: "{{ route('admin.fetchbranchbycompanyid') }}",
            data: {
                'company_id': companyId,
                'bank': 'true',
                'branch': 'no',
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                let myObj = JSON.parse(response);
                if (myObj.bank) {
                    var optionBank = `<option value="">Select Bank</option>`;
                    myObj.bank.forEach(element => {
                        optionBank +=
                            `<option value="${element.id}">${element.bank_name}</option>`;
                    });
                    $('#bank').html(optionBank);
                }
            }
        });
    });

	// $('#bank').on('change', function() {
	// 	var bank_id = $('option:selected', this).val();
	// 	var bank_name = $('option:selected', this).text();

	// 	$.ajax({
	// 		type: "POST",
	// 		url: "{!! route('admin.bank_account_list') !!}",
	// 		dataType: 'JSON',
	// 		data: {
	// 			bank_id: bank_id
	// 		},
	// 		headers: {
	// 			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	// 		},
	// 		success: function(response) {
	// 			if (response) {
	// 				$('#bank_account').find('option').remove();
	// 				$('#bank_account').append(
	// 					'<option value="">Select Bank Account</option>');
	// 				$.each(response.account, function(index, value) {
	// 					$("#bank_account").append("<option value='" + value.id +
	// 						"'>" + value.account_no + "</option>");


	// 				});
	// 			}


	// 		}
	// 	})
	// 	$('#name').val(bank_name);
	// })

	//  Viswajeet BRS Changes 22-06-2023 END


	$('#bank').on('change',function(selected_account){
		var bank_id=$(this).val();
	  	$.ajax({
		  type: "POST",  
		  url: "{!! route('admin.bank_account_list.inactive') !!}",
		  dataType: 'JSON',
		  data: {'bank_id':bank_id},
		  headers: {
			  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		  },
		  success: function(response) { 
			  
			$('#bank_account').find('option').remove();
			$('#bank_account').append('<option value="">Select account number</option>');
			 $.each(response.account, function (index, value) { 
					$("#bank_account").append("<option value='"+value.id+"'>"+value.account_no+"</option>");
				}); 
		  }
	  });
	})
	
	
	
	
	$('#filter').validate({
	  rules:{
		bank:{
		  required:true,
		},
		bank_account:{
		  required:true,
		},
		year:{
		  required:true,
		},
		month:{
		  required:true,
		},
	  },
	  messages:{
		bank:{
		  required:"Please Select Bank",
		},
		bank_account:{
		  required:"Please Select Bank Account",
		},
		year:{
		  required:"Please Select Year",
		},
		month:{
		  required:"Please Select Month",
		}

	  }
	})
	
	
	$('#submitGetData').on('click',function(){
		$("#daybook_closing_balance").val("");
		
		if($("#month").val() == ""){
			swal("Error!", "Please select month", "error");
			return false;
		}
		if($("#year").val() == ""){
			swal("Error!", "Please select year", "error");
			return false;
		}
		if($("#bank").val() == ""){
			swal("Error!", "Please select bank name", "error");
			return false;
		}
		if($("#bank_account").val() == ""){
			swal("Error!", "Please select account number", "error");
			return false;
		}
		if($("#month").val()!= "" && $("#year").val()!= "" && $("#bank_account").val()!= "" && $("#bank").val()!= ""){

			var company_id = $("#company_id").val();
			var bank_id = $("#bank").val();
			var bank_account = $("#bank_account").val();
			var year = $("#year").val();
			var month = $("#month").val();
			
			$.ajax({
				type: "POST",  
				url: "{!! route('admin.get_brs_report_closing_balance') !!}",
				dataType: 'JSON',
				data: {'company_id':company_id, 'bank_id':bank_id, 'bank_account':bank_account, 'year':year, 'month':month},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(response) {
				//var returnedData = JSON.parse(response);

				$("#daybook_closing_balance").val(response["openingbalance"]);
				$("#closing_balance").val(response["balance"]);
				$(".closingDate").text(response["endDates"]);
				$('#daybook_balance').modal('show');
				$(".btnDiv").css("display","block");
				
				//$('#Statement_balance').modal('show'); 
				}
			});
		}
	});
	
        
	$('#submitFinalData').on('click',function(){
		if($("#daybook_closing_balance").val() == ""){
			swal("Error!", "Please enter brs bank statement closing amount", "error");
			return false;
		} else {
			$('#daybook_balance').modal('hide');
			$('#Statement_balance').modal('show');
		}
	})
	
	
	/*
	checkBRS = $('#cheque_brs_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            responsive: true,
            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings ();
                
                $('html, body').stop().animate({
                    scrollTop: ($('#cheque_brs_listing').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.brs_reporting_listing') !!}",
                "type": "POST",
                "data":function(d) {d.searchform=$('form#filter').serializeArray()},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
              'createdRow': function(row, data, dataIndex){
               if(data.particular === 'Opening_Balance'){
                  $('td:eq(0)', row).removeAttr('class');

               }
            },
            columns: [
                {data: 'Sr_no', name: 'Sr_no'},
                {data: 'entry_date', name: 'entry_date'},
                {data: 'particular', name: 'particular'},
                {data: 'account_head_name', name: 'account_head_name'},
				{data: 'cheque_no', name: 'cheque_no'},
                {data: 'amount', name: 'amount'},
                {data: 'credit', name: 'credit'},
                {data: 'debit', name: 'debit'},
                {data: 'balance', name: 'balance'},
            ],
           
        });
         
        $(checkBRS.table().container()).removeClass( 'form-inline' );  
			$( document ).ajaxStart(function() {
			$( ".loader" ).show();
		   
		});
	 
		$( document ).ajaxComplete(function() {
			$( ".loader" ).hide();
		});
		*/
	
		
		$('#Statement_balance_submit').on('click',function(){
			$('#Statement_balance').modal('hide'); 
			$("#BrsDataBody").html("");
			
			if($("#month").val()!= "" && $("#year").val()!= "" && $("#bank_account").val()!= "" && $("#bank").val()!= ""){
				
				var company_id = $("#company_id").val();
				var bank_id = $("#bank").val();
				var bank_account = $("#bank_account").val();
				var year = $("#year").val();
				var month = $("#month").val();
				var created_at = $("#created_at").val();

				// alert(company_id);
				// return false;

				$.ajax({
					  type: "POST",  
					  url: "{!! route('admin.get_brs_report_data') !!}",
					  dataType: 'JSON',
					  data: {'company_id':company_id,'bank':bank_id, 'bank_account':bank_account, 'year':year, 'month':month, 'created_at':created_at},
					  headers: {
						  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					  },
					  success: function(data) {
							$("#BrsTableDiv").removeClass("d-none");
							if(data["data"]!= ""){
								$("#BrsDataBody").html(data["data"]);
							}
							if(data["data1"]!= ""){
								// console.log(bank_accounts);
								$("#EntryNewBrsDataBody").html(data["data1"]);
								var company_id = $("#company_id option:selected").val();
								console.log(company_id);
								var bank_accounts = $("#bank_account option:selected").val();
								$(".totalRecords #company_id").val(company_id);
								$("#bank_id").val(bank_id);
								$("#bank_account_detail").val(bank_accounts);
								$("#year_detail").val(year);
								$("#month_detail").val(month);

							}
							if(data["data"]!= "" || data["data1"]!= ""){
								$(".btnDiv").css("display","inline-flex;");
							} else {
								$(".btnDiv").css("display","inline-flex;");
								// $("#saveUserRecordsData").css("display","block");
								// $("#clearUserRecordsData").css("display","block");
							}
							var closing_balance = $("#closing_balance").val();
							$("#finalClosingBalance").val(closing_balance);
							var statementClosingBalance = $("#daybook_closing_balance").val();
							$("#statementClosingBalance").val(statementClosingBalance);
							$(".totalRecords").css("display","block");
					  }
				});
			}
		}); 
		
	
	

	$('#brs_bank_charge').validate({
	  rules:{
		bank:{
		  required:true,
		},
		bank_account:{
		  required:true,
		},
		date:{
		  required:true,
		},
		bank_charge:{
		  required:true,
		},
		description:{
		  required:true,
		},
		amount:{
		  required:true,
		  zero:true,
		  decimal:true,
		},
	  },
	  messages:{
		bank:{
		  required:"Please Select Bank",
		},
		bank_account:{
		  required:"Please Select Bank Account",
		},
		date:{
		  required:"Please Select Date",
		},
		bank_charge:{
		  required:"Please Select Bank Charge",
		},
		description:{
		  required:"Please Enter the Description",
		},
		amount:{
		  required:"Please Enter the Amount",
		  decimal : "Please enter a valid amount.",
		},

	  }
	})

    $( document ).ajaxStart(function() { 
          $( ".loader" ).show();
    });

	$( document ).ajaxComplete(function() {
		  $( ".loader" ).hide();
	});
	
	
	var arr = [];
	$(document).on('dblclick', '.notSave', function(e) {
		var id = $(this).attr("data-row-id");
		var checkValueInArr = arr.includes(id); 
		if(checkValueInArr == false){
			var entry_date = $(this).attr("data-row-entry_date");
			var particular = $(this).attr("data-row-particular");
			var account_head_name = $(this).attr("data-row-account_head_name");
			var cheque_no = $(this).attr("data-row-cheque_no");
			//var amount = $(this).attr("data-row-amount");
			var credit = $(this).attr("data-row-credit");
			var debit = $(this).attr("data-row-debit");
			//var balance = $(this).attr("data-row-balance");
			
			var html = '<td><input type="text" class="datePikars" name="entryDate['+id+'][]" value='+entry_date+'></td><td>'+particular+'</td><td>'+account_head_name+'</td><td>'+cheque_no+'</td><td>'+credit+'</td><td>'+debit+'</td>';
			$("#"+id).append(html);
			
			arr.push(id);
			$(".btnDiv").css("display","block");
		}
	});
		
	$('#saveUserRecordsData').on('click',function(e){

		var company_id = $(".totalRecords #company_id").val();
		var bank_id = $("#bank").val();
		var bank_account = $("#bank_account").val();
		var year = $("#year").val();
		var month = $("#month").val();
		var finalClosingBalance= $('#finalClosingBalance').val();
		var statementClosingBalance = $('#statementClosingBalance').val();
		var created_at = $('#created_at').val();

		$.ajax({
			url: "{!! route('admin.save_brs_report_data') !!}",
			// data: {'company_id':company_id,'bank_id':bank_id,'bank_account':bank_account,'year':year,'month':month,'finalClosingBalance':finalClosingBalance,'statementClosingBalance':statementClosingBalance,'created_at':created_at},
			data:$("#filterPerticuler").serialize(),
			type: 'POST',
			headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
			success: function (getResult) {
				swal("Saved!", "Data Saved Successfully", "success");
			}
		});
	}); 
	
	
	$('#clearUserRecordsData').on('click',function(){
		
		var form_data = new FormData($('#filterPerticuler')[0]);
		$.ajax({
			url: "{!! route('admin.clear_brs_report_data') !!}",
			data: form_data,
			//data:$("#filterPerticuler").serialize(),
			cache: false,
			contentType: false,
			processData: false,
			type: 'POST',
			headers: {
				  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			  },
			success: function (getResult) {
				arr.length = 0;
				arr = [];
				swal("Saved!", "Data Cancelled Successfully", "success");
				$("#EntryNewBrsDataBody").empty();
				$(".btnDiv").css("display","none");
			}
		});
		
		
	}); 
	
	
	
	
	$('#printUserRecordsData').on('click',function(){


			var closing_balance = $("#closing_balance").val();
			var form_data = new FormData($('#filterPerticuler')[0]);
			var company_id = $(".totalRecords #company_id").val();
			var bank_id = $("#bank").val();
			var bank_account = $("#bank_account").val();
			var year = $("#year").val();
			var month = $("#month").val();
			
			var url = window.location.origin+"/admin/brs/report/print?company_id="+company_id+"&bank="+bank_id+"&bank_account="+bank_account+"&month="+month+"&year="+year+"&closing_balance="+closing_balance;

			//var url = window.location.origin+"/admin/brs/report/print?bank="+bank_id+"&bank_account="+bank_account+"&month="+month+"&year="+year+"&closing_balance="+daybook_closing_balance;
			
			$.ajax({
				url: "{!! route('admin.print_brs_report_data') !!}",
				data: form_data,
				cache: false,
				contentType: false,
				processData: false,
				type: 'POST',
				headers: {
					  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				  },
				success: function (getResult) {
					swal("Saved!", "Data Printed Successfully", "success");
					$("#EntryNewBrsDataBody").empty();
					//$(".btnDiv").css("display","none");
					$(".btnDiv").css("display","block");
					// $("#saveUserRecordsData").css("display","block");
					// $("#clearUserRecordsData").css("display","block");
					window.open(url, '_blank');
				}
			});
	}); 
	
	
	
	$('#bank_account').on('change',function(){
		//$(".btnDiv").css("display","block");
	});
	
	$('#daybook_closing_balance').on('keyup',function(){
		var num1 = $(this).val();
			if(num1 > 0){
				
			} else {
				swal("Error", "Amount Should be positive and greater than zero", "error");
				$("#daybook_closing_balance").val("");
			}
	});
	
	
	
})
</script>