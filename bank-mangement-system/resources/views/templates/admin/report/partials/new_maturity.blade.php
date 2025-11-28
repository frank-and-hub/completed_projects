<script type="text/javascript">
    var maturityReport;
    $(document).ready(function() {
        $('#filter').validate({
            rules: {
                "company_id": {
                    required: true
                }
            },
            messages: {
                "company_id": {
                    required: "this field is required"
                }
            }
        });
        var date = new Date();
        const currentDate = $("#associate_report_currentdate").val();
        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            orientation: 'bottom',
            autoclose: true
        }).datepicker('setDate', currentDate).datepicker('fill');
        $('#end_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            orientation: 'bottom',
            autoclose: true
        }).datepicker('setDate', currentDate).datepicker('fill');
        // $(document).on('change', '#company_id', function() {
        //     $('#plan_id').find('option').remove().end()
        //         .append(' <option value="">Select Plan</option>').val('');
        //     var company_id = $('#company_id').val();
        //     $.ajax({
        //         type: "POST",
        //         url: "{!! route('admin.report.maturityListing.plans') !!}",
        //         dataType: 'JSON',
        //         data: {
        //             'company_id': company_id
        //         },
        //         headers: {
        //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //         },
        //         success: function(e) {
        //             if (e.data != '') {
        //                 $("#plan_id").append(e.data);
        //             }
        //         }
        //     });
        // });
        $(document).on('change','#company_id',function(){ 
            $("#plan_id").val(''); 
            $('#plan_id').find('option').remove();
            $('#plan_id').append('<option value="">Select Plan</option>');
            var company_id=$('#company_id').val();
            if(company_id!='')
            {
                $.ajax({
                    type: "POST",  
                    url: "{!! route('admin.report.maturityListing.plans') !!}",
                    dataType: 'JSON',
                    data: {'company_id':company_id},
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) { 
                        $.each(response.plans, function (index, value) { 
                            $("#plan_id").append("<option value='"+value.id+"'>"+value.name+"</option>");
                        });
                    }
                });
            }              
        });
        maturityReport = $('#maturity_list').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#maturity_list').offset().top)
                }, 1000);
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.report.maturityListing') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray(),
                        d.start_date = $('#start_date').val(),
                        d.end_date = $('#end_date').val(),
                        d.branch_id = $('#branch').val(),
                        d.loan_type = $('#plan').val(),
                        d.status = $('#status').val(),
                        d.company_id = $('#company_id').val(),
                        d.scheme_account_number = $('#scheme_account_number').val(),
                        d.member_id = $('#member_id').val(),
                        d.associate_code = $('#associate_code').val(),
                        d.name = $('#member_name').val(),
                        d.plan_id = $('#plan_id').val(),
                        d.is_search = $('#is_search').val(),
                        d.export = $('#export').val()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'company_name',
                    name: 'company_name'
                },
                {
                    data: 'branch',
                    name: 'branch'
                },
                {
                    data: 'account_no',
                    name: 'account_no'
                },
                {
                    data: 'customer_id',
                    name: 'customer_id'
                },
                {
                    data: 'member_id',
                    name: 'member_id'
                },
                {
                    data: 'member_name',
                    name: 'member_name'
                },
                {
                    data: 'plan_name',
                    name: 'plan_name'
                },
                {
                    data: 'tenure',
                    name: 'tenure'
                },
                {
                    data: 'deposit_amount',
                    name: 'deposit_amount'
                },
                {
                    data: 'deno',
                    name: 'deno'
                },
                {
                    data: 'maturity_type',
                    name: 'maturity_type'
                },
                {
                    data: 'maturity_amount',
                    name: 'maturity_amount'
                },
                {
                    data: 'maturity_payable_amount',
                    name: 'maturity_payable_amount'
                },
                {
                    data: 'maturity_date',
                    name: 'maturity_date'
                },
                {
                    data: 'associate_code',
                    name: 'associate_code'
                },
                {
                    data: 'associate_name',
                    name: 'associate_name'
                },
                {
                    data: 'opening_date',
                    name: 'opening_date'
                },
                {
                    data: 'due_amount',
                    name: 'due_amount'
                },
                {
                    data: 'roi',
                    name: 'roi'
                },
                {
                    data: 'tds_amount',
                    name: 'tds_amount'
                },
                {
                    data: 'total_amount',
                    name: 'total_amount'
                },
                {
                    data: 'payment_mode',
                    name: 'payment_mode'
                },
                {
                    data: 'payment_date',
                    name: 'payment_date'
                },
                {
                    data: 'cheque_no',
                    name: 'cheque_no'
                },
                {
                    data: 'rtgs_chrg',
                    name: 'rtgs_chrg'
                },
                {
                    data: 'ssb_ac',
                    name: 'ssb_ac'
                },
                {
                    data: 'bank_name',
                    name: 'bank_name'
                },
                {
                    data: 'bank_ac',
                    name: 'bank_ac'
                },
            ]
        });
        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
        $(maturityReport.table().container()).removeClass('form-inline');                
        $('.export-maturity').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#filter').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExports(0, chunkAndLimit, formData, chunkAndLimit);
                $("#cover").fadeIn(100);
            } else {
                var start_date = $('#start_date').val();
                $('#from_date').val(start_date);
                $('#start_date').val(start_date);
                $('#export').val(extension);
                $('form#filter').attr('action', "{!! route('admin.maturity.report.export') !!}");
                $('form#filter').submit();
            }
        });
        // function to trigger the ajax bit
        function doChunkedExports(start, limit, formData, chunkSize) {
            formData['start'] = start;
            formData['limit'] = limit;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('admin.maturity.report.export') !!}",
                data: formData,
                success: function(response) {
                    var extension = $('.export-maturity').attr('data-extension');
                    console.log(extension);
                    if (response.result == 'next') {
                        start = start + chunkSize;
                        doChunkedExports(start, limit, formData, chunkSize);
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
        $('#filter').validate({
            rules: {
                member_id: {
                    number: true,
                },
                associate_code: {
                    number: true,
                },
                //       start_date:{
                // 	required: function () {
                //               return $('#status').val().length > 0;
                //           },
                // },
                // end_date:{
                // 	required: function () {
                //               return $('#status').val().length > 0;
                //           },
                // },
            },
        })
        $('#status').on('change', function() {
            var status = $(this).val();
            var twoDigitMonth = ((date.getMonth().length + 1) === 1) ? (date.getMonth() + 1) : +(date.getMonth() + 1);
            var currentDate = date.getDate() + "/" + twoDigitMonth + "/" + date.getFullYear();
            if (status == 0) {
                $('#start_date').val(currentDate);
                $('#from_date').val(currentDate)
                $('#end_date').val(currentDate);
            } else {
                $('#start_date').val('').attr('disabled', false);
                $('#end_date').val('').attr('disabled', false);
            }
        })
    });
    function searchForm()
    {
        if ($('#filter').valid())
        {
            $(".loader").show();
            $('#is_search').val("yes");
            $(".table-section").removeClass('datatable');
            maturityReport.draw();
        }
    }
    function resetForm()
    {
        var form = $("#filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        const currentDate = $("#associate_report_currentdate").val();
        $('#is_search').val("yes");
        $('#branch').val('');
        $('#start_date').val(currentDate);
        $('#end_date').val(currentDate);
        $('#plan').val('');
        $('#status').val('');
        $('#company_id').val('0');
        $('#plan_id').val('');
        $('#member_name').val('');
        $('#associate_code').val('');
        $('#scheme_account_number').val('');
        $('#member_id').val('');
        // $('#start_date').attr('disabled', false);	
        maturityReport.draw();
        $(".table-section").addClass("datatable");
    }
</script>