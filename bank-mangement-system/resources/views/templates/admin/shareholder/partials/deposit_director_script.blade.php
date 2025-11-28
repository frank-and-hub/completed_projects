<script type="text/javascript">

    $(document).ready(function(){

        $.validator.addMethod("checkPenCard", function(value, element,p) {    

      if(this.optional(element) || /([A-Z]){5}([0-9]){4}([A-Z]){1}$/.test(value)==true)

      {

        result = true;

      }else{

        $.validator.messages.checkPenCard = "Please enter valid pan card no.";

        result = false;  
      }
        return result;

         }, "");

        $.validator.addMethod("checkAadhar", function(value, element,p) {    

      if(this.optional(element) || /^(\d{12}|\d{16})$/.test(value)==true)

      {

        result = true;

      }else{

        $.validator.messages.checkAadhar = "Please enter valid aadhar card  number.";

        result = false;  

      }

    return result;

  }, "");

        $.validator.addMethod("zero1", function(value, element,p) {     

      if(value>=0)

      {

        $.validator.messages.zero1 = "";

        result = true;

      }else{

        $.validator.messages.zero1 = "Amount must be greater than 0.";

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

        $.validator.messages.decimal = "Please enter valid numeric number.";

        result = false;  

      }

    return result;

  }, "");

        

        // Validate Form 

         $('#shareholder_form').validate({

            rules:{
              company:{
                required:true
              },
                name:

                {

                    required:true,  

                },

                address:

                {

                    required:true,  

                },

                pan_no:

                {

                    required:true,

                    checkPenCard :true, 

                },

                aadhar_no:

                {

                    checkAadhar:true,

                    required:true,  

                },

               
                father_name:
                {
                    required:true,  
                },

                amount:{

                  required:true,

                  decimal:true,

                  zero1:true,

                },

                deposit_amount:{

                  required:true,

                  decimal:true,

                  zero1:true,

                },

                payment_type:{

                  required:true,

                },

                branch:{

                  required:true,

                },

                bank:{

                  required:true,

                },

                bank_account:{

                  required:true,

                },

                payment_mode:{

                  required:true,

                },

                cheque_number:{

                  required:true,

                },

                utr_number:{

                  required:true,

                },
                branch_code:{
                  required:true,
                },
                daybook:{
                  required:true,
                },
                branch_total_balance:{
                  required:true,
                },
                online_bank: {

            required: function(element) {

              if (($( "#payment_mode" ).val()==1)) {

                return true;

              } else {

                return false;

              }

            },

          },

          online_bank_ac: {

            required: function(element) {

              if (($( "#payment_mode" ).val()==1)) {

                return true;

              } else {

                return false;

              }

            },

          },

          utr_date: {

            required: function(element) {

              if (($( "#payment_mode" ).val()==1)) {

                return true;

              } else {

                return false;

              }

            },

          },

          utr_no: {

            required: function(element) {

              if (($( "#payment_mode" ).val()==1)) {

                return true;

              } else {

                return false;

              }

            },

            

          },

          transaction_bank: {

            required: function(element) {

              if (($( "#payment_mode" ).val()==1)) {

                return true;

              } else {

                return false;

              }

            },

          },

          transaction_bank_ac: {

            required: function(element) {

              if (($( "#payment_mode" ).val()==1)) {

                return true;

              } else {

                return false;

              }

            },

            number:true,

          },

          cheque_no: {

            required: function(element) {

              if (($( "#payment_mode" ).val()==0)) {

                return true;

              } else {

                return false;

              }

            },

          },

            },

            messages:{
              company:{
                "required":"Enter Company Name",
              },
              
              name:{

                "required":"Please enter name.",

              },

              address:{

                "required":"Please enter address.",

              },

              pan_no:{

                "required":"Please enter pan number.",

              },

              aadhar_no:

              {

                "required":"Please enter aadhar number.",

              },

             
           
               father_name:{

                  "required":"Please enter father name.",

              },

              account_number:{

                "required":"Please enter account number.",

              },


              amount:{

                "required":"Please enter amount."

              },

              deposit_amount:{

                "required":"Please enter deposit amount."

              },

              payment_type:{

                "required": "Please select payment type "

              },

              branch:{

                "required":"Please select branch."

              },

              bank:{

                "required":"Please select bank."

              },

              bank_account:{

                "required":"Please select bank account."

              },

              payment_mode:{

                "required":"Please select payment mode."

              },

              cheque_number:{

                "required":"Please enter cheque number."

              },

               utr_number:{

                "required":"Please enter utr number."

              },
               daybook: {

            required: "Please select daybook",

          },

          branch_total_balance: {

            required:  "Please enter branch balance",

          },

          online_bank: {

            required: "Please select bank",

          },

          online_bank_ac: {

            required: "Please select bank account",

          },

          utr_date: {

            required:  "Please select date",

          },

          utr_no: {

            required: "Please enter utr/ transaction no.",

          },

          transaction_bank: {

            required: "Please enter transaction bank name",

          },

          transaction_bank_ac: {

            required: "Please enter transaction bank account no.",

          },

          cheque_no: {

            required: "Please select cheque no.",

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

              },

        })

       

         $('#head_type').on('change',function(){
          
        var type_id = $(this).val();

        $('#father_name').val('');
            $('#member_id').val('');
            $('#address').val('');
            $('#pan_no').val('');
            $('#aadhar_no').val('');
            $('#amount').val('');


       if(type_id>0)
       {

      $.ajax({

          type:"POST",

          url:"{!! route('admin.get_share_holder_detail') !!}",

          data:{id:type_id,},

          dataType:"JSON",

           headers: {

                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

              },

          success:function(response)

          { 

            $('#father_name').val(response.shareholder.father_name);

            $('#member_id').val(response.shareholder.member_id);

            $('#address').val(response.shareholder.address);

            $('#pan_no').val(response.shareholder.pan_card);

            $('#aadhar_no').val(response.shareholder.aadhar_card);

            if(response.shareholder.current_balance > 0)

            {

              $('#amount').val(parseFloat(response.shareholder.current_balance).toFixed(2));

            }

            else{

              $('#amount').val(parseFloat(0).toFixed(2));

            }

            

          }

      })
    }

        })



      $('#daybook').on('change',function(){ 

        var daybook=$('#daybook').val();

        var branch_id=$('#branch').val();

        var entrydate=$('#created_at').val();



    $('#branch_total_balance').val('0.00');

        if(branch_id>0 && daybook!='')

        {

            $.ajax({

              type: "POST",  

              url: "{!! route('admin.branchChkbalance') !!}",

              dataType: 'JSON',

              data: {'branch_id':branch_id,'daybook':daybook,'entrydate':entrydate},

              headers: {

                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

              },

              success: function(response) { 

               // alert(response.balance);

                $('#branch_total_balance').val(response.balance);

                



              }

          });

        }



      



    })

 $(document).on('change','#cheque_no',function(){

    $('.cheque').hide();

    $('#rd_cheque_no').val('');

                 $('#rd_branch_name').val('');

                 $('#rd_bank_name').val('');

                 $('#rd_cheque_date').val(''); 

                 $('#cheque-amt').val('');

    var cheque_no=$('#cheque_no').val();



          $.ajax({

              type: "POST",  

              url: "{!! route('admin.approve_cheque_details') !!}",

              dataType: 'JSON',

              data: {'cheque_id':cheque_no},

              headers: {

                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

              },

              success: function(response) { 

                



                 $('#cheque_number').val(response.cheque_no);

                 $('#cheque_party_bank').val(response.bank_name);

                // $('#rd_branch_name').val(response.branch_name);

                 $('#cheque_deposit_date').val(response.cheque_deposite_date);

                 $('#cheque_amount').val(parseFloat(response.amount).toFixed(2));

                $('#cheque_deposit_bank').val(response.deposit_bank_name);

                 $('#cheque_deposit_bank_ac').val(response.deposite_bank_acc);

                 $('#cheque_party_name').val(response.user_name);

                 $('#cheque_party_bank_ac').val(response.bank_ac);

                 $('.cheque').show();



              }

          });



  });



   $('#online_bank').on('change',function(){ 

        var bank_id=$(this).val();

        $.ajax({

            url: "{!! route('admin.bank_account_list') !!}",

            type:"POST",

            dataType: 'JSON',

            data: {'bank_id':bank_id},

            headers: {

                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

              },

          success: function(response) { 

            $('#online_bank_ac').find('option').remove();

            $('#online_bank_ac').append('<option value="">Select account number</option>');

             $.each(response.account, function (index, value) { 

                    $("#online_bank_ac").append("<option value='"+value.id+"'>"+value.account_no+"</option>");

                }); 



          }

       })

   })





         $('#branch').on('change',function(){
          $( "#daybook" ).val('');
            var branch_code = $('option:selected', this).attr('data-value');

            $('#branch_code').val(branch_code);

            $( "#daybook" ).trigger( "change" );

         } )



         // $('#withdrawal_amount').on('change',function(){

         //    var withdrawal_amount = $(this).val();

         //    $('#pay_amount').val(withdrawal_amount);

         //     $('#transfer_amount').val(withdrawal_amount);

            

         // })

         $('#payment_type').on('change',function(){
          
            var mode = $(this).val();

              $('.cash_mode').hide();
              $('.bank_mode').hide();
               $('#transaction_mode').hide();

            if(mode == 0 && mode!='')
            {

              $('.cash_mode').show();
              $('.bank_mode').hide();
               $('#transaction_mode').hide();

            }
            if(mode == 1 && mode!='')

            {

              $('.bank_mode').show();
              $('#transaction_mode').show();
               $('.cash_mode').hide();
            }

         })



         $('#payment_mode').on('change',function(){

          $('.payment_mode_cheque').hide();

              $('.cash_mode').hide();

              $('.cheque').hide();

              $('.payment_mode_online').hide();


            var mode = $(this).val();

            var withdrawal_amount = $(withdrawal_amount).val();



            if(mode == 0 && mode!='')

            {
           
            
             $('.cash_mode').hide();

             $('.payment_mode_cheque').show();

            $('.payment_mode_online').hide();

                  $('#pay_amount').val(withdrawal_amount);

                   $.ajax({

                          type: "POST",  

                          url: "{!! route('admin.approve_recived_cheque_lists') !!}",

                          dataType: 'JSON', 

                          data: {'amount':amount , 'name':name},

    
                          headers: {

                              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                          },

                          success: function(response) { 

                            $('#cheque_no').find('option').remove();

                            $('#cheque_no').append('<option value="">Select cheque number</option>');

                             $.each(response.cheque, function (index, value) { 

                                    $("#cheque_no").append("<option value='"+value.id+"'>"+value.cheque_no+"  ( "+parseFloat(value.amount).toFixed(2)+")</option>");

                                }); 



                          }

                      });



            }

            if(mode == 1 && mode!='')

            {

               $('#utr_date').datepicker({

                    format: "dd/mm/yyyy",

                    endDateHighlight: true,  

                    endDate: $('.create_application_date').val(), 

                    autoclose: true

                    startDate: '01/04/2021',


                  });

               $('.payment_mode_cheque').hide();

              $('.cash_mode').hide();

              $('.cheque').hide();

              $('.payment_mode_online').show();

              $('#pay_amount').val(withdrawal_amount);

            }

         })

         $('#bank').on('change',function(selected_account){

            var bank_id=$(this).val();



          $.ajax({

              type: "POST",  

              url: "{!! route('admin.bank_account_list') !!}",

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

    

        $( document ).ajaxStart(function() {

        $( ".loader" ).show();

    });



    $( document ).ajaxComplete(function() {

        $( ".loader" ).hide();

    });

   

    })

    

  

</script>