<script type="text/javascript">
    var cashInHand;
    $(document).ready(function() {

        var financialYear = $('#financial_year').find('option:selected').val();
        console.log(financialYear.split(' - '));

        // Date Picker Start
        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom auto",
            autoclose: true,
            setDate: new Date()
        });
        $('#to_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom auto",
            autoclose: true,
            setDate: new Date()
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
        // End Date Picker

        $('#profit-loss-filter').validate({
            rules: {
                start_date: {
                    required: true,
                    dateDdMm: true,
                },
                to_date: {
                    required: true,
                    dateDdMm: true,
                },
            }
        });

        $(document).ajaxStart(function() {
            $(".loader").show();
        });

        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });


        $('.export').on('click', function() {
            var extension = $(this).attr('data-extension');
            $('#export').val(extension);
            $('form#profit-loss-filter').attr('action', "{!! route('admin.profit-loss.report.export') !!}");
            $('form#profit-loss-filter').submit();
        });

        $('#financial_year').on('change', function() {
            var financialYear = $(this).find('option:selected').val();
            var year = financialYear.split(' - ');

            const d = new Date();
            let curryear = d.getFullYear();

            var minDate = "01/04/" + year[0];
            var startDate = '01/04/' + year[0];
            var endDate = '31/03/' + year[1];
            $('#start_date').val(minDate);
            if (year[1] <= curryear) {
                var maxDate = "31/03/" + year[1];
                $('#to_date').val(maxDate);
            } else {
                var month = d.getMonth() + 1; // Months start at 0!
                var day = d.getDate();
                var maxDate = day + '/' + month + '/' + curryear;

                $('#to_date').val(maxDate);
            }
            $("#start_date").datepicker('remove');
            $('#start_date').datepicker({
                format: "dd/mm/yyyy",
                orientation: "bottom auto",
                autoclose: true,
                startDate: startDate,
                endDate: maxDate,
                setDate: new Date()
            });
            $("#to_date").datepicker('remove');
            $('#to_date').datepicker({
                format: "dd/mm/yyyy",
                orientation: "bottom auto",
                autoclose: true,
                startDate: startDate,
                endDate: maxDate,
                setDate: new Date()
            });
            // var headList = $("#filter_data").find("a");
            // headList.each(function( index ) {
            //   var link = $( this ).attr('href');
            //   console.log( index + ": " + $( this ).attr('href') );
            //   $(this).attr('href', link+'&financial_year='+financialYear);
            // });
            // console.log( "AA", headList);
            // console.log("TT", minDate, maxDate, curryear, startDate, endDate );

        });


    });

    function searchForm() {
        if ($('#profit-loss-filter').valid()) {


            $('#is_search').val("yes");
            var is_search = $('#is_search').val();
            var f_date = $('#f_date').val();
            var start_date = $('#start_date').val();
            var to_date = $('#create_application_date').val();
            var branch = $('#branch').val();
            var financial_year = $('#financial_year').val();
            var companyId = $('#company_id').val();
            $('#filter_data').html('');
            $.ajax({
                type: "POST",
                url: "{!! route('admin.profit_loss_fillter') !!}",
                dataType: 'JSON',
                data: {
                    'is_search': is_search,
                    'start_date': start_date,
                    'to_date': to_date,
                    'branch_id': branch,
                    'financial_year': financial_year,
                    'company_id': companyId,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {

                    $('#filter_data').html(response.view);

                    $(".table-section").removeClass('hideTableData');
                }
            });
        }
    }

    function resetForm() {
        $('#profit-loss-filter')[0].reset();
        $('#is_search').val("no");
        var is_search = $('#is_search').val();
        var f_date = $('#f_date').val();
        var date = $('#start_date').val();
        var start_date = $('#start_date').val(f_date);
        var to_date = $('#to_date').val();
        $(".table-section").addClass("hideTableData");
        $('#filter_data').html('');
        $.ajax({
            type: "POST",
            url: "{!! route('admin.profit_loss_fillter') !!}",
            dataType: 'JSON',
            data: {
                'is_search': is_search,
                'start_date': start_date,
                'to_date': to_date
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#filter_data').html(response.view);
            }
        });


    }
</script>
