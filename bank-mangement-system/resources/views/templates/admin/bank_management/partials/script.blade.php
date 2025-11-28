<script type="text/javascript">
    $(document).ready(function()
    {

        
        $("#company_id").addClass('company_id');
        $("#searchForm").find('sup').remove();

        $("#add_bank").on("click",function()
        {
            $(".modal-title").text("Add Bank");
            $(".bank_name").val('');
            $("#company_id").removeAttr("disabled");
            $(".required").css("color","red");
        });

        $(document).on("click","#add_bank",function()
        {
            $('.bankform').trigger('reset');
            $('#searchForm').trigger('reset');
            $('#searchCompanyId-error').html('');
            $('#addBankCompanyId-error').html('');
            $("#edit").addClass('d-none');
            $("#create").removeClass('d-none');
            $(".company_id").trigger('reset');
            $("#company_id").val("");
            $(".company_id").removeAttr("disabled");
            $("#bankform").find('.col-md-4').removeClass('col-md-4');
            $("#company_id-error").html("");
            $("#bank_name-error").html("");
        });

        $(document).on("click","#add_account",function()
        {
            $('#bankAccountForm').trigger('reset');
            $('.error').html("");
            $(".modal-title").text("Add Account");
            $("#bank_id").addClass('bank_id').find('option').not(':first').remove();
            $(".col-md-4").removeClass();
            $(".required").css("color","red");
        });

        
        $(document).on("click",".edit_data",function()
        {
            $("#company_id-error").html("");
            $("#bank_name-error").html("");
        });

        /*-------------- Search Form Validation ---------------*/
        $("#searchForm").validate({
            rules:{
                'bank_id' : 
                {
                    'required' : true,
                },
                'searchCompanyId': {
                    'required' : true,
                }
            },
            messages:{
                'bank_id' : 
                {
                    'required' : 'Select the bank name',
                },
                'searchCompanyId': {
                    'required' : 'Select the company name',
                }
            },
        });
        
        /*------------ Form Validations ---------------*/
        $("#bankform").validate({
            rules:{
                'bank_name' : 
                {
                    'required' : true,
                    'pattern' : /^[a-zA-z\s]+$/,
                },
                'addBankCompanyId': {
                    'required' : true,
                },
            },
            messages:{
                'bank_name' : 
                {
                    'required' : 'Bank name is required',
                    'pattern' : 'Enter only characters',
                },
                'addBankCompanyId': {
                    'required' : 'Select the company name',
                },
            },
        });

       
        
        $( document ).ajaxStart(function() {
                        $( ".loader" ).show();
                    });

                    $( document ).ajaxComplete(function() {
                        $( ".loader" ).hide();
                    });
        //--------------- Search the data -------------------
        $(document).on('click','#search',function(e)
            {
                $('.data_div').addClass('d-none');
                e.preventDefault();
                if($("#searchForm").valid())
                {
                    // plantable.draw();
                   

                    //listing the data

                    var plantable = $('.bank_table').DataTable({
                        processing: true,
                        bServerSide: true,
                        pageLength: 20,
                        lengthMenu: [10, 20, 40, 50, 100],
                        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
                            var oSettings = this.fnSettings ();
                            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                            return nRow;
                        },
                        ajax:{
                            "url": "{{ route('admin.bank.listing') }}",
                            "type": "POST",
                            "data" : {
                                'companyId' : $("#searchForm").find('select').find(':selected').val(),
                            },
                            headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                        },
                        columns: [
                            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                            {data: 'company_id', name: 'company_id'},
                            {data: 'bank_name', name: 'bank_name'},
                            
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
                            {data: 'created_by', name: 'created_by'},
                            {data: 'created_at', name: 'created_at',searchable:true, orderable:true},
                            {data: 'action', name: 'action',searchable:false,orderable:false},
                        ],
                        "bDestroy": true,"ordering": false,

                    });
                    $('.data_div').removeClass('d-none');
                    
                    $(plantable.table().container()).removeClass( 'form-inline' );

                    

                    
                    
                }else{
                    $('.data_div').addClass('d-none');
                }
            });



        //----------------- Create the data ---------------  
            $("#create").on("click",function(e)
            {
                e.preventDefault();
                if($(".bankform").valid())
                {
                    $.ajax({
                        url : "{{route('admin.bank.create')}}",
                        type : 'post',
                        data : $('#bankform').serialize(),
                        success: function(e)
                        {
                            if(e.data>0)
                            {
                                $('.data_div').removeClass('d-none');
                                $("#bank_table").DataTable().ajax.reload();
                                $(".close").click();
                                $('.searchForm').trigger('reset');
                                swal("Success","Bank Added Successfully","success");
                            }
                            else{
                                swal("Error","Bank already added in selected company","error");
                            }
                        }
                    });
                }
            });

        /**Edit the data */
        $(document).on('click','#edit-data',function(e)
        {
            e.preventDefault();
            $("#create").addClass("d-none");
            $("#edit").removeClass("d-none");
            $("#bankform").find('select').attr('id','companyId').addClass('company_id');
            $(".company_id").parent().prev('label').find('sup').remove();
            $(".bankform").trigger('reset');
            $(".modal-title").text("Edit Bank");
            $("#bankform").find('.col-md-4').removeClass('col-md-4');
            $(".required").css("color","red");      
            $.ajax({
                url : '{{route("admin.bank.fetch")}}',
                type : 'post',
                data : {'id': $(this).data('id')},
                success:function(e)
                {
                    if(e != "")
                    {
                        $(".bank_id").val(e.bank_id);
                        $(".bank_name").val(e.bank_name);
                        $("#companyId").attr('disabled',true).find('option').html(e.company_id).attr('selected','selected');

                    }

                    $("#edit").on('click',function(e)
                    {
                        e.preventDefault();
                        if($(".bankform").valid())
                        {
                            $.ajax({
                                url : "{{route('admin.bank.update')}}",
                                type : 'POST',
                                data : $('#bankform').serialize(),
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(e)
                                {
                                    if(e.data>0)
                                    {
                                        $(".bank_table").DataTable().ajax.reload();
                                        $(".close").click();
                                        $('.searchForm').trigger('reset');
                                        swal("Success","Bank Updated Successfully","success");
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
            });
        });

        /*
        * Listing the Loan Commission 
        * Table loanCommissionDetails
        */
        
        // var plantable = $('#bank_table').DataTable({
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
        //         "url": "{{ route('admin.bank.listing') }}",
        //         "type": "post",
        //         headers: {
        //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //         },
        //     },
        //     columns: [
        //         {data: 'DT_RowIndex', name: 'DT_RowIndex'},
        //         {data: 'bank_name', name: 'bank_name'},
        //         {data: 'company_id', name: 'company_id'},
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
        //         {data: 'created_by', name: 'created_by'},
        //         {data: 'created_at', name: 'created_at',searchable:true, orderable:true},
        //         {data: 'action', name: 'action',searchable:false,orderable:false},
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
                    url : "{{ route('admin.bank.status') }}",
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
                            $(".bank_table").DataTable().ajax.reload();
                            $('.searchForm').trigger('reset');
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

        /*------- Insert code of ADD ACCOUNT ------------------- */
        //Table name is samraddh_bank_accounts -------------------
        $("#insert").on('click',function(e)
        {
            e.preventDefault();
            if($("#bankAccountForm").valid())
            {
                $.ajax({
                    url : "{{route('admin.bank.add-account.create')}}",
                    type : 'POST',
                    data : {'company_id': $(".company_id").val(),
                    'bank_id': $('.bank_id').find('option').attr('value'),
                    'account_no': $('.account_no').val(),
                    'ifsc_code': $('.ifsc_code').val(),
                    'branch_name': $('.branch_name').val(),
                    'address' : $('.address').val()}, 
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
                            swal("Error","Account not added","error");
                        }
                    }
                });
            }
        });
    });
</script>