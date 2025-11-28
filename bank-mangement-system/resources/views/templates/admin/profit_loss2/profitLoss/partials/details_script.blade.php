<script type="text/javascript">
    $('document').ready(function() {
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
        $(document).ajaxStart(function() {
            $(".loader").show();
        });

        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
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
        var branch_id = $('#branch_id').val();
        var company_id = $('#company').val();
        var start_date = $('#start_date').val();
        var label = $('#label').val();
        var head_id = $('#head_id').val();
        var end_date = $('#end_date').val();
        var queryParams = new URLSearchParams(window.location.search);

        queryParams.set("start_date", start_date);
        queryParams.set("end_date", end_date);
        queryParams.set("branch_id", branch_id);
        queryParams.set("label", label);
        queryParams.set("head_id", head_id);
        queryParams.set("company_id", company_id);

        window.location.href = "{{ url('/') }}/admin/profit-loss/head?" + queryParams;
    }

    function resettdsForm() {
      $('#is_search').val("no");
        var branch_id = $('#branch_id').val();
        var label = $('#label').val();
        var is_search = $('#is_search').val();
        var head_id = $('#head_id').val();
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        var company_id = $('#company').val();
        var queryParams = new URLSearchParams(window.location.search);

        queryParams.set("start_date", start_date);
        queryParams.set("end_date", end_date);
        queryParams.set("branch_id", branch_id);
        queryParams.set("label", label);
        queryParams.set("head_id", head_id);
        queryParams.set("company_id", company_id);

        window.location.href = "{{ url('/') }}/admin/profit-loss/head?" + queryParams;

    }
</script>
