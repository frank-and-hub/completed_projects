<script type="text/javascript">
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
               //     prepend : "<span class='tran_account_number' style='padding-left: 40px;line-height: 50px;'>A/C No &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+no+"</span>",
                    //Add this on bottom
                   // append : "<span><br/>Buh Bye!</span>",
                   header: null,               // prefix to html
                  footer: null,  
                    //Log to console when printing is done via a deffered callback
                    deferred: $.Deferred().done(function() { console.log('Printing done', arguments);})
                });

}
</script>