<script type="text/javascript">
var loanRecoveryTable;
var loanRequestTable;
var groupLoanRequestTable;
var loantable;
var groupLoanRecoveryTable;
var loanplantable;
$(document).ready(function() {
    $('.required').css('color','red');

    //Search Form Validation
    $("#filter").validate({
        rules: 
        {
            'company_id' : 'required',
            'loan_type' : 'required'
        },
        messages:
        {
            'company_id' : 'Select the Company.',
            'loan_type' : 'Select the loan type.'
        }
    });

    //Loan Recovery Search Form Validation
    $("#loan_recovery_filter").validate({
        rules: 
        {
            'loan_recovery_company_id' : 'required',
            'loan_recovery_type' : 'required'
        },
        messages:
        {
            'loan_recovery_company_id' : 'Select the Company.',
            'loan_recovery_type' : 'Select the loan type.'
        }
    });

    //Plans fetch as per company id for loan search form
    $(document).on('change', '#company_id', function() 
    {
        $('#plan').find('option').remove().end().append(' <option value="">----Select Loan Plan----</option>').val('');
        var company_id = $('#company_id').val();
       
        $.ajax({
            type: "POST",
            url : '{{ route("admin.loan.fetch") }}',
            dataType: 'JSON',
            data: {
                'company_id': company_id
            },
            success: function(e) {
                if (e.data != '') {
                    $("#plan").append(e.data);
                }

            }
        });
    });

    //Plans fetch as per company id for loan recovery search form
    $(document).on('change', '#loan_recovery_company_id', function() 
    {
        $('#plan').find('option').remove().end()
            .append(' <option value="">----Select Loan Plan----</option>').val('');
        var company_id = $('#loan_recovery_company_id').val();
       
        $.ajax({
            type: "POST",
            url : '{{route("admin.loan.fetch")}}',
            dataType: 'JSON',
            data: {
                'company_id': company_id
            },
            success: function(e) {
                if (e.data != '') {
                    $("#loan_recovery_plan").append(e.data);
                }

            }
        });
    });

    $('#searchForm').validate({
        rules:{
            searchCompanyId: 'required'
        },
        messages:{
            searchCompanyId:'Select the company.'
        }
    });

    $(document).on('click','#search',function(e)
    {
        $('.data_div').addClass('d-none');
        e.preventDefault();
        if($("#searchForm").valid())
        {
            // plantable.draw();
            

            //listing the data
            // var company_id = $('#searchCompanyId').val();
            
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
                "data" : {
                    'company_id' : $("#searchForm").find('select').find(':selected').val(),
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                {data: 'type', name: 'type'},
                {data: 'company_name', name: 'company_name'},
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
            ],"bDestroy": true,"ordering": false,
            });
            $('.data_div').removeClass('d-none');
            $(loanplantable.table().container()).removeClass( 'form-inline' );
                
        }else{
            $('.data_div').addClass('d-none');
        }
    });

    $('#loanplanform').validate({
        rules: {
            companyId:{
                required: true,
            },
            name:{
                required:true,
            },
            // code:{
            //     required:true,
            //     number: true
            // },
            
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
            'charges_emi_option':'required',
            'charges_tenure' : 'required',
            'insurance_charges_emi_option' : 'required',
            'insurance_charges_tenure': 'required',
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

  $('#charge_type').change(function (e) {
		
		var chargetype	=	jQuery("#charge_type").val();
		
		if(chargetype == 0){
			$('#charge').attr({min:0, max:100});	
		}else{
			
			$('#charge').removeAttr('max');
		}
	});	

  $('#ins_charge_type').change(function (e) {
        
        var chargetype  =   jQuery("#ins_charge_type").val();
        
        if(chargetype == 0){
            $('#ins_charge').attr({min:0, max:100});    
        }else{
            
            $('#ins_charge').removeAttr('max');
        }
    }); 

    $('#charge_type ').change(function(){
    $('#charge').val('');
  })
     $('#ins_charge_type ').change(function(){
    $('#ins_charge').val('');
  })

 
 
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
            "data":function(d) {d.searchform=$('form#loan_table').serializeArray()},
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
        ],"ordering": false,
    });
    $(loantable.table().container()).removeClass( 'form-inline' );

    
            $('#loan_type').on('change',function(){
                loanType = $('#loan_type option:selected').val();
                $('#loan_category').val('');
                if(loanType == 'L') {
                    $('.loan_cat') .show() ;    
                    $('.grploan_cat') .hide()
                }
                else{
                    $('.loan_cat') .hide() ;    
                    $('.grploan_cat') .show()
                }

             })

            //  $('#loan_category').on('change',function(){
            //    const  headId = $('#loan_category :selected').attr('data-head');
            //     $('#parent_head_id').val(headId);
               
            //  })

