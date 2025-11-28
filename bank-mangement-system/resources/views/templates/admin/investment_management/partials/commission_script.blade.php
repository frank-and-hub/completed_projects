<script type="text/javascript">
    var commissiontable;
    $(document).ready(function() {

        // Datatables
        commissiontable = $('#commission_listing').DataTable({
            processing: true,
            serverSide: true,
            paging: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.investment.commissionlisting') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray(),
                        d.company_id = $('#company_id').val(),
                        d.year = $('#year').val(),
                        d.month = $('#month').val(),
                        d.associate_name = $('#associate_name').val(),
                        d.associate_code = $('#associate_code').val(),
                        d.is_search = $('#is_search').val(),
                        d.commission_export = $('#commission_export').val()
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
                    name: 'total_amount',
                    "render": function(data, type, row) {
                        return row.total_amount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'qualifying_amount',
                    name: 'qualifying_amount',
                    "render": function(data, type, row) {
                        return row.qualifying_amount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }

                },
                {
                    data: 'commission_amount',
                    name: 'commission_amount',
                    "render": function(data, type, row) {
                        return row.commission_amount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
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
                /*{data: 'action', name: 'action',orderable: false, searchable: false},*/
            ]
        });
        $(commissiontable.table().container()).removeClass('form-inline');
        $('.export').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#commission_export').val(extension);
            var formData = jQuery('#filter').serializeObject();
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
                url: "{!! route('admin.investmentcommission.export') !!}",
                data: formData,
                success: function(response) {
                    console.log(response);
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



    function printDiv(elem) {
        printJS({
            printable: elem,
            type: 'html',
            targetStyles: ['*'],
        })
    }

    function searchForm() {
        if ($('#filter').valid()) {
            $('#is_search').val("yes");
            $(".table-section").removeClass('datatable');
            commissiontable.draw();
        }
    }

    function resetForm() {
        //const currentDate = $("#renewal_listing_currentdate").val();
        $('#is_search').val("no");
        $('#associate_name').val("");
        $('#associate_code').val("");
        $('#month').val("");
        $('#year').val("");

        $(".table-section").addClass("datatable");
    }

    $(document).on('change', "#company_id", function() {
        $('#plan_id').find('option').remove();
        const company_id = $(this).val();
        jQuery.ajax({
            url: "{!! route('admin.investment.getCompanyIdPlans') !!}",
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
                var select2 = $('#plan_id');
                var selectsomething = "Please Select Plan";
                select2.append('<option value="">' + selectsomething + '</option>');
                $.each(data.plan, function(key, value) {
                    select2.append('<option value="' + key + '">' + value + '</option>');
                });
            },
        });
    });
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
        $("#year").trigger("change");
        $('#filter').validate({
            rules: {
                // year: {
                //   required: true,
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
    });
</script>