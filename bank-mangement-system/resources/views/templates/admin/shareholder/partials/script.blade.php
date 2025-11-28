<script type="text/javascript">
    var shareListing;
    $(document).ready(function(){
        //var type =  $('#type2').val();
        $('.col-md-4').addClass('col-md-6').removeClass('col-md-4');
        $('.col-form-label.col-lg-12').addClass('col-lg-4').removeClass('col-lg-12');
        $('.col-lg-12.error-msg').addClass('col-lg-8').removeClass('col-lg-12');

        // Datepicker According to company create date
        $("#date").hover(function () {
            var date = $('#create_application_date').val();
            $('#date').datepicker({
                format: "dd/mm/yyyy",
                endHighlight: true,
                autoclose: true,
                endDate: date,
                startDate: '01/04/2019',
            })
        });
        
        
  dataid='{{ $dataid }}';
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
    $.validator.addMethod("checkMemberId", function(value, element,p) {    
      if(this.optional(element) || /^(\d{4})([A-Za-z]{2})(\d{6})$/.test(value)==true)
    {
        result = true;
      }else{
        $.validator.messages.checkMemberId = "Invalid Member ID.";
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

    // CHECK IF AADAHR CARD IS ALREADY EXIST IN SHAREHOLDER OR NOT
    $.validator.addMethod("aadharExist", function(value, element, params) {
    var result = true;
    var company_id = $('#company_id').val();
    var type = $('#type').val();

    $.ajax({
        type: "POST",  
        url: "{{route('admin.aadhar.exist')}}",  
        data: {
            'aadhar': value,'company_id':company_id,'type':type
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
    var company_id = $('#company_id').val();    
    var type = $('#type').val();

    $.ajax({
        type: "POST",  
        url: "{{route('admin.pan.exist')}}",  
        data: {
            'pan': value,'company_id':company_id,'type':type
        },
        async: false,
        
        success: function (response) {
            result = (response == false); 
        }
    });

        return result;
    }, "Pan card Already Exist.");
    // end check pan


    $('#filter').validate({
        rules:{
        type:
    {
        required:true,  
        },
    },
    messages:{
        type:{
        "required":"Please Select Type.",
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
        $(element.form).find("input[type=radio]").each(function (which) {
            $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
            $(this).addClass('is-invalid');
        });
        }
      },
    unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
    if($(element).attr('type') == 'radio'){
        $(element.form).find("input[type=radio]").each(function (which) {
            $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
            $(this).removeClass('is-invalid');
        });
        }
      },
    })
    // Validate Form 
    $('#shareholder_form').validate({
    rules: {
        company_id:{
                required:true
              },
        type:{
        required:true
        },
        
        name: {
            required: true,
        },
        address: {
            required: true,
        },
        pan_no: {
            required: true,
            checkPenCard: true,
            panExist:true,
        },
        aadhar_no: {
            checkAadhar: true,
            required: true,
            aadharExist:true,
        },
        contact_no: {
            required: true,
            number: true,
            minlength: 10,
            maxlength: 12,
        },
        email: {
            email: function (element) {
                if ($("#email").val() != '') {
                    return true;
                } else {
                    return false;
                }
            },
        },
        bank_name: {
            required: true,
        },
        branch_name: {
            required: true,
        },
        account_number: {
            required: true,
            number: true,
            minlength: 8,
            maxlength: 16,
        },
        ifsc_code: {
            required: true,
            checkIfsc: true,
        },
        // member_id: {
        //     required: true,
        //     checkMemberId: true,
        // },
        // ssb_account: {
        //     required: true,
        // },
        remark: {
            required: true,
        },
        father_name: {
            required: true,
        },
        amount: {
            required: true,
            decimal: true,
            zero1: true,
        },
        deposit_amount: {
            required: true,
            decimal: true,
            zero1: true,
        },
        withdrawal_amount: {
            required: true,
            decimal: true,
            zero1: true,
        },
        payment_type: {
            required: true,
        },
        branch: {
            required: true,
        },
        bank: {
            required: true,
        },
        bank_account: {
            required: true,
        },
        payment_mode: {
            required: true,
        },
        cheque_number: {
            required: true,
        },
        utr_number: {
            required: true,
        },
        new_person_father_name: {
            required: true,
        },
        new_person_address: {
            required: true,
        },
        new_person_pan_no: {
            required: true,
            checkPenCard: true,
        },
        new_person_aadhar_no: {
            required: true,
            checkAadhar: true,
        },
        new_person_contact_no: {
            required: true,
            number: true,
        },
        date: {
            required: true,
        },
    },
    messages: {
        company_id:{
                "required":"Plese select Company",
              },
             
        date:{
        "required":"Please Select date.",
            },
        type:{
            "required":"Please Select Type.",
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
        withdrawal_amount:{
            "required":"Please enter withdrawal amount."
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
    },
    errorElement: 'span',
    errorPlacement: function (error, element) {
        error.addClass(' ');
        element.closest('.error-msg').append(error);
    },
    highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
        if ($(element).attr('type') == 'radio') {
            $(element.form).find("input[type=radio]").each(function (which) {
                $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
                $(this).addClass('is-invalid');
            });
        }
    },
    unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
        if ($(element).attr('type') == 'radio') {
            $(element.form).find("input[type=radio]").each(function (which) {
                $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
                $(this).removeClass('is-invalid');
            });
        }
    },
});

        //Verify Member Id
        $('#member_id').on('change',function(){
            $('#date').datepicker('destroy');
            $('#date').val('');

            if($('#company_id').val()==''){
                swal("Error!", "Select Company First", "error");
                $('#member_id').val('');
                $('#ssb_account').val('');
                return false;
            }
            var member_id = $(this).val();
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
                 
                if(response.resCount > 0)
                {
                    $('#c_id').val(response.company_id);
                    $('#ssb_account').val(response?.ssbAccount?.account_no)
                    
                    if($('#ssb_account').val() == ''){
                        var sdate =response.memCreateDate;
                        
                        
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
                        //     $('#ssb_account').val('');
                        // }

                        $('#name').val(response.fullname);
                        $('#father_name').val(response.fatherName);
                        $('#address').val(response.address);
                        $('#contact_no').val(response.mobile_no);
                        $('#email').val(response.email);
                        $('#aadhar_no').val(response.aadhar);
                        $('#pan_no').val(response.panCard);
                        $('#bank_name').val(response.bankDetails[0]?.bank_name);
                        $('#branch_name').val(response.bankDetails[0]?.branch_name);
                        $('#account_number').val(response.bankDetails[0]?.account_no);
                        $('#ifsc_code').val(response.bankDetails[0]?.ifsc_code);
                        $('#aadhar_no').prop('readonly', response.aadhar !== '');
                        $('#pan_no').prop('readonly', response.panCard !== '');
                        $('#father_name').prop('readonly', response.fatherName !== '');
                        $('#address').prop('readonly', response.address !== '');
                        $('#contact_no').prop('readonly', response.mobile_no !== '');
                        
                    }

                }
                else{
                    if($('#member_id').val()==''){
                        return true;
                    }
                    else{
                    swal("Error!", "Member Id Does not Found in Selected Company!", "error");
                    $('#member_id').val('');
                    $('#ssb_account').val('');
                    $('#c_id').val('');
                    $('#aadhar_no').prop('readonly',false);
                    $('#pan_no').prop('readonly',false);
                    $('#contact_no').prop('readonly',false);
                    $('#address').prop('readonly',false);
                    $('#father_name').prop('readonly',false);
                    }
                }
            }
            });
        });
        
        $('#company_id').on('change',function(){
             $('#c_id').val('');
             $('#member_id').val('');
             $('#ssb_account').val('');
             $('#aadhar_no').prop('readonly',false);
             $('#pan_no').prop('readonly',false);
             $('#contact_no').prop('readonly',false);
             $('#address').prop('readonly',false);
             $('#father_name').prop('readonly',false);
            
        });


        $('#ssb_account').on('change',function(){
          var sysdate = $('#create_application_date').val();
          var m_id =    $('#member_id').val(); 
          var company_id = $('#company_id').val();
          if(m_id ==''){
            swal("Error!", "Enter Member id First!", "error");
            $('#ssb_account').val('');
            return false;
          }
          var ssb_account = $(this).val();
          var name = $('#name').val().toLowerCase().trim();
            $.ajax({
                type:"POST",
                url:"{!! route('admin.verify.ssbAccount') !!}",
                data:{member_id:m_id,company_id:company_id},
                dataType:"JSON",
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                success:function(response)
                { 
                
                if(response)
                {
                    var Da = $('#create_application_date').val();
                    var dateStr = response.created_at;
                    var dateObj = new Date(dateStr);
                    var year = dateObj.getFullYear();
                    var month = String(dateObj.getMonth() + 1).padStart(2, '0'); // Month is zero-based
                    var day = String(dateObj.getDate()).padStart(2, '0');
                    var forDate = day + '/' + month + '/' + year;
                    if(ssb_account === response.account_no) {
                        $('#ssb_account').val(response.account_no);
                        $('#ssb_id').val(response.id);
                        $('#date').datepicker('setDate', forDate);
                        $('#date').datepicker('setStartDate', forDate);
                    }
                    else{
                    swal("Error!", "SSB account holder member id or enter member id not match!", "error");
                    $('#ssb_account').val('');
                    $('#ssb_id').val('');
                    }
                }
                else{
                    swal("Error!", " SSB account not found!", "error");
                    $('#ssb_account').val('');
                    $('#ssb_id').val('');
                }
                }
            })
        });


       
        $('#member_id').on('keyup',function(){
            if($("#ssb_account").val()!='')
        {
            $("#ssb_account").trigger("change");
            }  
        });
        $('#ssb_account').on('keyup',function(){
            if($("#name").val()!='')
        {
            $("#name").trigger("change");
            }
        if($("#member_id").val()!='')
        {
            $("#member_id").trigger("change");
            }  
        }); 
    if(dataid>0)
    {
          if($("#name").val()!='')
        {
            $("#name").trigger("change");
        }
        if($("#member_id").val()!='')
        {
            $("#member_id").trigger("change");
        }
        if($("#ssb_account").val()!='')
        {
            $("#ssb_account").trigger("change");
        }  
    }


    $('#head_type').on('change',function(){
        var type_id = $(this).val();
        $.ajax({
            type:"POST",
        url:"{!!route('admin.get_share_holder_detail')!!}",
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
    });

    $('#daybook').on('change',function(){ 
    var daybook=$('#daybook').val();
    var branch_id=$('#branch').val();
    var entrydate=$('#created_at').val();
    $('#branch_total_balance').val('0.00');
        if(branch_id>0)
    {
        $.ajax({
            type: "POST",
            url: "{!! route('admin.branchChkbalance') !!}",
            dataType: 'JSON',
            data: { 'branch_id': branch_id, 'daybook': daybook, 'entrydate': entrydate },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                // alert(response.balance);
                $('#branch_total_balance').val(response.balance);
            }
        });
        }
    });

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
        url: "{!!route('admin.approve_cheque_details')!!}",
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
        url: "{!!route('admin.bank_account_list')!!}",
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
            $("#online_bank_ac").append("<option value='" + value.id + "'>" + value.account_no + "</option>");
                    }); 
            }
        })
   });
   
    $('#branch').on('change',function(){
        var branch_code = $('option:selected', this).attr('data-value');
        $('#branch_code').val(branch_code);
        $( "#daybook" ).trigger( "change" );
    });
         
        $('#payment_type').on('change', function () {
            var mode = $(this).val();
            if (mode == 0) {
                $('.cash_mode').show();
                $('.bank_mode').hide();
                $('#transaction_mode').hide();
            }
            if (mode == 1) {
                $('.bank_mode').show();
                $('#transaction_mode').show();
                $('.cash_mode').hide();
            }
        });

        $('#payment_mode').on('change',function(){
            var mode = $(this).val();
            var withdrawal_amount = $(withdrawal_amount).val();
            if(mode == 0)
            {
                $('.cash_mode').hide();
            $('.payment_mode_cheque').show();
            $('.payment_mode_online').hide();
            $('#pay_amount').val(withdrawal_amount);
            $.ajax({
                type: "POST",
            url: "{!!route('admin.approve_recived_cheque_lists')!!}",
            dataType: 'JSON',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
            success: function(response) {
                $('#cheque_no').find('option').remove();
                $('#cheque_no').append('<option value="">Select cheque number</option>');
                $.each(response.cheque, function (index, value) {
                    $("#cheque_no").append("<option value='" + value.id + "'>" + value.cheque_no + "  ( " + parseFloat(value.amount).toFixed(2) + ")</option>");
                                            }); 
                                    }
                                });
                        }
            if(mode == 1)
            {
                $('#utr_date').datepicker({
                    format: "dd/mm/yyyy",
                    endDateHighlight: true,
                    endDate: $('.create_application_date').val(),
                    autoclose: true,
                    startDate: '01/04/2021',
                });
            $('.payment_mode_cheque').hide();
            $('.cash_mode').hide();
            $('.cheque').hide();
            $('.payment_mode_online').show();
            $('#pay_amount').val(withdrawal_amount);
                    }
        });


        $('#bank').on('change',function(selected_account){
            var bank_id=$(this).val();
            $.ajax({
                type: "POST",
                url: "{!!route('admin.bank_account_list')!!}",
                dataType: 'JSON',
                data: {'bank_id':bank_id},
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                success: function(response) {
                    $('#bank_account').find('option').remove();
                $('#bank_account').append('<option value="">Select account number</option>');
                $.each(response.account, function (index, value) {
                    $("#bank_account").append("<option value='" + value.id + "'>" + value.account_no + "</option>");
                                }); 
                        }
                });
        });

        $('#name').on('keyup',function(){
            $('#member_id').val('');
            $('#ssb_account').val('');
            $('#aadhar_no').prop('readonly',false);
            $('#pan_no').prop('readonly',false);
            $('#contact_no').prop('readonly',false);
            $('#address').prop('readonly',false);
            $('#father_name').prop('readonly',false);
        });

            shareListing = $('#share_table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
                        var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
                scrollTop: ($('#share_table').offset().top)
                        }, 10);
            $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
                    },
            ajax: {
                "url": "{!!route('admin.share.report.listing')!!}",
            "type": "POST",
            "data":function(d) {d.searchform = $('form#filter').serializeArray()
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                    },
            columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'company', name: 'company'},
            {data: 'type', name: 'type'},
            {data: 'name', name: 'name'},
            {data: 'father_name', name: 'father_name'},
            {data: 'address', name: 'address'},
            {data: 'pan_card', name: 'pan_card'},
            {data: 'aadhar_card', name: 'aadhar_card'},
            {data: 'firm_name', name: 'firm_name'},
            {data: 'email', name: 'email'},
            {data: 'contact', name: 'contact'},
            {data: 'bank_name', name: 'bank_name'},
            {data: 'branch_name', name: 'branch_name'},
            {data: 'account_number', name: 'account_number'},
            {data: 'current_balance', name: 'current_balance',
            "render":function(data, type, row){
                                if ( row.current_balance>=0 ) {
                                    return row.current_balance+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                                }else {
                                    return "N/A";
                                }
                            }
                        },
            {data: 'ifsc_code', name: 'ifsc_code'},
            {data: 'member_id', name: 'member_id'},
            {data: 'ssb_account', name: 'ssb_account'},
            {data: 'remark', name: 'remark'},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action'},
            ]
        });
            $(shareListing.table().container()).removeClass( 'form-inline' );
    $( document ).ajaxStart(function() {
        $(".loader").show();
    });
    $( document ).ajaxComplete(function() {
        $(".loader").hide();
    });
    })
    function searchForm()
    {  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
    $(".table-section").removeClass('hideTableData');
    shareListing.draw();
    }
}
    function resetForm()
    {
        $('#is_search').val("no");
    $('#type').val('');
    $('#company_id').val('');
    $(".table-section").addClass("hideTableData");
    shareListing.draw();
    }


    function statusUpdate(id,headId)
    {
        swal({
            title: "Are you sure?",
            text: "You want to update status.",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-primary",
            confirmButtonText: "Yes",
            cancelButtonText: "No",
            cancelButtonClass: "btn-danger",
            closeOnConfirm: false,
            closeOnCancel: true
        },
            function (isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        type: "POST",
                        url: "{!! route('admin.update.status.share-holder') !!}",
                        dataType: 'JSON',
                        data: { 'id': id, 'head_id': headId },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response) {
                                shareListing.draw();
                                swal("Success", "Status updated  successfully!", "success");
                            }
                            else {
                                swal("Error", "Something went wrong.Try again!", "warning");
                            }
                        }
                    });
                }
            });
}
</script>