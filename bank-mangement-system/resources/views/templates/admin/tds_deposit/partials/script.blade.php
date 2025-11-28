
                                <<script type="text/javascript">

    var tdsListing;

    $(document).ready(function(){
    //var type =  $('#type2').val();
  // $('#start_date').datepicker({
  //                 format:"dd/mm/yyyy",
  //                orientation: 'bottom',
  //                 todayHighlight: true, 
  //                 autoclose:true,
  //                 endDate: start_date, 
  //             })
// $("#start_date").datepicker("setDate", new Date());
    
  

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

        // Validate Form 

         $('#tds_form').validate({

            rules:{  
              type:{
                required:true,
              },
              start_date:{
                required:true
              },           

                tds_per:{
                  required:true,
                  decimal:true, 
                },
                tds_amount:{
                  required:true,
                  decimal:true, 
                },

            },

            messages:{
               type:{
                "required":"Please Select the Type."
              },
              start_date:{
                  "required":"Please Select the Date.",
              },

              tds_per:{
                "required":"Please enter TDS %."
              },
              tds_amount:{
                  "required":"Please enter TDS amount.",
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
              },
        })

        
          // Datatables
    tdsListing = $('#tds_deposite_listing').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.tds_deposite_listing') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter').serializeArray()}, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },

        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'created_at', name: 'created_at'},
            {data: 'start_date', name: 'start_date'},
            {data: 'end_date', name: 'end_date'},
            {data: 'tds_percentage', name: 'tds_percentage'},
            {data: 'tds_amount', name: 'tds_amount'},
            {data: 'type', name: 'type'},
            
        ]
    });
    $(tdsListing.table().container()).removeClass( 'form-inline' );
          
    $('#type').on('change',function(){
      var type_id = $(this).val();
      
      var  date='';
       $.ajax({

          type:"POST",

          url:"{!! route('admin.get_tds_deposite_detail') !!}",

          data:{id:type_id,},

          dataType:"JSON",

           headers: {

                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

              },

          success:function(response)

          { 
            console.log(response.data);
          if(response.data)
          {
          
            if(response.data.start_date != null && response.data.end_date == null)
            {
            
             var start_date = response.data.start_date; 
               $('#start_date').val(response.date);
                    date = response.date;
                $('#start_date').datepicker({
                    format:"dd/mm/yyyy",
                   orientation: 'bottom',
                    todayHighlight: true, 
                    startDate:date, 
                })
            }
            else if(response.data.start_date != null && response.data.end_date != null)
            {
                var start_date = response.data.end_date;
                date = response.date;
               $('#start_date').val(response.date);
                 $('#start_date').datepicker({
                    format:"dd/mm/yyyy",
                    orientation: 'bottom',
                    todayHighlight: true, 
                    startDate:date, 
                })
            }
          }
          else{
               var date = new Date();
                 $('#start_date').val('');
                $('#start_date').datepicker({
               
                    format:"dd/mm/yyyy",
                    orientation: 'bottom',
                    todayHighlight: true, 
                    
                })
          }

          }

      })
    })
/*
    $('.export').on('click',function(){
  var extension = $(this).attr('data-extension');
  $('#export').val(extension);
  $('form#filter').attr('action',"{!! route('admin.tds_deposite_export') !!}");
  $('form#filter').submit();
  });
*/
$('.export').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#export').val(extension);
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
				$('#export').val(extension);

			$('form#filter').attr('action',"{!! route('admin.tds_deposite_export') !!}");

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
            url :  "{!! route('admin.tds_deposite_export') !!}",
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
           $("#start_date").datepicker("destroy");  
        $( ".loader" ).show();

    });



    $( document ).ajaxComplete(function() {

        $( ".loader" ).hide();

    });

   

    })

    


</script>