<script type="text/javascript">


 $('document').ready(function(){
    var financialYear = $('#financial_year').find('option:selected').val();
      var year = financialYear.split(' - ');

      const d = new Date();
      let curryear = d.getFullYear();

      var minDate = "01/04/"+year[0];
      var startDate = '01/04/'+year[0];
      var endDate = '31/03/'+year[1];
      if ( year[1] <= curryear ) {
        var maxDate = "31/03/"+year[1];
        $('#to_date').val(maxDate);
      } else {
        var month = d.getMonth() + 1; // Months start at 0!
        var day = d.getDate();
        var maxDate = day+'/'+month+'/'+curryear;
      }
$('#date').datepicker({
        //format: "dd/mm/yyyy",
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            startDate: startDate,
             endDate: maxDate,
            setDate: new Date()
    })
    
    $('#to_date').datepicker({
        format: "dd/mm/yyyy",
        orientation: "bottom",
        autoclose: true,
        startDate: startDate,
        endDate: maxDate,
        setDate: new Date()
    })  
    
    
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
           // required: true,
            dateDdMm : true,
          }, 
         to_date :{ 
           required: true,
          }, 

      },
      messages: {  
          start_date:{ 
            required: "Please enter date.",
          },
          branch:{ 
            required: 'Please select branch.'
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

// $('.submit').on('click',function(){
//   location.reload();
// })
$( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });

  
 detailList = $('#detailList').DataTable({

            processing: true,

            serverSide: true,

            pageLength: 20,

            lengthMenu: [10, 20, 40, 50, 100],

            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {

                var oSettings = this.fnSettings ();

                $('html, body').stop().animate({

                    scrollTop: ($('#detailList').offset().top)

                }, 10);

                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);

                return nRow;

            },

            ajax: {

                "url": "{!! route('admin.detailed.branch_wise') !!}",

                "type": "POST",

                "data":function(d) {
                    d.searchform=$('form#filter').serializeArray(),
                    d.head= $('#head').val(),
                    d.label= $('#label').val(),
                    d.date= $('#date').val(),
                    d.to_date= $('#to_date').val(),
                    d.branch= $('#branch').val()
                   
                        
                },

                headers: {

                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                },

            }, 
             "columnDefs": [{
                "render": function(data, type, full, meta) {
                   
                    return meta.row + 1; // adds id to serial no
                },
                "targets": 0,
                
            }],
            columns: [

                {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                {data: 'branch_code', name: 'branch_code'},
                {data: 'branch_name', name: 'branch_name'},
                {data: 'amount', name: 'amount'},
                {data: 'action', name: 'action'},    
            ]
            ,"ordering": false
        });

        $(detailList.table().container()).removeClass( 'form-inline' );  

        $('#financial_year').on('change',function(){
        var financialYear = $(this).find('option:selected').val();
        var year = financialYear.split(' - ');

        const d = new Date();
        let curryear = d.getFullYear();

        var minDate = "01/04/"+year[0];
        var startDate = '01/04/'+year[0];
        var endDate = '31/03/'+year[1];
        $('#date').val( minDate );
        if ( year[1] <= curryear ) {
            var maxDate = "31/03/"+year[1];
            $('#to_date').val(maxDate);
        } else {
            var month = d.getMonth() + 1; // Months start at 0!
            var day = d.getDate();
            var maxDate = day+'/'+month+'/'+curryear;
            
            $('#to_date').val(maxDate);
        }
        $("#date").datepicker('remove');
        $('#date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom auto",
            autoclose: true,
            startDate: startDate,
            endDate: maxDate,
            setDate: new Date()
        });
        $("#to_date").datepicker('remove');
        $('#to_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom auto",
            autoclose: true,
            startDate: startDate,
            endDate: maxDate,
            setDate: new Date()
        });
      // var headList = $("#filter_data").find("a");
      // headList.each(function( index ) {
      //   var link = $( this ).attr('href');
      //   console.log( index + ": " + $( this ).attr('href') );
      //   $(this).attr('href', link+'&financial_year='+financialYear);
      // });
      // console.log( "AA", headList);
      // console.log("TT", minDate, maxDate, curryear, startDate, endDate );
      
    });
});



 $('.export_report').on('click',function(){
  var extension = $(this).attr('data-extension');
  $('#export').val(extension);
  $('form#filter').attr('action',"{!! route('admin.profit-loss.branch_wise.report.export') !!}");
  $('form#filter').submit();
  });




