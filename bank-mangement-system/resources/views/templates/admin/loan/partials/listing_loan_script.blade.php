<script type="text/javascript">
    $(document).ready(function() {
        $('#year').on('change', function() {
            $('#month').val("");
            var selectedYear = $(this).val();
            $('#month option.myopt').each(function() {
                var allowedYears = $(this).data('year');
                if (allowedYears && allowedYears.includes(Number(selectedYear))) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        loanCommissionDetailTable = $('#loan-commission-detail').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#loan-commission-detail').offset().top)
                }, 1000);
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.loan_commission_list') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#commissionFilterDetail').serializeArray(),
                        d.is_search = $('#is_search').val(),
                        d.year = $('#year').val(),
                        d.month = $('#month').val(),
                        d.commission_export = $('#commission_export').val(),
                        d.id = $('#id').val()
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
                    data: 'month',
                    name: 'month'
                },
                {
                    data: 'associate_id',
                    name: 'associate_id'
                },

                {
                    data: 'associate_name',
                    name: 'associate_name'
                },

                {
                    data: 'carder_name',
                    name: 'carder_name'
                },
                {
                    data: 'total_amount',
                    name: 'total_amount'
                },
                {
                    data: 'qualifying_amount',
                    name: 'qualifying_amount'

                },
                {
                    data: 'commission_amount',
                    name: 'commission_amount'
                },
                {
                    data: 'percentage',
                    name: 'percentage'
                },
                {
                    data: 'carder_from',
                    name: 'carder_from'
                },
                {
                    data: 'carder_to',
                    name: 'carder_to'
                },
                {
                    data: 'commission_type',
                    name: 'commission_type'
                },
            ]
        });
        $(loanCommissionDetailTable.table().container()).removeClass('form-inline');

        $('.exportcommissionDetail').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#commission_export').val(extension);
            var formData = jQuery('#commissionFilterDetail').serializeObject();
            var chunkAndLimit = 50;
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
                url: "{!! route('admin.loan.loanCommissionExport') !!}",
                data: formData,
                success: function(response) {
                    if (response.result == 'next') {
                        start = start + chunkSize;
                        doChunkedExport(start, limit, formData, chunkSize);
                        $(".loaders").text(response.percentage + "%");
                    } else {
                        var csv = response.fileName;
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

    });

    $("#reset_form").click(function() {
        $('#is_search').val("no");
        $('#associate_name').val("");
        $('#associate_code').val("");
        $('#month').val("");
        $('#year').val("");
        loanCommissionDetailTable.draw();
       // $(".table-section").addClass('hideTableData');
      
        $(".table-section").addClass("datatable");

    });
    

    $('#commissionFilterDetail').validate({
        rules: {
            // year: {
            //     required: true,
            // },
            // month: {
            //     required: true,
            // },


        },
        messages: {
            year: {
                required: 'Please Select Year.'
            },
            month: {
                required: 'Please Select Month.'
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
                $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
                $(this).addClass('is-invalid');
                });
            }
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
            if ($(element).attr('type') == 'radio') {
                $(element.form).find("input[type=radio]").each(function(which) {
                    $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
                    $(this).removeClass('is-invalid');
                });
            }
        }
    });


    function searchCommissionDetailForm() {
        if ($('#commissionFilterDetail').valid()) {
            $('#is_search').val("yes");
            $(".table-section").addClass("datatable");
           loanCommissionDetailTable.draw();
        }
    }

    function resetCommissionDetailForm() {
        
        $("#commissionFilterDetail")[0].reset();
        $('#is_search').val("no");
        $('#month').val("");
        $('#year').val("");
        $('.table-section').addClass('datatable');
        $('.table_hidden').hide();

    }
   
</script>