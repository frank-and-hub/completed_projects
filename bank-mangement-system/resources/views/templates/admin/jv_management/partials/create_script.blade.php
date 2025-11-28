<script type="text/javascript">

var expense;

$(document).ready(function(){
	
	$(document).on('change','#company_id',function(){
		$('.account_head_dropdown').val('0').trigger('change');
		$('.account_head_more').val('').trigger('change');
		$(document).find('.head-contact').val('');
		$(document).find('.contact_more').val('');
	})

	$("#account_head").select2({dropdownAutoWidth : true});

	$(".account_head_dropdown").select2({dropdownAutoWidth : true});

	$('.head-contact').select2();

	var a=0;

	var b = 0;

	var mcount = 0;

	var today = new Date();
	$('.cre_date').datepicker( {
	   format: "dd/mm/yyyy",
	   orientation: "bottom",
	   autoclose: true,
	   endDate: "today",
       maxDate: today,
	   startDate: '01/04/2021',

	});

	$('#jv-form').validate({ // initialize the plugin
        rules: {
            'cre_date' : {required: true},
            'journal' : {required: true},
            'reference' : {required: true},
            'notes' : {required: true},
            'branch' : {required: true},
        },submitHandler: function() {
        	var contactHeads = ['56','34','90','55','32','61','67','72','73','76','143','87','88','63','15','19','31','33','64','65','66','67','97','36','57','58','59','80','81','82','83','84','85','122','77','78','79','139','62','35','89','68','69','70','91','28','71','60','74','75','144','207','234','294','362','403'];
        	var contactpHeads = ['15','19','18','236'];
        	//var row = $('#add_row').attr('data-row');
        	var inputs = $(".selecetd-head");

        	for(var i = 0; i < inputs.length; i++){
			    var hId = $(inputs[i]).val();
			    var hRow = $(inputs[i]).attr('data-value');
			    if($.inArray(hId, contactHeads) !== -1){
			    	var contactVal = $('option:selected', '.contact-'+hRow).val();
			    	if(contactVal == ''){
			    		swal('Warning','Please select contact','warning');
			    		return false;
			    	}
            	}
			}

			var pInputs = $(".selecetd-head-p");
        	for(var i = 0; i < pInputs.length; i++){
			    var phId = $(pInputs[i]).val();
			    var phRow = $(pInputs[i]).attr('data-value');
			    if(jQuery.inArray(phId, contactpHeads) !== -1){
			    	var pcontactVal = $('option:selected', '.contact-'+phRow).val();
			    	if(pcontactVal == ''){
			    		swal('Warning','Please select contact','warning');
			    		return false;
			    	}
            	}
			}

        	var deposit = 0;
			$(".debit-amount").each(function(){
                deposit += +$(this).val();
            }); 

            var credit = 0;
			$(".credit-amount").each(function(){
                credit += +$(this).val();
            });  

            if(deposit != credit)  
            {
            	swal("Warning!", "Debit and credit amount should be same!", "warning");
                return false;
            }else if(jQuery.inArray("test", contactHeads) !== -1){

            }else if(deposit == 0){
            	swal("Warning!", "Debit amount should be greater than 0!", "warning");
                return false;
            }else if(credit == 0){
            	swal("Warning!", "Credit amount should be greater than 0!", "warning");
                return false;
            }else{
				$('.submit').prop('disabled', true);
				$(".loader").show();
            	return true;
            }   
        }
    });

    $('#edit-jv-form').validate({ // initialize the plugin
        rules: {
            'cre_date' : {required: true},
            'journal' : {required: true},
            'reference' : {required: true},
            'notes' : {required: true},
            'branch' : {required: true},
        },submitHandler: function() {

        	var contactHeads = ['56','34','90','55','32','61','67','72','73','76','143','87','88','63','15','19','31','33','64','65','66','67','97','36','57','58','59','80','81','82','83','84','85','122','77','78','79','139','62','35','89','68','69','70','91','28','71','60','74','75','144','207','234','294','362','403'];
        	var contactpHeads = ['15','19','18','236'];
        	//var row = $('#add_row').attr('data-row');
        	var inputs = $(".selecetd-head");

        	for(var i = 0; i < inputs.length; i++){
			    var hId = $(inputs[i]).val();
			    var hRow = $(inputs[i]).attr('data-value');
			    if($.inArray(hId, contactHeads) !== -1){
			    	var contactVal = $('option:selected', '.contact-'+hRow).val();
			    	if(contactVal == ''){
			    		swal('Warning','Please select contact','warning');
			    		return false;
			    	}
            	}
			}

			var pInputs = $(".selecetd-head-p");
        	for(var i = 0; i < pInputs.length; i++){
			    var phId = $(pInputs[i]).val();
			    var phRow = $(pInputs[i]).attr('data-value');
			    if(jQuery.inArray(phId, contactpHeads) !== -1){
			    	var pcontactVal = $('option:selected', '.contact-'+phRow).val();
			    	if(pcontactVal == ''){
			    		swal('Warning','Please select contact','warning');
			    		return false;
			    	}
            	}
			}
        	var deposit = 0;
			$(".debit-amount").each(function(){
                deposit += +$(this).val();
            }); 

            var credit = 0;
			$(".credit-amount").each(function(){
                credit += +$(this).val();
            });  

            if(deposit != credit)  
            {
            	swal("Warning!", "Debit and credit amount should be same!", "warning");
                return false;
            }else if(deposit == 0){
            	swal("Warning!", "Debit amount should be greater than 0!", "warning");
                return false;
            }else if(credit == 0){
            	swal("Warning!", "Credit amount should be greater than 0!", "warning");
                return false;
            }else{
				$('.submit').prop('disabled', true);
				$(".loader").show();
            	return true;
            }    
        }
    });

    $("#add_row").trigger('click');

	$("#add_row").click(function(){

	 	b++;

	 	var b = $(this).attr('data-row');

	 	b = parseInt(b)+1;

	 	a = b;

	 	$(this).attr('data-row',b)
		
		$.ajax({

			type: "POST",  

	      	url: "{!! route('admin.jv.getHeads') !!}",

	      	dataType: 'JSON',

	        headers: {

	              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

	        },

		   	success: function(response) { 

				var len = response.length;

				//var expendHtml = '<tr><td> <div class="frm error-msg"><select name="account_head['+a+']" id="account_head_more'+a+'" class="account_head_more account-head-'+a+' form-control" data-value='+b+' data-row="'+b+'"> <option value="">Select Account Head1</option><input type="hidden" name="selecetd-head" class="selecetd-head selecetd-head-'+a+'" data-row="'+a+'" data-value="'+a+'"><input type="hidden" name="selecetd-head-p" class="selecetd-head-p selecetd-head-p-'+a+'" data-row="'+a+'" data-value="'+a+'">';

				var expendHtml = '<tr><td> <div class="frm error-msg"><select name="account_head['+a+']" id="account_head_more'+a+'" class="account_head_more account-head-'+a+' form-control" data-value='+b+' data-row="'+b+'" required> <option value="">Select Account Head1</option>  ';

				for(var i=0; i<len; i++){

					var head_id = response[i].head_id;

					var sub_head = response[i].sub_head;

					expendHtml += '<option value="'+head_id+'">'+sub_head+'</option>';

					if(i== len-1){

						expendHtml += '</select></div>';

					}

				}

				expendHtml += '<input type="hidden" name="selecetd-head" class="selecetd-head selecetd-head-'+a+'" data-row="'+a+'" data-value="'+a+'"><input type="hidden" name="selecetd-head-p" class="selecetd-head-p selecetd-head-p-'+a+'" data-row="'+a+'" data-value="'+a+'"><td> <div class="frm error-msg"><select name="sub_head1['+a+']" id="sub_head1_more'+a+'" class="form-control sub-head1-'+b+' '+b+'-sub_head1_more sub_head1_more" data-value='+b+' data-row="'+b+'"> <option value="">Select Account Head 2</option> </div> </td> <td> <div class="frm error-msg"><select name="sub_head2['+a+']" id="sub_head2_more'+a+'" class="form-control sub-head2-'+b+' '+b+'-sub_head2_more sub_head2_more" data-value='+b+' data-row="'+b+'"> <option value="">Select Account Head 3</option></div>  </td> <td> <div class="frm error-msg"><select name="sub_head3['+a+']" id="sub_head3_more'+a+'" class="form-control sub-head3-'+b+' '+b+'-sub_head3_more sub_head3_more" data-value='+b+' data-row="'+b+'"> <option value="">Select Account Head 4</option> </div> </td> <td> <div class="frm error-msg"><select name="sub_head4['+a+']" id="sub_head4_more'+a+'" class="form-control sub-head4-'+b+' '+b+'-sub_head4_more sub_head4_more" data-value='+b+' data-row="'+b+'"> <option value="">Select Account Head 5</option> </div> </td>';	

				 expendHtml += '<td > <div class="frm error-msg"><select name="contact['+a+']" id="contact_more'+a+'" class="contact_more head-contact head-contact-new contact-'+a+' form-control" data-value='+b+' data-row="'+b+'"> <option value="">Select Contact Number</option></select></div> <input type="hidden" name="contact_account['+a+']" class="contact-account-'+a+'"> </td><td> <div class="frm error-msg"> <input type="text" id="particular_more'+a+'" name="description['+a+']" class="form-control head-description description-'+a+' particular_more" data-row="'+b+'" required></td>';


				 //expendHtml += '<td > <div class="frm error-msg"><input class="contact_more form-control head-contact contact-'+a+'" placeholder="Contact" type="text" name="contact['+a+']" id="contact_more'+a+'" autocomplete="off" data-row='+b+' data-value='+b+'><div id="suggesstion-box-'+a+'"></div></div> <input type="hidden" name="contact_account['+a+']" class="contact-account-'+a+'"> </td><td> <div class="frm error-msg"> <input type="text" id="particular_more'+a+'" name="description['+a+']" class="form-control head-description description-'+a+' particular_more" data-row="'+b+'"></td>';
				 

				 if(a == 2){
				 	expendHtml += ' <td > <div class="frm error-msg"> <input type="text" id="debit_more'+a+'" name="debit['+a+']" class="form-control debit-amount debit-'+a+' debit_more t_amount" data-row="'+b+'" ></div> </td><td > <div class="frm error-msg"><input type="text" id="credit_more'+a+'" name="credit['+a+']" class="form-control credit-amount credit-'+a+' credit_more t_amount" data-row="'+b+'"></div> </td></tr>';
				 }else{
				 	expendHtml += ' <td > <div class="frm error-msg"> <input type="text" id="debit_more'+a+'" name="debit['+a+']" class="form-control debit-amount debit-'+a+' debit_more t_amount" data-row="'+b+'" ></div> </td><td > <div class="frm error-msg"><input type="text" id="credit_more'+a+'" name="credit['+a+']" class="form-control credit-amount credit-'+a+' credit_more t_amount" data-row="'+b+'"></div> </td> <td class=""><i class="fas fa-minus-circle  remCF " data-value='+b+' data-row="'+b+'"></i></td></tr>';
				 }
				

				$("#expense1").append(expendHtml);

				$('.account_head_more').select2({dropdownAutoWidth : true});

				a++;

			 	var date11=$('#create_application_date').val();

			   }

		})

	});

	// AJAX call for autocomplete 
	$(document).on('keyup','.select2-hidden-accessible',function(){
		
    	var keyword = $(this).val();
    	var row = $('option:selected', '.account-head-'+row).attr('data-row');

    	var head5 = $('option:selected', '.sub-head4-'+row).val();
		var hpId5 = $('option:selected', '.sub-head4-'+row).attr('data-parent-id');

		var head4 = $('option:selected', '.sub-head3-'+row).val();
		var hpId4 = $('option:selected', '.sub-head3-'+row).attr('data-parent-id');

		var head3 = $('option:selected', '.sub-head2-'+row).val();
		var hpId3 = $('option:selected', '.sub-head2-'+row).attr('data-parent-id');

		var head2 = $('option:selected', '.sub-head1-'+row).val();
		var hpId2 = $('option:selected', '.sub-head1-'+row).attr('data-parent-id');

		var head1 = $('option:selected', '.account-head-'+row).val();
		var hpId1 = $('option:selected', '.account-head-'+row).attr('data-parent-id');

		var branchId = $('option:selected', '#branch').val();

		if(head5 > 0){
			var hId = head5;
			var pId = hpId5;
		}else if(head4 > 0){
			var hId = head4;
			var pId = hpId4;
		}else if(head3 > 0){
			var hId = head3;
			var pId = hpId3;
		}else if(head2 > 0){
			var hId = head2;
			var pId = hpId2;
		}else if(head1 > 0){
			var hId = head1;
			var pId = hpId1;
		}
		
		if(hId > 0){
			contactList(hId,row,pId,branchId);
		}
    });

	$(document).on('change','.account_head_more',function(){ 
		var companyId = $('#company_id option:selected').val();
		var branchId = $('option:selected', '#branch').val();
		if (companyId == "") {
			swal('Warning','Please select company and branch first','warning');
			$('.account_head_more').val('0').trigger('change');
			return false;
		}


		var option='<option value="">Select Sub Head 2</option>';

		var id = $(this).val();
		var rclr = $(this);

		var row = $(this).attr('data-row');

		var pId = $('option:selected', this).attr('data-parent-id');

	
		

		var index = $(this).attr('data-value');

		$('.selecetd-head-'+row).val(id);

		$('.selecetd-head-p-'+row).val(pId);

		$('.'+index+'-sub_head1_more').empty();

		$('.'+index+'-sub_head2_more').empty();

		$('.'+index+'-sub_head2_more').attr('required',false);

		$('.'+index+'-sub_head2_more').append('<option value=0>Select Account Head 3</option>');

		$('.'+index+'-sub_head3_more').empty();

		$('.'+index+'-sub_head3_more').attr('required',false);

		$('.'+index+'-sub_head3_more').append('<option value=0>Select Account Head 4</option>');

		$('.'+index+'-sub_head4_more').empty();

		$('.'+index+'-sub_head4_more').attr('required',false);

		$('.'+index+'-sub_head4_more').append('<option value=0>Select Account Head 5</option>');

		$('.'+index+'-sub_head5_more').empty();

		$('.'+index+'-sub_head5_more').attr('required',false);

		$.ajax({

			type: "POST",  

			url: "{!! route('admin.get_indirect_expense_sub_head') !!}",

         	dataType: 'JSON',

		   	data: {
				'head_id':id,
				'jvVoucher': true,
				'company_id': companyId,
				},

         	headers: {

              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

          	},

		   	success: function(response) { 

			   	var len = response.account_heads.length;

				if(len > 0)
				{

					for(var i=0; i<len; i++){

						var head_id = response.account_heads[i].head_id;

						var sub_head = response.account_heads[i].sub_head;

						var parent_id = response.account_heads[i].parent_id;

						if(head_id != 6 && head_id != 16 && head_id != 17 && head_id != 26 && head_id != 126 && head_id != 145){

						 	option += '<option data-parent-id="'+parent_id+'" value="'+head_id+'">'+sub_head+'</option>';
						}

					}

					$('.'+index+'-sub_head1_more').select2({dropdownAutoWidth : true});

					$('.'+index+'-sub_head1_more').append(option);

					$('.'+index+'-sub_head1_more').attr('required', true);

				}else{

					$('.'+index+'-sub_head1_more').append('<option value=0>Select Account Head 2</option>');

				}

		   	}

		})

		if(id > 0){
			contactList(id,row,pId,branchId,companyId);
		}

	});

	$(document).on('change','.sub_head1_more ',function(){ 

		var option='<option value="">Select Sub Head 3</option>';

		var id = $(this).val();

		var row = $(this).attr('data-row');

		var pId = $('option:selected', this).attr('data-parent-id');

		var branchId = $('option:selected', '#branch').val();

		var index = $(this).attr('data-value');
		var companyId = $('#company_id option:selected').val();
		if (companyId == "") {
			swal('Warning','Please select company first','warning');
			return false;
		}

		$('.selecetd-head-'+row).val(id);

		$('.selecetd-head-p-'+row).val(pId);

		$('.'+index+'-sub_head2_more').empty();

		$('.'+index+'-sub_head3_more').empty();

		$('.'+index+'-sub_head3_more').attr('required',false);

		$('.'+index+'-sub_head3_more').append('<option value=0>Select Account Head 4</option>');

		$('.'+index+'-sub_head4_more').empty();

		$('.'+index+'-sub_head4_more').attr('required',false);

		$('.'+index+'-sub_head4_more').append('<option value=0>Select Account Head 5</option>');

		$('.'+index+'-sub_head5_more').empty();

		$('.'+index+'-sub_head5_more').attr('required',false);

		$.ajax({

			type: "POST",  

			url: "{!! route('admin.get_indirect_expense_sub_head') !!}",

         	dataType: 'JSON',

			 data: {
				'head_id':id,
				'jvVoucher': true,
				'company_id': companyId,
				},

         	headers: {

              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

          	},

		   	success: function(response) { 

			   	var len = response.account_heads.length;

				if(len > 0)

				{

					for(var i=0; i<len; i++){

						var head_id = response.account_heads[i].head_id;

						var sub_head = response.account_heads[i].sub_head;

						var parent_id = response.account_heads[i].parent_id;

						if(head_id != 6 && head_id != 16 && head_id != 17 && head_id != 26 && head_id != 126 && head_id != 145){

							option += '<option data-parent-id="'+parent_id+'" value="'+head_id+'">'+sub_head+ '</option>';
						}

					}

					$('.'+index+'-sub_head2_more').select2({dropdownAutoWidth : true});

					$('.'+index+'-sub_head2_more').append(option);

					$('.'+index+'-sub_head2_more').attr('required', true);

				}else{

					$('.'+index+'-sub_head2_more').append('<option value="">Select Account Head 3</option>');

				}

		   	}

		})

		if(id > 0){
			contactList(id,row,pId,branchId,companyId);
		}

	});

	$(document).on('change','.sub_head2_more ',function(){ 

		var option='<option value="">Select Sub Head 4</option>';

		var id = $(this).val();

		var row = $(this).attr('data-row');

		var pId = $('option:selected', this).attr('data-parent-id');

		var branchId = $('option:selected', '#branch').val();

		var index = $(this).attr('data-value');
		var companyId = $('#company_id option:selected').val();
		if (companyId == "") {
			swal('Warning','Please select company first','warning');
			return false;
		}

		$('.selecetd-head-'+row).val(id);

		$('.selecetd-head-p-'+row).val(pId);

		$('.'+index+'-sub_head3_more').empty();

		$('.'+index+'-sub_head4_more').empty();

		$('.'+index+'-sub_head4_more').attr('required',false);

		$('.'+index+'-sub_head4_more').append('<option value=0>Select Account Head 5</option>');

		$('.'+index+'-sub_head5_more').empty();

		$('.'+index+'-sub_head5_more').attr('required',false);

		$.ajax({

			type: "POST",  

			url: "{!! route('admin.get_indirect_expense_sub_head') !!}",

         	dataType: 'JSON',

			 data: {
				'head_id':id,
				'jvVoucher': true,
				'company_id': companyId,
				},

         	headers: {

              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

          	},

		   	success: function(response) { 

		   		var len = response.account_heads.length;

				if(len > 0)

				{

					for(var i=0; i<len; i++){

						var head_id = response.account_heads[i].head_id;

						var sub_head = response.account_heads[i].sub_head;

						var parent_id = response.account_heads[i].parent_id;

						if(head_id != 6 && head_id != 16 && head_id != 17 && head_id != 26 && head_id != 126 && head_id != 145){

							option += '<option data-parent-id="'+parent_id+'" value="'+head_id+'">'+sub_head+ '</option>';

						}

					}

					$('.'+index+'-sub_head3_more').select2({dropdownAutoWidth : true});

					$('.'+index+'-sub_head3_more').append(option);

					$('.'+index+'-sub_head3_more').attr('required', true);

				}else{

					$('.'+index+'-sub_head3_more').append('<option value="">Select Account Head 4</option>');

				}
		   }

		})

		if(id > 0){
			contactList(id,row,pId,branchId,companyId);
		}

	});

	$(document).on('change','.sub_head3_more ',function(){ 

		var option='<option value="">Select Sub Head 5</option>';

		var id = $(this).val();

		var row = $(this).attr('data-row');

		var pId = $('option:selected', this).attr('data-parent-id');

		var branchId = $('option:selected', '#branch').val();

		var index = $(this).attr('data-value');
		var companyId = $('#company_id option:selected').val();
		if (companyId == "") {
			swal('Warning','Please select company first','warning');
			return false;
		}

		$('.'+index+'-sub_head4_more').empty();

		$('.'+index+'-sub_head5_more').empty();

		$('.'+index+'-sub_head5_more').attr('required',false);

		$('.selecetd-head-'+row).val(id);

		$('.selecetd-head-p-'+row).val(pId);

		$.ajax({

			type: "POST",  

			url: "{!! route('admin.get_indirect_expense_sub_head') !!}",

         	dataType: 'JSON',

			 data: {
				'head_id':id,
				'jvVoucher': true,
				'company_id': companyId,
				},

         	headers: {

            	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

          	},

		   	success: function(response) { 

			   	var len = response.account_heads.length;

				if(len > 0)

				{

					for(var i=0; i<len; i++){

						var head_id = response.account_heads[i].head_id;

						var sub_head = response.account_heads[i].sub_head;

						var parent_id = response.account_heads[i].parent_id;

						if(head_id != 6 && head_id != 16 && head_id != 17 && head_id != 26 && head_id != 126 && head_id != 145){

							option += '<option data-parent-id="'+parent_id+'" value="'+head_id+'">'+sub_head+ '</option>';

						}

					}

					$('.'+index+'-sub_head4_more').select2({dropdownAutoWidth : true});

					$('.'+index+'-sub_head4_more').append(option);

					$('.'+index+'-sub_head4_more').attr('required', true);

				}else{

					$('.'+index+'-sub_head4_more').append('<option value="">Select Account Head 5</option>');

				}

		 	}

		})

		if(id > 0){
			contactList(id,row,pId,branchId ,companyId);
		}

	});

	$(document).on('change','.sub_head4_more ',function(){ 

		var id = $(this).val();

		var row = $(this).attr('data-row');

		var pId = $('option:selected', this).attr('data-parent-id');

		var branchId = $('option:selected', '#branch').val();
		var companyId = $('#company_id option:selected').val();

		$('.selecetd-head-'+row).val(id);

		$('.selecetd-head-p-'+row).val(pId);
		if(id > 0){
			contactList(id,row,pId,branchId,companyId);
		}

	});

	$(document).on('change','.debit-amount ',function(){
		var amount = $(this).val();
		var row = $(this).attr('data-row');

		if($.isNumeric(amount)) {
			$('.credit-'+row).attr('readonly', 'true');
		}else{
			$(this).val('');
			$('.credit-'+row).removeAttr('readonly');
		   	swal("Warning!", "Not a number!", "warning");
		}

		var deposit = 0;
		$(".debit-amount").each(function(){
            deposit += +$(this).val();
        }); 
        $('.debit-sub-total').html(deposit);
        $('.debit-total').html(deposit);

        var debitAmount = $('.debit-total').html();
        var creditAmount = $('.credit-total').html();
        $('.amount-diff').html(debitAmount-creditAmount);
	});

	$(document).on('change','.credit-amount ',function(){
		var amount = $(this).val();
		var row = $(this).attr('data-row');

		if($.isNumeric(amount)) {
			$('.debit-'+row).attr('readonly', 'true');
		}else{
			$(this).val('');
			$('.debit-'+row).removeAttr('readonly');
		   	swal("Warning!", "Not a number!", "warning");
		}
		
		var credit = 0;
		$(".credit-amount").each(function(){
            credit += +$(this).val();
        });
        $('.credit-sub-total').html(credit);
        $('.credit-total').html(credit);

        var debitAmount = $('.debit-total').html();
        var creditAmount = $('.credit-total').html();
        $('.amount-diff').html(debitAmount-creditAmount);
	});

    $('#expense1').on("keyup", ".t_amount", function () {

        var sum = 0;

        $('.t_amount').each(function () {

	        if($(this).val()==0 || $(this).val()>0)
	        {
	           	sum += Number($(this).val());
	        }

        });

        $('#total_amount').val(sum);

    });

    $("#expense").on('click','.remCF',function(){
    	var row = $(this).attr('data-row');
    	
    	$('.m-box-'+row).remove();
    	$('.e-box-'+row).remove();
    	$('.r-box-'+row).remove();
    	$('.a-box-'+row).remove();
		$('.d-box-'+row).remove();
		$('.s-box-'+row).remove();
    	$('.re-box-'+row).remove();
		$('.la-box-'+row).remove();
		$('.sa-box-'+row).remove();
		$('.bank-box-'+row).remove();
		$('.vendor-box-'+row).remove();
		$('.customer-box-'+row).remove();
		$('.creditCard-box-'+row).remove();
        $(this).parent().parent().remove();
        $( ".t_amount" ).trigger( "keyup" );
        $( ".e-box" ).trigger( "keyup" );
       	$('.company_bond-box-'+row).remove();

        if($('.m-box').children().length== 0)
        {
        	$('#Member').hide();
        }	
        
   		if($('.e-box').children().length== 0)
        {
        	$('#emp').hide();
        }

       
   		if($('.r-box').children().length == 0)
        {

        	$('#rent').hide();
        }

        
   		if($('.a-box').children().length== 0)
        {
        	$('#associate').hide();
        }

       
   		if($('.d-box').children().length== 0)
        {
        	$('#director').hide();
        }

       
   		if($('.s-box').children().length== 0)
        {
        	$('#shareholder').hide();
        }

        
   		if($('.re-box').children().length== 0)
        {
        	$('#investment').hide();
        }

        
   		if($('.la-box').children().length== 0)
        {
        	$('#load_account').hide();
        }

       
   		if($('.sa-box').children().length== 0)
        {
        	$('#saving_account').hide();
        }

        if($('.bank-box').children().length== 0)
        {
        	$('#bank_detail').hide();
        }

          if($('.vendor-box').children().length== 0)
        {
        	$('#vendor_detail').hide();
        }

         if($('.customer-box').children().length== 0)
        {
        	$('#customer_detail').hide();
        }

         if($('.creditCard-box').children().length== 0)
        {
        	$('#creditCard_detail').hide();
        }

        if($('.company_bond-box').children().length== 0)
        {
        	$('#company_bond_detail').hide();
        }
    });

   	expense = $('#expense_listing').DataTable({

        processing:true,

        serverSide:true,

        pageLength:20,

        lengthMenu:[10,20,40,50,100],

        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {

            var oSettings = this.fnSettings ();

            $('html, body').stop().animate({

                scrollTop: ($('#expense_listing').offset().top)

            }, 10);

            $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);

            return nRow;

        },

         ajax: {

            "url": "{!! route('admin.expense_listing') !!}",

            "type": "POST",

            "data":function(d) {d.searchform=$('form#filter').serializeArray()},

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            },

        },

         "columnDefs": [{

            "render": function(data, type, full, meta) {

                return meta.row + 1; // adds id to serial no

            },

            "targets": 0

        }],

         columns: [

             {data: 'DT_RowIndex', name: 'DT_RowIndex'},

            {data: 'branch_code', name: 'branch_code'},

            {data: 'branch_name', name: 'branch_name'},

           

			 {data: 'bill_date', name: 'bill_date'},

			  {data: 'payment_date', name: 'payment_date'},

			{data: 'account_head', name: 'account_head'},

            {data: 'sub_head1', name: 'sub_head1'},

            {data: 'sub_head2', name: 'sub_head2'},

			{data: 'particular', name: 'particular'},

			{data: 'receipt', name: 'receipt '},

            {data: 'amount', name: 'amount'},

          

        ],"ordering": false

    })

   	$('#contact_more').on('change',function(){
   		var index = $(this).attr('data-row');
   		var id = $(this).val();
   		if(id == 1)
   		{
   			$('#member').show();
   		}
   		else if(id == 2)
   		{
   			$('#employee').show();
   		}
   		else if(id == 2)
   		{
   			$('#rent').show();
   		}
   	})

   	$(document).on('change','.head-contact ',function(){
   		var resValue = $(this).val();
		// var company_id = $('#company_id option:selected');
   		var contactRow = $(this).attr('data-value');
   		
   		
   		$('.m-box-'+contactRow+'').remove();
   		$('.e-box-'+contactRow+'').remove();
   		$('.r-box-'+contactRow+'').remove();
   		$('.a-box-'+contactRow+'').remove();
   		$('.d-box-'+contactRow+'').remove(); 
   		$('.s-box-'+contactRow+'').remove(); 
   		$('.re-box-'+contactRow+'').remove();
   		$('.la-box-'+contactRow+'').remove();
   		$('.sa-box-'+contactRow+'').remove();
   		$('.bank-box-'+contactRow+'').remove();
   		$('.vendor-box-'+contactRow+'').remove();
   		$('.customer-box-'+contactRow+'').remove();
   		$('.creditCard-box-'+contactRow+'').remove();
   		$('.company_bond-box-'+contactRow+'').remove();
   		if($('.m-box').children().length== 0)
        {
        	$('#Member').hide();
        }

        
   		if($('.e-box').children().length== 0)
        {
        	$('#emp').hide();
        }

        
   		if($('.r-box').children().length== 0)
        {
        	$('#rent').hide();
        }

        
   		if($('.a-box').children().length== 0)
        {
        	$('#associate').hide();
        }

       
   		if($('.d-box').children().length== 0)
        {
        	$('#director').hide();
        }

        
   		if($('.s-box').children().length== 0)
        {
        	$('#shareholder').hide();
        }

      
   		if($('.re-box').children().length== 0)
        {
        	$('#investment').hide();
        }

      
   		if($('.la-box').children().length== 0)
        {
        	$('#load_account').hide();
        }

       
   		if($('.sa-box').children().length== 0)
        {
        	$('#saving_account').hide();
        }

        if($('.bank-box').children().length== 0)
        {
        	$('#bank_detail').hide();
        }

        if($('.vendor-box').children().length== 0)
        {
        	$('#vendor_detail').hide();
        }
   		
   		if($('.customer-box').children().length== 0)
        {
        	$('#customer_detail').hide();
        }

          if($('.creditCard-box').children().length== 0)
        {
        	$('#creditCard_detail').hide();
        }

        if($('.company_bond-box').children().length== 0)
        {
        	$('#company_bond_detail').hide();
        }
   		
   		var head5 = $('option:selected', '.sub-head4-'+contactRow).val();
   		var pId5 = $('option:selected', '.sub-head4-'+contactRow).attr('data-parent-id');

		var head4 = $('option:selected', '.sub-head3-'+contactRow).val();
		var pId4 = $('option:selected', '.sub-head3-'+contactRow).attr('data-parent-id');

		var head3 = $('option:selected', '.sub-head2-'+contactRow).val();
		var pId3 = $('option:selected', '.sub-head2-'+contactRow).attr('data-parent-id');

		var head2 = $('option:selected', '.sub-head1-'+contactRow).val();
		var pId2 = $('option:selected', '.sub-head1-'+contactRow).attr('data-parent-id');

		var head1 = $('option:selected', '.account-head-'+contactRow).val();
		var pId1 = $('option:selected', '.account-head-'+contactRow).attr('data-parent-id');
		
		if(head5 > 0){
			var headId = head5;
			var pId = pId5;
		}else if(head4 > 0){
			var headId = head4;
			var pId = pId4;
		}else if(head3 > 0){
			var headId = head3;
			var pId = pId3;
		}else if(head2 > 0){
			var headId = head2;
			var pId = pId2;
		}else if(head1 > 0){
			var headId = head1;
			var pId = pId1;
		}
		console.log('pid',pId);
		
   		if(headId == 34 || headId == 55 ){
   			$.ajax({
				type: "POST",  
		        url: "{!! route('admin.jv.getmemberdetails') !!}",
		        dataType: 'JSON',
		        data: {'resValue':resValue ,
			},
		        headers: {
		              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		        },
				success: function(response) { 
					if(response)
					{
						var first_name = '';
						var last_name = '';
						if(response.result.first_name && response.result.last_name)
						{
							 first_name = response.result.first_name;
							 last_name = response.result.last_name;
						}
						else if(response.result.first_name){
							 first_name = response.result.first_name;
							 last_name = '';
						}

						$('#Member').show();
						$('.m-box-'+contactRow+'').remove();

   						$('.m-box').append('<div class="m-box-'+contactRow+'" attr = '+mcount+'><table><tbody><tr><th>Member Id:</th><td class="p-2">'+response.result.member_id+'</td></tr><tr><th>Member Name:</th><td  class="p-2">'+first_name+' '+last_name+'</td></tr></tbody></table></div>');

   						if($('.m-box').children().length > 1)
   						{
   							if($('.m-box').children().last())
	   						{
	   							$('.m-box-'+contactRow+'').prepend('<hr style="margin:0px;"/>');
	   						}
   						}
   						
   						
   						
					}
					else{
						$('#Member').hide();
					}
					
				}
				
			})
   		}else if(headId == 32 || headId == 61 || headId == 72 || headId == 73 || headId == 76 || headId == 143 || pId == 86){
   			$.ajax({
				type: "POST",  
		        url: "{!! route('admin.jv.getemployeedetails') !!}",
		        dataType: 'JSON',
		        data: {'resValue':resValue},
		        headers: {
		              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		        },
				success: function(response) { 
					if(response)
					{	
						$('#emp').show();
	   					$('.e-box-'+contactRow+'').remove();
	   					$('.e-box').append('<div class="e-box-'+contactRow+'"><table><tbody><tr><th>Employee Code:</th><td class="p-2">'+response.result.employee_code+'</td></tr><tr><th>Employee Name:</th><td  class="p-2">'+response.result.employee_name+'</td></tr></tbody></table></div>');
	   					if($('.e-box').children().length > 1)
   						{
   							if($('.e-box').children().last())
	   						{
	   							$('.e-box-'+contactRow+'').prepend('<hr style="margin:0px;"/>');
	   						}
   						}
	   				}	
	   				else{
	   					$('#emp').hide();
	   				}
				}
			})
   		}else if(headId == 60 || headId == 74 || headId ==75 || headId ==144){
   			$.ajax({
				type: "POST",  
		        url: "{!! route('admin.jv.getrentdetails') !!}",
		        dataType: 'JSON',
		        data: {'resValue':resValue},
		        headers: {
		              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		        },
				success: function(response) { 
					if(response)
					{	
						$('#rent').show();
						$('.r-box-'+contactRow+'').remove();
   						$('.r-box').append('<div class="r-box-'+contactRow+'"><table><tbody><tr><th>Rent Id:</th><td class="p-2">'+response.result.id+'</td></tr><tr><th>Rent Owner:</th><td  class="p-2">'+response.result.owner_name+'</td></tr></tbody></table></div>');
   						if($('.r-box').children().length > 1)
   						{
   							if($('.r-box').children().last())
	   						{
	   							$('.r-box-'+contactRow+'').prepend('<hr style="margin:0px;"/>');
	   						}
   						}
					}
					else{
						$('#rent').hide();
					}
   					
				}
			})
   		}else if(headId == 35){
   			$.ajax({
				type: "POST",  
		        url: "{!! route('admin.jv.getinvestmentdetails') !!}",
		        dataType: 'JSON',
		        data: {'resValue':resValue},
		        headers: {
		              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		        },
				success: function(response) { 
   					$('.contact-account-'+contactRow).val(response.result.account_number);
				}
			})
   		}else if(headId == 87 || headId == 88 || headId == 63 || headId == 141){
			$.ajax({
				type: "POST",  
		        url: "{!! route('admin.jv.getassociatesdetails') !!}",
		        dataType: 'JSON',
		        data: {'resValue':resValue},
		        headers: {
		              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		        },
				success: function(response) {
					if(response)
					{
						var first_name = '';
						var last_name = '';
						if(response.result.first_name && response.result.last_name)
						{
							 first_name = response.result.first_name;
							 last_name = response.result.last_name;
						}
						else if(response.result.first_name){
							 first_name = response.result.first_name;
							 last_name = '';
						}
						$('#associate').show();
						$('.a-box-'+contactRow+'').remove();
   						$('.a-box').append('<div class="a-box-'+contactRow+'"><table><tbody><tr><th>Associate Id:</th><td class="p-2">'+response.result.associate_no+'</td></tr><tr><th>Associate Name:</th><td  class="p-2">'+first_name+' '+last_name+'</td></tr></tbody></table></div>');
   						if($('.a-box').children().length > 1)
   						{
   							if($('.a-box').children().last())
	   						{
	   							$('.a-box-'+contactRow+'').prepend('<hr style="margin:0px;"/>');
	   						}
   						}
					}
					else{
						$('#associate').hide();
					}
   					
				}
			})
		}else if(headId == 89){
			$.ajax({
				type: "POST",  
		        url: "{!! route('admin.jv.getreinvestmentsaccountsDetails') !!}",
		        dataType: 'JSON',
		        data: {'resValue':resValue},
		        headers: {
		              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		        },
				success: function(response) {
					if(response)
					{	
						var first_name = '';
						var last_name = ''; 
						if(response.result.member.first_name && response.result.member.last_name)
						{
							first_name =  response.result.member.first_name;
							last_name = response.result.member.last_name;
						}
						else{
							first_name =  response.result.member.first_name;
							last_name = '';
						}
						$('#investment').show();
	   					$('.re-box-'+contactRow+'').remove();
	   					$('.re-box').append('<div class="re-box-'+contactRow+'"><table><tbody><tr><th>Account No.:</th><td class="p-2">'+response.result.account_number+'</td></tr><tr><th>Member Name:</th><td class="p-2">'+first_name+' '+last_name+'</td></tr><tr><th>Plan Name:</th><td class="p-2">'+response.result.plan.name+'</td></tr></tbody></table></div>');
	   					if($('.re-box').children().length > 1)
   						{
   							if($('.re-box').children().last())
	   						{
	   							$('.re-box-'+contactRow+'').prepend('<hr style="margin:0px;"/>');
	   						}
   						}
	   				}	
	   				else{
	   					$('#investment').hide();
	   				}
				}
			})
		}else if(headId == 31 || headId == 33  || headId == 64 || headId == 65 || headId == 66 || headId == 67 || headId == 294 ){
			$.ajax({
				type: "POST",  
		        url: "{!! route('admin.jv.getloanaccountsdetails') !!}",
		        dataType: 'JSON',
		        data: {'resValue':resValue, headId:headId},
		        headers: {
		              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		        },
				success: function(response) {
					if(response)
					{	
						loanType = '';
						if(response.result.loan_type  == 1)
						{
							loanType = 'Personal Loan';
						}
						else if(response.result.loan_type  == 2)
						{
							loanType = 'Staff Loan';
						}
						else if(response.result.loan_type ==3)
						{
							loanType = 'Group Loan';
						}
						else if(response.result.loan_type == 4)
						{
							loanType = 'Loan Against Investment Plan(DL) ';
						}
						var first_name = '';
						var last_name = ''; 
						if(response.result.loan_member.first_name && response.result.loan_member.last_name)
						{
							first_name =  response.result.loan_member.first_name;
							last_name = response.result.loan_member.last_name;
						}
						else{
							first_name =  response.result.loan_member.first_name;
							last_name = '';
						}
						$('#load_account').show();
	   					$('.la-box-'+contactRow+'').remove();
	   					$('.la-box').append('<div class="la-box-'+contactRow+'"><table><tbody><tr><th>Account No.:</th><td class="p-2">'+response.result.account_number+'</td></tr><tr><th>Loan Type:</th><td class="p-2">'+loanType+'</td></tr><tr><th>Member Name:</th><td class="p-2">'+first_name+' '+last_name+'</td></tr></tbody></table></div>');
	   					if($('.la-box').children().length > 1)
   						{
   							if($('.la-box').children().last())
	   						{
	   							$('.la-box-'+contactRow+'').prepend('<hr style="margin:0px;"/>');
	   						}
   						}
	   				}
	   				else{
	   					$('#load_account').show();
	   				}	
				}
			})
		}else if( headId == 97 ){
			$.ajax({
				type: "POST",  
		        url: "{!! route('admin.jv.getloanfrombankdetail') !!}",
		        dataType: 'JSON',
		        data: {'resValue':resValue, headId:headId},
		        headers: {
		              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		        },
				success: function(response) {
					if(response)
					{	
						
						$('#load_account').show();
	   					$('.la-box-'+contactRow+'').remove();
	   					$('.la-box').append('<div class="la-box-'+contactRow+'"><table><tbody><tr><th>Account No.:</th><td class="p-2">'+response.result.loan_account_number+'</td></tr><tr><th>Bank Name:</th><td class="p-2">'+response.result.bank_name+'</td></tr></tbody></table></div>');
	   					if($('.la-box').children().length > 1)
   						{
   							if($('.la-box').children().last())
	   						{
	   							$('.la-box-'+contactRow+'').prepend('<hr style="margin:0px;"/>');
	   						}
   						}
	   				}
	   				else{
	   					$('#load_account').show();
	   				}	
				}
			})
		}else if( headId == 36 ||headId == 57 || headId == 58 || headId == 59 || headId == 80 || headId == 81 || headId == 82 || headId == 83 || headId == 84 || headId == 85 || headId == 122 || headId == 77 || headId == 78 || headId == 79 || headId == 139 || headId == 62 || headId == 35 ){
			$.ajax({
				type: "POST",  
		        url: "{!! route('admin.jv.getinvestmentsaccountsdetails') !!}",
		        dataType: 'JSON',
		        data: {'resValue':resValue, headId:headId},
		        headers: {
		              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		        },
				success: function(response) {

					if(response)
					{	var first_name = '';
						var last_name = ''; 
						if(response.result.member.first_name && response.result.member.last_name)
						{
							first_name =  response.result.member.first_name;
							last_name = response.result.member.last_name;
						}
						else{
							first_name =  response.result.member.first_name;
							last_name = '';
						}
						$('#investment').show();
	   					$('.re-box-'+contactRow+'').remove();
	   					$('.re-box').append('<div class="re-box-'+contactRow+'"><table><tbody><tr><th>Account No.:</th><td class="p-2">'+response.result.account_number+'</td></tr><tr><th>Member Name:</th><td class="p-2">'+first_name+' '+last_name+'</td></tr><tr><th>Plan Name:</th><td class="p-2">'+response.result.plan.name+'</td></tr></tbody></table></div>');
	   					if($('.re-box').children().length > 1)
   						{
   							if($('.re-box').children().last())
	   						{
	   							$('.re-box-'+contactRow+'').prepend('<hr style="margin:0px;"/>');
	   						}
   						}
	   				}
	   				else{
	   					$('#investment').hide();
	   				}	
				}
			})
		}else if(headId == 56 || pId == 406 || pId == '403'){
			let CompanyId = $('#company_id').val();
			$.ajax({
				type: "POST",  
		        url: "{!! route('admin.jv.getsavingaccountsdetails') !!}",
		        dataType: 'JSON',
		        data: {'resValue':resValue, headId:headId ,company_id: CompanyId},
		        headers: {
		              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		        },
				success: function(response) {
					if(response)
					{	
						var first_name = '';
						var last_name = ''; 
						var balance_show = "";
						if(response.result.customer_s_s_b.first_name && response.result.customer_s_s_b.last_name && response.result.balance)
						{
							first_name =  response.result.customer_s_s_b.first_name;
							last_name = response.result.customer_s_s_b.last_name;
							balance_show = response.result.balance;
						} 
						else{
							first_name =  response.result.customer_s_s_b.first_name;
							last_name = '';
							balance_show = response.result.balance;
						}
						$('#saving_account').show();	
	   					$('.sa-box-'+contactRow+'').remove();
	   					$('.sa-box').append('<div class="sa-box-'+contactRow+'"><table><tbody><tr><th>Account No.:</th><td class="p-2">'+response.result.account_no+'</td></tr><tr><th>Member Name:</th><td class="p-2">'+first_name+' '+last_name+'</td></tr><tr><th>Balance:</th><td class="p-2">'+balance_show+'</td></tr></tbody></table></div>');
	   					if($('.sa-box').children().length > 1)
   						{
   							if($('.sa-box').children().last())
	   						{
	   							$('.sa-box-'+contactRow+'').prepend('<hr style="margin:0px;"/>');
	   						}
   						}
	   				}
	   				else{
	   					$('#saving_account').hide();
	   				}
				}
			})
		}else if(headId == 68 || headId == 69 || headId == 70 || headId == 91 || headId == 27 || pId == 27 ){
			$.ajax({
				type: "POST",  
		        url: "{!! route('admin.jv.getbankAccountdetails') !!}",
		        dataType: 'JSON',
		        data: {'resValue':resValue, headId:headId},
		        headers: {
		              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		        },
				success: function(response) {
					// console.log(response);
					if(response)
					{	
						$('#bank_detail').show();	
	   					$('.bank-box-'+contactRow+'').remove();
	   					$('.bank-box').append('<div class="bank-box-'+contactRow+'"><table><tbody><tr><th>Account No.:</th><td class="p-2">'+response.result.account_no+'</td></tr><tr><th>Bank Name:</th><td class="p-2">'+response.name+'</td></tr><tr><th>IFSC Code:</th><td class="p-2">'+response.result.ifsc_code+'</td></tr></tbody></table></div>');
	   					if($('.bank-box').children().length > 1)
   						{
   							if($('.bank-box').children().last())
	   						{
	   							$('.bank-box-'+contactRow+'').prepend('<hr style="margin:0px;"/>');
	   						}
   						}
	   				}
	   				else{
	   					$('#bank_detail').hide();
	   				}
				}
			})
		}else if(headId == 140  ){
			$.ajax({
				type: "POST",  
		        url: "{!! route('admin.jv.getcustomerdetail') !!}",
		        dataType: 'JSON',
		        data: {'resValue':resValue, headId:headId,'type':0},
		        headers: {
		              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		        },
				success: function(response) {
					console.log(response);
					if(response)
					{	
						$('#vendor_detail').show();	
	   					$('.vendor-box-'+contactRow+'').remove();
	   					$('.vendor-box').append('<div class="vendor-box-'+contactRow+'"><table><tbody><tr><th>Vendor Name.:</th><td class="p-2">'+response.name+'</td></tr><tr><th>Company Name:</th><td class="p-2">'+response.company_name+'</td></tr></tbody></table></div>');
	   					if($('.vendor-box').children().length > 1)
   						{
   							if($('.vendor-box').children().last())
	   						{
	   							$('.vendor-box-'+contactRow+'').prepend('<hr style="margin:0px;"/>');
	   						}
   						}
	   				}
	   				else{
	   					$('#vendor_detail').hide();
	   				}
				}
			})
		}else if(headId == 140  ){
			$.ajax({
				type: "POST",  
		        url: "{!! route('admin.jv.getcustomerdetail') !!}",
		        dataType: 'JSON',
		        data: {'resValue':resValue, headId:headId,'type':0},
		        headers: {
		              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		        },
				success: function(response) {
					console.log(response);
					if(response)
					{	
						$('#vendor_detail').show();	
	   					$('.vendor-box-'+contactRow+'').remove();
	   					$('.vendor-box').append('<div class="vendor-box-'+contactRow+'"><table><tbody><tr><th>Vendor Name.:</th><td class="p-2">'+response.name+'</td></tr><tr><th>Company Name:</th><td class="p-2">'+response.company_name+'</td></tr></tbody></table></div>');
	   					if($('.vendor-box').children().length > 1)
   						{
   							if($('.vendor-box').children().last())
	   						{
	   							$('.vendor-box-'+contactRow+'').prepend('<hr style="margin:0px;"/>');
	   						}
   						}
	   				}
	   				else{
	   					$('#vendor_detail').hide();
	   				}
				}
			})
		}else if(headId == 142  ){
			$.ajax({
				type: "POST",  
		        url: "{!! route('admin.jv.getcustomerdetail') !!}",
		        dataType: 'JSON',
		        data: {'resValue':resValue, headId:headId,'type':1},
		        headers: {
		              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		        },
				success: function(response) {
					console.log(response);
					if(response)
					{	
						$('#customer_detail').show();	
	   					$('.customer-box-'+contactRow+'').remove();
	   					$('.customer-box').append('<div class="customer-box-'+contactRow+'"><table><tbody><tr><th>Vendor Name.:</th><td class="p-2">'+response.name+'</td></tr><tr><th>Company Name:</th><td class="p-2">'+response.company_name+'</td></tr></tbody></table></div>');
	   					if($('.vendor-box').children().length > 1)
   						{
   							if($('.customer-box').children().last())
	   						{
	   							$('.customer-box-'+contactRow+'').prepend('<hr style="margin:0px;"/>');
	   						}
   						}
	   				}
	   				else{
	   					$('#customer_detail').hide();
	   				}
				}
			})
		}else if(pId == 167  ){
			$.ajax({
				type: "POST",  
		        url: "{!! route('admin.jv.getcreditcarddetail') !!}",
		        dataType: 'JSON',
		        data: {'resValue':resValue, headId:headId},
		        headers: {
		              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		        },
				success: function(response) {
					console.log(response);
					if(response)
					{	
						$('#creditCard_detail').show();	
	   					$('.creditCard-box-'+contactRow+'').remove();
	   					$('.creditCard-box').append('<div class="creditCard-box-'+contactRow+'"><table><tbody><tr><th>Bank Name.:</th><td class="p-2">'+response.result.credit_card_bank+'</td></tr><tr><th>Account Numbery:</th><td class="p-2">'+response.result.credit_card_account_number+'</td></tr></tbody></table></div>');
	   					if($('.creditCard-box').children().length > 1)
   						{
   							if($('.creditCard-box').children().last())
	   						{
	   							$('.creditCard-box-'+contactRow+'').prepend('<hr style="margin:0px;"/>');
	   						}
   						}
	   				}
	   				else{
	   					$('#customer_detail').hide();
	   				}
				}
			})
		}else if(headId == 234  || pId == 236 || headId ==233){
			$.ajax({
				type: "POST",  
		        url: "{!! route('admin.jv.getcompanybondFd.completeDetail') !!}",
		        dataType: 'JSON',
		        data: {'resValue':resValue, headId:headId},
		        headers: {
		              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		        },
				success: function(response) {
					console.log(response);
					if(response)
					{	
						$('#company_bond_detail').show();	
	   					$('.company_bond-box-'+contactRow+'').remove();
	   					$('.company_bond-box').append('<div class="company_bond-box-'+contactRow+'"><table><tbody><tr><th>Bank Name.:</th><td class="p-2">'+response.result.bank_name+'</td></tr><tr><th>FD Number:</th><td class="p-2">'+response.result.fd_no+'</td></tr></tbody></table></div>');
	   					if($('.creditCard-box').children().length > 1)
   						{
   							if($('.company_bond-box').children().last())
	   						{
	   							$('.company_bond-box-'+contactRow+'').prepend('<hr style="margin:0px;"/>');
	   						}
   						}
	   				}
	   				else{
	   					$('#company_bond_detail').hide();
	   				}
				}
			})
		}else if(pId == 15 || pId == 19 ){
			$.ajax({
				type: "POST",  
		        url: "{!! route('admin.jv.getshareholdersdetails') !!}",
		        dataType: 'JSON',
		        data: {'headId':resValue, 'pId':resValue},
		        headers: {
		              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		        },
				success: function(response) { 
					
					if(pId == "15"){
						if(response)
						{
							$('#shareholder').show();	
							$('.s-box-'+contactRow+'').remove();
							$('.s-box').append('<div class="s-box-'+contactRow+'"><table><tbody><tr><th>Shareholder Id:</th><td class="p-2">'+response.result.member_id+'</td></tr><tr><th>Shareholder Name:</th><td class="p-2">'+response.result.name+'</td></tr></tbody></table></div>');
							if($('.s-box').children().length > 1)
	   						{
	   							if($('.s-box').children().last())
		   						{
		   							$('.s-box-'+contactRow+'').prepend('<hr style="margin:0px;"/>');
		   						}
	   						}
						}
						else{
							$('#shareholder').hide();
						}
					}	
					
					if(pId == "19"){
						if(response)
						{
							$('#director').show();
							$('.d-box-'+contactRow+'').remove();
							$('.d-box').append('<div class="d-box-'+contactRow+'"><table><tbody><tr><th>Director Id:</th><td class="p-2">'+response.result.member_id+'</td></tr><tr><th>Director Name:</th><td class="p-2">'+response.result.name+'</td></tr></tbody></table></div>');
							if($('.d-box').children().length > 1)
	   						{
	   							if($('.d-box').children().last())
		   						{
		   							$('.d-box-'+contactRow+'').prepend('<hr style="margin:0px;"/>');
		   						}
	   						}
						}	
						else{
							$('#director').hide();
						}	
					
					}
					
				}
			})
		}
   	})

	if($('.m-box').children().length== 0)
    {
    	$('#Member').hide();
    }	
   
	if($('.e-box').children().length== 0)
    {
    	$('#emp').hide();
    }

	if($('.r-box').children().length == 0)
    {

    	$('#rent').hide();
    }

	if($('.a-box').children().length== 0)
    {
    	$('#associate').hide();
    }
   
	if($('.d-box').children().length== 0)
    {
    	$('#director').hide();
    }

	if($('.s-box').children().length== 0)
    {
    	$('#shareholder').hide();
    }

	if($('.re-box').children().length== 0)
    {
    	$('#investment').hide();
    }

	if($('.la-box').children().length== 0)
    {
    	$('#load_account').hide();
    }

	if($('.sa-box').children().length== 0)
    {
    	$('#saving_account').hide();
    }

    if($('.bank-box').children().length== 0)
    {
    	$('#bank_detail').hide();
    }
    
     if($('.vendor-box').children().length== 0)
    {
    	$('#vendor_detail').hide();
    }

    if($('.customer-box').children().length== 0)
    {
    	$('#customer_detail').hide();
    }

    if($('.creditCard-box').children().length== 0)
    {
    	$('#creditCard_detail').hide();
    }

    if($('.company_bond-box').children().length== 0)
    {
    	$('#company_bond_detail').hide();
    }


   	$(document).on('click','.reset',function()
	{
		location.reload();

	})

	$(expense.table().container()).removeClass( 'form-inline' ); 

	$( document ).ajaxStart(function() { 

		$( ".loader" ).show();

	});

   	$( document ).ajaxComplete(function() {

    	$( ".loader" ).hide();

   	});

})