function searchForm()
{  
    
        $('#is_search').val("yes");

    var branch=$('#branch').val(); 
    var is_search=$('#is_search').val(); 
    var start_date=$('#date').val(); 
    var end_date = $('#to_date').val();  
     var queryParams = new URLSearchParams(window.location.search);
 
// Set new or modify existing parameter value. 
queryParams.set("date", start_date);
queryParams.set("to_date", end_date);
 
// Replace current querystring with the new one.
window.location.href = "{{url('/')}}/admin/profit-loss/detail/branch_wise/{{$head}}/{{$label}}?"+queryParams;
    
}

function resetForm()
{
    location.reload();
    $('#is_search').val("no");  
    $('#branch').val(""); 
    $('#start_date').val(""); 
  
    var is_search=$('#is_search').val();
     var start_date=$('#default_date').val(); 
     var end_date=$('#default_end_date').val(); 
    
    var queryParams = new URLSearchParams(window.location.search);
 
// Set new or modify existing parameter value. 
queryParams.set("date", start_date);
queryParams.set("to_date", end_date);
 
// Replace current querystring with the new one.
window.location.href = "{{url('/')}}/admin/profit-loss/detail/branch_wise/{{$head}}/{{$label}}?"+queryParams;
}





// ...........................................Rent Creditors report Start ...........................//

rentCrediorsList = $('#rentCreditorsList').DataTable({
                            processing: true,
                            serverSide: true,
                            pageLength: 20,
                            lengthMenu: [10, 20, 40, 50, 100],
                            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
                                var oSettings = this.fnSettings ();
                                $('html, body').stop().animate({
                                    scrollTop: ($('#rentCreditorsList').offset().top)
                                }, 10);
                                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                                return nRow;
                            },
                            ajax: {
                                "url": "{!! route('admin.balance-sheet.get_rent_creditors_report_listing') !!}",
                                "type": "POST",
                                "data":function(d) {
                                    d.searchform=$('form#filter').serializeArray(),
                                    d.date= $('#start_date').val(),
                                    d.branch= $('#branch_filter').val(),
                                    
                                    d.end_date = $('#ends_date').val()  
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                            }, 
                            columns: [
                            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                            {data: 'owner_name', name: 'owner_name'},
                            {data: 'sub_head', name: 'sub_head'},  
                            /*{data: 'rent_amount', name: 'rent_amount'},*/
                            {data: 'transfer_amount', name: 'transfer_amount'}, 
                            ],"ordering": false
                        });
                        
                        
                        

        function searchRentCreditorsForm()
        {  
            rentCrediorsList.ajax.reload();
        }
        
        function resetRentCreditorsForm(){
            $('#start_date').val("");  
            rentCrediorsList.ajax.reload(); 
        }
        $('.export_report_rent').on('click',function(){
    var extension = $(this).attr('data-extension');
    $('#export').val(extension);
    $('form#filter').attr('action',"{!! route('admin.balance_sheet.branch_wise.rent.export') !!}");
    $('form#filter').submit();
    return true;
});
        

// ...........................................Salary Creditors report Start ...........................//

salaryCrediorsList = $('#salaryCreditorsList').DataTable({
                            processing: true,
                            serverSide: true,
                            pageLength: 20,
                            lengthMenu: [10, 20, 40, 50, 100],
                            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
                                var oSettings = this.fnSettings ();
                                $('html, body').stop().animate({
                                    scrollTop: ($('#salaryCreditorsList').offset().top)
                                }, 10);
                                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                                return nRow;
                            },
                            ajax: {
                                "url": "{!! route('admin.balance-sheet.get_salary_creditors_report_listing') !!}",
                                "type": "POST",
                                "data":function(d) {
                                    d.searchform=$('form#filter').serializeArray(),
                                    d.date= $('#start_date').val(),
                                    d.branch= $('#branch_filter').val() 
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                            }, 
                            columns: [
                            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                            {data: 'owner_name', name: 'owner_name'},
                            {data: 'employee_code', name: 'employee_code'},  
                            /*{data: 'rent_amount', name: 'rent_amount'},*/
                            {data: 'transfer_amount', name: 'transfer_amount'}, 
                            ],"ordering": false
                        }); 



        function searchsalartCreditorsForm()
        {  
            salaryCrediorsList.ajax.reload();
        }
        
        
        function resetSalaryCreditorsForm(){
            $('#start_date').val("");  
            salaryCrediorsList.ajax.reload(); 
        }
        


// ...........................................CASH IN HAND Creditors report Start ...........................//

