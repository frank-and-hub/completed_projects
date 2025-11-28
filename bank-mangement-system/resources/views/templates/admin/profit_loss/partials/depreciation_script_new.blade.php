<script type="text/javascript">
    $('#company_id').val('{{ $company_id }}');
    $('#branch').val('{{ $branch_id }}');
    $('document').ready(function() {
        let startDate = $('#default_date').val();
        $("#date").hover(function() {
            $("#date").datepicker({
                format: "dd/mm/yyyy",
                autoclose: true,
                todayHighlight: true,
                setDate: '13/06/2023'
            }).on("changeDate", function(e) {
                $('#to_date').datepicker('setStartDate', e.date, 'format',
                    "dd/mm/yyyy");
            });
            $("#to_date").datepicker({
                format: "dd/mm/yyyy",
                autoclose: true,
                orientation: "bottom",
            });
        })
        $('#to_date').hover(function() {
            if ($('#editInput').val() != '') {
                let date = $('#from_datee').attr('data-val');
                $('#to_date').datepicker({
                    format: "dd/mm/yyyy",
                    autoclose: true,
                    todayHighlight: true,
                    startDate: date,
                })
            }
        });


        $('#filter').validate({
            rules: {
                date: {
                    required: true,
                },
                to_date: {
                    required: true,
                },
            },
            messages: {
                date: {
                    required: "Please enter the date.",
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

        //Export js start 
        $('.export').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#export').val(extension);

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
                url: "{!! route('admin.profit-loss.depreciation.export.new') !!}",
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
        //Export js end

        detailList = $('#depreciation_list').DataTable({
            processing: true,

            serverSide: true,

            pageLength: 20,

            lengthMenu: [10, 20, 40, 50, 100],

            "fnRowCallback": function(nRow, aData, iDisplayIndex) {

                var oSettings = this.fnSettings();

                $('html, body').stop().animate({

                    scrollTop: ($('#depreciation_list').offset().top)

                }, 10);

                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);

                return nRow;

            },

            ajax: {

                "url": "{!! route('admin.detailNewAjax') !!}",

                "type": "POST",

                "data": function(d, oSettings) {
                    let totalAmount;
                    if (oSettings.json != null) {
                        totalAmount = oSettings.json.total;
                    } else {
                        totalAmount = 0;
                    }
                    var page = ($('#depreciation_list').DataTable().page.info());
                    var currentPage = page.page + 1;
                    d.pages = currentPage;
                    d.searchform = $('form#filter').serializeArray();
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            "columnDefs": [{
                "render": function(data, type, full, meta) {
                    return meta.row + 1; // adds id to serial no
                },
                "targets": 0,
            }],
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'main_id',
                    name: 'main_id'
                },
                {
                    data: 'party_name',
                    name: 'party_name'
                },
                {
                    data: 'associate_no',
                    name: 'associate_no'
                },
                {
                    data: 'account_no',
                    name: 'account_no'
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
            ],"ordering": false
        });

        $(detailList.table().container()).removeClass('form-inline');

        $(document).ajaxStart(function() {
            $(".loader").show();
        });

        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });

        $('#submit_form').click(function() {
            if ($('#filter').valid()) {
                $('#is_search').val("yes");
                $('#profit_and_loss_table').show();
                detailList.draw();
            };
        })
        $('#reset_form').click(function() {
            $('#branch').val('');
            $('#profit_and_loss_table').hide();
            $('#is_search').val("no");
            detailList.draw();
        });
        $('#submit_form').trigger('click');
    });
</script>
