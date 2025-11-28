<script type="text/javascript">
    var salaryTable;
    $(document).ready(function () {
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
        salaryTable = $('#salary_list').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
                var oSettings = this.fnSettings ();
                $('html, body').stop().animate({
                scrollTop: ($('#salary_list').offset().top)
            }, 1000);
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.hr.employ_salary_listing') !!}",
                "type": "POST",
                "data":function(d) {
                    d.searchform=$('form#filter').serializeArray(),
                    d.company_id=$('#company_id').val(), 
                    d.category=$('#category').val(),
                    d.designation=$('#designation').val(),
                    d.employee_name=$('#employee_name').val(),
                    d.employee_code=$('#employee_code').val(), 
                    d.is_search=$('#is_search').val(),
                    d.month=$('#month').val(), 
                    d.year=$('#year').val(), 
                    d.status=$('#status').val(),
                    d.export=$('#export').val()
                }, 
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                {data: 'company_name', name: 'company_name'},
                {data: 'category_name', name: 'category_name'},
                {data: 'designation_name', name: 'designation_name'},
                {data: 'month', name: 'month'},
                {data: 'year', name: 'year'},
                {data: 'branch', name: 'branch'},                 
                {data: 'employee_name', name: 'employee_name'},
                {data: 'employee_code', name: 'employee_code'},
                {data: 'fix_salary', name: 'fix_salary'},
                {data: 'leave', name: 'leave'},
                {data: 'total_salary', name: 'total_salary'},
                {data: 'deduction', name: 'deduction'},
                {data: 'incentive_bonus', name: 'incentive_bonus'},

                {data: 'paybale_amount', name: 'paybale_amount'},
                {data: 'esi_amount', name: 'esi_amount'},
                {data: 'pf_amount', name: 'pf_amount'},
                {data: 'tds_amount', name: 'tds_amount'},
                {data: 'total_payable_salary', name: 'total_payable_salary'},

                
                {data: 'advance_payment', name: 'advance_payment'},
                {data: 'settle_amount', name: 'settle_amount'},
                {data: 'transferred_salary', name: 'transferred_salary'},
                {data: 'transferred_in', name: 'transferred_in'},            
                
                {data: 'employee_ssb', name: 'employee_ssb'},
                {data: 'employee_bank', name: 'employee_bank'},
                {data: 'employee_bank_ac', name: 'employee_bank_ac'}, 
                {data: 'employee_bank_ifsc', name: 'employee_bank_ifsc'},
                {data: 'transferred_date', name: 'transferred_date'},   
                {data: 'company_bank', name: 'company_bank'},
                {data: 'company_bank_ac', name: 'company_bank_ac'},
                {data: 'payment_mode', name: 'payment_mode'},
                {data: 'company_cheque_id', name: 'company_cheque_id'},
                {data: 'online', name: 'online'},
                {data: 'neft_charge', name: 'neft_charge'},
                {data: 'transfer_status', name: 'transfer_status'}, 
                {data: 'action', name: 'action',orderable: false, searchable: false},
            ]
        });
        $(salaryTable.table().container()).removeClass( 'form-inline' );

 
        /*
        $('.export').on('click',function(){
            var extension = $(this).attr('data-extension');
            $('#emp_export').val(extension);
            $('form#filter').attr('action',"{!! route('admin.hr.employ_salary_list_export') !!}");
            $('form#filter').submit();
            return true;
        }); 
        */
        $('.export').on('click',function(e){
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#emp_export').val(extension);
            
            var formData = jQuery('#filter').serializeObject();
            var chunkAndLimit = 50;
            $(".spiners").css("display","block");
            $(".loaders").text("0%");
            doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit);
            $("#cover").fadeIn(100);
        });
	
	
        // function to trigger the ajax bit
        function doChunkedExport(start,limit,formData,chunkSize){
            formData['start']  = start;
            formData['limit']  = limit;
            jQuery.ajax({
                type : "post",
                dataType : "json",
                url :  "{!! route('admin.hr.employ_salary_list_export') !!}",
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

        $( document ).ajaxStart(function() {
            $( ".loader" ).show();
        });

        $( document ).ajaxComplete(function() {
            $( ".loader" ).hide();
        });


        $('#filter').validate({
        rules: {
        //  status:"required",  

        },
        messages: {  
        //     status: "Please select status",
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

$(document).on('change','#category',function(){ 
    var category=$('#category').val();

          $.ajax({
              type: "POST",  
              url: "{!! route('admin.designationByCategorySalary') !!}",
              dataType: 'JSON',
              data: {'category':category},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                $('#designation').find('option').remove();
                $('#designation').append('<option value="">Select Designation</option>');
                $('#designation').append('<option value="all">All</option>');
                 $.each(response.data, function (index, value) { 
                        $("#designation").append("<option value='"+value.id+"'>"+value.designation_name+"</option>");
                        
                    }); 

              }
          });

  });


 
});

function searchForm()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
         $(".table-section").removeClass('hideTableData');
        salaryTable.draw();
    }
}
function resetForm()
{
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    $('#is_search').val("no");
 
    //$('#branch').val('');
    $('#category').val('');
    $('#company_id').val('');
    $('#designation').val('');
    $('#employee_name').val('');
    $('#employee_code').val(''); 
    $('#month').val(''); 
    $('#year').val(''); 
    $('#status').val('1');
    $(".table-section").addClass("hideTableData");
    salaryTable.draw();
}

</script>