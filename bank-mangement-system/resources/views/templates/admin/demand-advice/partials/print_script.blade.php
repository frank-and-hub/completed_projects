


<script type="text/javascript">

  $(document).ready(function() {

  $(document).on('click','.print-advice', function () {

        var demandId = $(this).attr('data-id');

        $.ajax({

            type: "POST",  

            url: "{!! route('admin.demand.updateprint') !!}",

            dataType: 'JSON',

            data: {'demandId':demandId},

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            },

            success: function(response) {

                $('.print-section').hide();
        
            }
        }); 
        

    });
});

function printDiv(elem) {
   $("#"+elem).print({
                    //Use Global styles
                    globalStyles : true,
                    //Add link with attrbute media=print
                    mediaPrint : true,
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
                   header: null,               // prefix to html
                  footer: null,  
                    //Log to console when printing is done via a deffered callback
                    deferred: $.Deferred().done(function() {    })
                });
}




</script>