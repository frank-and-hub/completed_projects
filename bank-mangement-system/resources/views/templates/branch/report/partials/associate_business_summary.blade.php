<script type="text/javascript">

    var employeeTable;

$(document).ready(function () {

   var date = new Date();
 var Startdate = new Date();
 Startdate.setMonth(Startdate.getMonth() - 3);
  $('#start_date').datepicker({

    format: "dd/mm/yyyy",

    todayHighlight: true,  
   startDate:Startdate,
    endDate: date, 

    autoclose: true

  });



  $('#end_date').datepicker({

    format: "dd/mm/yyyy",

    todayHighlight: true, 

    endDate: date,  

    autoclose: true

  });

     employeeTable = $('#associate_bussiness_listing').DataTable({

        processing: true,

        serverSide: true,

         pageLength: 20,

         lengthMenu: [10, 20, 40, 50, 100],

        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      

            var oSettings = this.fnSettings ();

            $('html, body').stop().animate({

            scrollTop: ($('#associate_bussiness_listing').offset().top)

        }, 1000);

            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);

            return nRow;

        },

        ajax: {

            "url": "{!! route('branch.report.associate_business_summary') !!}",

            "type": "POST",

            "data":function(d) {



                d.searchform=$('form#filter').serializeArray(),

                d.start_date=$('#start_date').val(),

                d.end_date=$('#end_date').val(),

                d.branch_id=$('#branch_id').val(), 

                d.zone=$('#zone').val(), 

                d.region=$('#region').val(), 

                d.sector=$('#sector').val(),

                d.associate_code=$('#associate_code').val(),

                d.is_search=$('#is_search').val(),

                d.export=$('#export').val()

            

            }, 

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            },

        },

        columns: [

           {data: 'DT_RowIndex', name: 'DT_RowIndex'},

            {data: 'branch', name: 'branch'},

            {data: 'branch_code', name: 'branch_code'},

            {data: 'sector_name', name: 'sector_name'},

            {data: 'region_name', name: 'region_name'},

            {data: 'zone_name', name: 'zone_name'},  

            {data: 'member_id', name: 'member_id'},

            {data: 'name', name: 'name'}, 

            {data: 'cadre', name: 'cadre'}, 

            



            {data: 'daily_new_ac', name: 'daily_new_ac'},

            {data: 'daily_deno_sum', name: 'daily_deno_sum'}, 

            {data: 'daily_renew_ac', name: 'daily_renew_ac'},

            {data: 'daily_renew', name: 'daily_renew'}, 





            {data: 'rd_new_ac', name: 'rd_new_ac'},

            {data: 'rd_deno_sum', name: 'rd_deno_sum'}, 

            {data: 'rd_renew_ac', name: 'rd_renew_ac'},

            {data: 'rd_renew', name: 'rd_renew'},





            {data: 'frd_new_ac', name: 'frd_new_ac'},

            {data: 'frd_deno_sum', name: 'frd_deno_sum'}, 

            {data: 'frd_renew_ac', name: 'frd_renew_ac'},

            {data: 'frd_renew', name: 'frd_renew'},



            {data: 'fd_new_ac', name: 'fd_new_ac'},

            {data: 'fd_deno_sum', name: 'fd_deno_sum'}, 

            // {data: 'fd_renew_ac', name: 'fd_renew_ac'},

            // {data: 'fd_renew', name: 'fd_renew'},



            {data: 'ffd_new_ac', name: 'ffd_new_ac'},

            {data: 'ffd_deno_sum', name: 'ffd_deno_sum'}, 

            // {data: 'ffd_renew_ac', name: 'ffd_renew_ac'},

            // {data: 'ffd_renew', name: 'ffd_renew'},



            {data: 'kanyadhan_new_ac', name: 'kanyadhan_new_ac'},

            {data: 'kanyadhan_deno_sum', name: 'kanyadhan_deno_sum'}, 

            {data: 'kanyadhan_renew_ac', name: 'kanyadhan_renew_ac'},

            {data: 'kanyadhan_renew', name: 'kanyadhan_renew'},



            {data: 'bhavhishya_new_ac', name: 'bhavhishya_new_ac'},

            {data: 'bhavhishya_deno_sum', name: 'bhavhishya_deno_sum'}, 

            {data: 'bhavhishya_renew_ac', name: 'bhavhishya_renew_ac'},

            {data: 'bhavhishya_renew', name: 'bhavhishya_renew'},



            {data: 'jeevan_new_ac', name: 'jeevan_new_ac'},

            {data: 'jeevan_deno_sum', name: 'jeevan_deno_sum'}, 

            {data: 'jeevan_renew_ac', name: 'jeevan_renew_ac'},

            {data: 'jeevan_renew', name: 'jeevan_renew'},



            {data: 'ssb_new_ac', name: 'ssb_new_ac'},

            {data: 'ssb_deno_sum', name: 'ssb_deno_sum'}, 

            {data: 'ssb_renew_ac', name: 'ssb_renew_ac'},

            {data: 'ssb_renew', name: 'ssb_renew'},



            {data: 'mi_new_ac', name: 'mi_new_ac'},

            {data: 'mi_deno_sum', name: 'mi_deno_sum'}, 

            {data: 'mi_renew_ac', name: 'mi_renew_ac'},

            {data: 'mi_renew', name: 'mi_renew'},

            {data: 'mb_new_ac', name: 'mb_new_ac'},

            {data: 'mb_deno_sum', name: 'mb_deno_sum'}, 

            {data: 'mb_renew_ac', name: 'mb_renew_ac'},

            {data: 'mb_renew', name: 'mb_renew'},



           /* {data: 'total_ni_ac', name: 'total_ni_ac'},

            {data: 'total_ni_amount', name: 'total_ni_amount'}, 



            {data: 'total_ac', name: 'total_ac'},

            {data: 'total_amount', name: 'total_amount'},  */



            {data: 'other_mt', name: 'other_mt'},

            {data: 'other_stn', name: 'other_stn'}, 



            {data: 'ni_m', name: 'ni_m'},

            {data: 'ni', name: 'ni'},

            {data: 'tcc_m', name: 'tcc_m'},

            {data: 'tcc', name: 'tcc'},



            {data: 'st_loan_ac', name: 'st_loan_ac'},

            {data: 'st_loan_amount', name: 'st_loan_amount'},

            {data: 'pl_loan_ac', name: 'pl_loan_ac'},

            {data: 'pl_loan_amount', name: 'pl_loan_amount'},

            {data: 'la_loan_ac', name: 'la_loan_ac'},

            {data: 'la_loan_amount', name: 'la_loan_amount'},

            {data: 'gp_loan_ac', name: 'gp_loan_ac'},

            {data: 'gp_loan_amount', name: 'gp_loan_amount'},



            {data: 'loan_ac', name: 'loan_ac'},

            {data: 'loan_amount', name: 'loan_amount'}, 



            {data: 'st_loan_recovery_ac', name: 'st_loan_recovery_ac'},

            {data: 'st_loan_recovery_amount', name: 'st_loan_recovery_amount'},

            {data: 'pl_loan_recovery_ac', name: 'pl_loan_recovery_ac'},

            {data: 'pl_loan_recovery_amount', name: 'pl_loan_recovery_amount'},

            {data: 'la_loan_recovery_ac', name: 'la_loan_recovery_ac'},

            {data: 'la_loan_recovery_amount', name: 'la_loan_recovery_amount'},

            {data: 'gp_loan_recovery_ac', name: 'gp_loan_recovery_ac'},

            {data: 'gp_loan_recovery_amount', name: 'gp_loan_recovery_amount'},



            {data: 'loan_recovery_ac', name: 'loan_recovery_ac'},

            {data: 'loan_recovery_amount', name: 'loan_recovery_amount'},  



            {data: 'new_associate', name: 'new_associate'}, 

            {data: 'total_associate', name: 'total_associate'}, 



            {data: 'new_member', name: 'new_member'}, 

            {data: 'total_member', name: 'total_member'}, 



          /*  {data: 'action', name: 'action',orderable: false, searchable: false},*/

        ]

    });

    $(employeeTable.table().container()).removeClass( 'form-inline' );



 
