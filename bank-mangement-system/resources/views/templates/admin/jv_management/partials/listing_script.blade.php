<script type="text/javascript">
    var designationTable;
    $(document).ready(function() {
        designationTable = $('#designation_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#designation_listing').offset().top)
                }, 1000);
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.jv.listing') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'journal',
                    name: 'journal'
                },
                {
                    data: 'company',
                    name: 'company'
                },
                {
                    data: 'branch',
                    name: 'branch'
                },
                {
                    data: 'reference_number',
                    name: 'reference_number'
                },
                // {data: 'status', name: 'status'},
                {
                    data: 'debit',
                    name: 'debit'
                },
                {
                    data: 'credit',
                    name: 'credit'
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
        $(designationTable.table().container()).removeClass('form-inline');
        $(document).on('click', '.delete-jv-journal', function(e) {
            var url = $(this).attr('href');
            e.preventDefault();
            swal({
                title: "Are you sure, you want to delete this entry?",
                text: "",
                icon: "warning",
                buttons: [
                    'No, cancel it!',
                    'Yes, I am sure!'
                ],
                dangerMode: true,
            }).then(function(isConfirm) {
                if (isConfirm) {
                    location.href = url;
                }
            });
        })
        $(document).ajaxStart(function() {
            $(".loader").show();
        });
        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
        $('.export').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#filter').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
                $("#cover").fadeIn(100);
            } else {
                $('#export').val(extension);
                $('form#filter').attr('action', "{!! route('admin.jv.list.export') !!}");
                $('form#filter').submit();
            }
        });
        // function to trigger the ajax bit
        function doChunkedExport(start, limit, formData, chunkSize) {
            formData['start'] = start;
            formData['limit'] = limit;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('admin.jv.list.export') !!}",
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
    function searchForm() {
        $('#is_search').val('yes');
        $('#jv_table_container').show();
        designationTable.draw();
    }
    function resetForm(){
        $('#company_id').val('0').trigger('change');
        $('#is_search').val('no');
        $('#jv_table_container').hide();
        designationTable.draw();
    }
</script>