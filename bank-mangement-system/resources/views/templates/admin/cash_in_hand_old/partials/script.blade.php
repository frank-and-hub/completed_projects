<script type="text/javascript">
    var cashInHand;
    var currentDate = $("#globalDate").val();
    $(document).ready(function() {
        var date = new Date();
        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date,
            autoclose: true,
            orientation: "bottom",
        }).on("changeDate", function(e) {
            $('#end_date').datepicker('setStartDate', e.date);
        });

        $('#end_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date,
            autoclose: true,
        }).datepicker('setDate', date);


        // End Date Picker

        $('#filter').validate({
            rules: {
                "company_id": {
                    required: true
                },
                "start_date": {
                    required: true
                },
                "end_date": {
                    required: true
                }
            },
            messages: {
                "company_id": {
                    required: "this field is required"
                },
                "start_date": {
                    required: "this field is required"
                },
                "end_date": {
                    required: "this field is required"
                }
            }
        });
        // $(document).on('click', '.searchform', function() {
        cashInHand = $('#cashInHand').DataTable({
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
                "url": "{!! route('admin.cash-in-hand.listing') !!}",
                "type": "POST",
                "beforeSend": function() {
                    $("#loader").show();
                },
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            "columnDefs": [{
                "render": function(data, type, full, meta) {
                    return meta.row + 1; // adds id to serial no
                },
                "targets": 0
            }],
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'company_name',
                    name: 'company_name'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'branch_code',
                    name: 'branch_code'
                },
                {
                    data: 'opening',
                    name: 'opening'
                },
                {
                    data: 'closing',
                    name: 'closing'
                },
                {
                    data: 'date',
                    name: 'date'
                },

            ],"ordering": false,
        });
        // });

        $(cashInHand.table().container()).removeClass('form-inline');

        // Show loading image
        // $('#submitt').click(function() {
        //     $(".loader").show();
        // });

        // Hide loading image
        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
    });

    function searchForm() {
        if ($('#filter').valid()) {
            // $(".loader").show();
            $('#is_search').val("yes");
            $('#tblRemittanceList').DataTable().destroy();
            $(".odd").remove();
            $(".even").remove();
            $("#cashInHand_wrapper").show();
            cashInHand.draw();
        }
    }

    // function resetForm()
    // {
    // $('#is_search').val(" ");
    // $('#start_date').val('');
    // $('#end_date').val('');
    // $('#branch').val("");
    // $('#company_id').val("");

    // cashInHand.draw();
    // }
    function resetForm() {
        var form = $("#filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        const currentDate = $("#globalDate").val();
        $('#end_date').val("");
        $('#start_date').val("");
        $('#branch').val('');
        $('#company_id').val('');
        $("#company_id").trigger('change');
        $("#cashInHand_wrapper").hide();


    }
</script>