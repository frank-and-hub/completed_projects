<script type="text/javascript">
    $(document).ready(function()
    {
        $("#searchForm").find('sup').remove();
        $('.dropDown').find('div').removeClass('col-md-4');

        $(document).on("click","#add_bank",function()
        {
            $(".bankAccountForm").trigger('reset');
            $(".searchForm").trigger('reset');
            $("#searchBankId-error").html("");
            $("#searchCompanyId-error").html("");
            $("#accountBankId-error").html("");
            $("#accountCompanyId-error").html("");
            $("#account_no-error").html("");
            $("#ifsc_code-error").html("");
            $("#branch_name-error").html("");
            $("#address-error").html("");
            $(".modal-title").text("Add Account");
            $("#accountBankId").find('option').not(':first').remove();
            $("#accountCompanyId").removeAttr("disabled");
            $(".col-md-4").removeClass();
            $(".required").css("color","red");
        });

        $(document).on('click',".close",function(){
            $(".bankAccountForm").trigger('reset');
            $("#accountBankId-error").html('');
            $("#accountCompanyId-error").html("");
        });

        // $(document).on('click','.edit_data',function()
        // {
        //     $(".editBankAccountForm").trigger('reset');
        //     $(".error").html('');
        // });
        
        /*-------------- Search Form Validation ---------------*/
        $("#searchForm").validate({
            rules:{
                'searchCompanyId': {
                    'required' : true,
                }
            },
            messages:{
                'searchCompanyId': {
                    'required' : 'Select the company name',
                }
            },
        });

        /*------------ Form Validations ---------------*/
        $("#bankAccountForm").validate({
            rules:{
                'accountBankId' : 
                {
                    'required' : true,
                },
                'accountCompanyId': {
                    'required' : true,
                },
                'account_no':{
                    'required' : true,
                    'digits' : true,
                    'maxlength' : 16,
                    'minlength' : 10,
                },
                'ifsc_code': {
                    'required' : true,
                    'pattern' : /^[A-z]{4}0[A-z0-9]{6}$/,
                    'maxlength' : 11,
                    'minlength' : 11,
                },
                'branch_name' :{
                    'required' : true,
                    'pattern': /^[a-zA-z\s]+$/,
                },
                'address':{
                    'required' : true,
                    'pattern' : /[A-Za-z0-9'\.\-\s\,]/,
                },
            },
            messages:{
                'accountBankId' : 
                {
                    'required' : 'Select the bank name',
                },
                'accountCompanyId': {
                    'required' : 'Select the company name',
                },
                'account_no':{
                    'required' : 'Account No is required',
                    'digits' : 'Enter only digits',
                    'maxlength' : 'Enter 16 digits only',
                    'minlength' : 'Enter at least 10 digits',
                },
                'ifsc_code': {
                    'required' : 'IFSC Code is required',
                    'pattern' : 'Enter correct pattern',
                    'maxlength' : 'Enter 11 characters only',
                    'minlength' : 'Enter at least 11 characters',
                },
                'branch_name' :{
                    'required' : 'Branch Name is required',
                    'pattern': 'Enter only alphabets',
                },
                'address':{
                    'required' : 'Address is required', 
                    'pattern': 'Enter correct pattern',
                },
            },
        });

        /*------------ Edit Account Form Validations ---------------*/
        $("#editBankAccountForm").validate({
            rules:{
                'account_no':{
                    'required' : true,
                    'digits' : true,
                    'maxlength' : 16,
                    'minlength' : 10,
                },
                'ifsc_code': {
                    'required' : true,
                    'pattern' : /^[A-z]{4}0[A-z0-9]{6}$/,
                    'maxlength' : 11,
                    'minlength' : 11,
                },
                'branch_name' :{
                    'required' : true,
                    'pattern': /^[a-zA-z\s]+$/,
                },
                'address':{
                    'required' : true,
                    'pattern' : /[A-Za-z0-9'\.\-\s\,]/,
                },
            },
            messages:{
                'account_no':{
                    'required' : 'Account No is required',
                    'digits' : 'Enter only digits',
                    'maxlength' : 'Enter 16 digits only',
                    'minlength' : 'Enter at least 10 digits',
                },
                'ifsc_code': {
                    'required' : 'IFSC Code is required',
                    'pattern' : 'Enter correct pattern',
                    'maxlength' : 'Enter 11 characters only',
                    'minlength' : 'Enter at least 11 characters',
                },
                'branch_name' :{
                    'required' : 'Branch Name is required',
                    'pattern': 'Enter only alphabets',
                },
                'address':{
                    'required' : 'Address is required', 
                    'pattern': 'Enter correct pattern',
                },
            },
        });

        //Company Name on change event for Search Form
        $(document).on('change','#searchCompanyId',function(){ 
            var company_id=$('#searchCompanyId').val();

                $.ajax({
                    type: "POST",  
                    url: "{!! route('admin.bank_list_by_company') !!}",
                    dataType: 'JSON',
                    data: {'company_id':company_id},
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) { 
                        $('#searchBankId').find('option').remove();
                        $('#searchBankId').append('<option value="">--Please Select Bank --</option>');
                        $.each(response.bankList, function (index, value) { 
                                $("#searchBankId").append("<option value='"+value.id+"'>"+value.bank_name+"</option>");
                        }); 

                    }
                });
        });

        //Company Name on change event for Add Account Form
        $(document).on('change','#accountCompanyId',function(){ 
            var company_id=$('#accountCompanyId').val();

                $.ajax({
                    type: "POST",  
                    url: "{!! route('admin.bank_list_by_company') !!}",
                    dataType: 'JSON',
                    data: {'company_id':company_id},
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) { 
                        $('#accountBankId').find('option').remove();
                        $('#accountBankId').append('<option value="">Select bank</option>');
                        $.each(response.bankList, function (index, value) { 
                                $("#accountBankId").append("<option value='"+value.id+"'>"+value.bank_name+"</option>");
                            }); 

                    }
                });
        });

        //--------------- Search the data -------------------
        $(document).on('click',"#search",function(e)
            {
                $('.data_div').addClass('d-none');
                e.preventDefault();
                if($("#searchForm").valid())
                {
                    $(".searchForm").find('#searchCompanyId').addClass('searchCompanyId');
                    var companyId = $('.searchCompanyId').val();

                    $(".searchForm").find('#searchBankId').addClass('searchBankId');
                    var bankId = $('.searchBankId').val();
                    

                   
                    var plantable = $('.account_table').DataTable({
                        processing: true,
                        serverSide: true,
                        pageLength: 20,
                        lengthMenu: [10, 20, 40, 50, 100],
                        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
                            var oSettings = this.fnSettings ();
                            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                            return nRow;
                        },
                        ajax:{
                            "url": "{{ route('admin.bank-accounts.listing') }}",
                            "type": "POST",
                            "data" : {
                                'companyId' : companyId,
                                'bankId' : bankId,
                                '_token' : '{{ csrf_token() }}'
                            },
                        },
                        columns: [
                            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                            {data: 'bank_id', name: 'bank_id'},
                            {data: 'company_id', name: 'company_id'},
                            {data: 'account_no', name: 'account_no'},
                            {data: 'ifsc_code', name: 'ifsc_code'},
                            {data: 'branch_name', name: 'branch_name',searchable:true,orderable:true},
                            {data: 'address', name: 'address'},
                            {data: 'status', name: 'status', 
                                "render":function(data, type, row){
                                    if(row.status==0){
                                        return "<span class='badge badge-danger'>Inactive</span>";
                                    }else{
                                        return "<span class='badge badge-success'>Active</span>";
                                    }
                                }
                            },
                            // {data: 'head_id', name: 'head_id'},
                            {data: 'created_at', name: 'created_at'},
                            {data: 'action', name: 'action',orderable:false, searchable:false},
                        ],
                        "bDestroy": true,"ordering": false,
                    });
                     
                    $(plantable.table().container()).removeClass( 'form-inline' );
                    $('.data_div').removeClass('d-none');
                    
                }
            });

            $( document ).ajaxStart(function() 
            {
                $( ".loader" ).show();
            });

            $( document ).ajaxComplete(function() 
            {
                $( ".loader" ).hide();
            });

        //----------------- Create the data ---------------  
        $("#create").on("click",function(e)
        {
            e.preventDefault();
            if($(".bankAccountForm").valid())
            {
                // console.log($('#bankAccountForm').serializeArray());return false;
                $.ajax({
                    url : "{{route('admin.bank-accounts.create')}}",
                    type : 'POST',
                    data : $('#bankAccountForm').serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(e)
                    {
                        if(e.data>0)
                        {
                            $(".close").click();
                            swal("Success","Account Added Successfully","success");
                        }
                        else{
                            $(".close").click();
                            swal("Error","Account No is already exists","error");
                        }
                    }
                });
            }
        });

        /**Edit the data */
        $(document).on('click','#edit-data',function(e)
        {
            e.preventDefault();
            $(".error").html('');
            $('.editBankAccountForm').trigger('reset');
            var id = $(this).data('id');
            $(".accountId").val(id);
            var bank_id = $(this).data('bank-id');
            var company_id = $(this).data('company-id');
            $(".modal-title").text("Edit Account");
            $(".col-md-4").removeClass();
            $(".required").css("color","red");   
            $.ajax({
                url : '{{route("admin.bank-accounts.collect")}}',
                type : 'post',
                'data' : {
                    'id': id,
                    'bank_id': bank_id,
                    'company_id': company_id
                },
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                success:function(e)
                {
                    if(e.bank_name != "" && e.company_name != '')
                    {
                        $(".companyId").attr('value',e.company_name);
                        $(".bankName").attr('value',e.bank_name);
                        $(".accountNo").attr('value',e.account_no);
                        $(".ifscCode").attr('value',e.ifsc_code);
                        $(".branchName").attr('value',e.branch_name);
                        $(".Address").html(e.address);

                        $("#update").on('click',function(e)
                        {
                            e.preventDefault();
                            if($(".editBankAccountForm").valid())
                            {
                                $.ajax({
                                    url : "{{route('admin.bank-accounts.update')}}",
                                    type : 'POST',
                                    data : $("#editBankAccountForm").serialize(),
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    success: function(e)
                                    {
                                        if(e.data>0)
                                        {
                                            $(".account_table").DataTable().ajax.reload();
                                            $(".close").click();
                                            swal("Success","Account Updated Successfully","success");
                                        }
                                        else{
                                            $(".account_table").DataTable().ajax.reload();
                                            $(".close").click();
                                            swal("Error","Account No is already exists","error");
                                        }
                                    },
                                    error: function()
                                    {
                                        alert("Error");
                                    }
                                });
                            }
                        });
                    }
                    else{
                        alert("data not found");
                    }
                },
                error:function()
                {
                    alert("Error");
                }
            });
        });

        /*
        * Listing the Loan Commission 
        * Table loanCommissionDetails
        */
        
        // var plantable = $('#bankaccounts_table').DataTable({
        //     processing: true,
        //     serverSide: true,
        //     pageLength: 20,
        //     lengthMenu: [10, 20, 40, 50, 100],
        //     "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
        //         var oSettings = this.fnSettings ();
        //         $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
        //         return nRow;
        //     },
        //     ajax: {
        //         "url": "{{ route('admin.bank-accounts.listing') }}",
        //         "type": "post",
        //         headers: {
        //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //         },
        //     },
        //     columns: [
        //         {data: 'DT_RowIndex', name: 'DT_RowIndex'},
        //         {data: 'bank_id', name: 'bank_id'},
        //         {data: 'company_id', name: 'company_id'},
        //         {data: 'account_no', name: 'account_no'},
        //         {data: 'ifsc_code', name: 'ifsc_code'},
        //         {data: 'branch_name', name: 'branch_name',searchable:true,orderable:true},
        //         {data: 'address', name: 'address'},
        //         {data: 'status', name: 'status', 
        //             "render":function(data, type, row){
        //                 if(row.status==0){
        //                     return "<span class='badge badge-danger'>Inactive</span>";
        //                 }else{
        //                     return "<span class='badge badge-success'>Active</span>";
        //                 }
        //             }
        //         },
        //         // {data: 'head_id', name: 'head_id'},
        //         {data: 'created_at', name: 'created_at'},
        //         {data: 'action', name: 'action',orderable:false, searchable:false},
        //     ]
        // });

        // $(plantable.table().container()).removeClass( 'form-inline' );

        // $( document ).ajaxStart(function() {
        //     $( ".loader" ).show();
        // });

        // $( document ).ajaxComplete(function() {
        //     $( ".loader" ).hide();
        // });

        /*
        * Status Change of the Bank Listing
        * table Samraddh_banks
        */

        $(document).on('click','.status_data',function()
        {
            var id = $(this).data("id");
            var status = $(this).data("status");
            swal({
                title : 'Warning',
                type : 'warning',
                text : 'Are you sure want to change the status?',
                showDenyButton : true,
                showCancelButton : true,
            },function(isConfirm){
                if(isConfirm)
                {
                    $.ajax({
                    url : "{{ route('admin.bank-accounts.status') }}",
                    type: 'post',
                    data : {
                        'id' : id,
                        'status' : status,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success : function(e)
                    {
                        if(e.data)
                        {
                            $(".account_table").DataTable().ajax.reload();
                            swal("Success","Status changed successfully","success");
                        }
                        else
                        {
                            swal("Error","Status not changed","error");
                        }
                    }
                    });
                }
            });
        });
    });
</script>