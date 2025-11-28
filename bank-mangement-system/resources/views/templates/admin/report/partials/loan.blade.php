<script type="text/javascript">
    'use strict';
    var loanReport;
    $(document).ready(function() {
        var date = new Date();
        const currentDate = $("#associate_report_currentdate").val();
        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date,
            autoclose: true,
            orientation: 'bottom'
        }).datepicker('setDate', currentDate).datepicker('fill');
        $('#end_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date,
            autoclose: true,
            orientation: 'bottom'
        }).datepicker('setDate', currentDate).datepicker('fill');
        loanReport = $('#loan_list').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.report.loanlist') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [
                { data : 'DT_RowIndex'},
                { data : 'status'},
                { data : 'applicant_name'},
                { data : 'customer_id'},
                { data : 'company'},
                { data : 'applicant_phone_number'},
                { data : 'account_number'},
                { data : 'branch'},
                { data : 'sector'},
                { data : 'member_id'},
                { data : 'sanctioned_amount'},
                { data : 'transfer_amount'},
                { data : 'sanctioned_date'},
                { data : 'emi_rate'},
                { data : 'no_of_installement'},
                { data : 'loan_mode'},
                { data : 'loan_type'},
                { data : 'loan_issue_date'},
                { data : 'loan_issue_mode'},
                { data : 'cheque_no'},
                { data : 'total_recovery_amount'},
                { data : 'total_recovery_emi_till_date'},
                { data : 'closing_amount'},
                { data : 'balance_emi'},
                { data : 'emi_should_be_received_till_date'},
                { data : 'future_emi_due_till_date'},
                { data : 'date'},
                { data : 'co_applicant_name'},
                { data : 'co_applicant_number'},
                { data : 'gname'},
                { data : 'gnumber'},
                { data : 'applicant_address'},
                { data : 'first_emi_date'},
                { data : 'loan_end_date'},
            ]
        });
        $(loanReport.table().container()).removeClass('form-inline');
        $('.export-loan').on('click', function() {
            //$('form#filter').attr('action',"{!! route('admin.loan.report.export') !!}");
            //$('form#filter').submit();
        });
        $('.export-loan').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            //$('#report_export').val(extension);
            var formData = {}
            formData['start_date'] = jQuery('#start_date').val();
            formData['end_date'] = jQuery('#end_date').val();
            formData['plan'] = jQuery('#plan').val();
            formData['branch_id'] = jQuery('#branch_id').val();
            formData['status'] = jQuery('#status').val();
            formData['application_number'] = jQuery('#application_number').val();
            formData['member_id'] = jQuery('#member_id').val();
            formData['customer_id'] = jQuery('#customer_id').val();
            formData['is_search'] = jQuery('#is_search').val();
            formData['export'] = jQuery('#export').val();
            var chunkAndLimit = 50;
            $(".spiners").css("display", "block");
            $(".loaders").text("0%");
            doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit, 1);
            $("#cover").fadeIn(100);
        });
        // function to trigger the ajax bit
        function doChunkedExport(start, limit, formData, chunkSize, page) {
            formData['start'] = start;
            formData['limit'] = limit;
            formData['page'] = page;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('admin.loan.report.export') !!}",
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log(response);
                    if (response.result == 'next') {
                        start = start + chunkSize;
                        page = page + 1;
                        doChunkedExport(start, limit, formData, chunkSize, page);
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
        /*
            jQuery.fn.serializeObject = function(){
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
        */
        $('#filter').validate({
            rules: {
                application_number: {
                    number: true,
                },
                member_id: {
                    number: true,
                },
                customer_id: {
                    // number: true,
                },
                company_id: {
                    required: true,
                },
            },
            messages:{
                company_id:{
                    required:"Please select Company Id",
                }
            }
        })
        $(document).on('change', "#company_id", function() {
            $('#plan').find('option').remove();
            const company_id = $(this).val();
            jQuery.ajax({
                url: "{!! route('admin.report.companyIdToLoan') !!}",
                type: "POST",
                data: {
                    'company_id': company_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    // get the select element by ID
                    var select2 = $('#plan');
                    var selectsomething = "Select Loan Type";
                    select2.append('<option value="">' + selectsomething + '</option>');
                    $.each(data.loan, function(key, value) {
                        select2.append('<option value="' + key + '">' + value + '</option>');
                    });
                },
            });
        });
        $(document).ajaxStart(function() {
            $(".loader").show();
        });
        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
    });
    function searchForm() {
        if ($('#filter').valid()) {
            $('#is_search').val("yes");
            $(".table-section").removeClass('datatable');
            loanReport.draw();
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
        $('#branch_id').val('');
        $('#start_date').val(currentDate);
        $('#end_date').val(currentDate);
        $('#plan').val('');
        $('#status').val('');
        $('#application_number').val('');
        $('#member_id').val('');
        $('#customer_id').val('');
        $('#company_id').val();
        $('#branch').empty();
        $('#plan').empty();
        $('#branch').append($('<option>', {
        value: '',
        text: 'Please Select Branch'
        }));
        $('#plan').append($('<option>', {
        value: '',
        text: 'Please Select Loan type'
        }));
        // loanReport.draw();
        $(".table-section").addClass("datatable");
    }
</script>