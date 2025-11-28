<script type="text/javascript">
    var transfer_listing;

    $(document).ready(function() {



        transfer_listing = $('#transfer-listing-detail').DataTable({

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

                "url": "{!! route('admin.associate.companycommission.detaillist') !!}",

                "type": "POST",

                "data": function(d) {

                    d.searchform = $('form#associateCommissionDetailFilter').serializeArray(),

                        d.associate_code = $('#associate_code').val(),

                        d.is_search = $('#is_search').val(),

                        d.commission_export = $('#companyComission_export').val(),

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

                    data: 'companyName',

                    name: 'companyName'

                },
                {

                    data: 'ledgerMonth',

                    name: 'ledgerMonth'

                },

                {

                    data: 'code',

                    name: 'code'

                },

                {

                    data: 'name',

                    name: 'name'

                },

                {

                    data: 'carder',

                    name: 'carder'

                },

                {

                    data: 'pan',

                    name: 'pan'

                },

                {

                    data: 'amount_tds',

                    name: 'amount_tds',

                    "render": function(data, type, row) {

                        return row.amount_tds +

                            " <img src='{{ url('/') }}/asset/images/rs.png' width='7'>";

                    }

                },

                {

                    data: 'tds',

                    name: 'tds',

                    "render": function(data, type, row) {

                        return row.tds +

                            " <img src='{{ url('/') }}/asset/images/rs.png' width='7'>";

                    }

                },

                {

                    data: 'total',

                    name: 'total',

                    "render": function(data, type, row) {

                        return row.total +

                            " <img src='{{ url('/') }}/asset/images/rs.png' width='7'>";

                    }

                },

                {

                    data: 'collection',

                    name: 'collection',

                    "render": function(data, type, row) {

                        return row.collection +

                            " <img src='{{ url('/') }}/asset/images/rs.png' width='7'>";

                    }

                },



                {

                    data: 'fuel',

                    name: 'fuel',

                    "render": function(data, type, row) {

                        return row.fuel +

                            " <img src='{{ url('/') }}/asset/images/rs.png' width='7'>";

                    }

                },



                {

                    data: 'account',

                    name: 'account'

                },

                {

                    data: 'status',

                    name: 'status'

                },

                {

                    data: 'created',

                    name: 'created'

                },

                {

                    data: 'action',

                    name: 'action',

                    orderable: false,

                    searchable: false

                },

            ],"ordering": false,

        });



        $('.leaserDetail').on('click', function(e) {

            e.preventDefault();

            var extension = $(this).attr('data-extension');

            $('#companyComission_export').val(extension);

            if (extension == 0) {

                var formData = jQuery('#associateCommissionDetailFilter').serializeObject();

                var chunkAndLimit = 50;

                $(".spiners").css("display", "block");

                $(".loaders").text("0%");

                doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);

                $("#cover").fadeIn(100);

            }

            // else {

            //     $('#companyComission_export').val(extension);



            //     $('form#associateCommissionDetailFilter').attr('action', "{!! route('admin.associate.commission.leaserDetailExport') !!}");



            //     $('form#associateCommissionDetailFilter').submit();

            // }

        });





        // function to trigger the ajax bit

        function doChunkedExport(start, limit, formData, chunkSize) {

            formData['start'] = start;

            formData['limit'] = limit;

            jQuery.ajax({

                type: "post",

                dataType: "json",

                url: "{!! route('admin.associate.commission.companycomissiondetailexport') !!}",

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



    });







    function searchCommissionDetailForm() {

        if ($('#associateCommissionDetailFilter').valid()) {

            $('#is_search').val("yes");
            $('#hidecommisiontabledata').show();
            transfer_listing.draw();

        }

    }



    function resetCommissionDetailForm() {

        $('#is_search').val("yes");
        $('#company_id').val('0');
        $('#associate_code').val('');
        $('#ledger_month').val('');
        $('#hidecommisiontabledata').hide();
        transfer_listing.draw();

    }
    $(document).on('change', '#ledger_month', function() {
        $('#month').val($('#ledger_month option:selected').data('month'));
        $('#year').val($('#ledger_month option:selected').data('year'));
        // console.log('month',$('#ledger_month option:selected').data('month'),'year',$('#ledger_month option:selected').data('month'));
    })
    $(document).on('change', '#company_id', function() {
        let companyId = $('#company_id option:selected').val();
        if (companyId != 0) {
            $('#ledger_month option').each(function() {
                if ($(this).data('company') == companyId) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        } else {
            var selectedOptions = [];
            $('#ledger_month option').each(function() {
                var month = $(this).data('month');
                var year = $(this).data('year');
                var key = month + '_' + year;
                if ($.inArray(key, selectedOptions) === -1) {
                    selectedOptions.push(key);
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    })
    $(document).ready(function() {
        var selectedOptions = [];
        $('#ledger_month option').each(function() {
            var month = $(this).data('month');
            var year = $(this).data('year');
            var key = month + '_' + year;
            if ($.inArray(key, selectedOptions) === -1) {
                selectedOptions.push(key);
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
</script>
<!-- uat -->
