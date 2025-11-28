<script type="text/javascript">
    $('document').ready(function() {
        var date = new Date();
        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            setDate: new Date()
        }).on("changeDate", function(e) {
            $('#end_date').datepicker('setStartDate', e.date);
        });

        $('#end_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            setDate: new Date(),
        });
        

        $.validator.addMethod("dateDdMm", function(value, element, p) {

            if (this.optional(element) ||
                /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g.test(value) == true) {
                $.validator.messages.dateDdMm = "";
                result = true;
            } else {
                $.validator.messages.dateDdMm = "Please enter valid date";
                result = false;
            }

            return result;
        }, "");

        $('#filter').validate({
            rules: {
                start_date: {
                    dateDdMm: true,
                },
                end_date: {
                    dateDdMm: true,
                },
            },
            messages: {
                start_date: {
                    required: "Please enter From date.",
                },
                end_date: {
                    required: "Please enter To date.",
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

        // $(document).on('change', '#company_id', function() {
        //     $('#company').val($('#company_id').val());
        // });
        $(document).ajaxStart(function() {
            $(".loader").show();
        });

        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });

{{--
        detailList = $('#details_report').DataTable({

            processing: true,

            serverSide: true,

            pageLength: 20,

            lengthMenu: [10, 20, 40, 50, 100],

            "fnRowCallback": function(nRow, aData, iDisplayIndex) {

                var oSettings = this.fnSettings();

                $('html, body').stop().animate({

                    scrollTop: ($('#details_report').offset().top)

                }, 10);

                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);

                return nRow;

            },

            ajax: {

                "url": "{!! route('admin.balance-sheet.loan_from_bank_detail_ledger') !!}",

                "type": "POST",

                "data": function(d, oSettings) {
                    let totalAmount;
                    if (oSettings.json != null) {
                        totalAmount = oSettings.json.total;
                        // var total = oSettings.json.total;
                    } else {
                        totalAmount = 0;
                    }
                    var page = ($('#details_report').DataTable().page.info());
                    var currentPage = page.page + 1;
                    d.pages = currentPage,
                        d.searchform = $('form#filter').serializeArray(),
                        d.head = $('#head_id').val(),
                        d.date = $('#start_date').val(),
                        d.end_date = $('#ends_date').val(),
                        d.total = totalAmount
                },
            },

            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'type',
                    name: 'type'
                },
                {
                    data: 'description',
                    name: 'description'
                },
                {
                    data: 'received_bank',
                    name: 'received_bank'
                },
                {
                    data: 'account_number',
                    name: 'account_number'
                },
                {
                    data: 'payment_bank',
                    name: 'payment_bank'
                },
                {
                    data: 'payment_account_number',
                    name: 'payment_account_number'
                },
                {
                    data: 'cr',
                    name: 'cr'
                },
                {
                    data: 'dr',
                    name: 'dr'
                },
                {
                    data: 'balance',
                    name: 'balance'
                },
            ],"ordering": false,

        });

        $(detailList.table().container()).removeClass('form-inline');
        --}}
    });


    $('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.balance_sheet_details.report.export') !!}");
        $('form#filter').submit();
    });


    function searchtdsForm() {
        $('#is_search').val("yes");
        var is_search = $('#is_search').val();
        var branch_id = $('#branch').val();
        var company_id = $('#company_id').val();
        var start_date = $('#start_date').val();
        var label = $('#label').val();
        var head_id = $('#head_id').val();
        var end_date = $('#end_date').val();
        // var queryParams = new URLSearchParams(window.location.search);

        // queryParams.set("start_date", start_date);
        // queryParams.set("end_date", end_date);
        // queryParams.set("branch_id", branch_id);
        // queryParams.set("label", label);
        // queryParams.set("head_id", head_id);
        // queryParams.set("company_id", company_id);
        $('#filter_data').html('');
        $.post("{!! route('balance-sheet.head') !!}",{
            'is_search':is_search,
            'start_date':start_date,
            'branch_id':branch_id,
            'label':label,
            'head_id':head_id,
            'company_id':company_id,
            'end_date':end_date
        },function(response) {
            console.log(response.view);
                  if(response.view)
                  {
                    $('.export').show();
                  }

                  $('#filter_data').html(response.view);

              },'JSON'
          );
        // window.location.href = "{{ url('/') }}/admin/balance-sheet/head?" + queryParams;
    }

    function resettdsForm() {
        $('#is_search').val("no");
        $('#branch').val('');
        $('#start_date').val('{{$start_date}}');
        $('#end_date').val('{{$end_date}}');    
        $('#hidden').hide();

    }
</script>
