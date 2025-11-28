<script type="text/javascript">
	var headLisiting;

    $(document).ready(function(){
      
        $('#fixed_asset').validate({
            rules:{
                title:{
                    required:true,
                },
                asset:{
                	required:true,
                },
            },
            messages:{
              title:{
                "required":"Please enter title.",
              },
            }
        })

    $('#child_asset').on('change',function(){

    	var child_asset_id = $(this).val(); 
    	$.ajax({
                type: "POST",  
                url: "{!! route('admin.get.child_asset') !!}",
                dataType: 'JSON',
                data: {'child_asset_id':child_asset_id},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                 success: function(response) { 
                     $('#sub_child_asset').find('option').remove();
                     $('#sub_child_asset').append('<option value="">Select Assets</option>');
                        $.each(response.sub_child_assets, function (index, value) { 
                        $("#sub_child_asset").append("<option value='"+value.head_id+"'>"+value.sub_head+"</option>");
                       
                });     
                }
            });
   })

    // headLisiting = $('#head_listing').DataTable({
    //         processing:true,
    //         serverSide:true,
    //         pageLength:20,
    //         lengthMenu:[10,20,40,50,100],
    //         "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
    //             var oSettings = this.fnSettings ();
    //             $('html, body').stop().animate({
    //                 scrollTop: ($('#head_listing').offset().top)
    //             }, 10);
    //             $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
    //             return nRow;
    //         },
    //          ajax: {
    //             "url": "{!! route('admin.fixed_asset.report.listing') !!}",
    //             "type": "POST",
    //             "data":function(d) {d.searchform=$('form#filter').serializeArray()},
    //             headers: {
    //                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //             },
    //         },
    //         //  "columnDefs": [{
    //         //     "render": function(data, type, full, meta) {
    //         //         return meta.row + 1; // adds id to serial no
    //         //     },
    //         //     "targets": 0
    //         // }],
    //          columns: [
    //             {title: 'S/N'},
    //             {data: 'name', name: 'name'},
    //             {data: 'status', name: 'status'},
    //             {data: 'action', name: 'action'},
              
    //         ]
    //     })
    //         $(headLisiting.table().container()).removeClass( 'form-inline' );

       
          // $.ajax({
          //       type: "POST",  
          //       url: "{!! route('admin.fixed_asset.report.listing') !!}",
          //       dataType: 'JSON',
                
          //       headers: {
          //           'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          //       },
          //        success: function(response) { 
          //            $.each(response, function(key, value){
          //           $("#head_listing  ").append("<tbody><tr><td>This is row " 
          //           + value.sub_head + "</td></tr></tbody>");
          //       });
          //       }
          //   });
        

         $( document ).ajaxStart(function() { 
              $( ".loader" ).show();
           });
    
           $( document ).ajaxComplete(function() {
              $( ".loader" ).hide();
           });        
    })

    function statusUpdate(id)
    {
        
        swal({
            title: "Are you sure?",
            text: "Do want to Change Status?",  
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-primary",
            confirmButtonText: "Yes",
            cancelButtonText: "No",
            cancelButtonClass: "btn-danger",
            closeOnConfirm: false,
            closeOnCancel: true
          },
          function(isConfirm) {
            if (isConfirm) {
                  $.ajax({
                  type: "POST",  
                  url: "{!! route('admin.update.status.fixed_asset') !!}",
                  dataType: 'JSON',
                  data: {'id':id},
                  headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                  },
                  success: function(response) {
                 
                        if(response)
                        {
                           
                            swal("Success", "Update Status successfully!", "success");
                            location.reload();
                        }
                        else
                        {
                              swal("Error", "Something went wrong.Try again!", "warning");
                        }
                    }
                  });
            }
          });
}
</script>