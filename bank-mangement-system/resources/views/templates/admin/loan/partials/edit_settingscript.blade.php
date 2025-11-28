<script type="text/javascript">
var loanRecoveryTable;
var loanRequestTable;
var groupLoanRequestTable;
var loantable;
var groupLoanRecoveryTable;
var loanplantable;
$(document).ready(function() {
const loan_cat =     $('#loan_category').val();
    if (loan_cat == 4) {
        $('#insurance_file_charge , #file_charge_include').hide();
    }
    $('#loanplanform').validate({
        rules: {
            name:{
                required:true,
            },
            code:{
                required:true,
                number: true
            },
            
            max_amount:{
                required:true,
                decimal: true,
                zero: true,
                maxAmount:true,
            },
            min_amount:{
                required:true,
                decimal: true,
                zero: true,
            },
            file_max_amount:{
                required:true,
                decimal: true,
                zero: true,    
                maxAmount:true, 
            },
            file_min_amount:{
                required:true,
                decimal: true,
                zero: true,    
                maxAmount:true, 
            },
            ins_min_amount:{
                required:true,
                decimal: true,
                zero: true,    
               
            },
            ins_max_amount:{
                required:true,
                decimal: true,
                zero: true,    
                maxAmount:true, 
            },
            charge:{
                required:true,
                decimal: true,
                zero: true,    
              
            },
            ins_charge:{
                required:true,
                decimal: true,
                zero: true,    
              
            },
            tenure:{
                required:true,
                number:true,
            },
            roi:{
                required:true,
                max:100,
                number:true,
                decimal: true,
            },

            'loan_type' : 'required',
            'loan_category' : 'required',
            'effective_from' : 'required',
            'emi_option':'required',
            'tenure_effective_from':'required',
            'charge_type' : 'required',
            'file_effective_from':'required',
            'ins_charge_type' : 'required',
            'ins_effective_from':'required',
           
        }
    });
   
    $('.add_qualification_trash').click(function(){
       const tenureId =  $(this).attr('data-Id');
       deleteRecord(tenureId,'tenure');
  })
  $('.ins_charge_btn_trash').click(function(){
       const insuranceId =  $(this).attr('data-Id');
       deleteRecord(insuranceId,'insurance');
  })
  $('.loan_file_btn_trash').click(function(){
       const fileId =  $(this).attr('data-Id');
       deleteRecord(fileId,'file');
  })
    $.validator.addMethod("maxAmount", function(value, element,p) {  
        const MinAmount = $('#min_amount').val();
        const maxAmount =  $('#max_amount').val();
        const FileMinAmount = $('#file_min_amount').val();
        const FilemaxAmount =  $('#file_max_amount').val();
        const InsMinAmount = $('#ins_min_amount').val();
        const InsmaxAmount =  $('#ins_max_amount').val();
        const EditInsMinAmount = $('#d_min_amount').val();
        const EditInsmaxAmount =  $('#d_max_amount').val();

        console.log(MinAmount,maxAmount,FileMinAmount,FilemaxAmount,InsMinAmount,InsmaxAmount);
      
        if(parseInt(MinAmount) > parseInt(maxAmount))
        {
           
         
            $.validator.messages.maxAmount = "Max Amount Should be Greater Than Minimum Amount";
               result = false;
        }
        else if(parseInt(FileMinAmount) > parseInt(FilemaxAmount))
        {
           
           
            $.validator.messages.maxAmount = "Max Amount Should be Greater Than Minimum Amount";
            result = false;

        }
        else if(parseInt(InsMinAmount) > parseInt(InsmaxAmount))
        {
           
           
            $.validator.messages.maxAmount = "Max Amount Should be Greater Than Minimum Amount";
            result = false;
        }
        else if(parseInt(EditInsMinAmount) > parseInt(EditInsmaxAmount))
        {
           
           
            $.validator.messages.maxAmount = "Max Amount Should be Greater Than Minimum Amount";
            result = false;
        }
        else{
           
            $.validator.messages.maxAmount = "";
            result = true;
        }
      
    
    return result;
  }, "");

  $('.charge_type').change(function (e) {
		
		var chargetype	=	jQuery(".charge_type").val();
		
		if(chargetype == 0){
			$('.charge').attr({min:0, max:100});	
		}else{
			
			$('.charge').removeAttr('max');
		}
	});	

 
    loantable = $('#loan_table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        searching:false,
        ordering: false,
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#loan_table').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.loan.list') !!}",
            "type": "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'type', name: 'type'},
            {data: 'name', name: 'name'},
            {data: 'tenure', name: 'tenure'},
            {data: 'emi_option', name: 'emi_option'},
            {data: 'roi', name: 'roi'}, 
            {data: 'effective_from', name: 'effective_from'},
            {data: 'effective_to', name: 'effective_to'},
            {data: 'created_by', name: 'created_by'},
            {data: 'status', name: 'status', searchable: false ,
                "render":function(data, type, row){
                    if(row.status==0){
                        return "<span title='Change Status' class='badge badge-danger' >Inactive</span>";
                    }else{
                        return "<span title='Change Status'  class='badge badge-success' onclick='change_status("+row.status+","+row.id+");' >Active</span>";
                    }
                }
            },
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false
    });
    $(loantable.table().container()).removeClass( 'form-inline' );
    loanplantable = $('#loan_plan_table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            searching:false,
            ordering: false,
            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings ();
                $('html, body').stop().animate({
                scrollTop: ($('#loan_plan_table').offset().top)
            }, 1000);
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.loan.planlist') !!}",
                "type": "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                {data: 'type', name: 'type'},
                {data: 'name', name: 'name'},
                {data: 'code', name: 'code'},
                {data: 'category', name: 'category'},
                {data: 'min_amount', name: 'min_amount'},
                {data: 'max_amount', name: 'max_amount'},
                {data: 'effective_from', name: 'effective_from'},
                {data: 'effective_to', name: 'effective_to'},
                {data: 'created_by', name: 'created_by'},
                {data: 'status', name: 'status', searchable: false ,
                    "render":function(data, type, row){
                        if(row.status==0){
                            return "<span title='Change Status' class='badge badge-danger' >Inactive</span>";
                        }else{
                            return "<span title='Change Status'  class='badge badge-success' onclick='change_plan_status("+row.id+");' >Active</span>";
                        }
                    }
                },
                {data: 'created_at', name: 'created_at'},
                {data: 'action', name: 'action',orderable: false, searchable: false},
            ],"ordering": false
            });
            $(loanplantable.table().container()).removeClass( 'form-inline' );


            $(document).on('mouseover','.effective_from,.effective_to',function(){
                var EndDate = $('#effective_from').val();
                $('#file_effective_from').datepicker( {
                format: "dd/mm/yyyy",
                orientation: "bottom",
                autoclose: true,
                startDate: EndDate,
                minDate: EndDate,
                }).on("changeDate", function (e) {
                    $('#file_effective_to').datepicker('setStartDate', e.date,'format', "dd/mm/yyyy");
                   
                });;
                $('#ins_effective_from').datepicker( {
                format: "dd/mm/yyyy",
                orientation: "bottom",
                autoclose: true,
                startDate: EndDate,
                minDate: EndDate,
                }).on("changeDate", function (e) {
                    $('#ins_effective_to').datepicker('setStartDate', e.date);
                });;
                $('#tenure_effective_from').datepicker( {
                format: "dd/mm/yyyy",
                orientation: "bottom",
                autoclose: true,
                startDate: EndDate,
                minDate: EndDate,
                }).on("changeDate", function (e) {
                    $('#tenure_effective_to').datepicker('setStartDate', e.date);
                });
                $('#d_effective_from').datepicker( {
                format: "dd/mm/yyyy",
                orientation: "bottom",
                autoclose: true,
                startDate: EndDate,
                minDate: EndDate,
                }).on("changeDate", function (e) {
                    $('#d_effective_to').datepicker('setStartDate', e.date);
                });;
                $('#effective_from').datepicker( {
                format: "dd/mm/yyyy",
                orientation: "bottom",
                autoclose: true,
                startDate: EndDate,
                minDate: EndDate,
                }).on("changeDate", function (e) {
                    $('#tenure_effective_from').datepicker('setDate', e.date);
                    $('#ins_effective_from').datepicker('setDate', e.date);
                    $('#file_effective_from').datepicker('setDate', e.date);
                });;;

                $('#file_effective_to,#ins_effective_to,#tenure_effective_to,#d_effective_to').datepicker( {
                format: "dd/mm/yyyy",
                orientation: "bottom",
                autoclose: true,
                startDate: EndDate,    
               
                });;

        })    

        // $(document).on('mouseover','.effective_from,.effective_to,.tenure_effective_from',function(){
        //     var EndDate = $('#create_application_date').val();
        //     $('.effective_from,.tenure_effective_from,.tenure_effective_to,.effective_to,.ins_effective_to,.ins_effective_from,.file_effective_to,.file_effective_from').datepicker( {
        //     format: "dd/mm/yyyy",
        //     orientation: "bottom",
        //     autoclose: true,
        //     startDate: EndDate,
        //     // startDate: '01/04/2021',
        //     minDate: EndDate,
        //     onSelect: function(selected) {
        //         $("#file_effective_to").datepicker("option", "minDate", selected)
        //     }
        //     });
        // })    
      
   
    var a=0;
    var b=0;
    var c=0;
    

  
  
  

    $.validator.addMethod("zero", function(value, element,p) {     
      if(parseFloat(value)>0)
      {
        $.validator.messages.zero = "";
        result = true;
      }else{
        $.validator.messages.zero = "Value must be greater than 0.";
        result = false;  
      }
    
    return result;
  }, "");
  $('.charge_type ').change(function(){
    $('#charge').val('');
  })
  $('#loan_type').on('change',function(){
            loanType = $('#loan_type option:selected').val();
            $('#loan_category').val('');
            if(loanType == 'L') {
                 $('.loan_cat') .show() ;    
                 $('.grploan_cat') .hide()
            }
            else{
                $('.loan_cat') .hide() ;
                $('.loan_cat') .prop('selected',false) ;        
                 $('.grploan_cat') .show()
            }
            

                

    })

  const deleteRecord = (tenureId,type) => {
    swal({
			title: "Delete Record?",
			text: "Are you sure want to Delete the Record. ?",
			type: "warning",
			showCancelButton: !0,
			confirmButtonText: "Yes",
			cancelButtonText: "No",
			reverseButtons: !0
			},
			function (changeStatus) {
			if (!changeStatus) return;
            $.ajax({
                                type: "POST",
                                url: "{!! route('admin.loan.delete_loan_tenure_charge') !!}",
                                dataType: 'JSON',
                                data: {'id':tenureId,'type':type},
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                    if(response.result)
                                    {
                                        swal( 'Success!', ''+response.message+'', 'success' )
                                        location.reload();
                                    }
                                    else{
                                        swal( 'Warning!', ''+response.message+'', 'info' )
                                    }
                                  
                                
                                }
                            });
			
		});
  }


   $.validator.addMethod("decimal", function(value, element,p) {     
      if(this.optional(element) || /^\d*\.?\d*$/g.test(value)==true)
      {
        $.validator.messages.decimal = "";
        result = true;
      }else{
        $.validator.messages.decimal = "Please enter valid numeric number.";
        result = false;  
      }
    
    return result;
  }, ""); 