caseinhandCreditorsList = $('#caseinhandCreditorsList').DataTable({
                            processing: true,
                            serverSide: true,
                            pageLength: 20,
                            lengthMenu: [10, 20, 40, 50, 100],
                            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
                                var oSettings = this.fnSettings ();
                                $('html, body').stop().animate({
                                    scrollTop: ($('#caseinhandCreditorsList').offset().top)
                                }, 10);
                                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                                return nRow;
                            },
                            ajax: {
                                "url": "{!! route('admin.balance-sheet.get_case_in_hand_report_listing') !!}",
                                "type": "POST",
                                "data":function(d) {
                                    d.searchform=$('form#filter').serializeArray(),
                                    d.date= $('#start_date').val(),
                                    d.end_date= $('#ends_date').val(),
                                    d.branch= $('#branch_filter').val() 
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                            }, 
                            columns: [
                            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                            {data: 'name', name: 'name'},
                            {data: 'branch_code', name: 'branch_code'},  
                            {data: 'sector', name: 'sector'},
                            {data: 'regan', name: 'regan'}, 
                            {data: 'zone', name: 'zone'},
                            {data: 'closing_balance', name: 'closing_balance'},  
                            {data: 'loan_closing_balance', name: 'loan_closing_balance'},
                            {data: 'total_closing_balance', name: 'total_closing_balance'}, 
                            ],"ordering": false
                        });         
                        
        

    function searchCashInHandCreditorsForm()
    {  
        caseinhandCreditorsList.ajax.reload();
    }
    
    function resetCashInHandCreditorsForm(){
        $('#start_date').val("");  
        caseinhandCreditorsList.ajax.reload(); 
    }
    
    
    
// ...........................................FIXED ASSETS Creditors report Start ...........................//

fixedAssetsCreditorsList = $('#fixedAssetsCreditorsList').DataTable({
                            processing: true,
                            serverSide: true,
                            pageLength: 20,
                            lengthMenu: [10, 20, 40, 50, 100],
                            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
                                var oSettings = this.fnSettings ();
                                $('html, body').stop().animate({
                                    scrollTop: ($('#fixedAssetsCreditorsList').offset().top)
                                }, 10);
                                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                                return nRow;
                            },
                            ajax: {
                                "url": "{!! route('admin.balance-sheet.get_fixed_assets_report_listing') !!}",
                                "type": "POST",
                                "data":function(d) {
                                    d.searchform=$('form#filter').serializeArray(),
                                    d.date= $('#start_date').val(),
                                    d.branch= $('#branch_filter').val(),
                                    d.head_id= $('#head_id').val()
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                            }, 
                            columns: [
                            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                            {data: 'assets_category', name: 'assets_category'},
                            {data: 'assets_subcategory', name: 'assets_subcategory'},  
                            {data: 'party_name', name: 'party_name'},
                            {data: 'mobile_number', name: 'mobile_number'}, 
                            {data: 'current_balance', name: 'current_balance'},
                            {data: 'amount', name: 'amount'},  
                            ],"ordering": false
                        });     
                        
    
    function searchFixedAsstesCreditorsForm()
    {  
        fixedAssetsCreditorsList.ajax.reload();
    }
    
    function resetFixedAsstesCreditorsForm(){
        $('#start_date').val("");  
        fixedAssetsCreditorsList.ajax.reload(); 
    }
    






// ...........................................ADVANCE PAYMENT Creditors report Start ...........................//

advancePaymentCreditorsList = $('#advancePaymentCreditorsList').DataTable({
                            processing: true,
                            serverSide: true,
                            pageLength: 20,
                            lengthMenu: [10, 20, 40, 50, 100],
                            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
                                var oSettings = this.fnSettings ();
                                $('html, body').stop().animate({
                                    scrollTop: ($('#advancePaymentCreditorsList').offset().top)
                                }, 10);
                                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                                return nRow;
                            },
                            ajax: {
                                "url": "{!! route('admin.balance-sheet.get_advance_payment_report_listing') !!}",
                                "type": "POST",
                                "data":function(d) {
                                    d.searchform=$('form#filter').serializeArray(),
                                    d.date= $('#start_date').val(),
                                    d.end_date= $('#ends_date').val(),
                                    d.branch= $('#branch_filter').val(),
                                    d.head_id= $('#head_id').val()
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                            }, 
                            columns: [
                            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                            {data: 'name', name: 'name'},
                            {data: 'code', name: 'code'}, 
                            {data: 'amount', name: 'amount'},  
                            ],"ordering": false
                        });     



        function searchAdvacnePaymentCreditorsForm()
        {  
            advancePaymentCreditorsList.ajax.reload();
        }
        
        function resetAdvacnePaymentForm(){
            $('#start_date').val("");  
            advancePaymentCreditorsList.ajax.reload(); 
        }
        
                            