$('#loan_type').on('change', function() {
  // Get the selected option from the first dropdown
  const loan_type = $('#loan_type').val();

  // Clear existing options in the second dropdown
  $('#loan_category').empty();

  // Create and append new options to the second dropdown
  if (loan_type == 'L') {
    $('#loan_category').append('<option value="1">Personal loan</option><option value="2">Staff Loan</option><option value="4">Loan against Investment</option>');
  }
  if (loan_type == 'G') {
    $('#loan_category').append('<option value="3">Group Loan</option>');
  }
});

            $(document).on('mouseover','.effective_from,.effective_to',function(){
                var EndDate = $('#create_application_date').val();
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
              url: "{--!! route('admin.loan.getactiveplanlist') !!--}",
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


    $('#save_tenure').click(function(e) {
        var formData = new FormData(document.forms['loanform']);

        $.ajax({
            type: "POST",
            url: "{--!! route('admin.loan.getDublicateTenure') !!--}",
            dataType: 'JSON',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response==1)
                {
                    swal({
                        title: "Tenure Already Exist",
                        text: "Are you sure,you want to create new teure?",
                        type: "warning",
                        showCancelButton: !0,
                        confirmButtonText: "Yes",
                        cancelButtonText: "No",
                        reverseButtons: !0
                        },
                        function (changeStatus) {
                        if (!changeStatus) return;
                        $("#loanform").submit();
                        
                    });
                }
                else if(response==2)
                {
                    swal( 'Tenure Already Exist!', 'Tenure allready created with same detail', 'warning' )
                }
                else
                {
                    $("#loanform").submit();
                }
            }
        });

        
    });


}); 

//Search Button Function 
function searchForm()
{
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        $('.d-none').removeClass('d-none');
        loantable.draw();
    }
}

//Reset Button Function
function resetForm()
{
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    $('#is_search').val("no");
    loantable.draw();
}

//Search Button Function for loan plan listing
function loanSearchForm()
{
    if($('#searchForm').valid())
    {
        $('#is_search').val("yes");
        $('.d-none').removeClass('d-none');
        loanplantable.draw();
    }
}
 
 

function change_status(status,id)
{
    created_at=$('#created_at').val(); 

        swal({
			title: "Change Status?",
			text: "Are you sure want to change the Status?",
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
                                url: "{!! route('admin.loan.plan_status_change') !!}",
                                dataType: 'JSON',
                                data: {'status':status,'id':id,'created_at':created_at},
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                    swal( 'Success!', ''+response.msg+'', 'success' )
                                    loantable.draw();
                                }
                            });
			
		});
}


          

function change_plan_status(id)
{
    created_at=$('#created_at').val(); 
    swal({
			title: "Change Status?",
			text: "Are you sure want to change the Status?",
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
				url: "{!! route('admin.loan.plan.statusChange') !!}",
				dataType: 'JSON',
				data: {'id':id,'gdate':created_at},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(data) {
					// swal( 'Success!', ''+response.msg+'', 'success' )
					// loanplantable.draw();
                    if(data.response == 1)
                    {
                        swal({
                            title: 'Status Changed',
                            text: 'Status changed successfully.',
                            type: 'success',
                        });
                        $('#search').click();
                    }
				}
			});
			
		});
}
$(document).on('change','#loan_category',function () {
    // console.log('loan_category',$(this).val());
    const loan_category = $(this).val();
    if (loan_category == 4) {
        $('#file_charge_include , #insurance_file_charge').hide();
    }
    else{
        $('#file_charge_include , #insurance_file_charge').show();
    }
})
</script>