/*
    $('.export').on('click',function(){

        var extension = $(this).attr('data-extension');

        $('#emp_application_export').val(extension);

        $('form#filter').attr('action',"{!! route('branch.report.associateBusinessSummaryExport') !!}");

        $('form#filter').submit();

        return true;

    });
*/
$('.export').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#export').val(extension);
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
            url :  "{!! route('branch.report.associateBusinessSummaryExport') !!}",
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
     





$(document).on('change','#zone',function(){ 

    var zone=$('#zone').val();

    $('#region').find('option').remove();

    $('#sector').find('option').remove();

    $('#branch_id').find('option').remove();



          $.ajax({

              type: "POST",  

              url: "{!! route('branch.report.branchRegionByZone') !!}",

              dataType: 'JSON',

              data: {'zone':zone},

              headers: {

                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

              },

              success: function(response) { 

                $('#region').find('option').remove();

                $('#region').append('<option value="">Select Region</option>');

                 $.each(response.data, function (index, value) {

                        if(value.regan!=null)

                        {

                             $("#region").append("<option value='"+value.regan+"'>"+value.regan+"</option>");

                        } 

                       

                    }); 



              }

          });



  });

$(document).on('change','#region',function(){ 

    var region=$('#region').val(); 



          $.ajax({

              type: "POST",  

              url: "{!! route('branch.report.branchSectorByRegion') !!}",

              dataType: 'JSON',

              data: {'region':region},

              headers: {

                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

              },

              success: function(response) { 

                $('#sector').find('option').remove();

                $('#sector').append('<option value="">Select Sector </option>');

                 $.each(response.data, function (index, value) {

                        if(value.sector!=null)

                        {

                             $("#sector").append("<option value='"+value.sector+"'>"+value.sector+"</option>");

                        } 

                       

                    }); 



              }

          });



  });



