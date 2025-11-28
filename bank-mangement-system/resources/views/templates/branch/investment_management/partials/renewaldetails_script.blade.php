<script type="text/javascript">
    var renewaldetails;

    
    $( document ).ajaxStart(function() {
            $( ".loader" ).show();
        });

        $( document ).ajaxComplete(function() {
            $( ".loader" ).hide();
        });

        
    $(document).ready(function () {
        $.validator.addMethod("dateDdMm", function(value, element,p) {
            if(this.optional(element) || /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g.test(value)==true)
            {
            $.validator.messages.dateDdMm = "";
            result = true;
            }else{
            $.validator.messages.dateDdMm = "Please enter valid date";
            result = false;  
            }
            return result;
        }, ""); 
        $('#filter').validate({
            rules: {
                member_id: {
                    number: true,
                },
                associate_code: {
                    number: true,
                },
                company_id: {
                       required : true,
                },                
                start_date:{
                    dateDdMm : true,
                },
                end_date:{
                    dateDdMm : true,
                }
            },
            messages: {
                member_id: {
                    number: 'Please enter valid member id.'
                },
                associate_code: {
                    number: 'Please enter valid associate code.'
                },
                company_id: {
                    required: 'Please select any company*'
                },
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass(' ');
                element.closest('.error-msg').append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
                if ($(element).attr('type') == 'radio') {
                    $(element.form).find("input[type=radio]").each(function (which) {
                        $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
                        $(this).addClass('is-invalid');
                    });
                }
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
                if ($(element).attr('type') == 'radio') {
                    $(element.form).find("input[type=radio]").each(function (which) {
                        $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
                        $(this).removeClass('is-invalid');
                    });
                }
            }
        });

        $(document).on('change',"#company_id", function(){
            $('#plan_id').find('option').remove();
            const company_id = $(this).val();
            jQuery.ajax({
                url: "{!! route('branch.renewal.getCompanyIdPlans') !!}",
                type: "POST",
                data: {'company_id':company_id},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success:function(response){
                   var data = JSON.parse(response);

                   // get the select element by ID
                    var select = $('#plan_id');
                    var selectsomething = "Please Select branch";
                    select.append('<option value="">' + selectsomething + '</option>');
                    // loop through the response data and append each as an option to the select element
                    $.each(data, function(key, value) {
                        select.append('<option value="' + key + '">' + value + '</option>');
                    });
                },
            });
        });



        $(document).on('change', '#branchid', function () {
            var bId = $('option:selected', this).attr('data-val');
            var sbId = $("#hbranchid option:selected").val();
            if (bId != sbId) {
                $('#branchid').val('');
                swal("Warning!", "Branch does not match from top dropdown state", "warning");
            }
        });
        $(document).on('click', '.selectmember', function () {
            var val = $(this).attr('data-val');
            var account = $(this).attr('data-account');
            var id = $(this).attr('value');
            $("#member_name").val(val + ' - (' + account + ')');
            $("#member_id").val(id);
            $("#suggesstion-box").hide();
        });
        var date = new Date();
        var Startdate = new Date();
        Startdate.setMonth(Startdate.getMonth() - 3);
        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "top",
            autoclose: true
        }).on("changeDate", function(e) {
            $('#end_date').datepicker('setStartDate', e.date, 'format',"dd/mm/yyyy");
        });
        $('#end_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "top",
            autoclose: true
        });
        $('input[name="start_date"]').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        });
        $('input[name="start_date"]').on('cancel.daterangepicker', function (ev, picker) {
            $(this).val('');
        });
        // Datatables
        renewaldetails = $('#renewaldetails-listing').DataTable({
            processing: true,
            serverSide: true,
            paging: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function (nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('renewaldetails.listing') !!}",
                "type": "POST",
                "data": function (d) {
                    d.searchform = $('form#filter').serializeArray(),
                        d.start_date = $('#start_date').val(),
                        d.end_date = $('#end_date').val(),
                        d.branch_id = $('#branch_id').val(),
                        d.transaction_by = $('#transaction_by').val(),
                        d.plan_id = $('#plan_id').val(),
                        d.scheme_account_number = $('#scheme_account_number').val(),
                        d.name = $('#name').val(),
                        d.member_id = $('#member_id').val(),
                        d.associate_code = $('#associate_code').val(),
                        d.is_search = $('#is_search').val(),
                        d.company_id = $('#company_id').val(),
                        d.investments_export = $('#investments_export').val(),
                        d.account_no = $('#account_no').val()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                { data: 'created_at', name: 'created_at' },
                { data: 'tran_by', name: 'tran_by' },
                { data: 'company', name: 'company' },
                { data: 'branch', name: 'branch' },
                // { data: 'branch_code', name: 'branch_code' },
                // { data: 'sector_name', name: 'sector_name' },
                // { data: 'region_name', name: 'region_name' },
                // { data: 'zone_name', name: 'zone_name' },
                { data: 'customer_id', name: 'customer_id' },
                { data: 'member_id', name: 'member_id' },
                { data: 'account_number', name: 'account_number' },
                { data: 'member', name: 'member' },
                { data: 'plan', name: 'plan' },
                { data: 'tenure', name: 'tenure' },
                {
                    data: 'amount', name: 'amount',
                    "render": function (data, type, row) {
                        return row.amount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                { data: 'associate_code', name: 'associate_code' },
                { data: 'associate_name', name: 'associate_name' },
                { data: 'payment_mode', name: 'payment_mode' },
                /*{data: 'action', name: 'action',orderable: false, searchable: false},*/
            ]
        });
        $(renewaldetails.table().container()).removeClass('form-inline');
        // Show loading image
        $(document).ajaxStart(function () {
            $(".loader").show();
        });
        // Hide loading image
        $(document).ajaxComplete(function () {
            $(".loader").hide();
        });
        /*
              $('.export').on('click',function(){
                var extension = $(this).attr('data-extension');
               $('#investments_export').val(extension);
                $('form#filter').attr('action',"{!! route('branch.renewal_list.report.export') !!}");
                $( "form#filter" ).submit();
               return true;
            });
        */
        $('.export').on('click', function (e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#investments_export').val(extension);
            var startdate = $("#start_date").val();
            var enddate = $("#end_date").val();
            if (startdate == '') {
                swal("Error!", "Please select start date, you can export last three months data!", "error");
                return false;
            }
            if (enddate == '') {
                swal("Error!", "Please select end date, you can export last three months data!", "error");
                return false;
            }
            var formData = jQuery('#filter').serializeObject();
            if(datediff()){
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
                $("#cover").fadeIn(100);
            }
        });
        // function to trigger the ajax bit
        function doChunkedExport(start, limit, formData, chunkSize) {
            formData['start'] = start;
            formData['limit'] = limit;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('branch.renewal_list.report.export') !!}",
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
        // A function to turn all form data into a jquery object
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
    });
    function printDiv(elem) {
        printJS({
            printable: elem,
            type: 'html',
            targetStyles: ['*'],
        })
    }
    function searchForm() {
        if ($('#filter').valid()) {
            $('#is_search').val("yes");
            $('.datatable').show();
            renewaldetails.draw();
        }
    }
    function resetForm() {
        $('#is_search').val("yes");
        $('#start_date').val('');
        $('#end_date').val('');
        $('#company_id').val('');
        $('#account_no').val('');
        $('#plan_id').empty();

        $('#plan_id').append($('<option>', {
        value: '',
        text: 'Please select Plan'
        }));
        
        $('#scheme_account_number').val('');
        $('#name').val('');
        $('#member_id').val('');
        $('#associate_code').val('');
        $('#amount_status').val('');
        $('.datatable').hide();
        let table = $('#renewaldetails-listing').DataTable();
            table.clear().draw();
    }
    function datediff() {
        moment.defaultFormat = "DD/MM/YYYY HH:mm";
        var f1 = moment($('#start_date').val() + ' 00:00', moment.defaultFormat).toDate();
        var f2 = moment($('#end_date').val() + ' 00:00', moment.defaultFormat).toDate();
        var from = new Date(Date.parse(f1));
        var to = new Date(Date.parse(f2));
        var threeMonthsLater = moment(from).add(3, 'months');
        if(!(to < threeMonthsLater)) {
            swal('Error','The date difference should not be more than 3 months.','error');
            return false;
        }else{
            return true;
        }
    };
</script>