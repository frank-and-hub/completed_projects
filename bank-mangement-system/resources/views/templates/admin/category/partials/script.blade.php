<script type="text/javascript">
$(document).ready(function() {
    var $form = $(this);
    var accountheadtable;
    var subaccountheadtable;

    
    jQuery.validator.addMethod("duplicate_account", function(value, element) {
        var duplicate = 0;
        var account_number = $('#account_number').val();
         $.ajax({
                type: "POST",  
                url: "{!! route('admin.accounthead.getaccountnumber') !!}",
                data: {'account_number':account_number},
                dataType: 'JSON',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data) {
                    duplicate = data;
                }
         });
        return this.optional(element) || duplicate == 1;
    }, "The username you entered is already used");

    $('#subaccounthead-create').validate({ // initialize the plugin
        rules: {
            'subaccounttype' : 'required',
            'sub_account_head_name' : 'required',
            'accounthead' : 'required',
            'account_number' : {required: true, number: true},
        },
        submitHandler: function() {
            var aType = $( "#subaccounttype option:selected" ).val();
            if(aType == 2){
                var aValue = $('#account-exist').val();
                if(aValue > 0){
                    return false;
                }else{
                    return true;
                }
            }else{
                return true;
            }
        }
    });

    $('#editsubaccounthead-create').validate({ // initialize the plugin
        rules: {
            'subaccounttype' : 'required',
            'sub_account_head_name' : 'required',
            'accounthead' : 'required',
            'account_number' : {required: true, number: true},
        },
        submitHandler: function() {
            var aType = $( "#subaccounttype option:selected" ).val();
            if(aType == 2){
                var aValue = $('#account-exist').val();
                if(aValue > 0){
                    return false;
                }else{
                    return true;
                }
            }else{
                return true;
            }
        }
    });

    $('#accounthead-create').validate({ // initialize the plugin
        rules: {
            'accounthead' : 'required',
            'account_head_name' : 'required',
            'account_number' : {required: true, number: true},
        },
        submitHandler: function(form) {     
            var aType = $( "#accounttype option:selected" ).val();
            if(aType == 2){
                var aValue = $('#account-exist').val();
                if(aValue > 0){
                    return false;
                }else{
                    return true;
                }
            }else{
                return true;
            }
        }
    });

    $('#editaccounthead-create').validate({ // initialize the plugin
        rules: {
            'accounthead' : 'required',
            'account_head_name' : 'required',
            'account_number' : {required: true, number: true},
        },
        submitHandler: function() {
            var aType = $( "#accounttype option:selected" ).val();
            if(aType == 2){
                var aValue = $('#account-exist').val();
                if(aValue > 0){
                    return false;
                }else{
                    return true;
                }
            }else{
                return true;
            }
        }
    });

    // Get registered account number
    $(document).on('change','#account_number',function(){
        var account_number = $(this).val();
        $.ajax({
            type: "POST",  
            url: "{!! route('admin.accounthead.getaccountnumber') !!}",
            data: {'account_number':account_number},
            dataType: 'JSON',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {
                if(data.accountnumber > 0){
                    $('#account-number-error').html('Account Number exists.');
                    $('#account-exist').val(1);
                }else{
                    $('#account-number-error').html('');
                    $('#account-exist').val(0);
                }

            }
        });  
    });

    // Get registered account number
    $(document).on('change','#edit_account_number',function(){
        var account_number = $('#edit_account_number').val();
        var ahid = $('#accountheadid').val();
        $.ajax({
            type: "POST",  
            url: "{!! route('admin.accounthead.geteditaccountnumber') !!}",
            data: {'account_number':account_number,'ahid':ahid},
            dataType: 'JSON',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {
                if(data.accountnumber > 0){
                    $('#account-number-error').html('Account Number exists.');
                    $('#account-exist').val(1);
                }else{
                    $('#account-number-error').html('');
                   $('#account-exist').val(0);
                }
            }
        });  
    });

    // Get registered account number
    $(document).on('change','#sub_account_number',function(){
        var account_number = $(this).val();
        $.ajax({
            type: "POST",  
            url: "{!! route('admin.subaccounthead.getaccountnumber') !!}",
            data: {'account_number':account_number},
            dataType: 'JSON',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {
                if(data.accountnumber > 0){
                    $('#account-number-error').html('Account Number exists.');
                    $('#account-exist').val(1);
                }else{
                    $('#account-number-error').html('');
                    $('#account-exist').val(0);
                }

            }
        });  
    });

    // Get registered account number
    $(document).on('change','#sub_edit_account_number',function(){
        var account_number = $(this).val();
        var ahid = $('#accountheadid').val();
        $.ajax({
            type: "POST",  
            url: "{!! route('admin.subaccounthead.geteditaccountnumber') !!}",
            data: {'account_number':account_number,'ahid':ahid},
            dataType: 'JSON',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {
                if(data.accountnumber > 0){
                    $('#account-number-error').html('Account Number exists.');
                    $('#account-exist').val(1);
                }else{
                    $('#account-number-error').html('');
                   $('#account-exist').val(0);
                }
            }
        });  
    });

    var accountheadtable = $('#account-head').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 100,
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.accounthead.list') !!}",
            "type": "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'created_at', name: 'created_at'},
            {data: 'account_type', name: 'account_type'},
            {data: 'head_fa_code', name: 'head_fa_code'},
            {data: 'title', name: 'title'},
            {data: 'status', name: 'status'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false
    });
    $(accountheadtable.table().container()).removeClass( 'form-inline' );

    var subaccountheadtable = $('#sub-account-head').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 100,
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.subaccounthead.list') !!}",
            "type": "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'created_at', name: 'created_at'},
            {data: 'account_type', name: 'account_type'},
            {data: 'acount_head', name: 'acount_head'},
            {data: 'head_fa_code', name: 'head_fa_code'},
            {data: 'sub_head_fa_code', name: 'sub_head_fa_code'},
            {data: 'title', name: 'title'},
            {data: 'status', name: 'status'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false
    });
    $(subaccountheadtable.table().container()).removeClass( 'form-inline' );

    $(document).on('change','#subaccounttype',function(){
        var accountType = $('option:selected', this).val();
        if(accountType == 2){
            $('.account-number').show();
            $('.account-head-list').hide();
        }else{
            $('.account-number').hide();
            $('.account-head-list').show();
            $.ajax({
                type: "POST",  
                url: "{!! route('admin.getaccounthead') !!}",
                data: {'accountType':accountType},
                dataType: 'JSON',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if(response.resCount > 0){
                        $('#accounthead').html('');
                        $.each(response.accountHeads, function( index, value ) {
                          $('#accounthead').append('<option value="'+value.id+'">'+value.title+'</option>');
                        });
                    }else{
                        swal("Error!", "Account Heads not found!", "error");
                    }      
                }
            });
        }
    });

    $(document).on('change','#accounttype',function(){
        var accountType = $('option:selected', this).val();
        if(accountType == 2){
            $('.account-number').show();
        }else{
            $('.account-number').hide();
        }
    });

    $(document).on('click', '.delete-account-head', function(e){
        var url = $(this).attr('href');
        e.preventDefault();
        swal({
          title: "Are you sure, you want to delete this account head?",
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

    $(document).on('click', '.delete-sub-account-head', function(e){
        var url = $(this).attr('href');
        e.preventDefault();
        swal({
          title: "Are you sure, you want to delete this sub account head?",
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

    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });
});
</script>