<script type="text/javascript">
    $(document).ready(function() {
       
        $(".total_amount").on("input", function() {
        $(this).val($(this).val().replace(/[^0-9 .]/g, ""));
    });
    });
</script>