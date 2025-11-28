<script type="text/javascript">
    "use strict"
    $('#table-data').hide();
    var data_table;
    $(document).ready(function() {
        var date = new Date();
        $('#current_start_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom auto",
            todayHighlight: true,
            endDate: date,
            autoclose: true
        });
        $('#current_end_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom auto",
            todayHighlight: true,
            endDate: date,
            autoclose: true
        });
        $('#compare_start_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom auto",
            todayHighlight: true,
            endDate: date,
            autoclose: true
        });
        $('#compare_end_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom auto",
            todayHighlight: true,
            endDate: date,
            autoclose: true
        });
        $('#current_start_date').change(function() {
            $("#current_end_date").val('');
            $("#current_end_date").datepicker("setStartDate", $('#current_start_date').val());
        });
        $('#compare_start_date').change(function() {
            $("#compare_end_date").val('');
            $("#compare_end_date").datepicker("setStartDate", $('#compare_start_date').val());
        });
        data_table = $('#associate_bussiness_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.common.associate_busniss_compare_list') !!}",
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
                    data: 'Company',
                    name: 'Company'
                },
                {
                    data: 'AssociateCode',
                    name: 'AssociateCode'
                },
                {
                    data: 'AssociateName',
                    name: 'AssociateName'
                },
                {
                    data: 'AssociateBranch',
                    name: 'AssociateBranch'
                },
                {
                    data: 'DailyNCC',
                    name: 'DailyNCC',
                    "render": function(data, type, row) {
                        return row.DailyNCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'DailyRenewal',
                    name: 'DailyRenewal',
                    "render": function(data, type, row) {
                        return row.DailyRenewal + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'MonthlyNCC',
                    name: 'MonthlyNCC',
                    "render": function(data, type, row) {
                        return row.MonthlyNCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'MonthlyRenewal',
                    name: 'MonthlyRenewal',
                    "render": function(data, type, row) {
                        return row.MonthlyRenewal + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'FDNCC',
                    name: 'FDNCC',
                    "render": function(data, type, row) {
                        return row.FDNCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'NCC',
                    name: 'NCC',
                    "render": function(data, type, row) {
                        return row.NCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'TCC',
                    name: 'TCC',
                    "render": function(data, type, row) {
                        return row.TCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'SSBNCC',
                    name: 'SSBNCC',
                    "render": function(data, type, row) {
                        return row.SSBNCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'SSBRenewal',
                    name: 'SSBRenewal',
                    "render": function(data, type, row) {
                        return row.SSBRenewal + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'T_NCC',
                    name: 'T_NCC',
                    "render": function(data, type, row) {
                        return row.T_NCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'T_TCC',
                    name: 'T_TCC',
                    "render": function(data, type, row) {
                        return row.T_TCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'NewLoansOTH',
                    name: 'NewLoansOTH'
                },
                {
                    data: 'LoanAmount',
                    name: 'LoanAmount',
                    "render": function(data, type, row) {
                        return row.LoanAmount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'LoanRecovery',
                    name: 'LoanRecovery',
                    "render": function(data, type, row) {
                        return row.LoanRecovery + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'NewLoanLAD',
                    name: 'NewLoanLAD'
                },
                {
                    data: 'LADAmount',
                    name: 'LADAmount',
                    "render": function(data, type, row) {
                        return row.LADAmount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'LADRecovery',
                    name: 'LADRecovery',
                    "render": function(data, type, row) {
                        return row.LADRecovery + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'NewMembers',
                    name: 'NewMembers'
                },
                {
                    data: 'NewAssociates',
                    name: 'NewAssociates'
                },
                {
                    data: 'c_DailyNCC',
                    name: 'c_DailyNCC',
                    "render": function(data, type, row) {
                        return row.c_DailyNCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'c_DailyRenewal',
                    name: 'c_DailyRenewal',
                    "render": function(data, type, row) {
                        return row.c_DailyRenewal + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'c_MonthlyNCC',
                    name: 'c_MonthlyNCC',
                    "render": function(data, type, row) {
                        return row.c_MonthlyNCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'c_MonthlyRenewal',
                    name: 'c_MonthlyRenewal',
                    "render": function(data, type, row) {
                        return row.c_MonthlyRenewal + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'c_FDNCC',
                    name: 'c_FDNCC',
                    "render": function(data, type, row) {
                        return row.c_FDNCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'c_NCC',
                    name: 'c_NCC',
                    "render": function(data, type, row) {
                        return row.c_NCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'c_TCC',
                    name: 'c_TCC',
                    "render": function(data, type, row) {
                        return row.c_TCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'c_SSBNCC',
                    name: 'c_SSBNCC',
                    "render": function(data, type, row) {
                        return row.c_SSBNCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'c_SSBRenewal',
                    name: 'c_SSBRenewal',
                    "render": function(data, type, row) {
                        return row.c_SSBRenewal + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'c_T_NCC',
                    name: 'c_T_NCC',
                    "render": function(data, type, row) {
                        return row.c_T_NCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'c_T_TCC',
                    name: 'c_T_TCC',
                    "render": function(data, type, row) {
                        return row.c_T_TCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'c_NewLoansOTH',
                    name: 'c_NewLoansOTH'
                },
                {
                    data: 'c_LoanAmount',
                    name: 'c_LoanAmount',
                    "render": function(data, type, row) {
                        return row.c_LoanAmount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'c_LoanRecovery',
                    name: 'c_LoanRecovery',
                    "render": function(data, type, row) {
                        return row.c_LoanRecovery + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'c_NewLoanLAD',
                    name: 'c_NewLoanLAD'
                },
                {
                    data: 'c_LADAmount',
                    name: 'c_LADAmount',
                    "render": function(data, type, row) {
                        return row.c_LADAmount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'c_LADRecovery',
                    name: 'c_LADRecovery',
                    "render": function(data, type, row) {
                        return row.c_LADRecovery + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'c_NewMembers',
                    name: 'c_NewMembers'
                },
                {
                    data: 'c_NewAssociates',
                    name: 'c_NewAssociates'
                },
                {
                    data: 'diff_DailyNCC',
                    name: 'diff_DailyNCC',
                    "render": function(data, type, row) {
                        return row.diff_DailyNCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'diff_DailyRenewal',
                    name: 'diff_DailyRenewal',
                    "render": function(data, type, row) {
                        return row.diff_DailyRenewal + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'diff_MonthlyNCC',
                    name: 'diff_MonthlyNCC',
                    "render": function(data, type, row) {
                        return row.diff_MonthlyNCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'diff_MonthlyRenewal',
                    name: 'diff_MonthlyRenewal',
                    "render": function(data, type, row) {
                        return row.diff_MonthlyRenewal + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'diff_FDNCC',
                    name: 'diff_FDNCC',
                    "render": function(data, type, row) {
                        return row.diff_FDNCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'diff_NCC',
                    name: 'diff_NCC',
                    "render": function(data, type, row) {
                        return row.diff_NCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'diff_TCC',
                    name: 'diff_TCC',
                    "render": function(data, type, row) {
                        return row.diff_TCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'diff_SSBNCC',
                    name: 'diff_SSBNCC',
                    "render": function(data, type, row) {
                        return row.diff_SSBNCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'diff_SSBRenewal',
                    name: 'diff_SSBRenewal',
                    "render": function(data, type, row) {
                        return row.diff_SSBRenewal + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'diff_T_NCC',
                    name: 'diff_T_NCC',
                    "render": function(data, type, row) {
                        return row.diff_T_NCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'diff_T_TCC',
                    name: 'diff_T_TCC',
                    "render": function(data, type, row) {
                        return row.diff_T_TCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'diff_NewLoansOTH',
                    name: 'diff_NewLoansOTH'
                },
                {
                    data: 'diff_LoanAmount',
                    name: 'diff_LoanAmount',
                    "render": function(data, type, row) {
                        return row.diff_LoanAmount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'diff_LoanRecovery',
                    name: 'diff_LoanRecovery',
                    "render": function(data, type, row) {
                        return row.diff_LoanRecovery + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'diff_NewLoanLAD',
                    name: 'diff_NewLoanLAD'
                },
                {
                    data: 'diff_LADAmount',
                    name: 'diff_LADAmount',
                    "render": function(data, type, row) {
                        return row.diff_LADAmount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'diff_LADRecovery',
                    name: 'diff_LADRecovery',
                    "render": function(data, type, row) {
                        return row.diff_LADRecovery + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'diff_NewMembers',
                    name: 'diff_NewMembers'
                },
                {
                    data: 'diff_NewAssociates',
                    name: 'diff_NewAssociates'
                },
            ],
            "bDestroy": true,
        });
        $(data_table.table().container()).removeClass('form-inline');
        $('.export').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#emp_application_export').val(extension);
            var formData = jQuery('#filter').serializeObject();
            var chunkAndLimit = 50000;
            $(".spiners").css("display", "block");
            $(".loaders").text("0%");
            doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
            $("#cover").fadeIn(100);
        });
        // function to trigger the ajax bit
        function doChunkedExport(start, limit, formData, chunkSize) {
            formData['start'] = start;
            formData['limit'] = limit;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('admin.common.associate_busniss_report_exportcompare') !!}",
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
        $(document).ajaxStart(function() {
            $(".loader").show();
        });
        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
        $('#filter').validate({
            rules: {
                associate: {
                    digits: {
                        depends: function(element) {
                            return $(element).val().length > 0;
                        }
                    },
                    minlength: {
                        depends: function(element) {
                            return $(element).val().length > 0;
                        },
                        param: 12
                    },
                    maxlength: {
                        depends: function(element) {
                            return $(element).val().length > 0;
                        },
                        param: 12
                    }
                },
                end_date: {
                    required: true
                },
                start_date: {
                    required: true
                },
                branch_id: {
                    required: true
                },
                compare_start_date: {
                    required: true
                },
                compare_end_date: {
                    required: true
                },
                current_start_date: {
                    required: true
                },
                current_end_date: {
                    required: true
                },
            },
            messages: {
                associate: {
                    minlength: "Please enter a code with at least 11 digits.",
                    maxlength: "Please enter a code with at most 11 digits."
                }
            },
        });
    });
    function searchForm() {
        if ($('#filter').valid()) {
            $('#is_search').val("yes");
            $('#table-data').show();
            data_table.draw();
        }
    }
    function resetForm() {
        var form = $("#filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#compare_start_date').val('');
        $('#compare_end_date').val('');
        $('#current_start_date').val('');
        $('#current_end_date').val('');
        $('#associate').val('');
        $('#company_id').val(0);
        $('#company_id').trigger('change');
        $('#is_search').val('no');
        $('#table-data').hide();
        data_table.draw();
    }
</script>