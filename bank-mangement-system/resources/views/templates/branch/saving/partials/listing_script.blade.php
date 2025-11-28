<script type="text/javascript">
$(document).ready(function () {
   

    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });


}); 



 
</script>