<script type="text/javascript"> 

$('#filter').validate({
      rules: {
        associate_code:{ 
            number : true,
            required: true,			
			minlength:12,
			maxlength: 12,
          },
		   month_id:{ 
            //number : true,
            required: true,
          },
		   year_id:{ 
            //number : true,
            required: true,
          },
		   type_id:{ 
            //number : true,
            required: true,
          },
      

      },
      messages: { 
        associate_code: {
            required: "Please enter associate code.",
            number: "Please enter  valid code.",
          },
		   month_id: {
            required: "Please enter month name.",
           
          },
		   year_id: {
            required: "Please enter month name.",
           
          },
		   type_id: {
            required: "Please enter month name.",
           
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
    
    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });

        var designationTable;
        $(document).ready(function () {
 

        designationTable = $('#commision_listing').DataTable({
        processing: true,
        serverSide: true,
         pageLength: 20,
         lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#commision_listing').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.commison.month.lists') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter').serializeArray()}, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'}, 
            {data: 'created_at', name: 'created_at'},
            {data: 'month', name: 'month'},
			      {data: 'year', name: 'year'},
            {data: 'created_by', name: 'created_by'},
			      {data: 'created_by_id', name: 'created_by_id'},
			
        ],"ordering": false,
    });
    $(designationTable.table().container()).removeClass( 'form-inline' );

  
 
});


</script>