<script type="text/javascript">
    var detailList;
    $('document').ready(function() {
        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            setDate: new Date()
        })

        $('#end_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            setDate: new Date()
        })
        //
        $('#filter').validate({
            rules: {
                start_date: {
                    required: true,

                },
                // end_date: {
                //     required: true,

                // },
            },
            messages: {
                start_date: {
                    required: "Please enter date.",
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

        $('.export_report').on('click', function() {
            var extension = $(this).attr('data-extension');
            $('#export').val(extension);
            $('form#filter').attr('action', "{!! route('admin.balance_sheet.branch_wise.export') !!}");
            $('form#filter').submit();
            return true;
        });
        // $(document).on('change', '#company_id', function() {
        //     $('#company').val($('#company_id').val());
        // });
        detailList = $('#listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#listing').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route($route) !!}",
                "type": "POST",
                "datatype": "JSON",
                "data": function(d, oSettings) {
                    let totalAmount, page, currentPage;
                    totalAmount = (oSettings.json != null) ? oSettings.json.total : 0;
                    page = ($('#listing').DataTable().page.info());
                    currentPage = page.page + 1;
                    d.pages = currentPage,
                        d.searchform = $('form#filter').serializeArray(),
                        d.start_date = $('#start_date').val(),
                        d.branch_id = $('#branch_id').val(),
                        d.end_date = $('#end_date').val(),
                        d.label = $('#label').val(),
                        d.company_id = $('#company').val(),
                        d.head_id = $('#head_id').val(),
                        d.total = totalAmount
                },
            },
            "columnDefs": [{
                "render": function(data, type, full, meta) {
                    return meta.row + 1; // adds id to serial no
                },
                "targets": 0,
            }],
            columns: [
                @foreach ($array as $key => $val)
                    {
                        data: '{{ $val }}',
                        name: '{{ $val }}'
                    },
                @endforeach
            ],
        });

        $(detailList.table().container()).removeClass('form-inline');

        $(document).ajaxStart(function() {
            $(".loader").show();
        });

        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
    });


    function searchtdsForm() {
        $('#is_search').val("yes");
        $('#hidden-table').show();
        detailList.draw();
    }

    function resettdsForm() {
        $('#is_search').val("no");
        $('#branch_id').val('');
        $('#start_date').val('{{ $start_date }}');
        $('#end_date').val('{{ $end_date }}');
        $('#hidden-table').hide();
        // detailList.draw();
    }
</script>
