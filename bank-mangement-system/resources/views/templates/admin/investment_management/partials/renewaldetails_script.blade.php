<script type="text/javascript">
    var renewaldetails;
    $(document).ready(function() {
        $('#filter').validate({
            rules: {
                member_id: {
                    number: true,
                },
                associate_code: {
                    number: true,
                },
                company_id: {
                    required: true,
                },
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
            errorPlacement: function(error, element) {
                error.addClass(' ');
                element.closest('.error-msg').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
                if ($(element).attr('type') == 'radio') {
                    $(element.form).find("input[type=radio]").each(function(which) {
                        $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
                        $(this).addClass('is-invalid');
                    });
                }
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
                if ($(element).attr('type') == 'radio') {
                    $(element.form).find("input[type=radio]").each(function(which) {
                        $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
                        $(this).removeClass('is-invalid');
                    });
                }
            }
        });
        $(document).on('change', '#branchid', function() {
            var bId = $('option:selected', this).attr('data-val');
            var sbId = $("#hbranchid option:selected").val();
            if (bId != sbId) {
                $('#branchid').val('');
                swal("Warning!", "Branch does not match from top dropdown state", "warning");
            }
        });
        // AJAX call for autocomplete 
        $("#member_name").keyup(function() {
            $.ajax({
                type: "POST",
                url: "{!! route('admin.investment.searchmember') !!}",
                data: 'keyword=' + $(this).val(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $("#search-box").css("background", "#FFF url(LoaderIcon.gif) no-repeat 165px");
                },
                success: function(data) {
                    $("#suggesstion-box").show();
                    $("#suggesstion-box").html(data);
                    $("#member_name").css("background", "#FFF");
                }
            });
        });
        $(document).on('click', '.selectmember', function() {
            var val = $(this).attr('data-val');
            var account = $(this).attr('data-account');
            var id = $(this).attr('value');
            $("#member_name").val(val + ' - (' + account + ')');
            $("#member_id").val(id);
            $("#suggesstion-box").hide();
        });
        var date = new Date();
        //const currentDate = $("#renewal_listing_currentdate").val();
        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date,
            autoclose: true,
            orientation: 'bottom',
        });
        //.datepicker('setDate', currentDate).datepicker('fill')
        $('#end_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date,
            autoclose: true,
            orientation: 'bottom',
        });
        $('input[name="start_date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        });
        $('input[name="start_date"]').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
        // Datatables
        renewaldetails = $('#renewaldetails-listing').DataTable({
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
                "url": "{!! route('admin.renewaldetails.listing') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray(),
                        d.start_date = $('#start_date').val(),
                        d.end_date = $('#end_date').val(),
                        d.company_id = $('#company_id').val(),
                        d.branch_id = $('#branch').val(),
                        d.plan_id = $('#plan_id').val(),
                        d.transaction_by = $('#transaction_by').val(),
                        d.scheme_account_number = $('#scheme_account_number').val(),
                        d.name = $('#name').val(),
                        d.member_id = $('#member_id').val(),
                        d.associate_code = $('#associate_code').val(),
                        d.is_search = $('#is_search').val(),
                        d.investments_export = $('#investments_export').val(),
                        d.account_no = $('#account_no').val()
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
                    data: 'tran_by',
                    name: 'tran_by'
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
                    data: 'customer_id',
                    name: 'customer_id'
                },
                {
                    data: 'member_id',
                    name: 'member_id'
                },
                {
                    data:'account_number',
                    name:'account_number',
                },
                {
                    data: 'member',
                    name: 'member'
                },
                {
                    data: 'plan',
                    name: 'plan'
                },
                {
                    data: 'tenure',
                    name: 'tenure'
                },
                {
                    data: 'amount',
                    name: 'amount',
                    "render": function(data, type, row) {
                        return row.amount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'associate_code',
                    name: 'associate_code'
                },
                {
                    data: 'associate_name',
                    name: 'associate_name'
                },
                {
                    data: 'payment_mode',
                    name: 'payment_mode'
                },

                {
                    data: 'account_opening_date',
                    name: 'account_opening_date'
                },
                {
                    data: 'demo_amount',
                    name: 'demo_amount'
                },
                {
                    data: 'mother_branch',
                    name: 'mother_branch'
                },
                /*{data: 'action', name: 'action',orderable: false, searchable: false},*/
            ],"ordering": false,
        });
        $(renewaldetails.table().container()).removeClass('form-inline');
        /*
    $('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#investments_export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.renewal_list.report.export') !!}");
        $('form#filter').submit();
        return true;
    });
	*/
        $('.export').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#investments_export').val(extension);
            var formData = jQuery('#filter').serializeObject();
            var chunkAndLimit = 50;
            $(".spiners").css("display", "block");
            $(".loaders").text("0%");
            doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
            $("#cover").fadeIn(100);
        });

        // function to trigger the ajax bit
        function doChunkedExport(start, limit, formData, chunkSize) {
            formData['start'] = start;
            formData['limit'] = limit;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('admin.renewal_list.report.export') !!}",
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
            $(".table-section").removeClass('datatable');
            renewaldetails.draw();
        }
    }

    function resetForm() {
        // var form = $("#filter"),
        //     validator = form.validate();
        // validator.resetForm();
        // form.find(".error").removeClass("error");
        const currentDate = $("#renewal_listing_currentdate").val();
        $('#is_search').val("yes");
        $('#company_id').val('');
        $('#account_no').val('');
        $('#plan_id').empty();
        $('#branch').empty();

        $('#plan_id').append($('<option>', {
        value: '',
        text: 'Please select Plan'
        }));

        $('#branch').append($('<option>', {
        value: '',
        text: 'Please select Branch'
        }));

        $('#start_date').val('');
        $('#end_date').val('');
        $('#branch').val('');
        $('#plan_id').val('');
        $('#scheme_account_number').val('');
        $('#name').val('');
        $('#member_id').val('');
        $('#associate_code').val('');
        $('#amount_status').val('');
        // renewaldetails.draw();
        $(".table-section").addClass("datatable");
    }

    $(document).on('change', "#company_id", function() {
        $('#plan_id').find('option').remove();
        const company_id = $(this).val();
        jQuery.ajax({
            url: "{!! route('admin.investment.getCompanyIdPlans') !!}",
            type: "POST",
            data: {
                'company_id': company_id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                var data = JSON.parse(response);

                // get the select element by ID
                var select2 = $('#plan_id');
                var selectsomething = "Please Select Plan";
                select2.append('<option value="">' + selectsomething + '</option>');
                $.each(data.plan, function(key, value) {
                    select2.append('<option value="' + key + '">' + value + '</option>');
                });
            },
        });
    });
</script>