<script type="text/javascript">
$(document).ready(function() {
    var plantable;
    /*Client Side Jquery Validation for Plan Category Form*/
    jQuery.validator.addMethod("lettersonly", function(value, element) {
return this.optional(element) || /^[a-z\s]+$/i.test(value);
}, "Only alphabetical characters");
    $('#plan_category_form').validate({ // initialize the plugin
        rules: {
            'name' : {
                'required' : true,
                'lettersonly' : true,
                'space' : false
            },
            'plan_code' : {
                'required' : true,
                'lettersonly' : true,
                'maxlength' : 1
            },
        },
        messages:
        {
            'name':
            {
                'required' : 'Name is required',
                'lettersonly' :'Use only alphabets',
                'space' : 'Dont use space'
            },
            'plan_code' :
            {
                'required' : 'Plan code is required',
                'lettersonly' : 'Use only Alphabets',
                'maxlength' : 'Please enter no more than 1 character'
            },
        },
    });

    /*set the validation on name field
    * if name already exists in the database then error display
    */
    $('#name').on('change',function()
    {
        $.ajax({
            'url' : "{{ route('admin.planCategoryCreate.check') }}",
            'type' : 'POST',
            'data': {'name':  $(this).val()},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success : function(e)
            {
                if(e.data_name == '1')
                {
                    $("#name").parent().find('span').html("Name is already exists");
                }
                else
                {
                    $("#name").parent().find('span').html("");
                    return false;
                }
            }
        })
    });

    $('#plan_code').on('change',function()
    {
        $.ajax({
            'url' : "{{ route('admin.planCategoryCreate.check') }}",
            'type' : 'POST',
            'data': {'plan_code' : $(this).val()},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success : function(e)
            {

                if(e.data_code == '1')
                {
                    $("#plan_code").parent().find('span').html("Plan code is already exists");
                }
                else
                {
                    $("#plan_code").parent().find('span').html("");
                    return false;
                }
            }
        })
    });
      

    var plantable = $('#planCategory_table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        ordering: false,
        sorting: false,
        searching: false,
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('investment.planCategory.listing') !!}",
            "type": "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'name', name: 'name'},
            {data: 'code', name: 'code'},
            {data: 'is_basic', name: 'is_basic'},
            {data: 'status', name: 'status', 
                "render":function(data, type, row){
                    if(row.status==0){
                        return "<span class='badge badge-danger'>Inactive</span>";
                    }else{
                        return "<span class='badge badge-success'>Active</span>";
                    }
                }
            },
            // {data: 'head_id', name: 'head_id'},
            {data: 'created_by', name: 'created_by'},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action'},
        ]
    });
    $(plantable.table().container()).removeClass( 'form-inline' );

    $("#name").keyup(function(){
        var Text = $(this).val();
        Text = Text.toLowerCase().replace(/ /g,'-').replace(/[^\w-]+/g,'');
        $("#slug").val(Text);    
    });

    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });

    /*Status change script with ajax*/
    $(document).on('click','.change_status',function(){
        const slug = $(this).data('slug');
        const status = $(this).data('status');
        
        swal({
            'title': 'Are you sure you want to change the status?',
            'type': 'warning',
            'showDenyButton': true,
            'showCancelButton': true,
        },
        function(isConfirmed){
          if (isConfirmed) {
            $.ajax({
                url : "{{route('investment.planCategory.listing.status')}}",
                type: "post",
                data:{'slug':slug,'status':status},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(e)
                {
                    if(e.data>0)
                    {
                        swal("Success","Status Updated ",'success');
                    }
                    else
                    {
                        swal("Warning","Status Already Inactive","error");
                    }
                    plantable.draw();
                },
                error:function(){
                    alert("error");
                }
            });
            swal('Saved!', '', 'success');

          }
        });      
    });
});
</script>