// ...........................................MemberShip report Start ...........................//
    
    member_ship = $('#member_ship').DataTable({
                            processing: true,
                            serverSide: true,
                            pageLength: 20,
                            lengthMenu: [10, 20, 40, 50, 100],
                            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
                                var oSettings = this.fnSettings ();
                                $('html, body').stop().animate({

                                scrollTop: ($('#member_ship').offset().top)

                }, 10);
                                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                                return nRow;
                            },
                            ajax: {
                                "url": "{!! route('admin.balance-sheet.get_member_ship_report_data') !!}",
                                "type": "POST",
                                "data":function(d) {
                                    d.searchform=$('form#filter').serializeArray(),
                                    d.date= $('#start_date').val(),
                                    d.branch= $('#branch_filter').val(),
                                    d.date= $('#date').val(),
                                    d.end_date= $('#ends_date').val()
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                            }, 
                        
                            columns: [
                            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                            {data: 'member_id', name: 'member_id'},
                            {data: 'member_name', name: 'member_name'}, 
                            {data: 'amount', name: 'amount'},  
                            ]
                        });     



        function searchmember_shipForm()
        {  
             $('#is_search').val("yes");

    
    var is_search=$('#is_search').val(); 
    var start_date=$('#start_date').val(); 
    var end_date= $('#ends_date').val();
     var queryParams = new URLSearchParams(window.location.search);
 
// Set new or modify existing parameter value. 
queryParams.set("date", start_date);
queryParams.set("end_date", end_date);
 
// Replace current querystring with the new one.
    window.location.href = "{{url('/')}}/admin/balance-sheet/current_liability/branch_wise/membership_fee?"+queryParams;
        }
        
        function resetmember_shipForm(){
            $('#is_search').val("no");  
    $('#branch').val(""); 
    $('#start_date').val(""); 
    var branch=$('#branch').val(); 
    var is_search=$('#is_search').val(); 
    var start_date=$('#start_date').val();  
    var end_date= $('#ends_date').val();
    var queryParams = new URLSearchParams(window.location.search);
 
// Set new or modify existing parameter value. 
queryParams.set("date", start_date);
queryParams.set("end_date", end_date);
 
// Replace current querystring with the new one.
    window.location.href = "{{url('/')}}/admin/balance-sheet/current_liability/branch_wise/membership_fee?"+queryParams; 
        }                   

