<script type="text/javascript">

    var cashInHand = $('#cashInHand').DataTable();
    var currentDate = $("#globalDate").val();

    $(document).ready(function () {
        $('#hiddentable').addClass('d-none');
        var date = new Date();
        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date,
            autoclose: true,
            orientation: "bottom",
        }).on("changeDate", function (e) {
            $('#end_date').datepicker('setStartDate', e.date);
        });

        $('#end_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date,
            autoclose: true,
        }).datepicker('setDate', date);

        $('#region').on('change', function () {
            var region = $(this).val();
            var _token = $('meta[name="csrf-token"]').attr('content');
            var url = "{{route('admin.region_sector')}}";
            var sectorSelectbranch_id = $('#branch_id');
                sectorSelectbranch_id.empty();
                sectorSelectbranch_id.append($('<option>', {
                    value: '0',
                    text: 'Please select Branch'
                }));
            if (region) {
                $.post(url, { 'region': region, '_token': _token })
                    .done(function (response) {
                        var sectorSelect = $('#sector');
                        sectorSelect.empty();
                        sectorSelect.append($('<option>', {
                            value: '0',
                            text: 'Please select Sector'
                        }));
                        $.each(response, function (id, sector) {
                            sectorSelect.append($('<option>', {
                                value: sector,
                                text: sector,
                                'data-region': region
                            }));
                        });
                    })
                    .fail(function (error) {
                        console.log("Error:", error);
                    });
            } else {
                var sectorSelectSector = $('#sector');
                sectorSelectSector.empty();
                sectorSelectSector.append($('<option>', {
                    value: '',
                    text: 'Please select Sector'
                }));
                var sectorSelectbranch_id = $('#branch_id');
                sectorSelectbranch_id.empty();
                sectorSelectbranch_id.append($('<option>', {
                    value: '0',
                    text: 'Please select Branch'
                }));
            }
        });
        $('#sector').on('change', function () {
            var sector = $(this).val();
            var region = $('option:selected', this).data('region');
            var _token = $('meta[name="csrf-token"]').attr('content');
            var url = "{{route('admin.sector_branch')}}";
            if (sector) {
                $.post(url, { 'region': region, 'sector': sector, '_token': _token })
                    .done(function (response) {
                        var sectorSelect = $('#branch_id');
                        sectorSelect.empty();
                        sectorSelect.append($('<option>', {
                            value: '0',
                            text: 'Please select Branch'
                        }));
                        $.each(response, function (id, v) {
                            sectorSelect.append($('<option>', {
                                value: id,
                                text: v
                            }));
                        });
                    })
                    .fail(function (error) {
                        console.log("Error:", error);
                    });
            } else {
                var sectorSelect = $('#branch_id');
                sectorSelect.empty();
                sectorSelect.append($('<option>', {
                    value: '0',
                    text: 'Please select Branch'
                }));
            }
        });
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
                },
                "region": {
                    required: true
                }
            },
            messages: {
                "company_id": {
                    required: "This field is required"
                },
                "start_date": {
                    required: "This field is required"
                },
                "end_date": {
                    required: "This field is required"
                },
                "region": {
                    required: "This field is required"
                }
            }
        });

        cashInHand = $('#cashInHand').DataTable({
            processing: true,
            serverSide: true,
            paging: true,
            pageLength: 20,
            searching:false,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function (nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.cash-in-hand.listing') !!}",
                "type": "POST",
                "beforeSend": function () {
                    $("#loader").show();
                },
                "data": function (d) {
                    d.searchform = $('form#filter').serializeArray()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            "columnDefs": [{
                "render": function (data, type, full, meta) {
                    return meta.row + 1; // adds id to serial no
                },
                "targets": 0
            }],
            columns: [
                { data: 'DT_RowIndex' },
                { data: 'company_name' },
                { data: 'date' },
                // { data: 'region' },
                // { data: 'sector' },
                { data: 'branch_name' },
                { data: 'opening' },
                { data: 'collection' },
                { data: 'payment' },
                { data: 'closing' },
                { data: 'banking' }
            ],
            "ordering": false,
            "bDestroy": true,
        });

        $(cashInHand.table().container()).removeClass('form-inline');

        // Hide loading image
        $(document).ajaxComplete(function () {
            $(".loader").hide();
        });

        $('.export').on('click',function(e){
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#export').val(extension);
            
            var formData = jQuery('#filter').serializeObject();
            var chunkAndLimit = 100;
            $(".spiners").css("display","block");
            $(".loaders").text("0%");
            doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit);
            $("#cover").fadeIn(100);
        });

    });

    function searchForm() {
        if ($('#filter').valid()) {
            $('#is_search').val("yes");
            $('#tblRemittanceList').DataTable().destroy();
            $(".odd").remove();
            $(".even").remove();
            $("#cashInHand_wrapper").show();
            cashInHand.draw();
            $('#hiddentable').removeClass('d-none');
        }
    }
    function populateSelect(select, options, defaultText = 'Please select') {
        select.empty();
        select.append($('<option>', {
            value: '0',
            text: defaultText
        }));
        $.each(options, function (id, value) {
            select.append($('<option>', {
                value: id,
                text: value
            }));
        });
    }
    function resetForm() {
        var form = $("#filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        const currentDate = $("#globalDate").val();
        $('#hiddentable').addClass('d-none');
        $('#end_date').val("");
        $('#start_date').val("");
        $('#region').val('');
        populateSelect($('#sector'));
        populateSelect($('#branch_id'));
        $('#company_id').val('');
        $('#is_search').val("yes");
        cashInHand.draw();
        $("#cashInHand_wrapper").hide();
    }
    

    function doChunkedExport(start,limit,formData,chunkSize){
        formData['start']  = start;
        formData['limit']  = limit;
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url :  "{!! route('admin.cashInHand.list.export') !!}",
            data : formData,
            success: function(response) {
                console.log(response);
                if(response.result=='next'){
                    start = start + chunkSize;
                    doChunkedExport(start,limit,formData,chunkSize);
					$(".loaders").text(response.percentage+"%");
                }else{
					var csv = response.fileName;
                    console.log('DOWNLOAD');
					$(".spiners").css("display","none");
					$("#cover").fadeOut(100); 
					window.open(csv, '_blank');
                }
            }
        });
    }
    jQuery.fn.serializeObject = function(){
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
    
</script>