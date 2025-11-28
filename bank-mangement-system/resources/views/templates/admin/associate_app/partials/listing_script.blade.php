<script type="text/javascript">
  $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });
    var memberTable;
$(document).ready(function () {

  
     memberTable = $('#transaction_listing').DataTable({
         processing: true,
         serverSide: true,
         pageLength: 20,
         lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#transaction_listing').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.associate.app_transaction_list') !!}",
            "type": "POST",
            "data":function(d) {
                d.searchform=$('form#filter').serializeArray(),
                d.type=$('#type').val(),
                d.associate_code=$('#associate_code').val(),
                d.associate_name=$('#associate_name').val(),
                d.associate_id=$('#associate_id').val(), 
                d.is_search=$('#is_search').val(),
                d.member_export=$('#member_export').val()
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'created_at', name: 'created_at'},
            {data: 'branch', name: 'branch'},
            {data: 'branch_code', name: 'branch_code'},
            {data: 'sector_name', name: 'sector_name'},
            {data: 'region_name', name: 'region_name'},
            {data: 'zone_name', name: 'zone_name'}, 
            {data: 'member_id', name: 'member_id'},
            {data: 'account_number', name: 'account_number'},
            {data: 'member', name: 'member'},
            {data: 'plan', name: 'plan'},
            {data: 'tenure', name: 'tenure'},
            {data: 'amount', name: 'amount',
                      "render":function(data, type, row){
                       return row.amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                      }
                    },
            {data: 'associate_code', name: 'associate_code'},
            {data: 'associate_name', name: 'associate_name'},
            {data: 'payment_mode', name: 'payment_mode'},
        ],"ordering": false,
    });
    $(memberTable.table().container()).removeClass( 'form-inline' );

     


    $('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#member_export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.associate.export') !!}");
        $('form#filter').submit();
        return true;
    });



    $(document).on('keyup','#associate_code',function(){
       
        var associate_code=$(this).val();
        var code = $(this).val();
      if(code!=0)
      $.ajax({
                type: "POST",  
                url: "{!! route('admin.seniorDetails') !!}",
                dataType: 'JSON',
                data: {'code':code},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) { 
                  $('#associate_msg').text('');
                  if(response.resCount>0)
                  {
                          if(response.msg=='block')
                          { 
                            $('#senior_name').val('');
                             $('#senior_id').val('');
                            $('#associate_msg').text('Associate Blocked.');
                            $('.invalid-feedback').show();
                          }
                          else
                          {
                          if(response.msg=='InactiveAssociate')
                          {
                              
                            $('#associate_name').val(''); 
                            $('#associate_id').val('');
                            $('#associate_msg').text('Associate Inactive.');
                            $('.invalid-feedback').show();
                          }                  
                          else
                          {
                             $.each(response.data, function (index, value) { 
                              // alert(value.first_name);
                                  $('#associate_name').val(value.first_name+' '+value.last_name);
                                  $('#associate_id').val(value.id);
                         
                        
                        
                            });
                          }
                        }

                  }
                  else
                  {
                    ('#associate_name').val(''); 
                  $('#associate_msg').text('No match found');
                  $('.invalid-feedback').show();

                  }
                  $('#associate_name').trigger('keypress');
                $('#associate_name').trigger('keyup');
                  
                }
            });
       
    });

    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });



 

$('#filter').validate({
      rules: {

        type: {
            required: true, 
          },
          associate_code: {
            required: true,
            number : true,
          },
          associate_name: {
            required: true, 
          },
          associate_id: {
            required: true, 
          }, 
         


      },
      messages: { 
        type: {
            required: "Please select transaction type", 
          },
          associate_code: {
            required: "Please enter associate code.",
            number: "Please enter valid number.",  
          },
          associate_name: {
            required: "Please enter associate name.",   
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


});

function searchForm()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
       // memberTable.draw();
    }
}

 


function resetForm()
{
    $('#is_search').val("yes"); 
    $('#associate_code').val(''); 
    $('#type').val('');

    memberTable.draw();
}

 
</script>