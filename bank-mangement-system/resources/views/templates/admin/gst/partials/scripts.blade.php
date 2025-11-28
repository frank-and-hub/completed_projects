<script type="text/javascript">
    var gst_payable_listing;
    $(document).ready(function() {

        // Craete Number jquey Validator
        $.validator.addMethod('gstValidation', function(value, element) {
            var reg =
                /^([0-9]{2}[a-zA-Z]{4}([a-zA-Z]{1}|[0-9]{1})[0-9]{4}[a-zA-Z]{1}([a-zA-Z]|[0-9]){3}){0,15}$/;
            var gst = value.toUpperCase();
            if (this.optional(element) || gst.match(reg)) {
                let gstVal = value.substr(2, 10);
                if (gstVal == 'AAYCS0810K') {
                    return true;
                } else {
                    return false;
                }

            } else {

                return false;
            }


        }, "Please specify a valid GSTTIN Number")


        $.validator.addMethod('checkStateGstCode', function(value, element) {
            let state_code = $('#state_id option:selected').attr('data-gst_code');
            state_code = (state_code.length == 1 ? ('0' + state_code) : '');
            const firstTwoletter = value.substr(0, 2);
            console.log(state_code, firstTwoletter);
            if (state_code == firstTwoletter) {
                return true;
            } else {
                return false;
            }
        }, "Please Verify the State Gst Code");

        $.validator.addMethod('checkState', function(value, element) {
            const state = $('#state_id').val();
            if (state == '') {
                $('#gst_no').val('');
                return false;
            } else {

                return true;
            }
        }, 'Please Select State First');
        // Validate Form

        $('#gst_setting').validate({
            rules: {
                gst_no: {
                    required: true,
                    checkState: true,
                    checkStateGstCode: true,
                    gstValidation: true,
                },
                state_id: {
                    required: true,
                },
                applicable_date: {
                    required: true,
                },
                company_id: {
                    required: true,
                },
                end_date: {
                    required: true,
                },
                category: {
                    required: true,
                }

            },
            messages: {
                gst_no: {
                    required: "Please Enter Gst Number",
                },
                state_id: {
                    required: "Please Select State",
                },
                applicable_date: {
                    required: "Please Enter Date",
                },
                company_id: {
                    required: "Please Select company name",
                },
                end_date: {
                    required: "Please Enter Date",
                },
                category: {
                    required: "Please Select a Category",
                }
            }
        })

        /**@abstract
         * empty gst_no on state change
         */
        $('#state_id').on('change', function() {
            $('#gst_no').val('');
        })
        //Head Setting

        $('#head_setting').validate({
            rules: {
                gst_percentage: {
                    required: true,
                    max: 100,

                },


            },
            messages: {
                gst_percentage: {
                    required: "Please Enter Gst Number",
                },

            }
        })

        // Apply Date Picker
        $('#applicable_date').datepicker({
            format: "dd/mm/yyyy",
            endDate: new Date(),
            todayHighlight: true,
        })

        // $('#end_date').datepicker({
        //     format: "dd/mm/yyyy",
        //     startDate: new Date(),
        //     todayHighlight: true,
        // })

        headListing = $('#head_listing').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                var oSetting = this.fnSettings();
                $('td:nth-child(1)', nRow).html(oSetting._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.gst.head_setting_listing_detail') !!}",
                "type": "POST",
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
                    data: 'head_name',
                    name: 'head_name'
                },
                {
                    data: 'gst_percentage',
                    name: 'gst_percentage'
                },
                {
                    data: 'action',
                    name: 'action'
                },

            ],"ordering": false,
        })
        $(headListing.table().container()).removeClass('form-inline');

        gstListing = $('#gst_listing').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                var oSetting = this.fnSettings();
                $('td:nth-child(1)', nRow).html(oSetting._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.gst.gst_setting_listing_detail') !!}",
                "type": "POST",
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
                    data: 'gst_number',
                    name: 'gst_number'
                },
                {
                    data: 'state',
                    name: 'state'
                },
                {
                    data: 'application_date',
                    name: 'application_date'
                },
                {
                    data: 'category',
                    name: 'category'
                },
                {
                    data: 'action',
                    name: 'action'
                },
            ],"ordering": false,
        })
        $(gstListing.table().container()).removeClass('form-inline');

        $('#start_date,#end_date').on('click hover', function() {
            var created_at = $('.created_at').val();
            var createdDate = new Date(created_at);
            console.log(created_at);
            $(this).datepicker({
                format: "dd/mm/yyyy",
                todayHighlight: true,
                autoclose: true,
                endDate: createdDate,
            });
        });
        jQuery.validator.addMethod("greaterThan",
            function(value, element, params) {
                if (!/Invalid|NaN/.test(new Date(value))) {
                    return new Date(value) > new Date($(params).val());
                }
                return isNaN(value) && isNaN($(params).val()) ||
                    (Number(value) > Number($(params).val()));
            }, 'Must be greater than {0}.');
        
        
        
        
    });

   

    function searchForm() {
        if ($('#filter').valid()) {
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();
            var headId = $('#head_id').val();
            var branchId = $('#branch').val();
            $('#is_search').val("yes");
            $(".table-section").removeClass('hideTableData');
            gst_payable_listing.draw();
        }
    }


    function resetForm() {
        var form = $("#tds_payable_filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#is_search').val("no");
        $('#start_date').val('');
        $('#end_date').val('');
        $('#head_id').val('');
        $('#branch').val('');
        $(".table-section").addClass("hideTableData");
        gst_payable_listing.draw();
    }
</script>
