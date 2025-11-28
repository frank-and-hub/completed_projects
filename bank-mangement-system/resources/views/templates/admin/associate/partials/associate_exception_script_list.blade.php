<script type="text/javascript">
    $(document).ready(function() {
        $(document).ajaxStart(function() {
            $(".loader").show();
        });
        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
        memberTable = $('#exception_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 100,
            bFilter: false,
            ordering: false,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#exception_listing').offset().top)
                }, 1000);
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.commison.exception.lists') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray(),
                        d.associate_code = $('#associate_code').val(),
                        d.is_search = $('#is_search').val(),
                        d.name = $('#name').val()
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
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'associate_name',
                    name: 'associate_name'
                },
                {
                    data: 'associate_code',
                    name: 'associate_code'
                },
                {
                    data: 'cardername',
                    name: 'cardername'
                },
                {
                    data: 'created_by',
                    name: 'created_by'
                },
                {
                    data: 'user_name',
                    name: 'user_name'
                },
                {
                    data: 'reason',
                    name: 'reason'
                },
                {
                    data: 'commission_status',
                    name: 'commission_status'
                },
                {
                    data: 'fuel_status',
                    name: 'fuel_status'
                },
                {
                    data: 'action',
                    name: 'action'
                }

            ],"ordering": false,
        });
        $(memberTable.table().container()).removeClass('form-inline');

        $('.export').on('click', function(e) {

            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#member_export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#filter').serializeObject();
                var chunkAndLimit = 500;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExportsa(0, chunkAndLimit, formData, chunkAndLimit);
                $("#cover").fadeIn(100);
            } else {
                $('#member_export').val(extension);

                $('form#filter').attr('action', "{!! route('admin.commision.export') !!}");

                $('form#filter').submit();
            }
        });

        function doChunkedExportsa(start, limit, formData, chunkSize) {
            formData['start'] = start;
            formData['limit'] = limit;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('admin.commision.export') !!}",
                data: formData,
                success: function(response) {
                    console.log(response);
                    if (response.result == 'next') {
                        start = start + chunkSize;
                        doChunkedExportsa(start, limit, formData, chunkSize);
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
        // A function to turn all form data into a jquery object
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
    })

    function searchForm() {
        if ($('#filter').valid()) {
            $('#is_search').val("yes");
            $(".table-section").removeClass('hideTableData');
            memberTable.draw();
        }
    }

    function resetForm() {
        $('#associate_code').val('');
        $('#name').val('');
        $(".table-section").addClass("hideTableData");
        memberTable.draw();
    }
    $(document).on('keyup', '#associate_code', function() {
        var validationRules = {
            associate_code: {
                number: true
            }
        };
        var validationMessages = {
            associate_code: {
                number: "Please enter a valid number."
            }
        };
        $('#filter').validate({
            rules: validationRules,
            messages: validationMessages
        });
    });

    function commissionStatus(id) {

        swal({
                title: "COMMISSION STATUS",
                text: " Are you sure you want to change the Commission status?",
                type: "warning",
                showCancelButton: true,
                showDenyButton: true,
            },
            function(commissionStatus) {
                if (!commissionStatus) return;
                var comissionid = id;
                $.ajax({
                    url: "{{ route('admin.commison.exception.lists') }}",
                    type: 'post',
                    dataType: 'json',
                    data: {
                        'comissionid': comissionid
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $("#exception_listing").DataTable().ajax.reload();
                        if (response.commissionStatus == true) {
                            swal("Success", "Commission Status changed successfully", "success");
                        } else {
                            swal("Error", "Commission Status not changed", "error");
                        }
                    }
                });

            });
    };

    function fuelStatus(id) {

        swal({
                title: "FUEL STATUS",
                text: " Are you sure you want to change the FUEL status?",
                type: "warning",
                showCancelButton: true,
                showDenyButton: true,
            },
            function(fuelStatus) {
                if (!fuelStatus) return;
                var fuelid = id;
                $.ajax({
                    url: "{{ route('admin.commison.exception.lists') }}",
                    type: 'post',
                    dataType: 'json',
                    data: {
                        'fuelid': fuelid
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $("#exception_listing").DataTable().ajax.reload();
                        if (response.fuelStatus == true) {
                            swal("Success", "FUEL Status changed successfully", "success");

                        } else {
                            swal("Error", "FUEL Status not changed", "error");
                        }
                    }
                });

            });
    };
</script>