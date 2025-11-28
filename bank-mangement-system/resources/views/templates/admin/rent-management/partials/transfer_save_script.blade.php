<script type="text/javascript">
window.addEventListener('pageshow', function(event) {
    if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
        window.location.reload();
    }
});

$(document).ready(function () {

  
$("#select_date").hover(function(){
      var date=$('#create_application_date').val();
  //var ntoday = new Date(date.getFullYear(), date.getMonth(), date.getDate());
var date1 = $('#ledger_date').val();
      $('#select_date').datepicker({
          format:"dd/mm/yyyy",
            endHighlight: true, 
            autoclose:true,
            startDate:date1,
            endDate:date, 
          }).on('change', function(){

            $('#bank_id').val('');
            $('#account_id').val('');
            $('#bank_balance').val('0.00');
            $( "#bank_id" ).trigger( "change" );
      });
   })



$.validator.addMethod("maxpDate", function(value, element) {

  moment.defaultFormat = "DD/MM/YYYY HH:mm";

     var f1 = moment($('#ledger_date').val()+' 00:00', moment.defaultFormat).toDate();

     var f2 = moment(value+' 00:00', moment.defaultFormat).toDate();



      var from = new Date(Date.parse(f1));

      var to = new Date(Date.parse(f2));



          var sDate = $('#ledger_date').val();
          var curDate = moment(sDate).format('DD/MM/YYYY'); 
          if (f2 >= f1)
              return true;
          return false;

}, "Payment date must be grather than Ledger creation date");

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



$.validator.addMethod("bchk", function(value, element,p) {  

    total_transfer_amount =$('#total_transfer_amount').val();

    bank_balance =$('#bank_balance').val();
    if (($( "#amount_mode" ).val()==2)) {
      if(parseFloat(total_transfer_amount)<=parseFloat(bank_balance))

      {

        $.validator.messages.bchk = "";

        result = true;

      }else{

        $.validator.messages.bchk = "Sufficient amount not available in bank account!";

        result = false;  

      }
    }
    else
    {
      $.validator.messages.bchk = "";
        result = true;
    }

    

    return result;

  }, "");




  $('form#transfer_save').submit(function(){
    if($('#transfer_save').valid())
    {
      $( ".loader" ).show();
      $(this).find(':button[type=submit]').prop('disabled', true);
    }
  });


  $('#transfer_save').validate({

      rules: { 
          select_date: {
            required:true,
            maxpDate:true,
          } , 
          amount_mode: "required",
          total_transfer_amount: {
            required:true,
            decimal: true, 

            bchk: function(element) {

              if (($( "#amount_mode" ).val()==2)) {

                return true;

              } else {

                return false;

              }

            },

          },

          

          bank_balance: {
            required:true,

            decimal: true, 

            bchk: function(element) {

              if (($( "#amount_mode" ).val()==2)) {

                return true;

              } else {

                return false;

              }

            },

          },

          payment_mode:{

            required: function(element) {

              if (($( "#amount_mode" ).val()==2)) {

                return true;

              } else {

                return false;

              }

            },

          },

          bank_id:{ 

            required:true,

          },

          account_id:{

            required:true,

          },

          cheque_id:{

            required: function(element) {

              if (($( "#payment_mode" ).val()==1)) {

                return true;

              } else {

                return false;

              }

            },

          },

          utr_tran:{

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

          },



      },

      messages: {  

        select_date: {
            required:"Please select date.",
             },

          amount_mode: {

            required:"Please amount mode.",

             },

          total_transfer_amount:{

            required: "Please enter total amount.",

             },

            bank_balance:{

            required: "Please enter amount.",

             },

          payment_mode: {

            required:"Please select payment mode.", 

             },

          bank_id:{

            required: "Please select bank.", 

             },

          account_id:{

            required: "Please select account.", 

             },

          cheque_id:{

            required: "Please select cheque.", 

             },

          utr_tran: {

            required:"Please enter UTR number / Transaction Number.",   

             },

          neft_charge: {

            required: "Please enter NEFT charge.",

            decimal : "Please enter a valid date.",

          

             },



      },

      submitHandler: function (){
              $('button[id="submit_transfer"]').prop('disabled',true);
              return true;
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

   



 

   





      $( document ).ajaxStart(function() { 

          $( ".loader" ).show();

       });



       $( document ).ajaxComplete(function() {

          $( ".loader" ).hide();

       });





$('#amount_mode').change(function(){

  $('.online').hide();

  $('.cheque').hide();



  $('#payment_mode').val('');

  $('#bank_id').val('');

  $('#account_id').val('');

  $('#cheque_id').val('');

  $('#utr_tran').val('');

  $('#neft_charge').val(''); 

   $('#online_total_amount').val('{{ number_format((float)$total_transfer, 2, '.', '') }}'); 

  $('#total_transfer_amount').val('{{ number_format((float)$total_transfer, 2, '.', '') }}'); 



  if($(this).val()==1)

  {

    $('.bank').hide();

  }

  else

  {

    $('.bank').show();

  }



}); 

$('#payment_mode').change(function(){



   $('#bank_id').val('');
   $('#bank_balance').val('');

  $('#account_id').val('');

  $('#cheque_id').val('');

  $('#utr_tran').val('');

  $('#neft_charge').val(''); 

  $('#online_total_amount').val('{{ number_format((float)$total_transfer, 2, '.', '') }}'); 

  $('#total_transfer_amount').val('{{ number_format((float)$total_transfer, 2, '.', '') }}'); 



  if($(this).val()==1)

  {

    $('.online').hide();

    $('.cheque').show();

  }

  else

  {

    $('.online').show();

    $('.cheque').hide();

  }



});

 $(document).on('change','#bank_id',function(){ 

    var bank_id=$('#bank_id').val();

    $('#bank_balance').val('0.00');



          $.ajax({

              type: "POST",  

              url: "{!! route('admin.bank_account_list') !!}",

              dataType: 'JSON',

              data: {'bank_id':bank_id},

              headers: {

                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

              },

              success: function(response) { 

                $('#account_id').find('option').remove();

                $('#account_id').append('<option value="">Select account number</option>');

                 $.each(response.account, function (index, value) { 

                        $("#account_id").append("<option value='"+value.id+"'>"+value.account_no+"</option>");

                    }); 



              }

          });



  }); 



 $(document).on('change','#account_id',function(){ 

  $('#bank_balance').val('0.00');
    var account_id=$('#account_id').val();
    var bank_id=$('#bank_id').val();
    var entrydate=$('#select_date').val();       
    // alert(entrydate);
    if(entrydate == '')
    {
      $('#account_id').val(' ');
        swal("Warning!", "Please select at payment date", "warning"); 
            
    }
    else
    {
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
                $('#cheque_id').find('option').remove();
                $('#cheque_id').append('<option value="">Select cheque number</option>');
                 $.each(response.chequeListAcc, function (index, value) { 
                        $("#cheque_id").append("<option value='"+value.id+"'>"+value.cheque_no+"</option>");
                    });              
               }
          });
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
                 $('#bank_balance').val(response.balance); 
              }

          });
      }
  });

 $(document).on('change','#cheque_id',function(){ 

    $('#cheque_number').val($("#cheque_id option:selected").text());



  });



 $(document).on('keyup','#neft_charge',function(){ 

  charge=$(this).val();

  total =$('#online_tran_amount').val();

  if(charge =='')

  {

    charge=0;

  }



  total_salary_transfer  = parseFloat(charge)+parseFloat(total);

    $('#online_total_amount').val(parseFloat(total_salary_transfer).toFixed(2));

     $('#total_transfer_amount').val(parseFloat(total_salary_transfer).toFixed(2));

    



});



$( "#amount_mode" ).trigger( "change" );



$('.export').on('click',function(){

      $('#amount_mode_exp').val($('#amount_mode').val());

        $('form#filter').attr('action',"{!! route('admin.ledger.exportRentTransfer') !!}");

        $('form#filter').submit();

        return true;

    });



 });

</script>