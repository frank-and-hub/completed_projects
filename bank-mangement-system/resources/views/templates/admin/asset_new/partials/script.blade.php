<script type="text/javascript">

	$(document).ready(function(){



		$.validator.addMethod("decimal", function(value, element,p) {     

       if(this.optional(element) || /^\d*\.?\d*$/g.test(value)==true)

      {

        $.validator.messages.decimal = "";

        result = true;

      }else{

        $.validator.messages.decimal = "Please Enter valid numeric number.";

        result = false;  

      }

    

    	return result;

  	}, "");



		$.validator.addMethod("zero", function(value, element,p) {     

      if(value>=0 )

      {

        $.validator.messages.zero = "";

        result = true;

      }else{

        $.validator.messages.zero = "Amount must be greater than or equal to 0.";

        result = false;  

      }

    

    	return result;

  	}, "");
$.validator.addMethod("zero1", function(value, element,p) { 

      if(value>0 )
      {
        $.validator.messages.zero1 = "";
        result = true;
      }else{
        $.validator.messages.zero1 = "Amount must be greater than or equal to 0.";
        result = false; 
      }    	return result;

  	}, "");
$.validator.addMethod("per_100", function(value, element,p) { 

      if(value<=100 )
      {
        $.validator.messages.per_100 = "";
        result = true;
      }else{
        $.validator.messages.per_100 = "Depreciation % must be less than or equal to 100.";
        result = false; 
      }    	return result;

  	}, "")



		



		$('#edit_asset').validate({

			rules:{

				new_status:{

					required:true,

				},

				branch_name:{

					required:true,

				},

				account_head_name:{

					required:true,

				},

				sub_account_head_name:{

					required:true,

				},

				depreciation_percentage:{
					required:true,
					decimal:true,
					zero1:true,
					per_100:true,
				},

				demand_date:{
					required:true,
				},

				advice_date:{
					required:true,
				},
				amount:{
					required:true,
					decimal: true,
				},
				party_name:{
					required:true,
				},

				mobile_no:{
					required:true,
				},

				// bill_no:{
				// 	required:true,
				// },

				bill_copy:{

					required:true,

				},

				old_status:{

					required:true,

				},

				new_status:{

					required:true,

					 decimal: true,

            		zero: true,

				},

				

				asset_name:{

					required:true,

	            	

				},

				asset_category:{

					required:true,

	            	

				},

				total_asset:{

					required:true,

	            	

				},

				current_asset_value:{

					required:true,

	            	

				},

				after_depreciation_asset_value:{

					required:true,
	            	decimal: true,
            		zero1:true,

				},
				remark :{
				    required:true
				}

			},

			 messages: {  

	          branch_name: "Please amount mode.",

	          account_head_name:{

	            required: "Please Enter Account Head.",

	             },

	            sub_account_head_name:{

	            required: "Please Enter Sub Account Head Name.",

	             },

	          demand_date: "Please Enter Demand Date.", 

	          advice_date: "Please Enter Advice Date.", 

	          amount:{

	          	required:"Please select account.", 

	          	

	          } ,

	          party_name: "Please Enter Party Name.", 

	          mobile_no: {

	          	required:"Please Enter Mobile Number."

	      		},   

	          bill_no: {

	            required: "Please Enter Bill Number.",

	           

	          },

	          bill_copy: {

	            required: "Please Enter Bill Copy.",

	          }, 

	          old_status: {

	            required: "Please Enter Old Status.",

	          }, 

	          new_status: {

	            required: "Please Select Status.",

	          },

	          depreciation_percentage:{

	          	required: "Please Enter Depreciation Percentage.",

	          	

	          },

	            asset_name: {

	            required: "Please Enter Asset Name.",

	           

	          },

	          asset_category: {

	            required: "Please Enter Asset Category.",

	          }, 

	          total_asset: {

	            required: "Please Enter Total Asset.",

	          }, 

	          current_asset_value: {

	            required: "Please Enter Current Asset Value.",

	          },

	          after_depreciation_asset_value:{

	          	required: "Please Enter After Depreciation Value.",

	          },
	          remark:{
	              required: "Please Enter Remark"
	          },

      		},

      		// 

			

		})




		$('#depreciation_percentage').on('keyup',function(){

			var percentage = $(this).val();

			var current_asset_value = $('#current_asset_value').val();

			if(percentage>0)

			{

				var asset_value = current_asset_value * (parseFloat(percentage).toFixed(2)/100) ;

				var amount  = current_asset_value - asset_value;

				$('#after_depreciation_asset_value').val(parseFloat(amount).toFixed(2));

			}
			else
			{
				$('#after_depreciation_asset_value').val('');
			}

		})



		$( document ).ajaxStart(function() { 

          $( ".loader" ).show();

       });



       $( document ).ajaxComplete(function() {

          $( ".loader" ).hide();

       });

	})

</script>