<script type="text/javascript">

    $(document).ready(function () {

        var date = new Date();
        $('#correction_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "top",
            autoclose: true
        });

        investmentCorrectionRequestTable = $('#investment_correction_request_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            "fnRowCallback": function (nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.correctionrequestlist') !!}",
                "type": "POST",
                //"data": {'type':$type,'searchform':$correctionFormData},
                "data": function (d) { d.searchform = $('form#correctionfilter').serializeArray() },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                { data: 'company', name: 'company' },
                { data: 'created_at', name: 'created_at' },
                { data: 'branch', name: 'branch' },
                //  {data: 'branch_code', name: 'branch_code'},
                // {data: 'sector', name: 'sector'},
                // {data: 'regan', name: 'regan'},
                // {data: 'zone', name: 'zone'},
                { data: 'account_no', name: 'account_no' },
                { data: 'in_context', name: 'in_context' },
                { data: 'printType', name: 'printType' },
                { data: 'correction', name: 'correction' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ], "ordering": false
        });
        $(investmentCorrectionRequestTable.table().container()).removeClass('form-inline');

        $(document).on('click', '.approve', function () {
            const userId = $(this).data('id');
            const correction_id = $(this).data('correctionid');

            $('#csid').val(userId);
            $('#corr_id').val(correction_id);

        })
        // Show loading image
        $(document).ajaxStart(function () {
            $(".loader").show();
        });

        // Hide loading image
        $(document).ajaxComplete(function () {
            $(".loader").hide();
        });

        /*
            $('.exportcorrection').on('click',function(){
                var extension = $(this).attr('data-extension');
                $('#correction_export').val(extension);
                $('form#correctionfilter').attr('action',"{!! route('admin.correction.export') !!}");
                $('form#correctionfilter').submit();
                return true;
            });
            */
        $('.exportcorrection').on('click', function (e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            var type = $('#type').val();

            $('#correction_export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#correctionfilter').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit, type);
                $("#cover").fadeIn(100);
            }
            else {
                $('#correction_export').val(extension);

                $('form#correctionfilter').attr('action', "{!! route('admin.correction.export') !!}");

                $('form#correctionfilter').submit();
            }

        });


        // function to trigger the ajax bit
        function doChunkedExport(start, limit, formData, chunkSize, type) {
            formData['start'] = start;
            formData['limit'] = limit;
            let URL = '';
            // if(type==0)
            // {
            // 	Let\ URL = "{!! route('admin.correction.export') !!}"
            // }
            // else if(type == 1)
            // {

            // }
            // else if(type == 2)
            // {

            // }
            // else if(type == 3)
            // {

            // }
            // else if(type == 4)
            // {

            // }
            // else if(type == 5)
            // {

            // }
            // else if(type ==6)
            // {

            // }


            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('admin.correction.export') !!}",
                data: formData,
                success: function (response) {
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
        jQuery.fn.serializeObject = function () {
            var o = {};
            var a = this.serializeArray();
            jQuery.each(a, function () {
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
        /*$('.exportcorrection').on('click',function(e){
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#correction_export').val(extension);
            if(extension == 0)
            {
            var formData = jQuery('#correctionfilter').serializeObject();
            var chunkAndLimit = 50;
            $(".spiners").css("display","block");
            $(".loaders").text("0%");
            doChunkedExporta(0,chunkAndLimit,formData,chunkAndLimit);
            $("#cover").fadeIn(100);
            }
            else{
                $('#correction_export').val(extension);

                $('form#correctionfilter').attr('action',"{!! route('admin.associatecorrection.export') !!}");

                $('form#correctionfilter').submit();
            }

        });


        // function to trigger the ajax bit
        function doChunkedExporta(start,limit,formData,chunkSize){
            formData['start']  = start;
            formData['limit']  = limit;
            jQuery.ajax({
                type : "post",
                dataType : "json",
                url :  "{!! route('admin.associatecorrection.export') !!}",
                data : formData,
                success: function(response) {
                    console.log(response);
                    if(response.result=='next'){
                        start = start + chunkSize;
                        doChunkedExporta(start,limit,formData,chunkSize);
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
            $('.exportcorrection').on('click',function(e){
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#correction_export').val(extension);
            if(extension == 0)
            {
            var formData = jQuery('#correctionfilter').serializeObject();
            var chunkAndLimit = 50;
            $(".spiners").css("display","block");
            $(".loaders").text("0%");
            doChunkedExportb(0,chunkAndLimit,formData,chunkAndLimit);
            $("#cover").fadeIn(100);
            }
            else{
                $('#correction_export').val(extension);

                $('form#correctionfilter').attr('action',"{!! route('admin.investmentcorrection.export') !!}");

                $('form#correctionfilter').submit();
            }

        });


        // function to trigger the ajax bit
        function doChunkedExportb(start,limit,formData,chunkSize){
            formData['start']  = start;
            formData['limit']  = limit;
            jQuery.ajax({
                type : "post",
                dataType : "json",
                url :  "{!! route('admin.investmentcorrection.export') !!}",
                data : formData,
                success: function(response) {
                    console.log(response);
                    if(response.result=='next'){
                        start = start + chunkSize;
                        doChunkedExportb(start,limit,formData,chunkSize);
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
                $('.exportcorrection').on('click',function(e){
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#correction_export').val(extension);
            if(extension == 0)
            {
            var formData = jQuery('#correctionfilter').serializeObject();
            var chunkAndLimit = 50;
            $(".spiners").css("display","block");
            $(".loaders").text("0%");
            doChunkedExportc(0,chunkAndLimit,formData,chunkAndLimit);
            $("#cover").fadeIn(100);
            }
            else{
                $('#correction_export').val(extension);

                $('form#correctionfilter').attr('action',"{!! route('admin.renewcorrectionexportcorrection.export') !!}");

                $('form#correctionfilter').submit();
            }

        });


        // function to trigger the ajax bit
        function doChunkedExportc(start,limit,formData,chunkSize){
            formData['start']  = start;
            formData['limit']  = limit;
            jQuery.ajax({
                type : "post",
                dataType : "json",
                url :  "{!! route('admin.renewcorrectionexportcorrection.export') !!}",
                data : formData,
                success: function(response) {
                    console.log(response);
                    if(response.result=='next'){
                        start = start + chunkSize;
                        doChunkedExportc(start,limit,formData,chunkSize);
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
                    $('.exportcorrection').on('click',function(e){
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#correction_export').val(extension);
            if(extension == 0)
            {
            var formData = jQuery('#correctionfilter').serializeObject();
            var chunkAndLimit = 50;
            $(".spiners").css("display","block");
            $(".loaders").text("0%");
            doChunkedExportd(0,chunkAndLimit,formData,chunkAndLimit);
            $("#cover").fadeIn(100);
            }
            else{
                $('#correction_export').val(extension);

                $('form#correctionfilter').attr('action',"{!! route('admin.printpasscorrectionexportcorrection.export') !!}");

                $('form#correctionfilter').submit();
            }

        });


        // function to trigger the ajax bit
        function doChunkedExportd(start,limit,formData,chunkSize){
            formData['start']  = start;
            formData['limit']  = limit;
            jQuery.ajax({
                type : "post",
                dataType : "json",
                url :  "{!! route('admin.printpasscorrectionexportcorrection.export') !!}",
                data : formData,
                success: function(response) {
                    console.log(response);
                    if(response.result=='next'){
                        start = start + chunkSize;
                        doChunkedExportd(start,limit,formData,chunkSize);
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
                        $('.exportcorrection').on('click',function(e){
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#correction_export').val(extension);
            if(extension == 0)
            {
            var formData = jQuery('#correctionfilter').serializeObject();
            var chunkAndLimit = 50;
            $(".spiners").css("display","block");
            $(".loaders").text("0%");
            doChunkedExporte(0,chunkAndLimit,formData,chunkAndLimit);
            $("#cover").fadeIn(100);
            }
            else{
                $('#correction_export').val(extension);

                $('form#correctionfilter').attr('action',"{!! route('admin.printcertificatecorrectionexportcorrection.export') !!}");

                $('form#correctionfilter').submit();
            }

        });


        // function to trigger the ajax bit
        function doChunkedExporte(start,limit,formData,chunkSize){
            formData['start']  = start;
            formData['limit']  = limit;
            jQuery.ajax({
                type : "post",
                dataType : "json",
                url :  "{!! route('admin.printcertificatecorrectionexportcorrection.export') !!}",
                data : formData,
                success: function(response) {
                    console.log(response);
                    if(response.result=='next'){
                        start = start + chunkSize;
                        doChunkedExporte(start,limit,formData,chunkSize);
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
        };*/
        $('#correctionfilter').validate({
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


        $(document).on('click', '.correction-view-button', function () {
            var corrections = $(this).attr('data-correction-details');
            $('.form-corrections').html('')
            $('.form-corrections').html(corrections)
        });

        $(document).on('click', '.correction-reject-button', function () {
            var correctionsId = $(this).attr('data-correction-id');
            $('#correction_id').val(correctionsId)
        });

        $('#correction-reject-form').validate({ // initialize the plugin
            rules: {
                'rejection': 'required',
            },
        });

    });
    $(document).on('change', '#company_id', function () {
        $("#branch_id").val('');
        var company_id = $('#company_id').val();

        $.ajax({
            type: "POST",
            url: "{!! route('admin.bank_list_by_company') !!}",
            dataType: 'JSON',
            data: { 'company_id': company_id },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                $('#bank_id').find('option').remove();
                $('#bank_id').append('<option value="">Select Bank</option>');
                $.each(response.bankList, function (index, value) {
                    $("#bank_id").append("<option value='" + value.id + "'>" + value.bank_name + "</option>");
                });

            }
        });

    });


    function correctionSearchForm() {
        if ($('#correctionfilter').valid()) {
            $('#is_search').val("yes");
            // $(".datatableblock").removeClass('hideTableData');
            $(".table-section").addClass("show-table");
            investmentCorrectionRequestTable.draw();
        }
    }

    function resetCorrectionForm() {
        var form = $("#correctionfilter"),
        validator = form.validate();
        validator.resetForm();
        $('#is_search').val("no");
        $('#branch_id').val('');
        $('#company_id').val('0');
        $('#correction_date').val('');
        $('#status').val('');
        // $(".datatableblock").addClass("hideTableData");
        $(".table-section").removeClass("show-table");
        $(".table-section").addClass("hide-table");
        investmentCorrectionRequestTable.draw();
    }
</script>

