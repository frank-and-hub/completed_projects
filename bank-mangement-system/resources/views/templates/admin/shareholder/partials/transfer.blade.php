<script type="text/javascript">
    var shareListing;
$(document).ready(function(){  
//  $("#date").hover(function(){
//       var date=$('#create_application_date').val();
//       $('#date').datepicker({
//           format:"dd/mm/yyyy",
//             endHighlight: true, 
//             autoclose:true, 
//             endDate:date, 
//             startDate: '01/04/2021',
//           })
//    }) 

      $('.col-md-4').addClass('col-md-6').removeClass('col-md-4');
      $('.col-form-label.col-lg-12').addClass('col-lg-4').removeClass('col-lg-12');
      $('.col-lg-12.error-msg').addClass('col-lg-8').removeClass('col-lg-12');


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
          $.ajax({
            url: "{{route('admin.shareholder.company')}}",
            type: "POST",
            data:{"company":company},
            headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
            success: function(response){
            console.log(response);
              $('#shareholder').find('option').remove();
              $('#shareholder').append('<option value="">--Please Select Shareholder--</option>');
              $.each(response, function (index, value) { 
                          $("#shareholder").append("<option value='"+value.id+"'>"+value.name+"</option>");
                      }); 
            }

          })
      });
  // CHECK IF AADAHR CARD IS ALREADY EXIST IN SHAREHOLDER OR NOT
  $.validator.addMethod("aadharExist", function(value, element, params) {
    var result = true;

    $.ajax({
        type: "POST",  
        url: "{{route('admin.aadhar.exist')}}",  
        data: {
            aadhar: value
        },
        async: false,
        
        success: function (response) {
            result = (response == false); 
        }
    });

        return result;
    }, "Aadhar card Already Exist.");


    //End aadharExist

    // check pan exist or not
    $.validator.addMethod("panExist", function(value, element, params) {
    var result = true;

    $.ajax({
        type: "POST",  
        url: "{{route('admin.pan.exist')}}",  
        data: {
            pan: value
        },
        async: false,
        
        success: function (response) {
            result = (response == false); 
        }
    });

        return result;
    }, "Pan card Already Exist.");
    // end check pan

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
  // Validate Form 
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
                 shareholder:{
                    required:true,  
                },  
                father_name:
                {
                    required:true,                
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
                contact_no:                
                {
                    required:true,
                    number: true,
                    minlength: 10,
                    maxlength:12
                },
                email: {
                  email :  function(element) {
                    if ($( "#email" ).val()!='') {
                      return true;
                    } else {
                      return false;
                    }
                  }, 
                },
                // member_id:
                // {
                //     required:true, 
                // },
                // ssb_account:
                // {
                //     required:true,  
                // },
                remark:
                {
                    required:true, 
                },
                amount:{
                  required:true,
                  decimal:true,
                  zero1:true,
                },
                new_amount:{
                  required:true,
                  decimal:true,
                  zero1:true, 
                },            
                new_person_father_name:{
                  required:true,
                },
                new_person_address:{
                  required:true,
                },
                new_person_pan_no:{
                  required:true,
                  checkPenCard :true, 
                  panExist:true,
                },
                new_person_aadhar_no:{
                  required:true,
                  checkAadhar:true,
                  aadharExist:true,
                },
                new_person_contact_no:{
                  required:true,
                    number: true,
                    minlength: 10,
                    maxlength:12
                },  
                 bank_name:
                {
                    required:true,  
                },
                 branch_name:
                {
                    required:true,  
                },
                account_number:
                {
                    required:true,  
                    number: true,
                    minlength: 8,
                    maxlength: 16
                },
                ifsc_code:
                {
                    required:true, 
                    checkIfsc:true,
                },         
            },
            messages:{
              company:{
                "required":"Enter Company Name",
              },
              date:{
                "required":"Please select date.",              
              },
              shareholder:{
                "required":"Please select shareholder.",              
              },
               email: {
                required: "Please enter email id.",
                email : "Please enter valid email id.",
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
              contact_no:
              {
                "required":"Please enter contact number.",
              }, 
               ifsc_code:{
                "required":"Please enter ifsc code.",
              },
              member_id:{
                "required":"Please enter member id.",
              },
              // ssb_account:{
              //   "required":"Please enter ssb account number.",              
              // },
              remark:{
                "required":"Please enter remark.",
              },
              amount:{
                "required":"Please enter amount."
              },
              new_amount:{
                "required":"Please enter amount."
              },
              bank:{
                "required":"Please select bank."
              },
              bank_name:{
                "required":"Please enter bank."
              },
               new_person_father_name:{
                  "required":"Please enter father name.",
              },
               new_person_address:{
                 "required":"Please enter address.",
                },
                new_person_pan_no:{
                 "required":"Please enter pan number.",
                },
                new_person_aadhar_no:{
                  "required":"Please enter aadhar number.",
                },
                new_person_contact_no:{
                  "required":"Please enter contact number.",
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
        //Verify Member Id
        
        $('#member_id').on('change',function(){
            $('#date').datepicker('destroy');
            $('#date').val('');
            var date = $('#create_application_date').val();
            var sdate=$('#rgister_date').val();;
            $('#date').datepicker({
                format:"dd/mm/yyyy",
                  endHighlight: true, 
                  autoclose:true, 
                  endDate:date, 
                  startDate: sdate,
            });
            let old_mem_id = $('#old_member_id').val();
            if($('#company_id').val()==''){
                swal("Error!", "Select Company First", "error");
                $('#member_id').val('');
                return false;
            }
            
            var member_id = $(this).val();
            if(member_id==old_mem_id){
              swal("Warning!", "Please Enter Diffrent Member Id ! Can not Transfer share to themselves", "warning");
              $('#member_id').val('');
              $('#ssb_account').val('');
              $('#new_person_aadhar_no').prop('readonly',false);
              $('#new_person_pan_no').prop('readonly',false);
              $('#new_person_address').prop('readonly', false);
              $('#new_person_contact_no').prop('readonly', false);
              $('#new_person_father_name').prop('readonly', false);
              return false;
            }
            var company_id = $('#company_id').val();
            var name = $('#name').val().toLowerCase().trim();
            var date = $('#create_application_date').val();
            $.ajax({
            type: "POST",  
            url: "{!! route('admin.verify.member') !!}",
            dataType: 'JSON',
            data: {'memberid':member_id,'company_id':company_id},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                 console.log(response);
                if(response.resCount > 0)
                {
                    $('#c_id').val(response.company_id);
                    $('#ssb_account').val(response?.ssbAccount?.account_no)
                    
                    if($('#ssb_account').val() == ''){
                        var sdate =response.memCreateDate;
                        
                        console.log(sdate);
                        var dateParts = sdate.split('-');
                        var year =  dateParts[2]; 
                        var day = dateParts[1];
                        var month = dateParts[0];
                        var formattedDate = day + '/' + month + '/' + year;
                        
                        $('#date').datepicker({
                            format: "dd/mm/yyyy",
                            autoclose: true,
                            endDate: date, 
                            startDate: formattedDate
                        });
                    }
                    else{
                            var sdate =(response.ssbAccount == '') ?  response.memCreateDate : response?.ssbAccount?.created_at;
                            sdate =(response.ssbAccount == '') ? sdate : new Date( response?.ssbAccount?.created_at).toLocaleDateString('en-US');
                            console.log(sdate);
                            var dateParts = sdate.split('/');
                            var year =  dateParts[2]; 
                            var day = dateParts[1];
                            var month = dateParts[0];
                            var formattedDate = day + '/' + month + '/' + year;
                            
                            $('#date').datepicker({
                                format: "dd/mm/yyyy",
                                autoclose: true,
                                endDate: date, 
                                startDate: formattedDate
                            });

                    }
                    if(response.company_id != company_id){
                        swal("Error!", "Member is not from selected company", "error");
                        $('#member_id').val('');
                        $('#ssb_account').val('');
                        return false;
                    }
                    else{
                        // if(member_id == response.member_id && (name ==response.name.toLowerCase().trim() || name ==response.fullname.toLowerCase().trim() ))
                        // {
                        //     $('#member_id').val(member_id); 
                        // }
                        // else{
                        //     swal("Error!", "Entered name and enter member id  name("+response.name.toLowerCase()+")  does not match!", "error");
                        //     $('#member_id').val('');
                        // }
                        $('#name').val(response.fullname);
                        $('#new_person_father_name').val(response.fatherName);
                        $('#new_person_address').val(response.address);
                        $('#new_person_contact_no').val(response.mobile_no);
                        $('#email').val(response.email);
                        $('#new_person_aadhar_no').val(response.aadhar);
                        $('#new_person_pan_no').val(response.panCard);
                        $('#bank_name').val(response.bankDetails[0]?.bank_name);
                        $('#branch_name').val(response.bankDetails[0]?.branch_name);
                        $('#account_number').val(response.bankDetails[0]?.account_no);
                        $('#ifsc_code').val(response.bankDetails[0]?.ifsc_code);
                        $('#new_person_aadhar_no').prop('readonly', response.aadhar !== '');
                        $('#new_person_pan_no').prop('readonly', response.panCard !== '');
                        $('#new_person_address').prop('readonly', response.address !== '');
                        $('#new_person_contact_no').prop('readonly', response.mobile_no !== '');
                        $('#new_person_father_name').prop('readonly', response.fatherName !== '');
                    }

                }
                else{
                    swal("Error!", "Member Id Does not Found in Selected Company!", "error");
                    $('#member_id').val('');
                    $('#ssb_account').val('');
                    $('#new_person_aadhar_no').prop('readonly',false);
                    $('#new_person_pan_no').prop('readonly',false);
                    $('#new_person_address').prop('readonly', false);
                    $('#new_person_contact_no').prop('readonly', false);
                    $('#new_person_father_name').prop('readonly', false);
                    $('#c_id').val('');
                }
            }
            });
        });
        
        
    
    //  $('#ssb_account').on('change',function(){
    //       var sysdate = $('#create_application_date').val();
    //       var m_id =    $('#member_id').val(); 
    //       if(m_id ==''){
    //         swal("Error!", "Enter Member id First!", "error");
    //         $('#ssb_account').val('');
    //         return false;
    //       }
    //       var ssb_account = $(this).val();
    //       var name = $('#name').val().toLowerCase().trim();
    //         $.ajax({
    //             type:"POST",
    //             url:"{!! route('admin.verify.ssbAccount') !!}",
    //             data:{member_id:m_id,},
    //             dataType:"JSON",
    //             headers: {
    //                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //                 },
    //             success:function(response)
    //             { 
    //             // console.log(response);
    //             if(response)
    //             {
    //                 var Da = $('#create_application_date').val();
    //                 var dateStr = response.created_at;
    //                 var dateObj = new Date(dateStr);
    //                 var year = dateObj.getFullYear();
    //                 var month = String(dateObj.getMonth() + 1).padStart(2, '0'); // Month is zero-based
    //                 var day = String(dateObj.getDate()).padStart(2, '0');
    //                 var forDate = day + '/' + month + '/' + year;
    //                 if(ssb_account === response.account_no) {
    //                     $('#ssb_account').val(response.account_no);
    //                     $('#ssb_id').val(response.id);
    //                     $('#date').datepicker('setDate', forDate);
    //                     $('#date').datepicker('setStartDate', forDate);
    //                 }
    //                 else{
    //                 swal("Error!", "SSB account holder member id or enter member id not match!", "error");
    //                 $('#ssb_account').val('');
    //                 $('#ssb_id').val('');
    //                 }
    //             }
    //             else{
    //                 swal("Error!", " SSB account not found!", "error");
    //                 $('#ssb_account').val('');
    //                 $('#ssb_id').val('');
    //             }
    //             }
    //         })
    //     });
// $('#name').on('keyup',function(){
//           if($("#member_id").val()!='')
//           {
//             $( "#member_id" ).trigger( "change" );
//           }
//           if($("#ssb_account").val()!='')
//           {
//             $( "#ssb_account" ).trigger( "change" );
//           }  
//       })   
$('#member_id').on('keyup',function(){
          if($("#ssb_account").val()!='')
          {
            $( "#ssb_account" ).trigger( "change" );
          }  
      }) 
// $('#ssb_account').on('keyup',function(){
//           if($("#name").val()!='')
//           {
//             $( "#name" ).trigger( "change" );
//           }
//           if($("#member_id").val()!='')
//           {
//             $( "#member_id" ).trigger( "change" );
//           }  
//       }) 

// shareholder change from dropdown to dropdown
$('#shareholder').on('change',function(){
  var type_id = $(this).val();
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
      var sdate=response.rgister_date;
      $('#date').datepicker({
          format:"dd/mm/yyyy",
            endHighlight: true, 
            autoclose:true, 
            endDate:date, 
            startDate: sdate,
      });
      $('#father_name').val(response.shareholder.father_name); 
      $('#company_id').val(response.shareholder.company_id); 
      $('#address').val(response.shareholder.address);
      $('#pan_no').val(response.shareholder.pan_card);
      $('#aadhar_no').val(response.shareholder.aadhar_card);
      $('#rgister_date').val(response.rgister_date);
      $('#company').val(response.shareholder.company.name);
      $('#company_id').val(response.shareholder.company.id);
      $('#old_member_id').val(response.member?.member_id);
      if(response.shareholder.current_balance > 0)
      {
        $('#amount ,#new_amount').val(parseFloat(response.shareholder.current_balance).toFixed(2));
      }
      else{
        $('#amount , #new_amount').val(parseFloat(0).toFixed(2));
      }
    }
  })
})
        $('#name').on('keyup',function(){
            $('#member_id').val('');
            $('#ssb_account').val('');
            $('#new_person_aadhar_no').prop('readonly',false);
            $('#new_person_pan_no').prop('readonly',false);
            $('#new_person_address').prop('readonly', false);
            $('#new_person_contact_no').prop('readonly', false);
            $('#new_person_father_name').prop('readonly', false);
        });
    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });
    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    }); 
})
</script>