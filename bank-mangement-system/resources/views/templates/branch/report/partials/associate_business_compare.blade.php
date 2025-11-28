<script type="text/javascript">

    var employeeTable;

$(document).ready(function () {

 var date = new Date();
  var Startdate = new Date();
  Startdate.setMonth(Startdate.getMonth() - 3);
  $('#current_start_date').datepicker({

    format: "dd/mm/yyyy",
    startDate:Startdate,
    todayHighlight: true,  

    endDate: date, 

    autoclose: true

  });



  $('#current_end_date').datepicker({

    format: "dd/mm/yyyy",

    todayHighlight: true, 

    endDate: date,  

    autoclose: true

  });


  $('#comp_start_date').datepicker({

    format: "dd/mm/yyyy",

    todayHighlight: true,  

    endDate: date, 

    autoclose: true

  });



  $('#comp_end_date').datepicker({

    format: "dd/mm/yyyy",

    todayHighlight: true, 

    endDate: date,  

    autoclose: true

  });





  $.validator.addMethod("currentdate", function(value, element,p) {

     

     moment.defaultFormat = "DD/MM/YYYY HH:mm";

     var f1 = moment($('#current_start_date').val()+' 00:00', moment.defaultFormat).toDate();

     var f2 = moment($('#current_end_date').val()+' 00:00', moment.defaultFormat).toDate();



      var from = new Date(Date.parse(f1));

      var to = new Date(Date.parse(f2));





     if(to>=from)

      {

        $.validator.messages.currentdate = "";

        result = true;

      }else{

        $.validator.messages.currentdate = "Current to date must be greater than current from date.";

        result = false;  

      }

      

    

    return result;

  }, "")



  $.validator.addMethod("compdate", function(value, element,p) {

     

     moment.defaultFormat = "DD/MM/YYYY HH:mm";

     var f1 = moment($('#comp_start_date').val()+' 00:00', moment.defaultFormat).toDate();

     var f2 = moment($('#comp_end_date').val()+' 00:00', moment.defaultFormat).toDate();



     var f33 = moment($('#current_start_date').val()+' 00:00', moment.defaultFormat).toDate();





      var from = new Date(Date.parse(f1));

      var to = new Date(Date.parse(f2));

      var current = new Date(Date.parse(f33));



      if(current>from)

      {

          if(to>from)

          {

            $.validator.messages.compdate = "";

            result = true;

          }else{

            $.validator.messages.compdate = "Compare  to date must be greater than compare  from date.";

            result = false;  

          }

      }

      else

      {

        $.validator.messages.compdate = "Current from date  must be greater than compare  from date.";

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

            "url": "{!! route('branch.report.associate_business_compare') !!}",

            "type": "POST",

            "data":function(d) {



                d.searchform=$('form#filter').serializeArray(),

                d.current_start_date=$('#current_start_date').val(),

                d.current_end_date=$('#current_end_date').val(),

                d.comp_start_date=$('#comp_start_date').val(),

                d.comp_end_date=$('#comp_end_date').val(),

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

            



            {data: 'current_daily_new_ac', name: 'current_daily_new_ac'},

            {data: 'current_daily_deno_sum', name: 'current_daily_deno_sum'}, 

            {data: 'current_daily_renew_ac', name: 'current_daily_renew_ac'},

            {data: 'current_daily_renew', name: 'current_daily_renew'}, 





            {data: 'current_monthly_new_ac', name: 'current_monthly_new_ac'},

            {data: 'current_monthly_deno_sum', name: 'current_monthly_deno_sum'}, 

            {data: 'current_monthly_renew_ac', name: 'current_monthly_renew_ac'},

            {data: 'current_monthly_renew', name: 'current_monthly_renew'},



            {data: 'current_fd_new_ac', name: 'current_fd_new_ac'},

            {data: 'current_fd_deno_sum', name: 'current_fd_deno_sum'}, 

            //{data: 'current_fd_renew_ac', name: 'current_fd_renew_ac'},

            //{data: 'current_fd_renew', name: 'current_fd_renew'},



            



            {data: 'current_ssb_new_ac', name: 'current_ssb_new_ac'},

            {data: 'current_ssb_deno_sum', name: 'current_ssb_deno_sum'}, 

            {data: 'current_ssb_renew_ac', name: 'current_ssb_renew_ac'},

            {data: 'current_ssb_renew', name: 'current_ssb_renew'}, 



           /* {data: 'current_total_ni_ac', name: 'current_total_ni_ac'},

            {data: 'current_total_ni_amount', name: 'current_total_ni_amount'}, 



            {data: 'current_total_ac', name: 'current_total_ac'},

            {data: 'current_total_amount', name: 'current_total_amount'},  */



            {data: 'current_other_mt', name: 'current_other_mt'},

            {data: 'current_other_stn', name: 'current_other_stn'}, 



            {data: 'current_ni_m', name: 'current_ni_m'},

            {data: 'current_ni', name: 'current_ni'},

            {data: 'current_tcc_m', name: 'current_tcc_m'},

            {data: 'current_tcc', name: 'current_tcc'},



            {data: 'current_loan_ac', name: 'current_loan_ac'},

            {data: 'current_loan_amount', name: 'current_loan_amount'},



            {data: 'current_loan_recovery_ac', name: 'current_loan_recovery_ac'},

            {data: 'current_loan_recovery_amount', name: 'current_loan_recovery_amount'},  



            {data: 'current_new_associate', name: 'current_new_associate'}, 

            {data: 'current_total_associate', name: 'current_total_associate'}, 



            {data: 'current_new_member', name: 'current_new_member'}, 

            {data: 'current_total_member', name: 'current_total_member'}, 







            {data: 'compare_daily_new_ac', name: 'compare_daily_new_ac'},

            {data: 'compare_daily_deno_sum', name: 'compare_daily_deno_sum'}, 

            {data: 'compare_daily_renew_ac', name: 'compare_daily_renew_ac'},

            {data: 'compare_daily_renew', name: 'compare_daily_renew'}, 





            {data: 'compare_monthly_new_ac', name: 'compare_monthly_new_ac'},

            {data: 'compare_monthly_deno_sum', name: 'compare_monthly_deno_sum'}, 

            {data: 'compare_monthly_renew_ac', name: 'compare_monthly_renew_ac'},

            {data: 'compare_monthly_renew', name: 'compare_monthly_renew'},



            {data: 'compare_fd_new_ac', name: 'compare_fd_new_ac'},

            {data: 'compare_fd_deno_sum', name: 'compare_fd_deno_sum'}, 

            //{data: 'compare_fd_renew_ac', name: 'compare_fd_renew_ac'},

            //{data: 'compare_fd_renew', name: 'compare_fd_renew'},



            



            {data: 'compare_ssb_new_ac', name: 'compare_ssb_new_ac'},

            {data: 'compare_ssb_deno_sum', name: 'compare_ssb_deno_sum'}, 

            {data: 'compare_ssb_renew_ac', name: 'compare_ssb_renew_ac'},

            {data: 'compare_ssb_renew', name: 'compare_ssb_renew'}, 


/*
            {data: 'compare_total_ni_ac', name: 'compare_total_ni_ac'},

            {data: 'compare_total_ni_amount', name: 'compare_total_ni_amount'}, 



            {data: 'compare_total_ac', name: 'compare_total_ac'},

            {data: 'compare_total_amount', name: 'compare_total_amount'},  */



            {data: 'compare_other_mt', name: 'compare_other_mt'},

            {data: 'compare_other_stn', name: 'compare_other_stn'}, 



            {data: 'compare_ni_m', name: 'compare_ni_m'},

            {data: 'compare_ni', name: 'compare_ni'},

            {data: 'compare_tcc_m', name: 'compare_tcc_m'},

            {data: 'compare_tcc', name: 'compare_tcc'},



            {data: 'compare_loan_ac', name: 'compare_loan_ac'},

            {data: 'compare_loan_amount', name: 'compare_loan_amount'},



            {data: 'compare_loan_recovery_ac', name: 'compare_loan_recovery_ac'},

            {data: 'compare_loan_recovery_amount', name: 'compare_loan_recovery_amount'},  



            {data: 'compare_new_associate', name: 'compare_new_associate'}, 

            {data: 'compare_total_associate', name: 'compare_total_associate'}, 



            {data: 'compare_new_member', name: 'compare_new_member'}, 

            {data: 'compare_total_member', name: 'compare_total_member'}, 





            {data: 'result_daily_new_ac', name: 'result_daily_new_ac'},

            {data: 'result_daily_deno_sum', name: 'result_daily_deno_sum'}, 

            {data: 'result_daily_renew_ac', name: 'result_daily_renew_ac'},

            {data: 'result_daily_renew', name: 'result_daily_renew'}, 





            {data: 'result_monthly_new_ac', name: 'result_monthly_new_ac'},

            {data: 'result_monthly_deno_sum', name: 'result_monthly_deno_sum'}, 

            {data: 'result_monthly_renew_ac', name: 'result_monthly_renew_ac'},

            {data: 'result_monthly_renew', name: 'result_monthly_renew'},



            {data: 'result_fd_new_ac', name: 'result_fd_new_ac'},

            {data: 'result_fd_deno_sum', name: 'result_fd_deno_sum'}, 

            //{data: 'result_fd_renew_ac', name: 'result_fd_renew_ac'},

            //{data: 'result_fd_renew', name: 'result_fd_renew'},



            



            {data: 'result_ssb_new_ac', name: 'result_ssb_new_ac'},

            {data: 'result_ssb_deno_sum', name: 'result_ssb_deno_sum'}, 

            {data: 'result_ssb_renew_ac', name: 'result_ssb_renew_ac'},

            {data: 'result_ssb_renew', name: 'result_ssb_renew'}, 



          /*  {data: 'result_total_ni_ac', name: 'result_total_ni_ac'},

            {data: 'result_total_ni_amount', name: 'result_total_ni_amount'}, 



            {data: 'result_total_ac', name: 'result_total_ac'},

            {data: 'result_total_amount', name: 'result_total_amount'},  */



            {data: 'result_other_mt', name: 'result_other_mt'},

            {data: 'result_other_stn', name: 'result_other_stn'}, 



            {data: 'result_ni_m', name: 'result_ni_m'},

            {data: 'result_ni', name: 'result_ni'},

            {data: 'result_tcc_m', name: 'result_tcc_m'},

            {data: 'result_tcc', name: 'result_tcc'},



            {data: 'result_loan_ac', name: 'result_loan_ac'},

            {data: 'result_loan_amount', name: 'result_loan_amount'},



            {data: 'result_loan_recovery_ac', name: 'result_loan_recovery_ac'},

            {data: 'result_loan_recovery_amount', name: 'result_loan_recovery_amount'},  



            {data: 'result_new_associate', name: 'result_new_associate'}, 

            {data: 'result_total_associate', name: 'result_total_associate'}, 



            {data: 'result_new_member', name: 'result_new_member'}, 

            {data: 'result_total_member', name: 'result_total_member'}, 





          /*  {data: 'action', name: 'action',orderable: false, searchable: false},*/

        ]

    });

    $(employeeTable.table().container()).removeClass( 'form-inline' );



 
/*
     $('.export').on('click',function(){

        var extension = $(this).attr('data-extension');

        $('#emp_application_export').val(extension);

        $('form#filter').attr('action',"{!! route('branch.report.associateBusinessCompareExport') !!}");

        $('form#filter').submit();

        return true;

    });
*/
$('.export').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#emp_application_export').val(extension);
		var startdate = $("#current_start_date").val();
			var enddate = $("#current_end_date").val();
			if( startdate =='')
			{
				swal("Error!", "please select start date!", "error");
			return false;	
			}
			
			if( enddate =='')
			{
				swal("Error!", "please select end date!", "error");
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
            url :  "{!! route('branch.report.associateBusinessCompareExport') !!}",
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





    $('#filter').validate({

      rules: {

        current_start_date:{

            required: true,

            dateDdMm:function(element) {

              if ($( "#current_start_date" ).val()!='') {

                return true;

              } else {

                return false;

              }

            },

            currentdate:function(element) {

              if ($( "#current_start_date" ).val()!='') {

                return true;

              } else {

                return false;

              }

            }, 

          },



          current_end_date:{

            required: true,

            dateDdMm:function(element) {

              if ($( "#current_end_date" ).val()!='') {

                return true;

              } else {

                return false;

              }

            },

            currentdate:function(element) {

              if ($( "#current_end_date" ).val()!='') {

                return true;

              } else {

                return false;

              }

            }, 

          }, 



          comp_start_date:{

            required: true,

            dateDdMm:function(element) {

              if ($( "#comp_start_date" ).val()!='') {

                return true;

              } else {

                return false;

              }

            },

            compdate:function(element) {

              if ($( "#comp_start_date" ).val()!='') {

                return true;

              } else {

                return false;

              }

            }, 

          },



          comp_end_date:{

            required: true,

            dateDdMm:function(element) {

              if ($( "#comp_end_date" ).val()!='') {

                return true;

              } else {

                return false;

              }

            },

            compdate:function(element) {

              if ($( "#comp_end_date" ).val()!='') {

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

          current_start_date: {

            required: "Please enter date.", 

          },

          current_end_date: {

            required: "Please enter date.", 

          },

          comp_start_date: {

            required: "Please enter date.", 

          },

          comp_end_date: {

            required: "Please enter date.", 

          },

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

 

    $('#current_start_date').val('{{$current_from}}');

    $('#current_end_date').val('{{$current_to}}');

    $('#comp_start_date').val('{{$comp_from}}');

    $('#comp_end_date').val('{{$comp_to}}'); 





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