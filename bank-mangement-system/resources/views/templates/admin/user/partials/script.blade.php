<script type="text/javascript">
$(document).ready(function() {
    var usertable;
    $('#user-create').validate({ // initialize the plugin
        rules: {
            'username' : 'required',
            'employee_code' : 'required',
            'employee_name' : 'required',
            'user_id' : 'required',
            'password' : {required: true,minlength: 6},
        }
    });

    $('#user-update').validate({ // initialize the plugin
        rules: {
            'username' : 'required',
            'employee_code' : 'required',
            'employee_name' : 'required',
            'user_id' : 'required',
            'password' : {minlength: 6},
        }
    });

    var usertable = $('#users').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 100,
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.users.list') !!}",
            "type": "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'user_name', name: 'user_name'},
            {data: 'employee_code', name: 'employee_code'},
            {data: 'employee_name', name: 'employee_name'},
            {data: 'user_id', name: 'user_id'},
            {data: 'status', name: 'status'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ]
    });
    $(usertable.table().container()).removeClass( 'form-inline' );


    $(document).on('click', '.delete-user', function(e){
        var url = $(this).attr('href');
        e.preventDefault();
        swal({
          title: "Are you sure, you want to delete this user?",
          text: "",
          icon: "warning",
          buttons: [
            'No, cancel it!',
            'Yes, I am sure!'
          ],
          dangerMode: true,
        }).then(function(isConfirm) {
          if (isConfirm) {
            location.href = url;
          } 
        });
    })

    $('.main-permission').on('click', function() {
        $(this).toggleClass('open');
        if ( $(this).parent().parent().next('tr').attr('style') ) {
            $(this).parent().parent().next('tr').removeAttr('style');
        } else if ( typeof ( $(this).parent().parent().next('tr').attr('style') ) == 'undefined' ) {
            $(this).parent().parent().next('tr').attr('style','display:none');
        }
    });

    $('.all-per').on('click', function() {
        var className = $(this).attr('data-id');
        if ( $(this).is(':checked')  ) {
            $("input:checkbox."+className).prop('checked',this.checked);
        } else {
            $("input:checkbox."+className).prop('checked',false);
        }

    });

    $("input[type='checkbox']").on('click', function() {

        var className = $(this).attr("class");
        var classArray = className.split(" ");
        var classIndex = classArray[0].split('-');
        var upDateClass = 'main-per-'+classIndex[1];
        $("input:checkbox."+upDateClass).prop('checked',false);
        console.log("CCC", className, classArray, classIndex);

    });

    $("#set_permission").on('click', function() {
        if($("#set_permission").prop('checked') == true){
           $('.permission-table').show();
        }else{
            $('.permission-table').hide();
        }
    });

    // Get registered member by id
    $(document).on('change','#employee_code',function(){
        var employee_code = $(this).val();
        $.ajax({
            type: "POST",  
            url: "{!! route('admin.getemployeecode') !!}",
            dataType: 'JSON',
            data: {'employee_code':employee_code},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.resCount > 0){
                    $('#employee_name').val(response.employeeName[0].employee_name);
                }else{
                    $('#employee_name').val('');
                }
                
            }
        });
    });

    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });
});
</script>