<script type="text/javascript">

var TransactionTable;



$(document).ready(function() {



	$('#ac_opening_date').datepicker( {



       format: "dd/mm/yyyy",



       orientation: "bottom auto",



       autoclose: true,



	   endDate:new Date(),
		//SACHIN NE DATE CHANGE KARAWAYA  =30-08-2022
	   startDate: '01/04/2019', 




    })		



	$.validator.addMethod("checkreinvestAccount", function(value, element,p) { 



      if(value.startsWith("R-"))

      {

        $.validator.messages.checkreinvestAccount = "";

        result = true;

      }else{

       swal("Error!",  "Please Enter Valid Account Number.", "error");;

        result = false;  

      }



	   return result;



	}, "")







    // Hide loading image



  	$( document ).ajaxStart(function() {



        $( ".loader" ).show();



    });







    // Hide loading image



    $( document ).ajaxComplete(function() {



        $( ".loader" ).hide();



    });







	 $('#filter2').validate({ // initialize the plugin



        rules: {



           // 'account_no' : {required: true,checkreinvestAccount:true},



			'eli_amount' : {required: true,number:true,},



			'ac_deno' : {required: true,},



			'ac_opening_date' : {required: true,},



			'mb_date' : {required: true,},



			'mb_amount' : {required: true,number:true,},



			'mb_transfer' : {required: true,number:true,},



			'mb_inst' : {required: true,number:true,},



			'mbfd_amount' : {required: true,number:true,},

        }



	});



	



	/*$('.formsubmit').on('click',function(){



		var eli_amount = $('#eli_amount').val();



		var ac_deno = $('#ac_deno').val();



		var ac_opening_date = $('#ac_opening_date').val();



		var mb_date = $('#mb_date').val();



		var mb_amount = $('#mb_amount').val();



		var mb_transfer = $('#mb_transfer').val();



		var mb_inst = $('#mb_inst').val();



		var mbfd_amount = $('#mbfd_amount').val();



		var balance = $('#balance').val();



		//var ssb_ac = $('#ssb_ac').val();



		//var eli_amount = $('#eli_amount').val();



		if(eli_amount==''||ac_deno==''||ac_opening_date==''||mb_date==''||mb_amount==''||mb_transfer==''||mb_inst==''||mbfd_amount==''||balance=='')



		{



			swal("Error!",  "Please Enter The Details.", "error");;



			return false;



		}



	})*/



	



	TransactionTable = $('#transaction_list').DataTable({



        processing: true,



        serverSide: true,



        pageLength: 20,



        lengthMenu: [10, 20, 40, 50, 100],



        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      



            var oSettings = this.fnSettings ();



            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);



            return nRow;



        },



        ajax: {



            "url": "{!! route('admin.transaction.list') !!}",



            "type": "POST",



            "data":function(d) {d.searchform=$('form#filter').serializeArray()}, 



            headers: {



                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')



            },



        },



        columns: [



            {data: 'DT_RowIndex', name: 'DT_RowIndex'},



            {data: 'transaction_id', name: 'transaction_id'},



            {data: 'date', name: 'date'},



            {data: 'description', name: 'description'},



            {data: 'ref_no', name: 'ref_no'},



            {data: 'withdrawal', name: 'withdrawal'},



            {data: 'deposite', name: 'deposite'},



			{data: 'balance', name: 'balance'},



        ],"ordering": false



    });



    $(TransactionTable.table().container()).removeClass( 'form-inline' );



	

	$('.submit').on('click',function(e){



		e.stopPropagation();



		var account = $('#account_no').val();



		var amount = $('#mb_amount').val();



		if(account == ''){



			$('#eli_amount').val('');



			$('#ac_deno').val('');



			$('#ac_opening_date').val('');



			$('#mb_date').val('');



			$('#mb_amount').val('');



			$('#mb_transfer').val('');



			$('#mb_inst').val('');



			$('#mbfd_amount').val('');



			$('#ssb_ac').val('');



			$('#balance').val('');	



			$('#deposite_amount').val('');	



			$('#total_amount').val('');	



			$(".customer-status").prop("checked", false);



			$('#transaction_table').hide();



			swal("Error!",  "Please Enter Account Number.", "error");;



			return false;



		}else{



		if(account.startsWith("R-")){



			$.ajax({



			type: "POST", 



            url: "{!! route('admin.getReinvestDetail') !!}",



            dataType: 'JSON',



            data: {'account_no':account},



            headers: {



                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')



            },



            success: function(response) {



				console.log(response);



				if(response.count == 1){



					$('#investmentId').val(response.record.id);



					$('#accountNumber').val(response.record.account_number);



					$('#eli_amount').val(response.eliAmount.deposit);



					$('#ac_deno').val(response.record.deposite_amount);



					if(response.record.ssb){

						$('#ssb_ac').val(response.record.ssb.account_no);



						$('#balance').val(response.record.ssb.balance);	

					}else{

						$('#ssb_ac').val('');



						$('#balance').val('');	

					}



					$('#transaction_table').show();



					TransactionTable.draw();



				}else{

					$('#eli_amount').val('');



					$('#ac_deno').val('');



					$('#ac_opening_date').val('');



					$('#mb_date').val('');



					$('#mb_amount').val('');



					$('#mb_transfer').val('');



					$('#mb_inst').val('');



					$('#mbfd_amount').val('');



					$('#ssb_ac').val('');



					$('#balance').val('');	



					$('#transaction_table').hide();



					$('#account_no').val('');



					if(response.count == 0){

						swal("Error!",  "Record not found!.", "warning");

					}else if(response.count == 2){

						swal("Error!",  "Eli Opening balance settled already!.", "warning");

					}else if(response.count == 3){

						swal("Error!",  "Maturity done for this plan!.", "warning");		

					}



					return false;

				}

            }

        });

		}



		else{



			$('#eli_amount').val('');



			$('#ac_deno').val('');



			$('#ac_opening_date').val('');



			$('#mb_date').val('');



			$('#mb_amount').val('');



			$('#mb_transfer').val('');



			$('#mb_inst').val('');



			$('#mbfd_amount').val('');



			$('#ssb_ac').val('');



			$('#balance').val('');	



			$('#transaction_table').hide();



			$('#account_no').val('');



			swal("Error!",  "Please Enter Valid Account Number.", "error");;



			return false;



		}}



		 return false;



	});



	$('#mb_inst').on('change',function(){



		var mb_inst = parseFloat($(this).val());



		var mb_transfer = parseFloat($('#mb_transfer').val());





		if(/*mb_inst > 0 && */mb_transfer > 0){

			if(mb_transfer){

				mb_transfer = mb_transfer;

			}else{

				mb_transfer = 0;

			}

			if(mb_inst){

				mb_inst = mb_inst;

			}else{

				mb_inst = 0;

			}



			var amnt = parseFloat(mb_transfer)+parseFloat(mb_inst);



			$('#mbfd_amount').val(amnt);



		}

	})



	Date.prototype.addDays = function (days) {

	    const date = new Date(this.valueOf());

	    date.setDate(date.getDate() + days);

	    return date;

	};



	$('#ac_opening_date').on('change',function(){

		let investmentId = $('#investmentId').val();



		if(investmentId > 0){

			let openingDate = $(this).val();

			let eliAmount = Number($('#eli_amount').val());

		   	let dateOneArray = openingDate.split("/");

			let formatted_date_one = dateOneArray[2] + "-" + dateOneArray[1] + "-" + dateOneArray[0];

			const date = new Date(formatted_date_one);

			let moneyBackDate = date.addDays(365);

			let dateString = moment(moneyBackDate).format('DD/MM/YYYY');

			$('#mb_date').val(dateString);



			$.ajax({

	            type: "POST",  

	            url: "{!! route('eli.getdepositamount') !!}",

	            dataType: 'JSON',

	            data: {'investmentId':investmentId,'openingDate':openingDate,'dateString':dateString},

	            headers: {

	                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

	            },

	            success: function(response) {

	            	let depositAmount = Number(response.depositAmount);
	           		$('#deposite_amount').val(depositAmount);
	           		$('#total_amount').val(eliAmount+depositAmount);
	           		$('#carryforwardamount').val(response.carryForwardAmount);
	           		//$('#fdamount').val(response.fdAmount);
	           		$('#totaldeposit').val(response.totalDeposit);
	           		$('.customer-status').trigger('click');

	            }

	        });

		}else{

			$(this).val('');

			swal("Warning!",  "Please Enter Account Number First.", "warning");;

	        return false;

		}

	})



	$('.customer-status').on('click',function(){

		let cusStatus = $(this).val();

		let totalAmount = Number($('#total_amount').val());

		if(cusStatus == 1){

			var mbAmout = Number(60*totalAmount/100);

			$('#mb_amount').val(mbAmout);

		}else{

			var mbAmout = 0;

			$('#mb_amount').val(mbAmout);

		}

		$('#mb_transfer').val(totalAmount-mbAmout);

		$('#mb_inst').trigger('change');

	})

	/*$('.customer-status').on('click',function(){
		let cusStatus = $(this).val();
		let totalAmount = Number($('#totaldeposit').val());
		let fdamount = Number($('#fdamount').val());
		if(cusStatus == 1){
			var mbAmout = Number(60*totalAmount/100);
			$('#mb_amount').val(mbAmout);
		}else{
			var mbAmout = 0;
			$('#mb_amount').val(mbAmout);
		}
		$('#mb_transfer').val(fdamount-mbAmout);
		$('#mb_inst').trigger('change');
	})*/

	$('.export').on('click',function(){







        var extension = $(this).attr('data-extension');



		 var account_no = $('#account_no').val();











        $('#export_value').val(extension);



		$('#export_account_no').val(account_no);







        $('form#export_form').attr('action',"{!! route('admin.export_e_invest_transaction') !!}");







        $('form#export_form').submit();







        return true;

    });

});



function resetForm()

{



	$('#account_no').val('');



	$('#eli_amount').val('');



	$('#ac_deno').val('');



	$('#ac_opening_date').val('');



	$('#mb_date').val('');



	$('#mb_amount').val('');



	$('#mb_transfer').val('');



	$('#mb_inst').val('');



	$('#mbfd_amount').val('');



	$('#ssb_ac').val('');



	$('#balance').val('');



	$('#transaction_table').hide();



	location.reload();

}



function CompareDate(startDate,endDate) {    

   //Note: 00 is month i.e. January    

   	let dateOne = new Date(startDate); //Year, Month, Date  

   	var dateTwo = new Date(endDate); //Year, Month, Date     



   	var dateOneArray = startDate.split("/");

   	var dateTwoArray = endDate.split("/");



	let formatted_date_one = dateOneArray[2] + "-" + dateOneArray[1] + "-" + dateOneArray[0];

	let formatted_date_two = dateTwoArray[2] + "-" + dateTwoArray[1] + "-" + dateTwoArray[0];

    

   

   if (formatted_date_one < formatted_date_two) {    

        return false; 

    }else {    

        return true;   

    }    

}

</script>