$.validator.addMethod("roi100", function(value, element,p) {  
      var val1 = $('#roi').val();
      
      var sum =parseInt(val1);
              if(sum>100)
                {
                  result = false;
                  $.validator.messages.roi100 = "ROI percentage not greater than 100";
                  
                }
                else
                {
                  result = true;
                  $.validator.messages.roi100 = "";
                }
      
      
    
    return result;
  }, "");





    // $('#loanform').validate({
    //     rules: { 
    //         loan_id:{
    //             required:true,
    //         },
    //         // tenure:{
    //         //     required:true,
    //         //     number: true
    //         // },
    //         // roi:{
    //         //     required:true,
    //         //     decimal: true,
    //         //     zero: true,
    //         //     roi100: true,
    //         // },  
           
    //         'emi_option' : 'required',
    //         'effective_from' : 'required',
    //     }
    // });
    
    // $('#max_amount,#min_amount,.max_amount,.min_amount').on('keyup',function(){
    //     const MinAmount = $('#min_amount').val();
    //     const maxAmount =  $('#max_amount').val();
    //     if(parseInt(MinAmount) > parseInt(maxAmount))
    //     {
    //         $('#warning-msg').html('Max Amount Should be Greater Than Minimum Amount');
    //     }
    //     else{
    //         $('#warning-msg').html('');
    //     }
    // })

    
 
 
    
    //Get Loan type onchange get create tenure
    $('#loan_type_plan').on('change',function(){
        loanType = $('#loan_type_plan option:selected').val();
        $('#loan_id') .empty();
        $.ajax({
              type: "POST",
              url: "{!! route('admin.loan.getactiveplanlist') !!}",
              dataType: 'JSON',
              data: {'loan_type':loanType},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) {

                $('#loan_id').find('option').remove();
                $('#loan_id').append('<option value="">------- Select---- </option>');
                 $.each(response.loanPlanList, function (index, value) {
                        $("#loan_id").append("<option value='"+value.id+"'>"+value.name+" ("+value.code+")</option>");
                    });
              }
        });

    })
 

    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });
    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });


  



}); 
 
 $(document).on('change','#loan_category',function(){
    if ($(this).val() == 4) {
        $('#insurance_file_charge , #file_charge_include').hide();
    }
    else{
        $('#insurance_file_charge , #file_charge_include').show();
    }
 })

          
 $('.status_button').on('click',function(){
    var id = $(this).data('id');
    var gdate = $('.gdate').text();
    swal({
        type: 'warning',
        title:'Status Change',
        text:'Are you sure want to change the status change?',
        showCancelButton: true,
        confirmButtonColor: '#000',
        cancelButtonColor: '#a1a1a1',
        cancelButtonText: 'Cancel'

    },function(isConfirm)
    {
        if(isConfirm)
        {
            $.ajax({
                url: "{{route('admin.loan.loan_tenure_status')}}",
                type: "POST",
                data:{id:id,
                gdate: gdate},
                success: function(e)
                {
                    if(e.msg > 0)
                    {
                        swal({
                            type: 'success',
                            title:'Success',
                            text: 'Status Changed Successfully.',
                        },function(isConfirm){
                            if(isConfirm)
                            {
                                window.location.reload();
                            }
                        });
                    }
                    else
                    {
                        swal('Wrong','Something Wrong.','error');
                    }
                },
                error: function(error)
                {
                    alert('Something is wrong.');
                }
            });
        }
    });
});
</script>
