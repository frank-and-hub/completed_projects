<script type="text/javascript">
    var bankLedger;
    $(document).ready(function() {
        var date = new Date();

        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: 'bottom',
            todayHighlight: true,
            autoclose: true,
            endDate: date,
        })

        $('#end_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: 'bottom',
            todayHighlight: true,
            autoclose: true,
            endDate: date,

        })



        $('#filter').validate({
            rules: {
                bank_name: {
                    required: true,
                },
                bank_account: {
                    required: true,
                },
                start_date: {
                    required: true,
                },
                end_date: {
                    required: true,
                },
                company_id: {
                    required: true,
                },
            },

            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass(' ');
                element.closest('.error-msg').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
                if ($(element).attr('type') == 'radio') {
                    $(element.form).find("input[type=radio]").each(function(which) {
                        $(element.form).find("label[for=" + this.id + "]").addClass(
                            errorClass);
                        $(this).addClass('is-invalid');
                    });
                }
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
                if ($(element).attr('type') == 'radio') {
                    $(element.form).find("input[type=radio]").each(function(which) {
                        $(element.form).find("label[for=" + this.id + "]").addClass(
                            errorClass);
                        $(this).removeClass('is-invalid');
                    });
                }
            }
        });
        $('.export').on('click', function() {
            var extension = $(this).attr('data-extension');
            $('#fund_transfer_export').val(extension);
            $('form#filter').attr('action', "{!! route('admin.bankLedger.report.export') !!}");
            $("form#filter").submit();
            return true;

        });

        $('#bank_name').on('change', function() {
            var bank_id = $('option:selected', this).val();
            var bank_name = $('option:selected', this).text();

            $.ajax({
                type: "POST",
                url: "{!! route('admin.bank_account_list.inactive') !!}",
                dataType: 'JSON',
                data: {
                    bank_id: bank_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response) {
                        $('#bank_account').find('option').remove();
                        $('#bank_account').append(
                            '<option value="">Select Bank Account</option>');
                        $.each(response.account, function(index, value) {
                            $("#bank_account").append("<option value='" + value.id +
                                "'>" + value.account_no + "</option>");
                        });
                    }


                }
            })
            $('#name').val(bank_name);
        })
        // $('#bank_account').on('change',function(){
        //     var bank_account = $('option:selected',this).val();
        //     var bank_id = $('option:selected','#bank_name').val();

        //     if(bank_id !='' && bank_account == '')
        //     {
        //         $('#msg').html("Please Select Bank Account Number");
        //         $('#submit').attr('disabled',true);
        //     }
        //     else{
        //          $('#submit').attr('disabled',false);
        //         search();
        //     }
        // })
        bankLedger = $('#bank_ledger').DataTable({
            processing: true,
            serverSide: true,
            bFilter: false,
            ordering: false,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            responsive: true,
            pagination: false,
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();

                $('html, body').stop().animate({
                    scrollTop: ($('#bank_ledger').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },

            ajax: {
                "url": "{!! route('admin.bank-ledger.report.listing') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray(), d.balance = 0
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            'createdRow': function(row, data, dataIndex) {
                if (data.particular === 'Opening_Balance') {
                    // Add COLSPAN attribute
                    $('td:eq(0)', row).removeAttr('class');

                }
            },

            // "columnDefs": [{
            //             { targets: [1,2,3], orderable: false, searchable: false }


            // }],

            columns: [{
                    data: 'Sr_no',
                    name: 'Sr_no'
                },
                {
                    data: 'company',
                    name: 'company'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'branch_code',
                    name: 'branch_code'
                },
                {
                    data: 'branch_name',
                    name: 'branch_name'
                },
                {
                    data: 'member_name',
                    name: 'member_name'
                },
                // {data: 'm_account', name: 'm_account'},
                {
                    data: 'account_number',
                    name: 'account_number'
                },
                {
                    data: 'particular',
                    name: 'particular'
                },
                // {data: 'account_head_no', name: 'account_head_no'},
                // {data: 'account_head_name', name: 'account_head_name'},
                {
                    data: 'cheque_no',
                    name: 'cheque_no'
                },
                {
                    data: 'credit',
                    name: 'credit'
                },
                {
                    data: 'debit',
                    name: 'debit'
                },
                {
                    data: 'balance',
                    name: 'balance',
                    "render": function(data, type, row) {
                        return " <img src='{{ url('/') }}/asset/images/rs.png' width='7'>" +
                            row.balance;
                    }
                },

            ],"ordering": false,

        });

        $(bankLedger.table().container()).removeClass('form-inline');



    });
    $(document).ajaxStart(function() {
        $(".loader").show();

    });

    $(document).ajaxComplete(function() {
        $(".loader").hide();
    });
    /*function openingbalance(){
       
       $("#bank_ledger #opening").hide();
      $("#bank_ledger #closing").hide();
       var start_date = $('#start_date').val();
        var created_date = $('#created_at').val();
         var bank_id = $('#bank_name').val();
        $.ajax({
                 type: "POST",  
              url: "{!! route('admin.bank-ledger.balance') !!}",
                 dataType: 'JSON',
                 data:{start_date:start_date,date:created_date,bank_id:bank_id},
                 headers: {
                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                 },
                 success: function(response) { 
                   
                     if(response)
                     {
                    
                       var  b = $("#bank_ledger").prepend('<tbody id="opening"><tr><td></td><td></td><td></td><td></td><td></td><td>Opening Balance</td><td></td><td></td><td></td><td></td><td></td><td>'+response.opening_balance+'</td></tr></tbody>');
                         
                     }
                  
               }
          })
       }
       
       function closingbalance(){
    
      $("#bank_ledger #opening").hide();
      $("#bank_ledger #closing").hide();
       var end_date = $('#end_date').val();
        var created_date = $('#created_at').val();
         var bank_id = $('#bank_name').val();
        $.ajax({
                 type: "POST",  
                 url: "{!! route('admin.bank-ledger.closingbalance') !!}",
                 dataType: 'JSON',
                 data:{end_date:end_date,date:created_date,bank_id:bank_id},
                 headers: {
                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                 },
                 success: function(response) { 
                    
                     if(response)
                     {
                    
                       var  b = $("#bank_ledger").last().append('<tbody id="closing"><tr><td></td><td></td><td></td><td></td><td></td><td>Closing Balance</td><td></td><td></td><td></td><td></td><td></td><td>'+response+'</td></tr></tbody>');
                         
                     }
                  
               }
          })
       }*/
    function searchForm() {

        if ($('#filter').valid()) {
            $('#is_search').val("yes");

            $("#bank_ledger").show();
            $('#table').show();
            $('#msg').remove();
            bankLedger.draw();

        }

    }


    function resetForm() {
        $('#table').hide();
        $('#is_search').val("no");
        $('#bank_account').children().remove();
        $('#bank_account').append('<option >Select Bank Account</option>');
        $('#bank_name').val('');
        $('#branch').val('');
        $('#company_id').val('');
        $('#end_date').val('');
        $('#start_date').val('');

        bankLedger.draw();
    }

    $('.findBranh').change(function(e) {
        e.preventDefault();
        var companyId = $(this).val();
        $.ajax({
            type: "POST",
            url: "{{ route('admin.fetchbranchbycompanyid.inactive') }}",
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
                    var optionBank = `<option value="">Select Bank</option>`;
                    myObj.bank.forEach(element => {
                        optionBank +=
                            `<option value="${element.id}">${element.bank_name}</option>`;
                    });
                    $('#bank_name').html(optionBank);
                }
            }
        });
    });
</script>
<!-- bank ledger  -->
