<script type="text/javascript">
$(document).ready(function () { 
  $('.col-md-4').addClass('col-md-6').removeClass('col-md-4');
  $('.col-form-label.col-lg-12').addClass('col-lg-4').removeClass('col-lg-12');
  $('.col-lg-12.error-msg').addClass('col-lg-8').removeClass('col-lg-12');
// $("#date").hover(function(){
  
//       var date=$('#create_application_date').val();
//       $('#date').datepicker({
//           format:"dd/mm/yyyy",
//             endHighlight: true, 
//             autoclose:true, 
//             endDate:date, 
//             startDate: '01/04/2021',
//           }).on('change', function(){             
//             $('#branch_total_balance').val('0.00'); 
//             $('#branch').val(''); 
//             $( "#branch" ).trigger( "change" );
//             $( "#daybook" ).trigger( "change" );
//       });
//     })
$('#head_type').on('change',function(){
            $('#date').datepicker('destroy');
            $('#date').val('');
        var type_id = $(this).val();
            $('#father_name').val('');
            $('#member_id').val('');
            $('#address').val('');
            $('#pan_no').val('');
            $('#aadhar_no').val('');
            $('#rgister_date').val('');
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
            var date=$('#create_application_date').val();
            var sdate = response.rgister_date;
            $('#date').datepicker({
                format:"dd/mm/yyyy",
                  endHighlight: true, 
                  autoclose:true, 
                  endDate:date, 
                  startDate: sdate,
                }).on('change', function(){             
                  $('#branch_total_balance').val('0.00'); 
                  $('#branch').val(''); 
                  $( "#branch" ).trigger( "change" );
                  $( "#daybook" ).trigger( "change" );
            });
            $('#father_name').val(response.shareholder.father_name);
            $('#member_id').val(response.shareholder.member_id);
            $('#address').val(response.shareholder.address);
            $('#pan_no').val(response.shareholder.pan_card);
            $('#aadhar_no').val(response.shareholder.aadhar_card);
            $('#rgister_date').val(response.rgister_date);
            $('#company_id').val(response.shareholder.company.id);
            // $('#company').val(response.shareholder.company.name);
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
    $('#payment_type').on('change',function(){
      
      var company = $('#company_id').val();
      var director = $('#head_type').val();
        if(director == ''){
          swal("Warning!", "Please select Director First", "warning");
          return false;
        }
        else{
          
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
          $('.cash_mode').hide();
                  $('.bank_mode').hide();
                  $('#transaction_mode').hide();
                var mode = $(this).val();
                if(mode == '')
                {
                  $('.cash_mode').hide();
                  $('.bank_mode').hide();
                  $('#transaction_mode').hide();
                }
                if(mode==0 && mode!='')
                {
                  $('.cash_mode').show();
                  $('.bank_mode').hide();
                  $('#transaction_mode').hide();
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
                if(mode ==1 && mode!='')
                {
                  var cdate = $('#rgister_date').val();
                  $('#date').datepicker('setStartDate',cdate);
                  $('.bank_mode').show();
                  $('#transaction_mode').show();
                  $('.cash_mode').hide();            
                }
    }});
    $('#date').on('change', function(){
      var date = $('#date').val();
      $('#utr_date').val(date);
    });
   $('#payment_mode').on('change',function(){
    var amount = $('#deposit_amount').val();
    var companyId = $('#company_id').val();
    var name = $('#head_type option:selected').text();
    var rgister_date = $('#rgister_date').val();
    var date = $('#date').val();
     $('#utr_date').val(date);
     $('#branch_total_balance').val(''); 
     $('#daybook').val(0);
     $('#cheque_no').val();
     $('#online_bank').val('');
     $('#online_bank_ac').val('');
     
     $('#utr_no').val('');
     $('#transaction_bank').val('');
     $('#transaction_bank_ac').val(''); 
     $('.cheque').hide();
     $('.cash_mode').hide();
     $('.payment_mode_cheque').hide();
     $('.payment_mode_online').hide();
     var mode = $('option:selected',this).val();
     
     if(mode ==2)
     {
        $('.payment_mode_cheque').hide();
        $('.cash_mode').hide();
        $('.payment_mode_online').show();
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
            $('#online_bank').find('option').remove();
            $('#online_bank').append('<option value="">Select bank account </option>');
             $.each(response.banks, function (index, value) { 
                    $("#online_bank").append("<option value='"+value.id+"'>"+value.bank_name+"</option>");
                }); 
          }
       });
     }


     else if(mode ==1)
     {
        if(amount == ''){
          swal("Warning!", "Please Enter Deposite Amount", "warning");
          return false;
        }else{
        $('.payment_mode_cheque').show();
        $('.cash_mode').hide();
        $('.payment_mode_online').hide();
        $.ajax({
              type: "POST",  
              url: "{!! route('admin.approve_recived_cheque_list_company') !!}",
              data: {'companyId':companyId,'amount':amount,'name':name},
              dataType: 'JSON', 
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
        }})


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

     $('#branch').on('change',function(){
         $( "#daybook" ).val(0);
         var branch_code= $('option:selected',this).attr('data-value');
         $('#branch_code').val(branch_code);
         $( "#daybook" ).trigger( "change" );
     });

      
    $('#bank_id').on('change',function(){ 
          $('#bank_balance').val('0.00');
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
              $('#bank_account').find('option').remove();
              $('#bank_account').append('<option value="">Select account number</option>');
              $.each(response.account, function (index, value) { 
                      $("#bank_account").append("<option value='"+value.id+"'>"+value.account_no+"</option>");
                  }); 
            }
        })
   });


   $('#bank_account').on('change',function(){ 
    var bank_id=$('#bank_id').val();
    var account_id=$('#bank_account').val();
    var entrydate=$('#created_at').val();
    $('#bank_balance').val('0.00');
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
          data: {'branch_id':branch_id,'daybook':daybook,'entrydate':entrydate,'company_id':company_id},
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
    });

    // on change company director will show according to company
    $('#company_id').on('change', function(){
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

   $.validator.addMethod("chkbankCheque", function(value, element,p) {
      if($( "#payment_mode" ).val()==1 && ($( "#payment_type" ).val()==1))
      {  
      if(parseFloat($('#cheque_amount').val()) == parseFloat($('#deposit_amount').val()))
      {
        $.validator.messages.chkbankCheque = "";
        result = true;
      }else{
        $.validator.messages.chkbankCheque = "Cheque amount must be  equal to  deposit amount";
        result = false;  
      }
    }
    else
    {
      $.validator.messages.chkbankCheque = "";
        result = true;
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
        rules: {          
            date:
                {
                  required:true,  
                  maxpDate:true,
                },
            branch: "required", 
            father_name: "required",
            pan_no: "required",
            payment_type: "required", 
            name:{
              required:true,  
            },
            email:{
              email:true,  
            },
            address:
            {
              required:true, 
            }, 
            aadhar_no:
            {
              checkAadhar:true,
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
            branch_total_balance: {
              required: function(element) {
                if (($( "#payment_type" ).val()==0)) {
                  return true;
                } else {
                  return false;
                }
              },
            },
            online_bank: {
              required: function(element) {
                if (($( "#payment_mode" ).val()==2)) {
                  return true;
                } else {
                  return false;
                }
              },
            },
            online_bank_ac: {
              required: function(element) {
                if (($( "#payment_mode" ).val()==2)) {
                  return true;
                } else {
                  return false;
                }
              },
            },
            utr_date: {
              required: function(element) {
                if (($( "#payment_mode" ).val()==2)) {
                  return true;
                } else {
                  return false;
                }
              },
            },
            utr_no: {
              required: function(element) {
                if (($( "#payment_mode" ).val()==2)) {
                  return true;
                } else {
                  return false;              }
              },
              
            },
            transaction_bank: {
              required: function(element) {
                if (($( "#payment_mode" ).val()==2)) {
                  return true;
                } else {
                  return false;
                }
              },
            },
            transaction_bank_ac: {
              required: function(element) {
                if (($( "#payment_mode" ).val()==2)) {
                  return true;
                } else {
                  return false;
                }
              },
              number:true,
            },
            cheque_no: {
              required: function(element) {
                if (($( "#payment_mode" ).val()==1) &&  ($( "#payment_type" ).val()==1)) {
                  return true;
                } else {
                  return false;
                }
              },
            },
            deposit_amount: {
              required:true,
              decimal:true,  
              chkbankCheque: function(element) {
                if (($( "#payment_mode" ).val()==1) && ($( "#payment_type" ).val()==1)) {
                  return true;
                } else {
                  return false;
                }
              }, 
            },
            company:{
              required:true,
            }
        },
        messages: { 
          company:{
              "required":"Please Enter Company.",
            },
            date:{
                  "required":"Please select date.",
                },
            branch: {
              required:"Please select branch",
            },
            payment_type: "Please select payment type",
            email: { 
              email : "Please enter valid email id.",
            },
                name:{
                  "required":"Please select director.",
                },
                father_name:{
                  "required":"Please enter father name.",
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
            deposit_amount: {
              required:"Please enter amount",
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
    });
</script>