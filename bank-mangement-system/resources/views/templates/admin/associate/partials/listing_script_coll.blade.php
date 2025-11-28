<script type="text/javascript">
  $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });
    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });
    var memberTable;
$(document).ready(function () {
  $('#commissionFilter').validate({
      rules: {
        associate_code :{ 
            number : true,
        },  
      },
      messages: { 
          associate_code:{ 
            number: 'Please enter valid associate code.'
          },
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
    $('#kotabusinessFilter').validate({
      rules: {
        associate_code :{ 
            number : true,
        },  
      },
      messages: { 
          associate_code:{ 
            number: 'Please enter valid associate code.'
          },
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
    var date = new Date();
  $('#start_date').datepicker({
    format: "dd/mm/yyyy",
    todayHighlight: true,  
    endDate: date, 
    autoclose: true
  });
  $('#end_date').datepicker({
    format: "dd/mm/yyyy", 
    todayHighlight: true, 
    endDate: date,  
    autoclose: true
  });
     memberTable = $('#member_listing').DataTable({
         processing: true,
         serverSide: true,
         pageLength: 20,
         lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#member_listing').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.associate_listing') !!}",
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
            {data: 'member_id', name: 'member_id'},
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email',orderable: true, searchable: true},
            {data: 'mobile_no', name: 'mobile_no'},
            {data: 'associate_code', name: 'associate_code'},
            {data: 'associate_name', name: 'associate_name'},
            {data: 'status', name: 'status'}, 
            {data: 'achieved_target', name: 'achieved_target'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false,
    });
    $(memberTable.table().container()).removeClass( 'form-inline' );
    associateCommissionTable = $('#associate-commission-listing').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#associate-commission-listing').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.associate.collectionlist') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#commissionFilter').serializeArray()}, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'branch_name', name: 'branch_name'},
            {data: 'associate_name', name: 'associate_name'},
            {data: 'associate_code', name: 'associate_code'},
            {data: 'associate_carder', name: 'associate_carder'},            
            {data: 'total_amount', name: 'total_amount',
                "render":function(data, type, row){
                 return row.total_amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                }
              },
            {data: 'commission_amount', name: 'commission_amount',
                "render":function(data, type, row){
                 return row.commission_amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                }
              }, 
            {data: 'senior_code', name: 'senior_code'},
            {data: 'senior_name', name: 'senior_name'},
            {data: 'senior_carder', name: 'senior_carder'}, 
        ],"ordering": false,
    });
    $(associateCommissionTable.table().container()).removeClass( 'form-inline' );
    associateCommissionDetailTable = $('#associate-commission-detail').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#associate-commission-detail').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.associate.commissionDetaillist') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#commissionFilterDetail').serializeArray()}, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'created_at', name: 'created_at'},
            {data: 'investment_account', name: 'investment_account'},
            {data: 'plan_name', name: 'plan_name'},
            {data: 'total_amount', name: 'total_amount',
                "render":function(data, type, row){
                 return row.total_amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                }
              },
            {data: 'commission_amount', name: 'commission_amount',
                "render":function(data, type, row){
                 return row.commission_amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                }
              }, 
            {data: 'percentage', name: 'percentage'},
            {data: 'carder_name', name: 'carder_name'},   
            {data: 'emi_no', name: 'emi_no'},
            {data: 'commission_type', name: 'commission_type'},
            {data: 'associate_exist', name: 'associate_exist'},
            {data: 'pay_type', name: 'pay_type'},
            {data: 'is_distribute', name: 'is_distribute'}, 
        ],"ordering": false,
    });
    $(associateCommissionDetailTable.table().container()).removeClass( 'form-inline' );
    $('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#member_export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.associate.export') !!}");
        $('form#filter').submit();
        return true;
    });
    $('.exportcommission').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#commission_export').val(extension);
        $('form#commissionFilter').attr('action',"{!! route('admin.associate.exportcommissionCollection') !!}");
        $('form#commissionFilter').submit();
        return true;
    });
    $('.exportkotabusiness').on('click',function(){
      var extension = $(this).attr('data-extension');
      $('#kotareport_export').val(extension);
      $('form#kotabusinessFilter').attr('action',"{!! route('admin.associate.exportkotabusiness') !!}");
      $('form#kotabusinessFilter').submit();
      return true;
    });
     $('.exportcommissionDetail').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#commission_export').val(extension); 
        $('form#commissionFilterDetail').attr('action',"{!! route('admin.associate.exportcommissionDetail') !!}");
        $('form#commissionFilterDetail').submit();
        return true;
    });
    $(document).on('keyup','#associate_code',function(){
      if($('#commissionFilter').valid())
      {
        var associate_code=$(this).val();
        $.ajax({
            type: "POST",
            url: "{!! route('admin.associate.getAssociateCarder') !!}",
            dataType: 'JSON',
            data: {'associate_code':associate_code},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
              if(response!=0)
              {
               $("#cader_id option[value=" + response.carder.current_carder_id +"]").prop("selected",true) ;
              }
            }
        })
      }
    });
    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });
    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });
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
        start_date:{ 
            dateDdMm : true,
          },
          end_date:{
            dateDdMm : true,
          }, 
          associate_code :{ 
            number : true,
          },  
      },
      messages: {  
          associate_code:{ 
            number: 'Please enter valid associate code.'
          },
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
});
function searchForm()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        memberTable.draw();
    }
}
function searchCommissionForm()
{  
    if($('#commissionFilter').valid())
    {
        $('#is_search').val("yes");
        associateCommissionTable.draw();
    }
}
function searchCommissionDetailForm()
{  
    if($('#commissionFilterDetail').valid())
    {
        $('#is_search').val("yes");
        associateCommissionDetailTable.draw();
    }
}
function resetForm()
{
    $('#is_search').val("no");
    $('#member_name').val('');
    $('#name').val('');
    $('#associate_code').val('');
    $('#branch_id').val('');
    $('#end_date').val('');
    $('#end_date').val('');
    $('#start_date').val('');
    $('#sassociate_code').val('');
    $('#achieved').val('');
    memberTable.draw();
}
function resetCommissionForm()
{
    $('#is_search').val("no");
    $('#member_name').val('');
    $('#name').val('');
    $('#associate_code').val('');
    $('#branch_id').val('');
    $('#end_date').val('');
    $('#end_date').val('');
    $('#start_date').val('');
    $('#sassociate_code').val('');
    $('#achieved').val('');
    associateCommissionTable.draw();
}
 function resetCommissionDetailForm()
{
    $('#is_search').val("no");
    $('#end_date').val('');
    $('#start_date').val('');
    $('#plan_id').val('');
    associateCommissionDetailTable.draw();
}
</script>