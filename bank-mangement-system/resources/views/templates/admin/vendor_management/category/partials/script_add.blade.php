<script type="text/javascript">
$(document).ready(function () {
  $("#name").on("keypress", function(e) {
    if (e.which === 32 && !this.value.length)
        e.preventDefault(); 
});

  $('#category').validate({
      rules: {   
          name: "required",
          status: "required", 
      },
      messages: { 
          name: "Please enter name.",
          status: "Please select status.",  
      },
      errorElement: 'label',
      errorPlacement: function (error, element) {
        error.addClass(' ');
        element.closest('.error-msg').append(error);
      },
      highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
      },
      unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
      },
      submitHandler:function(){
        $('button[type="submit"]').prop('disabled',true);
        return true;
      }
  });
      $( document ).ajaxStart(function() { 
          $( ".loader" ).show();
       });
       $( document ).ajaxComplete(function() {
          $( ".loader" ).hide();
       });
 });

 
</script>