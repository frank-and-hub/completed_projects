<script type="text/javascript">
  "use strict";
    var employeeTable;
  $(document).ready(function () {
    var date = new Date();
    var Startdate = new Date();
  Startdate.setMonth(Startdate.getMonth() - 3);
    $('#start_date').datepicker({
      format: "dd/mm/yyyy",
      todayHighlight: true, 
    // startDate:Startdate,
      endDate: date, 
      autoclose: true
    }).on("changeDate", function(e) {
        $('#end_date').datepicker('setStartDate', e.date, 'format',"dd/mm/yyyy");
    });

    $('#end_date').datepicker({
      format: "dd/mm/yyyy",
      todayHighlight: true, 
      endDate: date,  
      autoclose: true
    }); 

      employeeTable = $('#emp_listing').DataTable({
          processing: true,
          serverSide: true,
          pageLength: 20,
          lengthMenu: [10, 20, 40, 50, 100],
          "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
              var oSettings = this.fnSettings ();
              $('html, body').stop().animate({
              scrollTop: ($('#emp_listing').offset().top)
          }, 1000);
              $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
              return nRow;
          },
          ajax: {
              "url": "{!! route('branch.hr.employee_listing') !!}",
              "type": "POST",
              "data":function(d) {d.searchform=$('form#filter').serializeArray()}, 
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
          },
          columns: [
              {data: 'DT_RowIndex', name: 'DT_RowIndex'},
              {data: 'company_name', name: 'company_name'},
              {data: 'designation', name: 'designation'},
              {data: 'category', name: 'category'}, 
              {data: 'branch', name: 'branch'},
              // {data: 'branch_code', name: 'branch_code'},
              // {data: 'sector', name: 'sector'},
              // {data: 'regan', name: 'regan'},
              // {data: 'zone', name: 'zone'},
              {data: 'rec_employee_name', name: 'rec_employee_name'}, 
              {data: 'employee_name', name: 'employee_name'},
              {data: 'employee_code', name: 'employee_code'},
              {data: 'dob', name: 'dob'},
              {data: 'gender', name: 'gender'},
              {data: 'mobile_no', name: 'mobile_no'},
              {data: 'email', name: 'email'},
              {data: 'guardian_name', name: 'guardian_name'},
              {data: 'guardian_number', name: 'guardian_number'},
              {data: 'mother_name', name: 'mother_name'},
              {data: 'pen_card', name: 'pen_card'},
              {data: 'aadhar_card', name: 'aadhar_card'},
              {data: 'voter_id', name: 'voter_id'}, 
              {data: 'esi', name: 'esi'}, 
              {data: 'pf', name: 'pf'},
              {data: 'status', name: 'status'},
              {data: 'resign', name: 'resign'},
              {data: 'terminate', name: 'terminate'},
              {data: 'transfer', name: 'transfer'},
              {data: 'created_at', name: 'created_at'},
              {data: 'action', name: 'action',orderable: false, searchable: false},
          ]
      });
      $(employeeTable.table().container()).removeClass( 'form-inline' );

  
      /*
      $('.export').on('click',function(){
          var extension = $(this).attr('data-extension');
          $('#emp_export').val(extension);
          $('form#filter').attr('action',"{!! route('branch.hr.employee_export') !!}");
          $('form#filter').submit();
          return true;
      }); 
    */
    $('.export').on('click',function(e){

      e.preventDefault();
      var extension = $(this).attr('data-extension');
          $('#emp_export').val(extension);
            var startdate = $("#start_date").val();
        var enddate = $("#end_date").val();
          //var employeename = $("#employee_name").val();
          //var reemployeename = $("#reco_employee_name").val();
        
          
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
        if(datediff()){        
          var chunkAndLimit = 50;
          $(".spiners").css("display","block");
          $(".loaders").text("0%");
          doChunkedExports(0,chunkAndLimit,formData,chunkAndLimit);
          $("#cover").fadeIn(100);
        }
    });
        function doChunkedExports(start,limit,formData,chunkSize){
              formData['start']  = start;
              formData['limit']  = limit;
              jQuery.ajax({
                type : "post",
                dataType : "json",
                url :  "{!! route('branch.hr.employee_export') !!}",
                  headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
                data : formData,
                success: function(response) {
                  console.log(response);
                  if(response.result=='next'){
                    start = start + chunkSize;
                    doChunkedExports(start,limit,formData,chunkSize);
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
                url: "{!! route('branch.designationByCategory') !!}",
                dataType: 'JSON',
                data: {'category':category},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) { 
                  $('#designation').find('option').remove();
                  $('#designation').append('<option value="">Select Designation</option>');
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
        $(".table-section").removeClass('datatable');
        employeeTable.draw();
    }
}
function resetForm()
{
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    $('#is_search').val("no");

    $('#start_date').val('');
    $('#end_date').val(''); 
    $('#category').val('');
    $('#designation').val('');
    $('#employee_name').val('');
    $('#employee_code').val('');
    $('#reco_employee_name').val('');
    $('#status').val('active');
    $(".table-section").addClass('datatable');
    employeeTable.draw();
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