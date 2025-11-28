<script type="text/javascript">
    var fundTransferReportListing;
    var branchToHoTable;
    $(document).ready(function() {
        $('#company_id').change();
        jQuery('#fund-transfer-head-office').validate({
            rules: {
                date: {
                    required: true
                },
                branch_id: {
                    required: true
                },
                branch_code: {
                    required: true
                },
                company_id: {
                    required: true
                },
                transfer_mode: {
                    required: true
                },
                transfer_amount: {
                    required: true,
                    number: true,
                    checkAmount: true
                },
                conform_transfer_amount: {
                    required: true,
                    equalTo: "#transfer_amount"
                },
                bank: {
                    required: true
                },
                from_Bank_account_no: {
                    required: true
                },
                bank_slip: {
                    required: true
                }
            },
            submitHandler: function() {
                var transfer_amount = $("#transfer_amount").val();
                var micro_daybook_amount = $("#micro_daybook_amount").val();

                if (!(Math.round(micro_daybook_amount) >= Math.round(transfer_amount))) {
                    swal("Error!",
                        "Transfer Amount should be less than or equal to micro daybook amount!",
                        "error");
                    return false;
                }

                return true;

            }
        });



        $('#company_id').on('change', function() {
            let companyId = $('#company_id option:selected').val();
            var bank_id = $(this).val();



            $.ajax({
                type: "POST",
                url: "{{ route('admin.fetchbranchbycompanyBank') }}",
                data: {
                    'company_id': companyId,
                    'bank': 'true',
                    'branch': 'no',
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    let myObj = JSON.parse(response);
                    if (myObj.bank) {
                        var optionBank =
                            `<option value="">----Please Select Bank---</option>`;
                        myObj.bank.forEach(element => {
                            optionBank +=
                                `<option value="${element.id}">${element.bank_name}</option>`;
                        });
                        $('#bank').html(optionBank);

                        $('#bank_ac').find('option').remove();
                        $('#bank_ac').append(
                            '<option value="">Select account number</option>');



                    }
                }
            })
        });


        $('#bank').on('change', function() {
            var bank_id = $(this).val();
            $.ajax({
                url: "{!! route('admin.bank_accountNumber') !!}",
                type: "POST",
                dataType: 'JSON',
                data: {
                    'bank_id': bank_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log(response);
                    $('#bank_ac').find('option').remove();
                    $('#bank_ac').append(
                        '<option value="">Select account number</option>');
                    $.each(response.account, function(index, value) {
                        $("#bank_ac").append("<option value='" + value.id +
                            "'>" + value.account_no + "</option>");
                    });
                }
            })
        })

        // Delete Bank to Bank Confirm 
        $(document).on('click', '.delete-bank-to-bank-transfer', function (e) {
            var url = $(this).attr('href');
            e.preventDefault();
            swal({
                title: "Are you sure, you want to delete request?",
                text: "",
                icon: "warning",
                buttons: [
                    'No, cancel it!',
                    'Yes, I am sure!'
                ],
                dangerMode: true,
            }).then(function (isConfirm) {
                if (isConfirm) {
                    location.href = url;
                }
            });
        })




        //	
        //Delete Branch to Ho Confirm 
        $(document).on('click', '.delete-branch-to-ho', function(e) {
            var url = $(this).attr('href');
            e.preventDefault();
            swal({
                title: "Are you sure, you want to delete request?",
                text: "",
                icon: "warning",
                buttons: [
                    'No, cancel it!',
                    'Yes, I am sure!'
                ],
                dangerMode: true,
            }).then(function(isConfirm) {
                if (isConfirm) {
                    location.href = url;
                }
            });
        })

        //	
        $('#edit-fund-transfer-head-office').validate({
            rules: {
                date: {
                    required: true
                },
                branch_id: {
                    required: true
                },
                branch_code: {
                    required: true
                },
                transfer_mode: {
                    required: true
                },
                transfer_amount: {
                    required: true,
                    checkAmount: true,
                    number: true
                },
                conform_transfer_amount: {
                    required: true,
                    equalTo: "#transfer_amount"
                },
                bank: {
                    required: true
                },
                from_Bank_account_no: {
                    required: true
                },
            },
            submitHandler: function() {
                var paymentModeVal = $("#transfer_mode option:selected").val();
                var transferAmount = $("#transfer_amount").val();
                if (paymentModeVal == 0) {
                    var DaybookAmount = $("#loan_daybook_amount").val();
                    if (parseInt(transferAmount) > parseInt(loanDaybookAmount)) {
                        swal("Error!",
                            "Transfer Amount should be less than or equal to loan daybook amount!",
                            "error");
                        return false;
                    }
                }
                if (paymentModeVal == 1) {
                    var microDaybookAmount = $("#micro_daybook_amount").val();
                    if (parseInt(transferAmount) > parseInt(microDaybookAmount)) {
                        swal("Error!",
                            "Transfer Amount should be less than or equal to micro daybook amount!",
                            "error");
                        return false;
                    }
                }

                return true;

            }
        });

        $("#start_date,#end_date").hover(function() {
            const EndDate = $('#create_application_date').val();
            $('#start_date,#end_date').datepicker({
                format: "dd/mm/yyyy",
                orientation: "bottom",
                autoclose: true,
                endDate: EndDate,
            });
        })

        $("#date").hover(function() {
            const EndDate = $('#create_application_date').val();
            $('#date').datepicker({
                format: "dd/mm/yyyy",
                orientation: "bottom",
                autoclose: true,
                endDate: EndDate,
            });
        })

        // branch to ho listing
        // branchToHoTable = $('#head_logs_listing').DataTable({
           
        //     processing: true,
        //     serverSide: true,
        //     pageLength: 20,
        //     lengthMenu: [10, 20, 40, 50, 100],
        //     "fnRowCallback": function(nRow, aData, iDisplayIndex) {
        //         var oSettings = this.fnSettings();
        //         $('html, body').stop().animate({
        //             scrollTop: ($('#head_logs_listing').offset().top)
        //         }, 10);
        //         $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
        //         return nRow;
        //     },
           
        //     ajax: {
        //         "url": "{!! route('admin.head_logs.listing') !!}",
        //         "type": "POST",
        //         "data": function(d) {
        //             d.searchBranchToHo = $('form#filter').serializeArray()
        //         },
        //         headers: {
        //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //         },
        //     },
        //     "columnDefs": [{
        //         "render": function(data, type, full, meta) {
        //             return meta.row + 1; // adds id to serial no
        //         },
        //         "targets": 0
        //     }],
        //     columns: [{
        //             title: 'S/N'
        //         },
        //         {
        //             data: 'parent_id',
        //             name: 'parent_id'
        //         },
        //         {
        //             data: 'head_id',
        //             name: 'head_id'
        //         },
        //         {
        //             data: 'type',
        //             name: 'type'
        //         },

        //         {
        //             data: 'company_id',
        //             name: 'company_id'
        //         },
        //         // {
        //         //     data: 'branch_code',
        //         //     name: 'branch_code'
        //         // },
              
        //         // {data: 'transfer_mode', name: 'transfer_mode'},
        //         {
        //             data: 'description',
        //             name: 'description'
        //         },
                
        //         {
        //             data: 'created_by',
        //             name: 'created_by'
        //         },
        //         {
        //             data: 'created_at',
        //             name: 'created_at'
        //         },
        //         {
        //             data: 'action',
        //             name: 'action',
        //             orderable: false,
        //             searchable: false
        //         },
                
        //     ]
        // });

        branchToHoTable = $('#head_logs_listing').DataTable({
    processing: true,
    serverSide: true,
    pageLength: 20,
    lengthMenu: [10, 20, 40, 50, 100],
    "fnRowCallback": function(nRow, aData, iDisplayIndex) {
        var oSettings = this.fnSettings();
        $('html, body').stop().animate({
            scrollTop: ($('#head_logs_listing').offset().top)
        }, 10);
        $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
        return nRow;
    },
    ajax: {
        "url": "{!! route('admin.head_logs.listing') !!}",
        "type": "POST",
        "data": function(d) {
            d.searchBranchToHo = $('form#filter').serializeArray()
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
    },
    "order": [
        [0, 'desc'] // Assuming the first column is 'id', change it if necessary
    ],
    "columnDefs": [{
        "render": function(data, type, full, meta) {
            return meta.row + 1; // adds id to serial no
        },
        "targets": 0
    }],
    columns: [{
            title: 'S/N'
        },
        {
            data: 'parent_id',
            name: 'parent_id'
        },
        {
            data: 'head_id',
            name: 'head_id'
        },
        {
            data: 'type',
            name: 'type'
        },
        {
            data: 'company_id',
            name: 'company_id'
        },
        {
            data: 'description',
            name: 'description'
        },
        {
            data: 'created_by',
            name: 'created_by'
        },
        {
            data: 'created_at',
            name: 'created_at'
        },
        {
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false
        },
    ]
});
 
        $(branchToHoTable.table().container()).removeClass('form-inline');

        jQuery.validator.addMethod("notEqual", function(value, element, param) {
            return this.optional(element) || value != $(param).val();
        }, "Please select another bank");


        jQuery('#fund-transfer-bank').validate({
            rules: {
                from_bank: {
                    required: true,
                    notEqual: "#to_bank"
                },
                to_bank: {
                    required: true,
                    notEqual: "#from_bank"
                },
                from_Bank_account_no: {
                    required: true
                },
                to_Bank_account_no: {
                    required: true
                },
                from_cheque_number: {
                    required: true
                },
                to_cheque_number: {
                    required: true
                },
                //  to_cheque_number:{
                //     required: true,
                //     equalTo: "#from_utr_number"
                // },
                rtgs_neft_charge: {
                    required: true,
                    number: true,
                },
                bank_transfer_amount: {
                    required: true,
                    number: true,
                    checkAmount: true,
                },
                bank_receive_amount: {
                    required: true
                },
                remark: {
                    required: true
                },
            },
            submitHandler: function() {
                var amount = $('#bank_transfer_amount').val();
                var bank_balance = $('#bank-current-balance').val();

                if (Math.round(amount) > Math.round(bank_balance)) {
                    $('#bank_receive_amount').val(amount);
                    swal("Warning!", "Insufficient balance!", "warning");
                    event.preventDefault();
                    return false;
                } else {
                    return true;
                }

            }
        })

        jQuery('#edit-fund-transfer-bank').validate({
            rules: {
                from_bank: {
                    required: true,
                    notEqual: "#to_bank"
                },
                to_bank: {
                    required: true,
                    notEqual: "#from_bank"
                },
                from_Bank_account_no: {
                    required: true
                },
                to_Bank_account_no: {
                    required: true
                },
                from_cheque_number: {
                    required: true
                },
                to_cheque_number: {
                    required: true,
                    //equalTo: "#from_cheque_number"
                },
                rtgs_neft_charge: {
                    required: true,
                    number: true,
                },
                bank_transfer_amount: {
                    required: true,
                    number: true,
                    checkAmount: true,
                },
                bank_receive_amount: {
                    required: true,
                    //equalTo: "#bank_transfer_amount"
                },
                remark: {
                    required: true
                },
            }
        })

        $(document).on('change', '#fund_transfer', function() {
            var fundSection = $(this).val();
            if (fundSection == 0) {
                $('#branch-to-ho').css("display", "block");
                $('#bank-to-bank').css("display", "none");
            } else {
                $('#branch-to-ho').css("display", "none");
                $('#bank-to-bank').css("display", "block");
            }
        });

        // $(document).on('change', '#from_bank', function() {
        //     var account = $('option:selected', this).val();
        //     $('#from_Bank_account_no').val('');
        //     $('.from-bank-account').hide();

        //     $('.' + account + '-from-bank-account').show();
        // });

        $(document).on('change', '#from_Bank_account_no', function() {
            var account = $('option:selected', this).attr('data-account');
            /*var bank = $('option:selected', '#from_bank).val();*/

            $('#from_cheque_number').val('');
            $('.from_cheque').hide();

            $('.' + account + '-from_cheque').show();

        });

        $(document).on('change', '#bank_transfer_amount', function() {
            var amount = $(this).val();
            $('#bank_receive_amount').val(amount);

        });

        $(document).on('change', '#from_bank', function() {
            var fromBankId = $(this).val();
            var date = $('#date').val();

            if (date == '') {
                var branch = $('#from_bank').val('');
                $('.from-bank-account').hide();
                swal("Warning!", "Please select a transfer date first!", "warning");
                return false;
            }

            $.ajax({
                type: "POST",
                url: "{!! route('admin.getbankdaybookamount') !!}",
                dataType: 'JSON',
                data: {
                    'fromBankId': fromBankId,
                    'date': date
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#bank-current-balance').val(response.bankDayBookAmount);
                }
            });

        });

        $("#fund-transfer-bank").submit(function(event) {
            var amount = $('#bank_transfer_amount').val();
            var bank_balance = $('#bank-current-balance').val();

            if (Math.round(amount) > Math.round(bank_balance)) {
                $('#bank_receive_amount').val(amount);
                swal("Warning!", "Insufficient balance!", "warning");
                event.preventDefault();
            } else {
                $('#bank_receive_amount').val(amount);
                //$('#msg').remove();
                return true;
            }

        });

        $("#edit-fund-transfer-bank").submit(function(event) {
            var amount = $('#bank_transfer_amount').val();
            var bank_balance = $('#bank-current-balance').val();

            if (Math.round(amount) > Math.round(bank_balance)) {
                $('#bank_receive_amount').val(amount);
                $('#msg').text('Insufficient balance');
                event.preventDefault();
            } else {
                $('#bank_receive_amount').val(amount);
                $('#msg').remove();
                return true;
            }

        });

        // $(document).on('change', '#to_bank', function() {
        //     var account = $('option:selected', this).val();
        //     var date = $('#date').val();
        //     if (date == '') {
        //         $('#to_bank').val('');
        //         $('.to-bank-account').hide();
        //         swal("Warning!", "Please select a transfer date first!", "warning");
        //         return false;
        //     }
        //     $('#to_Bank_account_no').val('');
        //     $('.to-bank-account').hide();
        //     $('.' + account + '-to-bank-account').show();
        // });

        /*$(document).on('change','#to_Bank_account_no', function () {
            var account = $('option:selected', '#to_bank').val();
            var date = $('#date').val();
            var toBankId = $('#to_bank').val();
            var accountNumber = $(this).val();
            $.ajax({
                type: "POST",  
                url: "{!! route('admin.gettobankrecord') !!}",
                dataType: 'JSON',
                data: {'date':date,'toBankId':toBankId,'accountNumber':accountNumber},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if(response.resCount == 0){
                        swal("Warning!", "Bank Account not available before this date!", "warning");
                        $('#to_bank').val('');
                        $('#to_Bank_account_no').val('');
                        $('.to-bank-account').hide();
                        $('.'+account+'-to-bank-account').show();
                    }
                }
            });
        });*/

        // $(document).on('change', '#bank', function() {
        //     var account = $('option:selected', this).val();
        //     var date = $('#date').val();
        //     if (date == '') {
        //         $('#bank').val('');
        //         $('.from_Bank_account_no').hide();
        //         swal("Warning!", "Please select a transfer date first!", "warning");
        //         return false;
        //     }
        //     $('#from_Bank_account_no').val('');
        //     $('.bank-account').hide();
        //     $('.' + account + '-bank-account').show();
        // });

        // $(document).on('change','#from_Bank_account_no', function () {
        //     var account = $('option:selected', '#bank').val();
        //     var date = $('#date').val();
        //     var toBankId = $('#bank').val();
        //     var accountNumber = $(this).val();
        //     $.ajax({
        //         type: "POST",  
        //         url: "{!! route('admin.gettobankrecord') !!}",
        //         dataType: 'JSON',
        //         data: {'date':date,'toBankId':toBankId,'accountNumber':accountNumber},
        //         headers: {
        //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //         },
        //         success: function(response) {
        //             if(response.resCount == 0){
        //                 swal("Warning!", "Bank Account not available before ths date!", "warning");
        //                 $('#bank').val('');
        //                 $('#from_Bank_account_no').val('');
        //                 $('.bank-account').hide();
        //                 $('.'+account+'-bank-account').show(); 
        //             }
        //         }
        //     });
        // });

        memberTable = $('#fund_transfer_listing').DataTable({

            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#fund_transfer_listing').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },

            ajax: {
                "url": "{!! route('admin.fund.transfer.listing') !!}",
                "type": "POST",
                "data": function(d) {
            d.searchIndex = $('form#filter').serializeArray();
            d.status = $('#status').val(); // Add the status to the data
        },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            "columnDefs": [{
                "render": function(data, type, full, meta) {
                    return meta.row + 1; // adds id to serial no
                },
                "targets": 0
            }],
            columns: [{
                    title: 'S/N'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'company',
                    name: 'company'
                },
                {
                    data: 'from_bank',
                    name: 'from_bank'
                },
                {
                    data: 'from_bank_account_number',
                    name: 'from_bank_account_number'
                },
                {
                    data: 'to_bank',
                    name: 'to_bank'
                },
                {
                    data: 'bank_account_number',
                    name: 'bank_account_number'
                },
                {
                    data: 'transfer_amount',
                    name: 'transfer_amount'
                },
                {
                    data: 'transfer_mode',
                    name: 'transfer_mode'
                },
                {
                    data: 'cheque_no',
                    name: 'cheque_no'
                },
                {
                    data: 'utr_no',
                    name: 'utr_no'
                },
                {
                    data: 'rtgs_neft_charges',
                    name: 'rtgs_neft_charges'
                },
                {
                    data: 'remark',
                    name: 'remark'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });
        // Show chequw or utr number

        $(document).on('change', '#transfer_mode', function() {
            var cheque_no = $(this).val();
            if (cheque_no == 0 && cheque_no != '') {
                $('#cheque_no').css("display", "flex");
                $('.utr_no').css("display", "none");

            } else if (cheque_no == 1 && cheque_no != '') {
                $('#cheque_no').css("display", "none");
                $('.utr_no').css("display", "flex");
            } else {
                $('#cheque_no').css("display", "none");
                $('.utr_no').css("display", "none");
            }

        });
        // Received Cheque number show auto
        $(document).on('change', '#from_cheque_number', function() {
            var cheque_no = $('option:selected', this).attr('data-cheque');
            $('#to_cheque_number').val(cheque_no);


        });
        $(document).on('change', '#from_utr_number', function() {
            var utr_no = $(this).val();
            $('#to_cheque_number').val(utr_no);


        });

        $(document).on('change', '#date', function() {
            var account = $('option:selected', '#to_bank').val();
            var bankaccount = $('option:selected', '#bank').val();
            $('#to_bank').val('');
            $('#to_Bank_account_no').val('');
            $('.to-bank-account').hide();
            $('.' + account + '-to-bank-account').show();

            $('#bank').val('');
            $('#from_Bank_account_no').val('');
            $('.bank-account').hide();
            $('.' + bankaccount + '-bank-account').show();
        });

        // branch to ho show branch code autoto_cheque_number
        $(document).on('change', '#branch,#date', function() {
            var branchId = $("#branch option:selected").val();
            var editId = 0;
            var date = $('#date').val();
            var company_id = $('#company_id').val();

            if (date == '') {
                var branch = $('#branch').val('');
                swal("Warning!", "Please select a transfer date first!", "warning");
                return false;
            } else {
                var branch_code = $('option:selected', "#branch").attr('data-value');
                $('#branch_code').val(branch_code);
            }
            $.ajax({
                type: "POST",
                url: "{!! route('admin.branchBankBalance') !!}",
                dataType: 'JSON',
                data: {
                    'branch_id': branchId,
                    'entrydate': date,
                    'company_id': company_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // alert(response.balance);
                    $('#micro_daybook_amount').val(response.balance);
                }
            });

        });

        // branch to ho show branch code auto
        $(document).on('change', '#branch_name', function() {
            var branchCode = $('option:selected', this).attr('data-value');
            $('#filter_branch_code').val(branchCode);
        });

        $(document).on('change', '#edit_branch', function() {
            var branchId = $(this).val();
            var editId = $('#id').val();
            var date = $('#date').val();
            // var company_id = $('#company_id').val();
            var branch_code = $('option:selected', this).attr('data-value');
            $('#branch_code').val(branch_code);
            /*
                        $.ajax({
                            type: "POST",  
                            url: "{!! route('admin.getdaybookamount') !!}",
                            dataType: 'JSON',
                            data: {'branchId':branchId,'editId':editId,'date':date},
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                // $('#loan_daybook_amount').val(response.loanDayBookAmount);
                                $('#micro_daybook_amount').val(response.microDayBookAmount);
                            }
                        });
            */
            $.ajax({
                type: "POST",
                url: "{!! route('admin.branchBankBalanceAmount') !!}",
                dataType: 'JSON',
                data: {
                    'branch_id': branchId,
                    'entrydate': date
                    // 'company_id': company_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // alert(response.balance);
                    $('#micro_daybook_amount').val(response.balance);
                }
            });
        });

        var bankToBankTable = $('#bank_to_bank_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#bank_to_bank_listing').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.fundtransfer.banktobranchlisting') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            "columnDefs": [{
                "render": function(data, type, full, meta) {
                    return meta.row + 1; // adds id to serial no
                },
                "targets": 0
            }],
            columns: [{
                    title: 'S/N'
                },
                {
                    data: 'transfer_type',
                    name: 'transfer_type'
                },
                {
                    data: 'transfer_date_time',
                    name: 'transfer_date_time'
                },
                {
                    data: 'transfer_mode',
                    name: 'transfer_mode'
                },
                {
                    data: 'head_office_bank_account_number',
                    name: 'head_office_bank_account_number'
                },
                {
                    data: 'from_bank_account_number',
                    name: 'from_bank_account_number'
                },
                {
                    data: 'to_bank_account_number',
                    name: 'to_bank_account_number'
                },
                {
                    data: 'from_cheque_utr_no',
                    name: 'from_cheque_utr_no'
                },
                {
                    data: 'transfer_amount',
                    name: 'transfer_amount'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                /* {data: 'action', name: 'action',orderable: false, searchable: false},*/
            ]
        });
        $(bankToBankTable.table().container()).removeClass('form-inline');

        // Report Data
        fundTransferReportListing = $('#report_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#report_listing').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.fund-transfer.report_lisiting') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'request_type',
                    name: 'request_type'
                },
                {
                    data: 'company',
                    name: 'company'
                },
                {
                    data: 'branch_name',
                    name: 'branch_name'
                },
                {
                    data: 'branch_code',
                    name: 'branch_code'
                },
                // {data:'loan_day_book_amount',name:'loan_day_book_amount'},
                {
                    data: 'micro_day_book_amount',
                    name: 'micro_day_book_amount'
                },
                {
                    data: 'transfer_amount',
                    name: 'transfer_amount'
                },
                {
                    data: 'transfer_date_time',
                    name: 'transfer_date_time'
                },
                {
                    data: 'transaction_no',
                    name: 'transaction_no'
                },
                {
                    data: 'from_bank_name',
                    name: 'from_bank_name'
                },
                {
                    data: 'from_bank_account_number',
                    name: 'from_bank_account_number'
                },
                {
                    data: 'transfer_mode',
                    name: 'transfer_mode'
                },
                {
                    data: 'cheque_no',
                    name: 'cheque_no'
                },
                {
                    data: 'rtgs_neft_charge',
                    name: 'rtgs_neft_charge'
                },
                /*{data: 'receive_bank_name', name: 'receive_bank_name'},*/
                {
                    data: 'receive_bank_acc',
                    name: 'receive_bank_acc'
                },
                {
                    data: 'receive_cheque_no',
                    name: 'receive_cheque_no'
                },
                {
                    data: 'receive_amount',
                    name: 'receive_amount'
                },
                {
                    data: 'request_date',
                    name: 'request_date'
                },
                {
                    data: 'bank_slip',
                    name: 'bank_slip'
                },
                // {data: 'approve_reject_date', name: 'approve_reject_date'},
                {
                    data: 'remark',
                    name: 'remark'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                /* {data: 'action', name: 'action',orderable: false, searchable: false},*/
            ]
        });
        $(fundTransferReportListing.table().container()).removeClass('form-inline');

        let branchid = $("#hbranchid option:selected").val();
        $(document).on('click', '.approve-request', function() {
            var href = $(this).attr('href');
            $(this).attr('href', href + '/' + branchid);
        });

        // End
        $(document).ajaxStart(function() {
            $(".loader").show();
        });

        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
    });

    $('.export').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            var formData = jQuery('#cron_filter').serializeObject();
            console.log(formData);
            var chunkAndLimit = 50;
            $(".spiners").css("display", "block");
            $(".loaders").text("0%");
            doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
            $("#cover").fadeIn(100);
        });

    // function to trigger the ajax bit
    // function doChunkedExport(start, limit, formData, chunkSize) {
    //     formData['start'] = start;
    //     formData['limit'] = limit;
    //     jQuery.ajax({
    //         type: "post",
    //         dataType: "json",
    //         url: "{!! route('admin.logs.listing') !!}",
    //         data: formData,
    //         success: function(response) {
    //             console.log(response);
    //             if (response.result == 'next') {
    //                 start = start + chunkSize;
    //                 doChunkedExport(start, limit, formData, chunkSize);
    //                 $(".loaders").text(response.percentage + "%");
    //             } else {
    //                 var csv = response.fileName;
    //                 console.log('DOWNLOAD');
    //                 $(".spiners").css("display", "none");
    //                 $("#cover").fadeOut(100);
    //                 window.open(csv, '_blank');
    //             }
    //         }
    //     });
    // }



    function doChunkedExport(start, limit, formData, chunkSize) {
        formData['start'] = start;
        formData['limit'] = limit;
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: "{!! route('admin.logs.listing') !!}",
            data: formData,
            success: function(response) {
                console.log(response);
                if (response.result == 'next') {
                    start = start + chunkSize;
                    doChunkedExport(start, limit, formData, chunkSize);
                    $(".loaders").text(response.percentage + "%");
                } else {
                    var csv = response.fileName;
                    console.log('DOWNLOAD');
                    $(".spiners").css("display", "none");
                    $("#cover").fadeOut(100);
                    window.open(csv, '_blank');
                }
            }
        });
    }

    jQuery.fn.serializeObject = function() {
        var o = {};
        var a = this.serializeArray();
        jQuery.each(a, function() {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };
    // Filter
    function searchForm() {
        if ($('#filter').valid()) {
            $('#is_search').val("yes");
            $(".table-section").removeClass('hideTableData');
            fundTransferReportListing.draw();
        }
    }

    function searchheadlogs() {
        if ($('#filter').valid()) {
            $('#is_search').val("yes");
            $(".table-section").addClass("show-table");
            branchToHoTable.draw();
        }
    }

    function searchBranchToBank() {
        if ($('#filter').valid()) {
            $('#is_search').val("yes");
            $(".table-section").addClass("show-table");
            memberTable.draw();
        }
    }

    function searchIndex() {
        if ($('#filter').valid()) {
            $('#is_search').val("yes");
            $(".table-section").addClass("show-table");
            memberTable.draw();
        }
    }
    jQuery('#filter').validate({
        rules: {
            company_id: {
                required: true
            },
        },
        messages: {
            company_id: {
                required: 'Please select company'
            },
        }
    });

    function resetForm() {
        var form = $("#filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#is_search').val("no");
        $('#start_date').val('');
        $('#end_date').val('');
        $('#branch_name').val('');
        $('#filter_branch_code').val('');
        $('#category').val('');
        $('#company_id').val('');
        $('#status').val('');
        $(".table-section").addClass("hideTableData");
        fundTransferReportListing.draw();
    }

    function resetFormHo() {
        var form = $("#filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#is_search').val("no");
        $('#company_id').val('0').trigger('change');
        $('#status').val('');
        $('#start_date').val('');
        $('#end_date').val('');
        $(".table-section").removeClass("show-table");
        $(".table-section").addClass("hide-table");
    }

    function resetFormBank() {
        var form = $("#filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#is_search').val("no");
        $('#company_id').val('');
        $(".table-section").removeClass("show-table");
        $(".table-section").addClass("hide-table");
    }




    // BRANCH TO HO FUND TRANSFER FUNCTION
    function statusUpdate(id, status, branchId, companyId) {
        swal({
                title: "Confirm Update",
                text: "Are you sure you want to Approve Request?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willUpdate) => {
                if (willUpdate) {
                    let url =
                        "{{ route('admin.fund.transfer.updateStatus', ['id' => ':id', 'status' => ':status', 'branchid' => ':branchId', 'companyId' => ':companyId']) }}";
                    url = url.replace(':id', id).replace(':status', status).replace(':branchId', branchId).replace(
                        ':companyId', companyId);

                    $.ajax({
                        type: "GET",
                        url: url,
                        dataType: 'json',
                        data: {
                            'id': id,
                            'status': status,
                            'branchid': branchId,
                            'companyId': companyId
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response) {
                                branchToHoTable.draw();
                                // swal("Success", "Payment Approved successfully!", "success");
                            } else {
                                swal("Error", "Something went wrong. Try again!", "warning");
                            }
                        },
                        error: function() {
                            swal("Error", "Something went wrong. Try again!", "warning");
                        }
                    });
                } else {
                    // swal("Cancelled", "REquest Reject.", "info");
                }
            });
    }
    // BRANCH TO HO FUND TRANSFER FUNCTION END

    // BANK TO BANK FUND TRANSFER
    function statusUpdateBtoB(id, status, branchId, companyId) {
        swal({
                title: "Confirm Update",
                text: "Are you sure you want to Approve Request?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willUpdate) => {
                if (willUpdate) {
                    let url =
                        "{{ route('admin.fund.transfer.updateStatus', ['id' => ':id', 'status' => ':status', 'branchid' => ':branchId', 'companyId' => ':companyId']) }}";
                    url = url.replace(':id', id).replace(':status', status).replace(':branchId', branchId).replace(
                        ':companyId', companyId);

                    $.ajax({
                        type: "GET",
                        url: url,
                        dataType: 'json',
                        data: {
                            'id': id,
                            'status': status,
                            'branchid': branchId,
                            'companyId': companyId
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response) {
                                memberTable.draw();
                                // swal("Success", "Payment Approved successfully!", "success");
                            } else {
                                swal("Error", "Something went wrong. Try again!", "warning");
                            }
                        },
                        error: function() {
                            swal("Error", "Something went wrong. Try again!", "warning");
                        }
                    });
                } else {
                    // swal("Cancelled", "REquest Reject.", "info");
                }
            });
    }
    // BANK TO BANK FUND TRANSFER END

    $('.export').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            var formData = jQuery('#cron_filter').serializeObject();
            console.log(formData);
            var chunkAndLimit = 50;
            $(".spiners").css("display", "block");
            $(".loaders").text("0%");
            doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
            $("#cover").fadeIn(100);
        });

    	// function to trigger the ajax bit
        function doChunkedExporsst(start,limit,formData,chunkSize){
        formData['start']  = start;
        formData['limit']  = limit;
      
        jQuery.ajax({
            type : "get",
            dataType : "json",
            url :  "{!! route('admin.logs.listing') !!}",
            data : formData,
            success: function(response) {
                console.log(response);
                if(response.result=='next'){
                    start = start + chunkSize;
                    doChunkedExporsst(start,limit,formData,chunkSize);
					$(".loaders").text(response.percentage+"%");
                }else{
					var csv = response.fileName;
                    console.log('DOWNLOAD');
					$(".spiners").css("display","none");
					$("#cover").fadeOut(100); 
					window.open(csv, '_blank');
                }
            }
        });
    }
</script>
<!-- admin  -->
