<script type="text/javascript">
    "use strict";
 var memberTable;
$(document).ready(function () {
    var date = new Date();	

    var Startdate = new Date();
    Startdate.setMonth(Startdate.getMonth() - 3);
 

    $('#start_date').datepicker({
            format: 'dd/mm/yyyy',
            autoclose:true,
            // startDate:Startdate,
            endDate: date,
            
    }).on("changeDate", function(e) {
        $('#end_date').datepicker('setStartDate', e.date, 'format',"dd/mm/yyyy");
    });

    $('#end_date').datepicker({
        format: "dd/mm/yyyy",
        todayHighlight: true, 
        endDate: date,  
        autoclose: true
    });
    
    $.validator.addMethod("dateDdMm", function(value, element,p) {
     var result = true;
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
            start_date:{
                dateDdMm : true,
            },
            end_date:{
                dateDdMm : true,
            },
           
           

        },
        messages: {
           
            // member_id:{
            //     number: 'Please enter valid member id.',
            // },
            // associate_code:{
            //     number: 'Please enter valid associate code.',
            // },
        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass(' ');
            element.closest('.error-msg').append(error);
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass('is-invalid');
            if($(element).attr('type') == 'radio'){
                $(element.form).find("input[type=radio]").each(function(which){
                    $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
                    $(this).addClass('is-invalid');
                });
            }
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
            if($(element).attr('type') == 'radio'){
                $(element.form).find("input[type=radio]").each(function(which){
                    $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
                    $(this).removeClass('is-invalid');
                });
            }
        }
    });

    memberTable = $('#customer_listing').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
                scrollTop: ($('#customer_listing').offset().top)
            }, 10);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax:{
            "url": "{!! route('branch.customer_listing') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter').serializeArray()},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'join_date', name: 'join_date'},
            {data: 'branch', name: 'branch'}, 

            {data: 'name', name: 'name'},
            // {data: 'sector_name', name: 'sector_name'},
            // {data: 'region_name', name: 'region_name'},
            // {data: 'zone_name', name: 'zone_name'},
            /*{data: 'member_id', name: 'member_id'},*/
            // {data: 'member_id', name: 'member_id',
            // "render":function(data, type, row) {
            //     var accountNumber = row.reinvest_old_account_number;
            //     if ( accountNumber ) {
            //         return 'R-' + row.member_id;
            //     } else {
            //         return row.member_id;
            //     }
            // }
            // },
            {data: 'customer_id', name: 'customer_id'},
          
            {data: 'dob', name: 'dob'},
            {data: 'gender', name: 'gender'},
            /*{data: 'ssb_account', name: 'ssb_account'},*/
            {data: 'mobile_no', name: 'mobile_no'},
            {data: 'state', name: 'state'},
            {data: 'district', name: 'district'},
            {data: 'city', name: 'city'},
            {data: 'village', name: 'village'},
            {data: 'pin_code', name: 'pin_code'},
            {data: 'firstId', name: 'firstId'},
            {data: 'secondId', name: 'secondId'},
            {data: 'nominee_name',name:'nominee_name'},
            {data: 'nominee_age',name:'nominee_age'},
            {data: 'relation',name:'relation'},
            {data: 'associate_name', name: 'associate_name'},
            {data: 'associate_code', name: 'associate_code'},
            {data: 'status', name: 'status'},
            {data: 'is_upload', name: 'is_upload'},
            //{data: 'nominee_gender', name: 'nominee_gender'},
            //{data: 'address', name: 'address'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ]
    });
    $(memberTable.table().container()).removeClass( 'form-inline' );

    $(document).on('click','.m-correction',function(){
        var mId = $(this).attr('data-id');
        var cStatus = $(this).attr('data-correction-status');
        if(cStatus == '0'){     
            swal("Warning!", 'Correction request already submitted!', "warning");
                $('#correction-form').modal("hide");
            }
            $('#correction_type_id').val(mId);
        });

        $('#member-correction-form').validate({ // initialize the plugin
            rules: {
                'corrections' : 'required',
            },
        });

        $( document ).ajaxStart(function() {
            $( ".loader" ).show();
        });

        $( document ).ajaxComplete(function() {
            $( ".loader" ).hide();
        });
    
    $('.export').on('click',function(e){
        e.preventDefault();
        var extension = $(this).attr('data-extension');
        
        $('#member_export').val(extension);
        if(extension == 0)
        {
            var formData = jQuery('#filter').serializeObject();
            var startdate = $("#start_date").val();
            var enddate = $("#end_date").val();
            if( startdate =='')
            {
                swal("Error!", "Please select start date, you can export last three months data!", "error");
                return false;	
            }
        
            if( enddate =='')
            {
                swal("Error!", "Please select end date, you can export last three months data!", "error");
                return false;
            }
        
        
    
        
            if(datediff()){
                var chunkAndLimit = 50;	   
                $(".spiners").css("display","block");
                $(".loaders").text("0%");
                doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit);
                $("#cover").fadeIn(100);
            }
        }else{
            $('#customer_export').val(extension);

            $('form#filter').attr('action',"{!! route('branch.customer.export') !!}");

            $('form#filter').submit();
        }
    });
	
    // function to trigger the ajax bit
    function doChunkedExport(start,limit,formData,chunkSize){
        formData['start']  = start;
        formData['limit']  = limit;
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url :  "{!! route('branch.customer.export') !!}",
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
	
    // A function to turn all form data into a jquery object
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

});

    function searchForm()
    {
        if($('#filter').valid())
        {
            $('#is_search').val("yes");
            $(".table-section").removeClass('hideTableData');
            memberTable.draw();
        }
    }
    
    function resetForm()
    {
        var form = $("#filter"),
        validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#is_search').val("no");
        $('#name').val('');
       
        $('#customer_id').val(''); 
        $('#branch_id').val('');
        $('#end_date').val('');
        $('#end_date').val('');
        $('#start_date').val('');
        $('#associate_code').val(''); 
        
        $(".table-section").addClass("hideTableData");
       // memberTable.draw();
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