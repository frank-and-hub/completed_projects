<script type="text/javascript">
    var shareListing;
    $(document).ready(function(){
      $('.col-md-4').addClass('col-md-6').removeClass('col-md-4');
      $('.col-form-label.col-lg-12').addClass('col-lg-4').removeClass('col-lg-12');
      $('.col-lg-12.error-msg').addClass('col-lg-8').removeClass('col-lg-12');
    // on change company director will show according to company
      $('#company_id').on('change', function(){
        $('#date').datepicker('destroy');
        var company = $(this).val();
        $('#father_name').val('');
        $('#member_id').val('');
        $('#address').val('');
        $('#pan_no').val('');
        $('#aadhar_no').val('');
        $('#rgister_date').val('');
        $('#amount').val('');
        $('#date').val('');
        $('#payment_type').val('');
        $('.cash_mode').hide();
        $('.bank_mode').hide();
        $('#transaction_mode').hide();
        $.ajax({
          url: "{{route('admin.director.company')}}",
          type: "POST",
          data:{"company":company},
          headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                  },
          success: function(response){
          
            $('#head_type').find('option').remove();
            $('#head_type').append('<option value="">--Please Select Director--</option>');
            $.each(response, function (index, value) { 
                        $("#head_type").append("<option value='"+value.id+"'>"+value.name+"</option>");
                    }); 
          }

        })
      });
      $('#date').on('change',function(){
                
                $('#bank_account').val('');
                $('#bank_available_balance').val('0.00');
                $('#branch_total_balance').val('0.00');
                $('#ssbbalance').val('0.00');
                $('#branch').val('');
                // $( "#payment_type" ).trigger( "change" );
                // $( "#father_name" ).trigger( "keypress" );
                var entrydate=$('#date').val();
                var type_id=$('#head_type').val();
                $('#amount').val('0.00');
                if(type_id>0)
                {

                
                            if(entrydate == '')
                            {
                              
                                swal("Warning!", "Please select  payment date", "warning");
                                $('#amount').val('0.00');           
                            }
                            else
                            {
                                
                                  $.ajax({
                                    type: "POST",  
                                    url: "{!! route('admin.directorBalanceDate') !!}",
                                    dataType: 'JSON',
                                    data: {'id':type_id,'entrydate':entrydate},
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    success: function(response) { 
                                    // alert(response.balance);
                                      $('#amount').val(response.balance);  
                                    }
                                });
                            }
              }
        });

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

      if(this.optional(element) || /^\d*\.?\d*$/g.test(value)==true)

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

         $('#head_type').on('change',function(){

          var type_id = $(this).val();
            // alert('asda');
              $('#date').datepicker('destroy');
              $('#date').val('');
              $('#father_name').val('');
              $('#member_id').val('');
              $('#address').val('');
              $('#pan_no').val('');
              $('#aadhar_no').val('');
              $('#rgister_date').val('');
              $('#amount').val('');
              $('#ssb_account_number').val('');
              $('#ssbid').val('');
              $('#ssb_account_holder_name').val('');


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
                    console.log(response);
                    var date=$('#create_application_date').val();
                    var sdate = response.rgister_date;
                    $('#date').datepicker({
                        format:"dd/mm/yyyy",
                          endHighlight: true, 
                          autoclose:true, 
                          endDate:date, 
                          startDate:sdate,

                        });
                    
                    $('#father_name').val(response?.shareholder?.father_name);
                    if(response.member)
                    {
                      $('#member_id').val(response?.member?.member_id);
                      $('#ssb_account_holder_name').val(response?.member.first_name+' '+response?.member?.last_name);
                    }
                    
                    $('#address').val(response?.shareholder?.address);
                    $('#pan_no').val(response?.shareholder?.pan_card);
                    $('#aadhar_no').val(response?.shareholder?.aadhar_card);
                    $('#rgister_date').val(response.rgister_date);
                    $('#company_id').val(response?.shareholder?.company_id);
                    $('#company').val(response?.shareholder?.company.name);
                    $('#ssb_account_number').val(response?.ssb?.account_no); 
                    // $('#amount').val(response?.shareholder?.current_balance); 
                      
                      

                        if(response.ssb)
                      {
                        $('#ssbid').val(response?.ssb?.id);  
                        let ssb_date = response.ssb_created_date;
                        $('#date').datepicker('startDate',ssb_date);
                      }

                      var entrydate=$('#date').val(); 
                     
                            if($('#payment_type').val()==2)
                            {
                                var ssbid=response?.ssb?.id; 
                                
                                $('#ssbbalance').val('0.00');
                                if(entrydate == '')
                                {
                                  
                                    swal("Warning!", "Please select  payment date", "warning");
                                    $('#ssbbalance').val('0.00');   
                                    $('#payment_type').val('');  
                                    $('.cash_mode').hide();
                                          $('.bank_mode').hide();
                                          $('#transaction_mode').hide();
                                          $('.ssb_mode').hide();         
                                }
                                else
                                {

                                    if(ssbid>0)
                                    {

                                        $.ajax({
                                          type: "POST",  
                                          url: "{!! route('admin.ssbDateBalanceChk') !!}",
                                          dataType: 'JSON',
                                          data: {'ssbid':ssbid,'entrydate':entrydate},
                                          headers: {
                                              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                          },
                                          success: function(response) { 
                                          // alert(response.balance);
                                            $('#ssbbalance').val(response?.balance);  
                                          }
                                      });
                                    }

                                    


                                  }
                                }


                  }

      })

        })



         $('#daybook').on('change',function(){ 

            var daybook=$('#daybook').val();

            var branch_id=$('#branch').val();

            var entrydate=$('#date').val();

            var company_id = $('#company_id').val();

            $('#branch_total_balance').val('0.00');
            if(entrydate == '')
            {
                swal("Warning!", "Please select  payment date", "warning");
                $('#branch_total_balance').val('0.00');            
            }
            else
            {

                if(branch_id>0 && daybook!='')

                {

                    $.ajax({
                      type: "POST", 
                      url: "{!! route('admin.branchBankBalanceAmount') !!}",
                      dataType: 'JSON',
                      data: {'branch_id':branch_id,'entrydate':entrydate,'company_id':company_id},
                      headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                      },
                      success: function(response) { 
                        // alert(response.balance);
                        $('#branch_total_balance').val(response.balance);  
                      }
                    });
                }
              }

          })

 





         $('#branch').on('change',function(){
            $( "#daybook" ).val(0);
            var branch_code = $('option:selected', this).attr('data-value');
           

            $('#branch_code').val(branch_code);

            $( "#daybook" ).trigger( "change" );

         } )



         $('#payment_type').on('change',function(){

          company = $('#company_id').val();
            var director = $('#head_type').val();
              if(director == ''){
                swal("Warning!", "Please select Director First", "warning");
                return false;
              }
              $('#branch').val('');
              $('#branch_code').val('');
              $('#bank').val('');  
              $('#payment_mode').val('');
              $( "#payment_mode" ).trigger( "change" );
              $('#branch_total_balance').val(''); 

              $('#daybook').val(0);

              $('#cheque_no').val();

              $('#online_bank').val('');

              $('#online_bank_ac').val('');

              $('#utr_date').val('');

              $('#utr_no').val('');

              $('#transaction_bank').val('');

              $('#transaction_bank_ac').val(''); 
              $('.cheque_mode').hide();
              $('.cash_mode').hide();
              $('.bank_mode').hide();
              $('#transaction_mode').hide();
              $('.ssb_mode').hide();


            var mode = $(this).val();


          if(mode == '')            
          {

              $('.cash_mode').hide();
              $('.bank_mode').hide();
               $('#transaction_mode').hide();
               $('.ssb_mode').hide();
}
            if(mode ==0 && mode!='')

            {

              $('.cash_mode').show();
              $('.bank_mode').hide();
              $('#transaction_mode').hide();
              $('.ssb_mode').hide();
              $('#date').val('');
              if(company == 1){
                    var date = '05/08/2021';
                    $('#date').datepicker('setStartDate',date);
              }
              var company_id = $('#company_id').val();
              $.ajax({
                  url: "{!! route('admin.getBranches') !!}",
                  type:"GET",
                  dataType: 'JSON',
                  data: {'company_id':company_id},
                  headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                success: function(response) { 
                  
                  $('#branch').find('option').remove();
                  $('#branch').append('<option value="">Select Branch </option>');
                  $.each(response.branch, function (index, value) { 
                          $("#branch").append("<option value='"+value.branch.id+"' data-value = '"+value.branch.branch_code+"'>"+value.branch.name+"</option>");
                      }); 
                }
            });

            }

            if(mode == 1 && mode!='')

            {
              var cdate = $('#rgister_date').val();
              $('#date').datepicker('setStartDate',cdate);
              $('.bank_mode').show();

              $('#transaction_mode').show();

               $('.cash_mode').hide();

               $('.ssb_mode').hide();
               var company_id = $('#company_id').val();
               $.ajax({
                  url: "{!! route('admin.banks_list') !!}",
                  type:"POST",
                  dataType: 'JSON',
                  data: {'company_id':company_id},
                  headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                success: function(response) { 
                  // console.log(response);
                  $('#bank').find('option').remove();
                  $('#bank').append('<option value="">Select bank account </option>');
                  $.each(response.banks, function (index, value) { 
                          $("#bank").append("<option value='"+value.id+"'>"+value.bank_name+"</option>");
                      }); 
                }
            });

            }

            if(mode == 2 && mode!='')

            {

              $('.cash_mode').hide();

              $('.ssb_mode').show();

              $('.bank_mode').hide();

              $('.utr_mode').hide();

              $('.cheque_mode').hide();

              $('#transaction_mode').hide();
              var cdate = $('#rgister_date').val();
              $('#date').datepicker('setStartDate',cdate);
              var ssbid=$('#ssbid').val(); 
              var entrydate=$('#date').val();
              $('#ssbbalance').val('0.00');
              if(entrydate == '')
              {
                
                  swal("Warning!", "Please select  payment date", "warning");
                  $('#ssbbalance').val('0.00');   
                  $('#payment_type').val('');  
                  $('.cash_mode').hide();
                        $('.bank_mode').hide();
                        $('#transaction_mode').hide();
                        $('.ssb_mode').hide();         
    }
    else
    {

        if(ssbid>0)
        {

            $.ajax({
              type: "POST",  
              url: "{!! route('admin.ssbDateBalanceChk') !!}",
              dataType: 'JSON',
              data: {'ssbid':ssbid,'entrydate':entrydate},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
               // alert(response.balance);
                $('#ssbbalance').val(response.balance);  
              }
          });
        }
      }

             

              

            }

         })



         $('#payment_mode').on('change',function(){


            var mode = $(this).val();

            var withdrawal_amount = $(withdrawal_amount).val();



            if(mode == 0)

            {

             	$('.cash_mode').hide();

             	$('.cheque_mode').show();

            	$('.payment_mode_online').hide();

            	$('.utr_mode').hide();

              $('#pay_amount').val(withdrawal_amount);

              $('.ssb_mode').hide();
              

            }

            if(mode == 1)

            {

               

              $('.cash_mode').hide();

              $('.cheque_mode').hide();

               $('.ssb_mode').hide();

              $('#pay_amount').val(withdrawal_amount);

              $('.utr_mode').show();

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

$.validator.addMethod("chkAmount", function(value, element,p) {
       
      if(parseFloat($('#amount').val()) >= parseFloat($('#withdrawal_amount').val()))
      {
        $.validator.messages.chkAmount = "";
        result = true;
      }else{
        $.validator.messages.chkAmount = "Amount must be grather than or equal to  withdrawal amount";
        result = false;  
      }
    
    return result;
  }, "");

$.validator.addMethod("chkbank", function(value, element,p) {
      if($( "#payment_type" ).val()==1)
      {  
      if(parseFloat($('#bank_available_balance').val()) >= parseFloat($('#withdrawal_amount').val()))
      {
        $.validator.messages.chkbank = "";
        result = true;
      }else{
        $.validator.messages.chkbank = "Bank available balance must be grather than or equal to  withdrawal amount";
        result = false;  
      }
    }
    else
    {
      $.validator.messages.chkbank = "";
        result = true;
    } 
    return result;
  }, "");



$.validator.addMethod("ssbbalancechk", function(value, element,p) {
    if($( "#payment_type" ).val()==2)
      {  
      if(parseFloat($('#ssbbalance').val()) >= parseFloat($('#withdrawal_amount').val()))
      {
        $.validator.messages.ssbbalancechk = "";
        result = true;
      }else{
        $.validator.messages.ssbbalancechk = "SSB account balance must be grather than or equal to  withdrawal amount";
        result = false;  
      }
    }
    else
    {
      $.validator.messages.ssbbalancechk = "";
        result = true;
    } 
    return result;
  }, "");



$.validator.addMethod("chkBranch", function(value, element,p) {
    if($( "#payment_type" ).val()==0)
    {  
      if(parseFloat($('#branch_total_balance').val()) >= parseFloat($('#withdrawal_amount').val()))
      {
        $.validator.messages.chkBranch = "";
        result = true;
      }else{
        $.validator.messages.chkBranch = "Branch total balance must be grather than or equal to  withdrawal amount";
        result = false;  
      }
    }
    else
    {
      $.validator.messages.chkBranch = "";
        result = true;
    } 
    return result;
  }, "");
$.validator.addMethod("neft", function(value, element,p) {
    if($( "#payment_type" ).val()==1 && $( "#payment_mode" ).val()==2)
      {  
        a= parseFloat($('#neft_charge').val())+parseFloat($('#withdrawal_amount').val());
      if(parseFloat($('#bank_available_balance').val()) >= parseFloat(a))
      {
        $.validator.messages.neft = "2";
        result = true;
      }else{
        $.validator.messages.neft = "Bank available balance must be grather than or equal to  sum of withdrawal amount or NEFT charge";
        result = false;  
      }
    }
    else
    {
      $.validator.messages.neft = "2";
        result = true;
    } 
    return result;
  }, "");

$.validator.addMethod("maxpDate", function(value, element) {

  moment.defaultFormat = "DD/MM/YYYY HH:mm";

     var f1 = moment($('#rgister_date').val()+' 00:00', moment.defaultFormat).toDate();

     var f2 = moment(value+' 00:00', moment.defaultFormat).toDate();



      var from = new Date(Date.parse(f1));

      var to = new Date(Date.parse(f2)); 
          if (f2 >= f1)
              return true;
          return false;

}, "Payment date must be grather than  creation date");


         $('#shareholder_form').validate({

            rules:{
              company:{
                required:true
              },
              date:
              {
                required:true,  
                maxpDate:true,
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
                required:true, 
                checkAadhar:true, 
              },
              father_name:
              {
                required:true, 
              },
              amount:{
                required:true,
                 decimal:true,
               },
               withdrawal_amount:{
                required:true,
                decimal:true,
                // zero1:true,
                chkAmount:true,
              },
              payment_type:{
                  required:true,
                },
              
          payment_mode:{ 
            required:true,
          },     
          daybook: {
            required: function(element) {
              if (($( "#payment_type" ).val()==0)) {
                return true;
              } else {
                return false;
              }
            },
          },
          branch: {
            required: function(element) {
              if (($( "#payment_type" ).val()==0)) {
                return true;
              } else {
                return false;
              }
            },
          },
       
          branch_total_balance: {
            required: function(element) {
              if (($( "#payment_type" ).val()==0)) {
                return true;
              } else {
                return false;
              }
            },
            chkBranch: function(element) {
              if (($( "#payment_type" ).val()==0)) {
                return true;
              } else {
                return false;
              }
            },
          },

          bank_available_balance:{
            chkbank: function(element) {
              if (($( "#payment_type" ).val()==1)) {
                return true;
              } else {
                return false;
              }
            },
            neft: function(element) {
              if (($( "#payment_mode" ).val()==2)) {
                return true;
              } else {
                return false;
              }
            },
          },
          cheque_number:{
            required: function(element) {
              if (($( "#payment_mode" ).val()==1)) {
                return true;
              } else {
                return false;
              }
            },
          },
          utr_number:{
            required: function(element) {
              if (($( "#payment_mode" ).val()==2)) {
                return true;
              } else {
                return false;
              }
            },
            
          },
          neft_charge:{
            required: function(element) {
              if (($( "#payment_mode" ).val()==2)) {
                return true;
              } else {
                return false;
              }
            },
            decimal:true,
            zero1:true,
          },
          ssb_account_number:{
            required: function(element) {
              if (($( "#payment_type" ).val()==2)) {
                return true;
              } else {
                return false;
              }
            },},
            member_id:{
            required: function(element) {
              if (($( "#payment_type" ).val()==2)) {
                return true;
              } else {
                return false;
              }
            },},
            ssb_account_holder_name:{
            required: function(element) {
              if (($( "#payment_type" ).val()==2)) {
                return true;
              } else {
                return false;
              }
            },},
            ssbbalance: {
            required: function(element) {
              if (($( "#payment_type" ).val()==2)) {
                return true;
              } else {
                return false;
              }
            },
            ssbbalancechk: function(element) {
              if (($( "#payment_type" ).val()==2)) {
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
              ssb_account_number:{
                "required":"Please  enter ssb account no.",
              },
              member_id:{
                "required":"Please  enter member id.",
              },
              ssb_account_holder_name:{
                "required":"Please enter account holder name.",
              },
              name:{
                "required":"Please select director.",
              },
              date:{
                "required":"Please select date.",
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

              contact_no:

              {

                "required":"Please enter contact number.",

              },

              bank_name:{

                  "required":"Please enter bank name.",

              },

               father_name:{

                  "required":"Please enter father name.",

              },

               branch_name:{

                  "required":"Please enter branch name.",

              },

              account_number:{

                "required":"Please enter account number.",

              },

               ifsc_code:{

                "required":"Please enter ifsc code.",

              },

              member_id:{

                "required":"Please enter member id.",

              },

              ssb_account:{

                "required":"Please enter ssb account number.",

              },

              remark:{

                "required":"Please enter remark.",

              },

              amount:{

                "required":"Please enter amount."

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

              withdrawal_amount:{

                "required":"Please enter withdrawal amount."

              },

               daybook: {
                "required": "Please select daybook",
              },
              branch_total_balance: {
                "required":  "Please enter branch balance",
              },
              branch_code: {
                "required":  "Please enter branch code",
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

        

        

        $('#bank_account').on('change',function(){ 
            var bank_id=$('#bank').val();
            var account_id=$('#bank_account').val();
            var entrydate=$('#date').val();
            $('#bank_balance').val('0.00');

            $('#cheque_number').val('');
            if(entrydate == '')
            {
                swal("Warning!", "Please select  payment date", "warning");
                $('#bank_account').val('');  
                $('#bank_balance').val('0.00');          
            }
            else
            {
                    $.ajax({
                            type: "POST",  
                            url: "{!! route('admin.bankChkbalance') !!}",
                            dataType: 'JSON',
                            data: {'account_id':account_id,'bank_id':bank_id,'entrydate':entrydate},
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) { 
                            // alert(response.balance);
                              $('#bank_available_balance').val(response.balance);
                            }
                        });

                    $.ajax({

                      type: "POST",  

                      url: "{!! route('admin.bank_cheque_list') !!}",

                      dataType: 'JSON',

                      data: {'account_id':account_id},

                      headers: {

                          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                      },

                      success: function(response) { 

                        //alert(response);

                        $('#cheque_number').find('option').remove();

                        $('#cheque_number').append('<option value="">Select cheque number</option>');

                        $.each(response.chequeListAcc, function (index, value) { 

                                $("#cheque_number").append("<option value='"+value.id+"'>"+value.cheque_no+"</option>");



                            }); 



                      }

          });
      }

    })        



        $( document ).ajaxStart(function() {

        $( ".loader" ).show();

    	});



	    $( document ).ajaxComplete(function() {

	        $( ".loader" ).hide();

	    });

   $( "#shareholder_form" ).submit(function( event ) {
      if($('#shareholder_form').valid())
      {
        $('input[type="submit"]').prop('disabled',true);
      }
    })

})

 





</script>