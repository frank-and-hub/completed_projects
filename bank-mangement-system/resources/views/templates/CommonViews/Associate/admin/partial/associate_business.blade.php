<script type="text/javascript">
    "use strict"
    $('#table-data').hide();
    var data_table;
    $(document).ready(function() {
        var date = new Date();
        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom auto",
            todayHighlight: true,
            endDate: date,
            autoclose: true
        });
        $('#end_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom auto",
            todayHighlight: true,
            endDate: date,
            autoclose: true
        });
        $('#start_date').change(function() {
            $("#end_date").val('');
            $("#end_date").datepicker("setStartDate", $('#start_date').val());
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
                "url": "{!! route('admin.common.associate_busniss_report_list') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray(),
                        d.length = d.length
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
                        if (row.DailyNCC >= 0) {
                            return row.DailyNCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                        } else {
                            return "N/A";
                        }
                    }
                },
                {
                    data: 'DailyRenewal',
                    name: 'DailyRenewal',
                    "render": function(data, type, row) {
                        if (row.DailyRenewal >= 0) {
                            return row.DailyRenewal + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                        } else {
                            return "N/A";
                        }
                    }
                },
                {
                    data: 'MonthlyNCC',
                    name: 'MonthlyNCC',
                    "render": function(data, type, row) {
                        if (row.MonthlyNCC >= 0) {
                            return row.MonthlyNCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                        } else {
                            return "N/A";
                        }
                    }
                },
                {
                    data: 'MonthlyRenewal',
                    name: 'MonthlyRenewal',
                    "render": function(data, type, row) {
                        if (row.MonthlyRenewal >= 0) {
                            return row.MonthlyRenewal + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                        } else {
                            return "N/A";
                        }
                    }
                },
                {
                    data: 'FDNCC',
                    name: 'FDNCC',
                    "render": function(data, type, row) {
                        if (row.FDNCC >= 0) {
                            return row.FDNCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                        } else {
                            return "N/A";
                        }
                    }
                },
                {
                    data: 'NCC',
                    name: 'NCC',
                    "render": function(data, type, row) {
                        if (row.NCC >= 0) {
                            return row.NCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                        } else {
                            return "N/A";
                        }
                    }
                },
                {
                    data: 'TCC',
                    name: 'TCC',
                    "render": function(data, type, row) {
                        if (row.TCC >= 0) {
                            return row.TCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                        } else {
                            return "N/A";
                        }
                    }
                },
                {
                    data: 'SSBNCC',
                    name: 'SSBNCC',
                    "render": function(data, type, row) {
                        if (row.SSBNCC >= 0) {
                            return row.SSBNCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                        } else {
                            return "N/A";
                        }
                    }
                },
                {
                    data: 'SSBRenewal',
                    name: 'SSBRenewal',
                    "render": function(data, type, row) {
                        if (row.SSBRenewal >= 0) {
                            return row.SSBRenewal + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                        } else {
                            return "N/A";
                        }
                    }
                },
                {
                    data: 'T_NCC',
                    name: 'T_NCC',
                    "render": function(data, type, row) {
                        if (row.T_NCC >= 0) {
                            return row.T_NCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                        } else {
                            return "N/A";
                        }
                    }
                },
                {
                    data: 'T_TCC',
                    name: 'T_TCC',
                    "render": function(data, type, row) {
                        if (row.T_TCC >= 0) {
                            return row.T_TCC + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                        } else {
                            return "N/A";
                        }
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
                        if (row.LoanAmount >= 0) {
                            return row.LoanAmount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                        } else {
                            return "N/A";
                        }
                    }
                },
                {
                    data: 'LoanRecovery',
                    name: 'LoanRecovery',
                    "render": function(data, type, row) {
                        if (row.LoanRecovery >= 0) {
                            return row.LoanRecovery + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                        } else {
                            return "N/A";
                        }
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
                        if (row.LADAmount >= 0) {
                            return row.LADAmount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                        } else {
                            return "N/A";
                        }
                    }
                },
                {
                    data: 'LADRecovery',
                    name: 'LADRecovery',
                    "render": function(data, type, row) {
                        if (row.LADRecovery >= 0) {
                            return row.LADRecovery + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                        } else {
                            return "N/A";
                        }
                    }
                },
                {
                    data: 'MaturityPayment',
                    name: 'MaturityPayment',
                    "render": function(data, type, row) {
                        if (row.MaturityPayment >= 0) {
                            return row.MaturityPayment + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                        } else {
                            return "N/A";
                        }
                    }
                },
                // {
                //     data: 'Commission',
                //     name: 'Commission',
                //     "render": function(data, type, row) {
                //         if (row.Commission >= 0) {
                //             return row.Commission + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                //         } else {
                //             return "N/A";
                //         }
                //     }
                // },
                {
                    data: 'NewMembers',
                    name: 'NewMembers'
                },
                {
                    data: 'NewAssociates',
                    name: 'NewAssociates'
                },
            ],
            "bDestroy": true,
        });
        $(data_table.table().container()).removeClass('form-inline');
        $('.export').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            // $('#emp_application_export').val(extension);
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
                url: "{!! route('admin.common.associate_busniss_report_export') !!}",
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
                }
            },
            messages: {
                associate: {
                    minlength: "Please enter a code with at least 11 digits.",
                    maxlength: "Please enter a code with at most 11 digits."
                }
            },
        });
    });
    function searchForm()
    {
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
        $('#start_date').val('');
        $('#end_date').val('');
        $('#company_id').val(0);
        $('#company_id').trigger('change');
        $('#table-data').hide();
        $('#associate').val('');
        $('#is_search').val('no');
        data_table.draw();
    }
</script>