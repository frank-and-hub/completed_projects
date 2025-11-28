<script src="https://momentjs.com/downloads/moment.js"></script>
<script type="text/javascript">
    var passbookTable;
    $(document).ready(function() {
        var date11 = new Date();
        moment.defaultFormat = "DD/MM/YYYY";
        var date = moment(date11, moment.defaultFormat).toDate();

        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date,
            autoclose: true
        });

        $('#end_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date,
            autoclose: true
        });

        passbookTable = $('#passbook').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 100,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#passbook').offset().top)
                }, 1000);
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('branch.passbook_listing') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('#fillter').serializeArray()
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
                    data: 'account_no',
                    name: 'account_no'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'plan',
                    name: 'plan'
                },
                {
                    data: 'member',
                    name: 'member'
                },
                {
                    data: 'customer_id',
                    name: 'customer_id'
                },
                {
                    data: 'member_id',
                    name: 'member_id'
                },
                {
                    data: 'transaction',
                    name: 'transaction',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'cover',
                    name: 'cover',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'is_passbook_print',
                    name: 'is_passbook_print',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'maturity',
                    name: 'maturity',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'branch_name',
                    name: 'branch_name'
                },
                
            ]
        });

        $(passbookTable.table().container()).removeClass('form-inline');

        // $(document).ajaxStart(function() {
        //     $(".loader").show();
        // });
        // $(document).ajaxComplete(function() {
        //     $(".loader").hide();
        // });

    });

    $('#fillter').validate({
        rules: {
            company_id: {
                required: true,
            },

        },
        messages: {
            company_id: {
                "required": "Please select company name.",
            },

        }
    })

    function searchForm() {
        if ($('#fillter').valid()) {            
            $('#is_search').val("yes");
            $(".table-section").addClass("show-table");
            passbookTable.draw();
        }
    }

    function resetForm() {
        var form = $("#fillter"),
        validator = form.validate();
        validator.resetForm();
        $('#is_search').val("yes");
        $('#member_name').val('');
        $('#member_id').val('');
        $('#branch_id').val('');
        $('#end_date').val('');
        $('#end_date').val('');
        $('#start_date').val('');
        $('#company_id').val('');
        $(".table-section").removeClass("show-table");
        $(".table-section").addClass("hide-table");

        passbookTable.draw();
    }
</script>