function printDiv(elem) {

   $("#"+elem).print({

        //Use Global styles

        globalStyles : true,

        //Add link with attrbute media=print

        mediaPrint : true,

        //Custom stylesheet

        //Print in a hidden iframe

        iframe : false,

        //Don't print this

        noPrintSelector : ".avoid-this",

        //Add this at top

      	//  prepend : "Hello World!!!<br/>",

        //Add this on bottom

       	// append : "<span><br/>Buh Bye!</span>",

       	header: null,               // prefix to html

      	footer: null,  

        //Log to console when printing is done via a deffered callback

        deferred: $.Deferred().done(function() {    })

    });
}

function contactList(headId,row,pId,branchId ,company_id = null)
{ 

	console.log(headId,row,pId,branchId);

	var userType = '';
	$(".contact-"+row).html('');

	$(".contact-"+row).append("<option value=''>Select Contact Number</option>");

	if(headId == 56 || pId == 406 || pId == 403 ){
		var url = "{!! route('admin.jv.getsavingaccounts') !!}"
	}else if( headId == 34 || headId == 55 ){
		var url = "{!! route('admin.jv.getmembers') !!}"
	}else if(headId == 32 || headId == 61  || headId == 72 || headId == 73 || headId == 76 || headId == 143 || pId == 86){
		var url = "{!! route('admin.jv.getemployees') !!}"
	}else if(headId == 87 || headId == 88 || headId == 63){
		var url = "{!! route('admin.jv.getassociates') !!}"
	}else if(headId == 15 || pId == 15 || headId == 19 || pId == 19){
		var url = "{!! route('admin.jv.getshareholders') !!}"
	}else if(headId == 31 || headId == 33  || headId == 64 || headId == 65 || headId == 66 || headId == 67 || headId == 90 || headId == 294){
		var url = "{!! route('admin.jv.getloanaccounts') !!}"
	}else if( headId == 36 || headId == 57 || headId == 58 || headId == 59 || headId == 80 || headId == 81 || headId == 82 || headId == 83 || headId == 84 || headId == 85 || headId == 122 || headId == 77 || headId == 78 || headId == 79 || headId == 139 || headId == 62 || headId == 35 || headId == 207){
		var url = "{!! route('admin.jv.getinvestmentsaccounts') !!}"
	}else if(headId == 89){
		var url = "{!! route('admin.jv.getreinvestmentsaccounts') !!}"
	}else if(headId == 68 || headId == 69 || headId == 70 || headId == 91 || headId == 27 || pId == 27){
		var url = "{!! route('admin.jv.getbank') !!}"
	}else if(headId == 18 || pId == 18 || headId == 97){
		var url = "{!! route('admin.jv.getloanfrombank') !!}"
	}else if(headId == 28 || headId == 71){
		var url = "{!! route('admin.jv.getbranch') !!}"
	}else if(headId == 60 || headId == 74 || headId ==75 || headId ==144){
		var url = "{!! route('admin.jv.getrentliability') !!}"
	}
	else if(pId == 26 || headId == 142){
		var url = "{!! route('admin.jv.getcustomer') !!}"
		$("#type").val("26");
		var userType = 1;
	}
	else if(headId == 140 || headId == 176 || headId == 185 ){
		var url = "{!! route('admin.jv.getcustomer') !!}"
		var userType = 0;
		$("#type").val("26");
	}

	else if(headId == 234 || pId  == 236 || headId==233){
		var url = "{!! route('admin.jv.getcompanybondFd') !!}"
	}


	$(".contact-"+row).select2({
		minimumInputLength: 3,
	    ajax: {

			type: "POST", 

			delay: 250, 

	        url: url,

	        dataType: 'JSON',

	        //data: {'headId':headId,'pId':pId,'branchId':branchId},

	        /*headers: {

	              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

	        },*/

	        data: function(params) {
                return {
                    query: params.term, // search term
                    headId:headId,
                    pId:pId,
                    branchId:branchId,
                    userType:userType,
					company_id:company_id,

                    "_token": "{{ csrf_token() }}",
                };
            },
            processResults: function(response) {
                

                /*$.each(response, function (index, value) {
                	console.log('r',value.dataoptiontypevalue);

                	$(".contact-"+row).append("<option data-option-type='member' data-option-type-value='"+value.dataoptiontypevalue+"' data-option-value='"+value.member_id+"' value='"+value.id+"'>"+value.member_id+"</option>");

                }); */

                return {
                    results: response
                };
            },
            cache: true
			/*success: function(response) { 

				$.each(response.result, function (index, value) { 

					$(".contact-"+row).select2({dropdownCssClass : 'frm',dropdownCssClass : 'search-table-outter'});

					if(headId == 56){
	                	$(".contact-"+row).append("<option data-option-type='saving' data-option-value='"+value.account_no+"' value='"+value.id+"'>"+value.account_no+"</option>");
	                }else if(headId == 101171500075 || headId == 33 || headId == 36 || headId == 122 || headId == 34 || headId == 90 || headId == 35 || headId == 55 || headId == 62){
	                	$(".contact-"+row).append("<option data-option-type='member' data-option-type-value='"+value.first_name+" "+value.last_name+"' data-option-value='"+value.member_id+"' value='"+value.id+"'>"+value.member_id+"</option>");
	                }else if(headId == 61 || headId == 67 || headId == 72){
						$(".contact-"+row).append("<option data-option-type='employee' data-option-type-value='"+value.employee_code+"' data-option-value='"+value.employee_name+"' value='"+value.id+"'>"+value.employee_name+"</option>");
					}else if(headId == 87 || headId == 88 || headId == 63){
						$(".contact-"+row).append("<option data-option-type='associate' data-option-value='"+value.associate_no+"' value='"+value.id+"'>"+value.associate_no+"</option>");
					}else if(headId == 15 || pId == 15 || headId == 19 || pId == 19){
						$(".contact-"+row).append("<option data-option-type='shareholder' data-option-value='"+value.name+"' value='"+value.id+"'>"+value.name+"</option>");
					}else if(headId == 64 || headId == 65 || headId == 66 || headId == 67){
						$(".contact-"+row).append("<option data-option-type='loan' data-option-value='"+value.account_number+"' value='"+value.id+"'>"+value.account_number+"</option>");
					}else if(headId == 57 || headId == 58 || headId == 59 || headId == 80 || headId == 81 || headId == 82 || headId == 83 || headId == 84 || headId == 85 || headId == 77 || headId == 78 || headId == 79 || headId == 89){
						$(".contact-"+row).append("<option data-option-type='investment' data-option-value='"+value.account_number+"' value='"+value.id+"'>"+value.account_number+"</option>");
					}else if(headId == 68 || headId == 69 || headId == 70 || headId == 91){
						$(".contact-"+row).append("<option data-option-type='bank' data-option-value='"+value.account_no+"' value='"+value.id+"'>"+value.account_no+"</option>");
					}else if(headId == 18 || pId == 18){
						$(".contact-"+row).append("<option data-option-type='loanfrombank' data-option-value='"+value.loan_account_number+"' value='"+value.id+"'>"+value.loan_account_number+"</option>");
					}else if(headId == 28 || headId == 71){
						$(".contact-"+row).append("<option data-option-type='cash' data-option-value='"+value.name+"' value='"+value.id+"'>"+value.name+"</option>");
					}else if(headId == 60){
						$(".contact-"+row).append("<option data-option-type='rent' data-option-type-value='"+value.id+"' data-option-value='"+value.owner_name+"' value='"+value.id+"'>"+value.owner_name+"</option>");
					}
	            });


	            pagination: {
			    	more: true
			  	}

			}*/

		}
	});
}

</script>