$(document).on('change','#sector',function(){ 

    var sector=$('#sector').val(); 



          $.ajax({

              type: "POST",  

              url: "{!! route('branch.report.branchBySector') !!}",

              dataType: 'JSON',

              data: {'sector':sector},

              headers: {

                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

              },

              success: function(response) { 

                $('#branch_id').find('option').remove();

                $('#branch_id').append('<option value="">Select Branch </option>');

                 $.each(response.data, function (index, value) {

                        

                             $("#branch_id").append("<option value='"+value.id+"'>"+value.name+"("+value.branch_code+")</option>");

                        

                       

                    }); 



              }

          });



  });





    $( document ).ajaxStart(function() {

        $( ".loader" ).show();

    });



    $( document ).ajaxComplete(function() {

        $( ".loader" ).hide();

    });





    

    $.validator.addMethod("currentdate", function(value, element,p) {

     

     moment.defaultFormat = "DD/MM/YYYY HH:mm";

     var f1 = moment($('#start_date').val()+' 00:00', moment.defaultFormat).toDate();

     var f2 = moment($('#end_date').val()+' 00:00', moment.defaultFormat).toDate();



      var from = new Date(Date.parse(f1));

      var to = new Date(Date.parse(f2));





     if(to>=from)

      {

        $.validator.messages.currentdate = "";

        result = true;

      }else{

        $.validator.messages.currentdate = "To date must be greater than current from date.";

        result = false;  

      }

      

    

    return result;

  }, "")



  $.validator.addMethod("dateDdMm", function(value, element,p) {

     

      if(this.optional(element) || /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g.test(value)==true)

      {

        $.validator.messages.dateDdMm = "";

        result = true;

      }else{

        $.validator.messages.dateDdMm = "Please enter valid date.";

        result = false;  

      }

    

    return result;

  }, "");





    $('#filter').validate({

      rules: {

        start_date:{  

            dateDdMm:function(element) {

              if ($( "#start_date" ).val()!='') {

                return true;

              } else {

                return false;

              }

            },

            currentdate:function(element) {

              if ($( "#start_date" ).val()!='') {

                return true;

              } else {

                return false;

              }

            }, 

          },



          end_date:{ 

            dateDdMm:function(element) {

              if ($( "#end_date" ).val()!='') {

                return true;

              } else {

                return false;

              }

            },

            currentdate:function(element) {

              if ($( "#end_date" ).val()!='') {

                return true;

              } else {

                return false;

              }

            }, 

          }, 

          associate_code:{ 

            number:function(element) {

              if ($( "#associate_code" ).val()!='') {

                return true;

              } else {

                return false;

              }

            },  

          },





          





      },

       messages: {  

          

          associate_code: {

            number: "Please enter valid code.", 

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

        employeeTable.draw();

    }

}

function resetForm()

{

    var form = $("#filter"),

    validator = form.validate();

    validator.resetForm();

    form.find(".error").removeClass("error"); 

 

   $('#start_date').val('');

    $('#end_date').val('');

    $('#branch_id').val('');



    $('#zone').val('');

    $('#region').val('');

    $('#sector').val('');

     $('#associate_code').val('');



    $('#is_search').val('yes'); 



    var sector=''; 



          $.ajax({

              type: "POST",  

              url: "{!! route('branch.report.branchBySector') !!}",

              dataType: 'JSON',

              data: {'sector':sector},

              headers: {

                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

              },

              success: function(response) { 

                $('#branch_id').find('option').remove();

                $('#branch_id').append('<option value="">Select Branch </option>');

                 $.each(response.data, function (index, value) {

                        

                             $("#branch_id").append("<option value='"+value.id+"'>"+value.name+"("+value.branch_code+")</option>");

                        

                       

                    }); 



              }

          });



    employeeTable.draw();

}



</script>