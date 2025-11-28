<script type="text/javascript">
$(document).ajaxStart(function() {
    $(".loader").show();
});

$(document).ajaxComplete(function() {
    $(".loader").hide();
});
var memberTable;
$(document).ready(function() {



    $('#kotabusinessFilter').validate({
        rules: {
            associate_code: {
                number: true,
            },
        },
        messages: {
            associate_code: {
                number: 'Please enter valid associate code.'
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

    var date = new Date();
    $('#start_date').datepicker({
        format: "dd/mm/yyyy",
        todayHighlight: true,
        endDate: date,
        autoclose: true,
        orientation: "bottom auto",

    });

    $('#end_date').datepicker({
        format: "dd/mm/yyyy",
        todayHighlight: true,
        endDate: date,
        autoclose: true,
        orientation: "bottom auto",

    });


    kotaBusinessTable = $('#kota-business-report').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback": function(nRow, aData, iDisplayIndex) {
            var oSettings = this.fnSettings();
            $('html, body').stop().animate({
                scrollTop: ($('#kota-business-report').offset().top)
            }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.quotabusiness.listing') !!}",
            "type": "POST",
            "data": function(d) {
                d.searchform = $('form#kotabusinessFilter').serializeArray()
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
                data: 'branch_name',
                name: 'branch_name'
            },
            {
                data: 'branch_code',
                name: 'branch_code'
            },
            {
                data: 'sector',
                name: 'sector'
            },
            {
                data: 'regan',
                name: 'regan'
            },
            {
                data: 'zone',
                name: 'zone'
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
                data: 'associate_carder',
                name: 'associate_carder'
            },
            {
                class: 'self',
                data: 'quota_business_target_self_amt',
                name: 'quota_business_target_self_amt'
            },
            {
                class: 'self',
                data: 'achieved_target_self_amt',
                name: 'achieved_target_self_amt'
            },
            {
                class: 'self',
                data: 'quota_business_target_self_percentage',
                name: 'quota_business_target_self_percentage'
            },
            {
                class: 'self',
                data: 'achieved_target_self_percentage',
                name: 'achieved_target_self_percentage'
            },
            {
                data: 'senior_code',
                name: 'senior_code'
            },
            {
                data: 'senior_name',
                name: 'senior_name'
            },
            {
                data: 'senior_carder',
                name: 'senior_carder'
            },
            {
                class: 'team',
                data: 'quota_business_target_team_amt',
                name: 'quota_business_target_team_amt'
            },
            {
                class: 'team',
                data: 'achieved_target_team_amt',
                name: 'achieved_target_team_amt'
            },
            {
                class: 'team',
                data: 'quota_business_target_team_percentage',
                name: 'quota_business_target_team_percentage'
            },
            {
                class: 'team',
                data: 'achieved_target_team_percentage',
                name: 'achieved_target_team_percentage'
            },
            {
                data: 'joining_date',
                name: 'joining_date'
            },
            {
                data: 'mobile_number',
                name: 'mobile_number'
            },
            {
                data: 'status',
                name: 'status'
            },
        ]
    });
    $(kotaBusinessTable.table().container()).removeClass('form-inline');




    kotaBusinessTable1 = $('#kota-business-report1').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback": function(nRow, aData, iDisplayIndex) {
            var oSettings = this.fnSettings();
            $('html, body').stop().animate({
                scrollTop: ($('#kota-business-report1').offset().top)
            }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.quotabusiness.listing') !!}",
            "type": "POST",
            "data": function(d) {
                d.searchform = $('form#kotabusinessFilter').serializeArray()
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
                data: 'branch_name',
                name: 'branch_name'
            },
            {
                data: 'branch_code',
                name: 'branch_code'
            },
            {
                data: 'sector',
                name: 'sector'
            },
            {
                data: 'regan',
                name: 'regan'
            },
            {
                data: 'zone',
                name: 'zone'
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
                data: 'associate_carder',
                name: 'associate_carder'
            },
            {
                class: 'self',
                data: 'quota_business_target_self_amt',
                name: 'quota_business_target_self_amt'
            },
            {
                class: 'self',
                data: 'achieved_target_self_amt',
                name: 'achieved_target_self_amt'
            },
            {
                class: 'self',
                data: 'quota_business_target_self_percentage',
                name: 'quota_business_target_self_percentage'
            },
            {
                class: 'self',
                data: 'achieved_target_self_percentage',
                name: 'achieved_target_self_percentage'
            },
            {
                data: 'senior_code',
                name: 'senior_code'
            },
            {
                data: 'senior_name',
                name: 'senior_name'
            },
            {
                data: 'senior_carder',
                name: 'senior_carder'
            },
            {
                data: 'joining_date',
                name: 'joining_date'
            },
            {
                data: 'mobile_number',
                name: 'mobile_number'
            },
            {
                data: 'status',
                name: 'status'
            },
        ]
    });
    $(kotaBusinessTable1.table().container()).removeClass('form-inline');



    kotaBusinessTable2 = $('#kota-business-report2').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback": function(nRow, aData, iDisplayIndex) {
            var oSettings = this.fnSettings();
            $('html, body').stop().animate({
                scrollTop: ($('#kota-business-report2').offset().top)
            }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.quotabusiness.listing') !!}",
            "type": "POST",
            "data": function(d) {
                d.searchform = $('form#kotabusinessFilter').serializeArray()
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
                data: 'branch_name',
                name: 'branch_name'
            },
            {
                data: 'branch_code',
                name: 'branch_code'
            },
            {
                data: 'sector',
                name: 'sector'
            },
            {
                data: 'regan',
                name: 'regan'
            },
            {
                data: 'zone',
                name: 'zone'
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
                data: 'associate_carder',
                name: 'associate_carder'
            },
            {
                class: 'team',
                data: 'quota_business_target_team_amt',
                name: 'quota_business_target_team_amt'
            },
            {
                class: 'team',
                data: 'achieved_target_team_amt',
                name: 'achieved_target_team_amt'
            },
            {
                class: 'team',
                data: 'quota_business_target_team_percentage',
                name: 'quota_business_target_team_percentage'
            },
            {
                class: 'team',
                data: 'achieved_target_team_percentage',
                name: 'achieved_target_team_percentage'
            },
            {
                data: 'senior_code',
                name: 'senior_code'
            },
            {
                data: 'senior_name',
                name: 'senior_name'
            },
            {
                data: 'senior_carder',
                name: 'senior_carder'
            },
            {
                data: 'joining_date',
                name: 'joining_date'
            },
            {
                data: 'mobile_number',
                name: 'mobile_number'
            },
            {
                data: 'status',
                name: 'status'
            },
        ]
    });
    $(kotaBusinessTable2.table().container()).removeClass('form-inline');


    /*
        $('.exportkotabusiness').on('click',function(){
          var extension = $(this).attr('data-extension');
          $('#kotareport_export').val(extension);
          $('form#kotabusinessFilter').attr('action',"{!! route('admin.quotabusiness.export') !!}");
          $('form#kotabusinessFilter').submit();
          return true;
        });
    */
    $('.exportkotabusiness').on('click', function(e) {
        e.preventDefault();
        var extension = $(this).attr('data-extension');
        $('#kotareport_export').val(extension);
        if (extension == 0) {
            var formData = jQuery('#kotabusinessFilter').serializeObject();
            var chunkAndLimit = 50;
            $(".spiners").css("display", "block");
            $(".loaders").text("0%");
            doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
            $("#cover").fadeIn(100);
        } else {
            $('#kotareport_export').val(extension);

            $('form#kotabusinessFilter').attr('action',
                "{!! route('admin.quotabusiness.export') !!}");

            $('form#kotabusinessFilter').submit();
        }
    });


    // function to trigger the ajax bit
    function doChunkedExport(start, limit, formData, chunkSize) {
        formData['start'] = start;
        formData['limit'] = limit;
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: "{!! route('admin.quotabusiness.export') !!}",
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


    $(document).on('keyup', '#associate_code', function() {
        var associate_code = $(this).val();
        $.ajax({
            type: "POST",
            url: "{!! route('admin.associate.getAssociateCarder') !!}",
            dataType: 'JSON',
            data: {
                'associate_code': associate_code
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $("#cader_id option[value=" + response.carder.current_carder_id + "]").prop(
                    "selected", true);
            }
        })
    });

    $(document).ajaxStart(function() {
        $(".loader").show();
    });

    $(document).ajaxComplete(function() {
        $(".loader").hide();
    });




    $.validator.addMethod("dateDdMm", function(value, element, p) {

        if (this.optional(element) || /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g
            .test(value) == true) {
            $.validator.messages.dateDdMm = "";
            result = true;
        } else {
            $.validator.messages.dateDdMm = "Please enter valid date";
            result = false;
        }

        return result;
    }, "");

    $('#filter').validate({
        rules: {
            start_date: {
                dateDdMm: true,
            },
            end_date: {
                dateDdMm: true,
            },
            associate_code: {
                number: true,
            },

        },
        messages: {
            associate_code: {
                number: 'Please enter valid associate code.'
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


});



function searchKotaBusinessForm() {
    if ($('#kotabusinessFilter').valid()) {
        $('#is_search').val("yes");
        $(".table-section").removeClass('hideTableData');
        $('#kota-business-report_div').hide();
        $('#kota-business-report_div1').hide();
        $('#kota-business-report_div2').hide();


        var businessMode = $("#business_mode option:selected").val();
        if (businessMode == 0) {
            $('#kota-business-report_div1').show();

            kotaBusinessTable1.draw();


        } else if (businessMode == 1) {
            $('#kota-business-report_div2').show();

            kotaBusinessTable2.draw();


        } else if (businessMode == 2) {
            $('#kota-business-report_div').show();

            kotaBusinessTable.draw();
        }

    }
}




function resetKotaBusinessForm() {
    $('#is_search').val("no");
    $('#member_name').val('');
    $('#name').val('');
    $('#associate_code').val('');
    $('#business_mode').val('');
    $('#branch_id').val('');
    $('#end_date').val('');
    $('#start_date').val('');
    $('#sassociate_code').val('');
    $('#achieved').val('');
    $('#cader_id').val('');

    $('#kota-business-report_div').hide();
    $('#kota-business-report_div1').hide();
    $('#kota-business-report_div2').hide();
    $('#kota-business-report_div').show();
    $(".table-section").addClass("hideTableData");
    kotaBusinessTable.draw();
}
</script>