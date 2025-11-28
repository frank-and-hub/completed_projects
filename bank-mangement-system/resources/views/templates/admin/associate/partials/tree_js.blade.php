<script type="text/javascript">
$(document).ready(function () {

  $('#filter').validate({
      rules: {
          associate_code: "required"
      },
      messages: {
          associate_code: "Please enter associate code.",          
      },
      errorElement: 'span',
      errorPlacement: function (error, element) {
        error.addClass('invalid-feedback');
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


function resetForm()
{
    
    $('#associate_code').val('');
    $('#show_tree').hide() 
}


$('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
       // alert(extension);
        $('#member_export').val(extension);
        $('form#export_form').attr('action',"{!! route('admin.associate.exportAssociateTree') !!}");
        $('form#export_form').submit();
        return true;
    });

$( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });

</script>