// ...........................................Fixed Deposite report Start ...........................//
    
    fixed_deposit = $('#fixed_deposit').DataTable({
                            processing: true,
                            serverSide: true,
                            pageLength: 20,
                            lengthMenu: [10, 20, 40, 50, 100],
                            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
                                var oSettings = this.fnSettings ();
                                $('html, body').stop().animate({
                                    scrollTop: ($('#fixed_deposit').offset().top)
                                }, 10);
                                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                                return nRow;
                            },
                            ajax: {
                                "url": "{!! route('admin.balance-sheet.get_fixed_deposite_report_data') !!}",
                                "type": "POST",
                                "data":function(d) {
                                    d.searchform=$('form#filter').serializeArray(),
                                    d.date= $('#start_date').val(),
                                    d.branch= $('#branch_filter').val(),
                                    d.end_date= $('#ends_date').val(),
                                    d.head_id= $('#head_id').val(),
                                    d.info= $('#head_no').val()
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                            }, 
                            columns: [
                            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                            {data: 'member_id', name: 'member_id'},
                            {data: 'member_name', name: 'member_name'}, 
                            {data: 'amount', name: 'amount'},  
                            ]
                        });     



        function searchfixed_depositForm()
{  
    
        $('#is_search').val("yes");

    
    var is_search=$('#is_search').val(); 
    var start_date=$('#start_date').val();
    var end_date= $('#ends_date').val();
     var queryParams = new URLSearchParams(window.location.search);
 
// Set new or modify existing parameter value. 
queryParams.set("date", start_date);
queryParams.set("end_date", end_date);
 
// Replace current querystring with the new one.
    window.location.href = "{{url('/')}}/admin/balance-sheet/current_liability/branch_wise/fixed_deposite?"+queryParams;

    
}
    function resetfixed_depositForm()
{
    $('#is_search').val("no");  
    $('#branch').val(""); 
    $('#start_date').val(""); 
    var branch=$('#branch').val(); 
    var is_search=$('#is_search').val(); 
    var start_date=$('#start_date').val();  
    var queryParams = new URLSearchParams(window.location.search);
 
// Set new or modify existing parameter value. 
queryParams.set("date", start_date);
 
// Replace current querystring with the new one.
    window.location.href = "{{url('/')}}/admin/balance-sheet/current_liability/branch_wise/fixed_deposite?"+queryParams;
}
// ...........................................Tds report Start ...........................//
    
    tds_report = $('#tds_report').DataTable({
                            processing: true,
                            serverSide: true,
                            pageLength: 20,
                            lengthMenu: [10, 20, 40, 50, 100],
                            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
                                var oSettings = this.fnSettings ();
                                $('html, body').stop().animate({
                                    scrollTop: ($('#tds_report').offset().top)
                                }, 10);
                                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                                return nRow;
                            },
                            ajax: {
                                "url": "{!! route('admin.balance-sheet.get_tds_report_data') !!}",
                                "type": "POST",
                                "data":function(d) {
                                    d.searchform=$('form#filter').serializeArray(),
                                    d.date= $('#start_date').val(),
                                    d.branch= $('#branch_filter').val(),
                                    d.end_date= $('#ends_date').val(),
                                    d.head_id= $('#head_id').val()
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                            }, 
                            columns: [
                            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                            {data: 'member_id', name: 'member_id'},
                            {data: 'member_name', name: 'member_name'}, 
                            {data: 'amount', name: 'amount'},  
                            ]
                        });     



        function searchtdsForm()
{  
    
        $('#is_search').val("yes");

    
    var is_search=$('#is_search').val(); 
    var start_date=$('#start_date').val(); 
    var end_date=$('#ends_date').val(); 
     var queryParams = new URLSearchParams(window.location.search);
 
// Set new or modify existing parameter value. 
queryParams.set("date", start_date);
queryParams.set("end_date", end_date);
 
// Replace current querystring with the new one.
    window.location.href = "{{url('/')}}/admin/balance-sheet/current_liability/branch_wise/report?"+queryParams;

    
}
    function resettdsForm()
{
    $('#is_search').val("no");  
    $('#branch').val(""); 
    $('#start_date').val(""); 
    var branch=$('#branch').val(); 
    var is_search=$('#is_search').val(); 
    var start_date=$('#start_date').val();  
    var queryParams = new URLSearchParams(window.location.search);
 
// Set new or modify existing parameter value. 
queryParams.set("date", start_date);
 
// Replace current querystring with the new one.
    window.location.href = "{{url('/')}}/admin/balance-sheet/current_liability/branch_wise/tds_report?"+queryParams;
}
// ...........................................Saving report Start ...........................//
    
    saving = $('#saving_report').DataTable({
                            processing: true,
                            serverSide: true,
                            pageLength: 20,
                            lengthMenu: [10, 20, 40, 50, 100],
                            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
                                var oSettings = this.fnSettings ();
                                $('html, body').stop().animate({
                                    scrollTop: ($('#saving_report').offset().top)
                                }, 10);
                                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                                return nRow;
                            },
                            ajax: {
                                "url": "{!! route('admin.balance-sheet.saving_listing') !!}",
                                "type": "POST",
                                "data":function(d) {
                                    d.searchform=$('form#filter').serializeArray(),
                                    d.date= $('#start_date').val(),
                                    d.branch= $('#branch_filter').val(),
                                    d.end_date= $('#ends_date').val(),
                                    d.head_id= $('#head_id').val()
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                            }, 
                            columns: [
                            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                            {data: 'date', name: 'date'},
                            {data: 'payment_type', name: 'payment_type'},
                            {data: 'member_id', name: 'member_id'},
                            {data: 'member_name', name: 'member_name'}, 
                            {data: 'amount', name: 'amount'},  
                            ]
                        });     



        function searchsavingForm()
{  
    
        $('#is_search').val("yes");

    
    var is_search=$('#is_search').val(); 
    var start_date=$('#start_date').val(); 
    var end_date= $('#ends_date').val();
     var queryParams = new URLSearchParams(window.location.search);
 
// Set new or modify existing parameter value. 
queryParams.set("date", start_date);
queryParams.set("end_date", end_date);
 
// Replace current querystring with the new one.
    window.location.href = "{{url('/')}}/admin/balance-sheet/current_liability/branch_wise/report?"+queryParams;

    
}
    function resetsavingForm()
{
    $('#is_search').val("no");  
    $('#branch').val(""); 
    $('#start_date').val(""); 
    var branch=$('#branch').val(); 
    var is_search=$('#is_search').val(); 
    var start_date=$('#start_date').val();  
    var queryParams = new URLSearchParams(window.location.search);
 
// Set new or modify existing parameter value. 
queryParams.set("date", start_date);
 
// Replace current querystring with the new one.
    window.location.href = "{{url('/')}}/admin/balance-sheet/current_liability/branch_wise/tds_report?"+queryParams;
}

</script>