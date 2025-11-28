<script type="text/javascript">
	$(document).ready(function(){

		// DatePicker Apply
        // var fdCreateDate = $('#fd_create_date').val();
        // var currentDAte =  $('#create_application_date').val();
		// $('#date').datepicker({
		// 	format: "dd/mm/yyyy",
        //     orientation: "bottom",
        //     autoclose: true,
        //     setDate: new Date(),
            // endDate:currentDAte,
		// });

        $('#date').on('mouseenter', function() {
            var enddate = $('#create_application_date').val();
            // alert(enddate);
            var fdCreateDate = $('#fd_create_date').val();
            
            $('#date').datepicker({
                format: "dd/mm/yyyy",
                orientation: "bottom",
                autoclose: true,
                endDate: enddate,
                startDate : fdCreateDate,
            });
        });

		
        $('#received_bank_name').on('change',function(selected_account){
            var bank_id=$(this).val();
          $.ajax({
              type: "POST", 
              url: "{!! route('admin.bank_account_list') !!}",
              dataType: 'JSON',
              data: {'bank_id':bank_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) {                 

                $('#received_bank_account').find('option').remove();
                $('#received_bank_account').append('<option value="">Select account number</option>');
                 $.each(response.account, function (index, value) { 
                        $("#received_bank_account").append("<option value='"+value.id+"'>"+value.account_no+"</option>");

                    });
              }
          });
        })
        


        $.validator.addMethod("zero", function(value, element,p) {     
          if(value>=0)
          {
            $.validator.messages.zero = "";
            result = true;
          }else{
            $.validator.messages.zero = "Amount must be greater than or equal to 0.";
            result = false;  
          }
        
        return result;
        }, "");

    
        $.validator.addMethod("decimal", function(value, element,p) {     
          if(this.optional(element) || $.isNumeric(value)==true)
          {
            $.validator.messages.decimal = "";
            result = true;
          }else{
            $.validator.messages.decimal = "Please Enter valid numeric number.";
            result = false;  
          }
        
          return result;
        }, "");


        $('#company_fd_interest').validate({
            rules:{
              
                
                date:{
                    required:true
                },
                interest_amount:{
                    required:true,
                    zero:true,
                    decimal:true,
                },
                tds_amount:{
                     required:true,
                     zero:true,
                     decimal:true,
                },
              
                received_bank_name:{
                    required:true
                },
                received_bank_account:{
                    required:true
                },
                remark:{
                    required:true
                },
                interest_type:{
                    required:true,
                }
            },
            messages:{
              
                date:{
                    required:'Please Enter Date',
                },
               
                interest_amount:{
                    required:"Please Enter the Amount",
                    decimal : "Please enter a valid amount.",
                },
                tds_amount:{
                    required:"Please Enter the Amount",
                    decimal : "Please enter a valid amount.",
                },
                received_bank_name:{
                    required:'Please Select Receive Bank Name',
                },
                received_bank_account:{
                    required:'Please Select Account Number',
                },
                remark:{
                    required:'Please Enter Remark',
                },
                interest_type:{
                    required:'Please Select Interest Type',
                }
            }
        })      

    transaction = $('#transaction_list').DataTable({
        processing: true,
        serverSide: true,
         pageLength: 20,
         lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#transaction_list').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.interest.transaction_listing') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter').serializeArray(),
                                d.bound_id = $('#bound_id').val()
            }, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'transaction_date', name: 'transaction_date'},
            {data: 'bank_name', name: 'bank_name'},
            {data: 'transaction_type', name: 'transaction_type'},
            {data: 'fd_no', name: 'fd_no'},
            {data: 'interest_amount', name: 'interest_amount'},

            {data: 'tds_amount', name: 'tds_amount'},
            {data: 'total_amount', name: 'total_amount'},
            {data: 'withdrawal_amount', name: 'withdrawal_amount'},
            {data: 'remark', name: 'remark'},
            {data: 'receive_bank', name: 'receive_bank'},
            {data: 'receive_bank_account', name: 'receive_bank_account'},
            
        ],"ordering": false
    });
    $(transaction.table().container()).removeClass( 'form-inline' );

     $('.export').on('click',function(e){
        e.preventDefault();
        var extension = $(this).attr('data-extension');
        $('#company_bond_transaction').val(extension);
        if(extension == 0)
        {
        var formData = jQuery('#filter').serializeObject();
        var chunkAndLimit = 50;
        $(".spiners").css("display","block");
        $(".loaders").text("0%");
        doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit);
        $("#cover").fadeIn(100);
        }
        else{
            $('#company_bond_transaction').val(extension);

            $('form#filter').attr('action',"{!! route('admin.comapnyBond.interest_transaction.export') !!}");

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
            url :  "{!! route('admin.comapnyBond.interest_transaction.export') !!}",
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

		
    $('#interest_type').on('change',function(){
       
        let type = $('option:selected',this).val();
       if(type == 0)
       {
        $('.bank_details').show();
       } 
       else if(type == 1){
        $('.bank_details').hide();
       }
       $('#interest_type').val(type);
    })
});
</script>