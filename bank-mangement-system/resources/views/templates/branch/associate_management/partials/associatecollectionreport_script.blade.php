<script type="text/javascript">
    var associateTable = '';
    associateTable = '';

    $(document).ready(function() {



        var date = new Date();
        const currentDate = $('#adm_report_currentdate').val();
        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date,
            autoclose: true
        }).datepicker('setDate', currentDate).datepicker('fill');

        $('#end_date').datepicker({

            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date,
            autoclose: true
        }).datepicker('setDate', currentDate).datepicker('fill');


        associate_collectionreport_listing = $('#associatecollectionreport').DataTable({
            processing: true,
            serverSide: true,
            bFilter: false,
            ordering: false,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],

            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;

            },


            ajax: {
                url: "{!! route('branch.associate.associatecollectionreportlist') !!}",
                type: "POST",
                "data": function(d) {
                    d.searchform = $('form#associate_collection_filter').serializeArray(),
                        d.start_date = $('#start_date').val(),
                        d.end_date = $('#end_date').val(),
                        d.branch_id = $('#branch_id').val(),
                        d.associate_code = $('#associate_code').val()
                    ''
                    d.is_search = $('#is_search').val(),
                        d.company_id = $('#company_id').val()


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
                    data: 'branch_name',
                    name: 'branch_name'
                },
                {
                    data: 'branch_code',
                    name: 'branch_code'
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
                    data: 'total_collection',
                    name: 'total_collection'
                },
            ]


        });
        $(associate_collectionreport_listing.table().container()).removeClass('form-inline');





        $('.associate_collection_export').on('click', function(e) {

            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#associate_collection_export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#associate_collection_filter').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
                $("#cover").fadeIn(100);
            } else {
                $('#associate_collection_export').val(extension);

                $('form#associate_collection_filter').attr('action', "{!! route('branch.associate.associatecollectionreportexport') !!}");

                $('form#associate_collection_filter').submit();
            }
        });

        // function to trigger the ajax bit
        function doChunkedExport(start, limit, formData, chunkSize) {
            formData['start'] = start;
            formData['limit'] = limit;


            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('branch.associate.associatecollectionreportexport') !!}",
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
        };


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

        $('#associate_collection_filter').validate({
            rules: {
                associate_code: {
                    number: true,
                },
                start_date: {
                    required: true
                },
                end_date: {
                    required: true
                }
            },
            messages: {
                associate_code: {
                    number: 'Please enter valid associate code.'
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






        $(document).ajaxStart(function() {
            $(".loader").show();
            $("#associatecollectionreport_processing").css("margin-top", "20px");
        });
        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });

    });
    // search Filter and Data

    function searchForm() {
        if ($('#associate_collection_filter').valid()) {
            $('#is_search').val("yes");
            $(".table-section").removeClass('datatable');
            $("#associatecollectionreport").css({
                width: '100%'
            });
            associate_collectionreport_listing.draw();
        }
    }

    // Form Reset Function start
    function resetForm() {
        var form = $("#associate_collection_filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        const currentDate = $("#adm_report_currentdate").val();
        $('#is_search').val("no");
        $('#start_date').val(currentDate);
        $('#end_date').val(currentDate);
        $('#associate_code').val('');
        $('#company_id').val('0');
        $('#branch_id').val('');
        associate_collectionreport_listing.draw();
        $(".table-section").addClass("datatable");
    }
    // Form Reset Function end
</script>