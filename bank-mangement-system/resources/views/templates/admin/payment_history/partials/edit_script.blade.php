<script type="text/javascript">
	$(document).ready(function(){
		

		$('#pay_list').on("keyup", ".t_amount", function () {
        	var sum = 0;
	        $('.t_amount').each(function () {
	          if($(this).val()==0 || $(this).val()>0)
	          {
	            sum += Number($(this).val());
	          }
	        });
	        $('#total_amount').val(sum);

    	});

    	// $('#pay_list').on("change", ".t_amount", function () {
     //    	var sum = 0;
	    //     $('.t_amount').each(function () {
	    //       if($(this).val()==0 || $(this).val()>0)
	    //       {
	    //       	// var due_amount = $()
	    //         sum += Number($(this).val());
	    //       }
	    //     });
	    //     $('#total_amount').val(sum);

    	// });
	})
</script>