<script type="text/javascript">

function idPrint(elem) {
	document.getElementById(elem).style.marginTop = "200px";
    $("#"+elem).print({
                    //Use Global styles
					
                    globalStyles : false,
                    //Add link with attrbute media=print
                    mediaPrint : false,
                    //Custom stylesheet
                    stylesheet : "{{url('/')}}/asset/print.css",
                    //Print in a hidden iframe
                    iframe : false,
                    //Don't print this
                    noPrintSelector : ".avoid-this",
                    //Add this at top
                  //  prepend : "Hello World!!!<br/>",
                    //Add this on bottom
                   // append : "<span><br/>Buh Bye!</span>",
                   header: false,               // prefix to html
                  footer: false,  
                    //Log to console when printing is done via a deffered callback
                    deferred: $.Deferred().done(function() {  })
					
                });
				
		
				
}
$(document).on('click','#print',function(){
	 var id = $(this).attr('data-id');
				$.ajax({

            type: "POST",  

            url: "{!! route('admin.memberLoans.update_no_dues_print_status') !!}",

            dataType: 'JSON',

            data: {'id':id,"_token": "{{ csrf_token() }}"},

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            },

            success: function(response) {
				console.log(response);

                $('#print').hide();
        
            }
        }); 
		
			
        
	})
</script>