<script type="text/javascript">
    var cron;
    $(document).ready((e) => {
        var date = $('#created_at').val(); // date format is "2023-10-25 16:22:15"
        var formattedDate = date.split(' ')[0].split('-').reverse().join('/'); // convert to "dd/mm/yyyy" format
        $('#date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            setDate: formattedDate,
            todayHighlight: true
        });
        $('#date').datepicker('setDate', new Date());
        
        $('.delete_cron').on('click',function(e){
            e.preventDefault();
            var id = [];
            $('.checkbox-row:checked').each(function () {
                id.push($(this).data('id'));
            });            
            $.post("{{route('admin.cron.delete')}}",{id:id},function(response)
                {
                    alert(response);
                },'JSON'
           );
        });

        $('.export').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            var formData = jQuery('#cron_filter').serializeObject();
            console.log(formData);
            var chunkAndLimit = 50;
            $(".spiners").css("display", "block");
            $(".loaders").text("0%");
            doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
            $("#cover").fadeIn(100);
        });

                

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


        //-------------------------------------

        $(".table-section").removeClass('hideTableData');
        

        cron = $('#cron_listing').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ordering: false,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#cron_listing').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.cron.listing') !!}",
                "type": "post",
                "data": function(d) {
                    d.searchform = $('form#cron_filter').serializeArray();
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            "columnDefs": [{                
                "targets": 0
            }],
            columns: [
                {
                    data:'id',
                    name:'id'
                },
                {
                    data: 'cron_name',
                    name: 'cron_name'
                },
                {
                    data: 'start_date',
                    name: 'start_date'
                },
                {
                    data: 'end_date',
                    name: 'end_date'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'action',
                    name: 'action'
                }

            ],"ordering": false,
        })
        $(cron.table().container()).removeClass('form-inline');

        
        $(document).ajaxStart(function() {
            $(".loader").show();
        });

        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
        $("#filter_holiday").validate({
            rules: {
                year: {
                    required: true,
                }
            },
            messages: {
                year: {
                    required: "Please select a holiday year",
                }
            },
            submitHandler: function(form) {
                var formData = $(form).serializeArray();
                var $form = $(form);
                $.post("{{route('admin.cron.getSaturday.run')}}",{formData},function(e) {
                    console.log(e.msg);
                    if (e.type == 'success') {
                        swal('Success', e.msg, 'success');
                    } else {
                        swal('Warning', e.msg, 'warning');
                    }
                    $form[0].reset();
                },'JSON');
            }
        });
        $("#filter").validate({
            rules: {
                account_no: {
                    required: true,
                    digits: true,  // Ensures it is a positive integer
                },
                date: {
                    required: true,
                }
            },
            messages: {
                account_no: {
                    required: "Please enter a acount_no",
                },
                date: {
                    required: "Please pickup a date",
                }
            },
            submitHandler: function(form) {
                var formData = $(form).serializeArray();
                // Store the form element in a variable for reset process
                var $form = $(form);
                $.post("{{route('admin.cron.amount_transfer_cron.run')}}",{formData},function(e) {
                    console.log(e.msg);
                    if (e.type == 'success') {
                        swal('Success', e.msg, 'success');
                    } else {
                        swal('Warning', e.msg, 'warning');
                    }
                    $('#account_details').html('');
                    $form[0].reset();
                },'JSON');
            }
        });
        $('#account_no').on('change',function(){
            var accountNo = $(this).val();
            if($('#filter').valid() && (accountNo != null)){
                var type = $('#cron_type').val();
                $.post("{{route('admin.cron.investmentdetails')}}",{'accountNo':accountNo,'type':type},function(res){ 
                    if(res.msg_type == 'success'){
                        $('#account_details').html(res.view);
                    }else{
                        $('#account_details').html('');
                        swal('Warning', res.msg_type, 'warning');
                    }
                },"JSON");
            }else{
                $('#account_details').html('');
            }
        });

    });
    $(document).on('click', '.reject_block', function() {
        var expense_id = $(this).attr("data-row-id");
        var type = $(this).attr("data-type");
        $('#reason-form').modal();
        $('#expense_id').val(expense_id);
        $('#type').val(type);
        $('#reason').val('');

        var text_type = "";
        if (type == 2) {
            text_type = "Reject";
        } else {
            text_type = "Block";
        }
        $('#reason_text').html(text_type);
    });

    $(document).ready(function() {
        $('#comment-form').validate({ // initialize the plugin
            rules: {
                'reason': 'required',
            },
            messages: {
                reason: "Please enter Comments!",
            },
        });
    });

    $('#cron_filter').validate({
        rules:{
            date: 'required',
        },
        messages:{
            date: 'This field is required',
        }
    });
    $(document).ready(function() {
        $(document).on('blur', '.amount_more', function() {
            $('.amount_more_error').remove();
            $.each($('.amount_more'), function(index, valueOfElement) {
                // console.log('index', indexInArray, 'value', $('.amount_more').eq(indexInArray).val());
                if ($('.amount_more').eq(index).val() <= 0) {
                    $('.amount_more').eq(index).after(
                        '<label  class="error amount_more_error" >Amount must be greater than 0.</label>'
                    );
                    $('#mySubmitBtn').prop('disabled', true);
                    return false;
                } else {
                    console.log('else');
                    $('.amount_more_error').remove();
                    $('#mySubmitBtn').prop('disabled', false);
                }

            });
        })

    });
    // function to trigger the ajax bit
    function doChunkedExport(start, limit, formData, chunkSize) {
        formData['start'] = start;
        formData['limit'] = limit;
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: "{!! route('admin.cron.export') !!}",
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
    function searchForm() {
        if ($('#cron_filter').valid()) {
            $('#is_search').val("yes");
            $(".table-section").removeClass('hideTableData');
            cron.draw();
        }
    }

    function resetForm() {
        $('#is_search').val("no");
        $('#start_date').val('');
        $('#end_date').val('');
        $(".table-section").addClass("hideTableData");
        cron.draw();
    }

    function printDiv(elem) {
        $("#" + elem).print({
            //Use Global styles
            globalStyles: true,
            //Add link with attrbute media=print
            mediaPrint: true,
            //Custom stylesheet
            stylesheet: "{{ url('/') }}/asset/print.css",
            //Print in a hidden iframe
            iframe: false,
            //Don't print this
            noPrintSelector: ".avoid-this",
            //Add this at top
            //  prepend : "Hello World!!!<br/>",
            //Add this on bottom
            // append : "<span><br/>Buh Bye!</span>",
            header: null, // prefix to html
            footer: null,
            //Log to console when printing is done via a deffered callback
            deferred: $.Deferred().done(function() {})
        });
    }
</script>
