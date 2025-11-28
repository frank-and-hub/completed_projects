<script type="text/javascript">
        $(document).ready(function() {
            // Branch Form validations
            $('#branch-create').validate({ // initialize the plugin
                rules: {
                    name : 'required',
                    state : 'required',
                    city : 'required',
                    zone : 'required',
                    pin_code : {
                        required: true,
                        minlength: 6,
                        maxlength: 6,
                        digits: true
                    },
                    address : 'required',
                    phone : {
                        required: true,
                        minlength: 10,
                        maxlength: 12,
                        digits: true
                    },
                    password: {
                        required: true,
                        minlength : 6
                    },
                    password_confirmation: {
                        required: true,
                        minlength : 6,
                        equalTo : "#password"
                    },
                },
                messages: {
                    name:{
                        required: 'Please enter valid Branch Name.',
                    },
                    state:{
                        required: 'Please select a State.',
                    },
                    city:{
                        required: 'Please select a City.',
                    },
                    zone:{
                        required: 'Please enter Zone/Sector.',
                    },
                    pin_code:{
                        required: 'Please enter Postal Code.',
                        minlength: 'Please enter at least 6 digit.',
                        maxlength: 'Please enter no more than 6 digit',
                        digits: 'Please enter only digits',
                    },
                    address:{
                        required: 'Please enter Address.',
                    },
                    phone:{
                        required: 'Please enter valid Phone Number.',
                        minlength: 'Please enter at least 10 digit.',
                        maxlength: 'Please enter no more than 12 digit',
                        digits:  'Please enter only digits'
                    },
                    password:{
                        required: 'Please enter Password.',
                        minlength: 'Please enter at least 6 characters.',
                    },
                    password_confirmation:{
                        required: 'Please enter Password.',
                        minlength: 'Please enter at least 6 characters.',
                        equalTo:  'Password did not matched'
                    },
                }
            });
            $('#branch-update').validate({
                rule: {
                    phone : {
                        required: true,
                        minlength: 10,
                        maxlength: 12,
                        digits: true
                    },
                    password: {
                        minlength : 6
                    },
                    password_confirmation: {
                        minlength : 6,
                        equalTo : "#password"
                    },
                },
                message: {
                    phone:{
                        required: 'Please enter valid Phone Number.',
                        minlength: 'Please enter at least 10 digit.',
                        maxlength: 'Please enter no more than 12 digit.',
                        digits:  'Please enter only digits'
                    },
                    password:{
                        minlength: 'Please enter at least 6 characters.',
                    },
                    password_confirmation:{
                        minlength: 'Please enter at least 6 characters.',
                        equalTo:  'Password did not matched'
                    },
                }

            });
           /** Ip Address Validation ******/
            $.validator.addMethod('IP4Checker', function(value) {
                var ip = /^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/;
                ip = /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/;
                return value.match(ip);
            }, 'Please enter valid Ip Address.');

            $('#update-ip').validate({ // initialize the plugin
                rules: {
                    ip_address : {
                        required: true,
                        IP4Checker: true
                    }
                },
                messages: {
                    ip_address:{
                        required: 'Please enter Ip Address.',
                    },
                }
            });

            $('#add-ip').validate({ // initialize the plugin
                rules: {
                    ip_address : {
                        required: true,
                        IP4Checker: true
                    }
                },
                messages: {
                    ip_address:{
                        required: 'Please enter Ip Address.',
                    },
                }
            });
            $( "form[name='change-password']" ).submit(function() {
                alert("hello ");
                return this.some_flag_variable;
            });
            
            var branchTable = $('#branch').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
                    var oSettings = this.fnSettings ();
                    $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                    return nRow;
                },
                ajax: {
                    "url": "{!! route('branch.listing') !!} ",
                    "type": "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                    {data: 'name', name: 'name'},
                    {data: 'branch_code', name: 'branch_code'},
                    {data: 'state_id', name: 'state_id'},
                    {data: 'city_id', name: 'city_id'},
                    {data: 'phone', name: 'phone'},
                    {data: 'address', name: 'address'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'status', name: 'status', searchable: false ,
                        "render":function(data, type, row){
                            if(row.status==0){
                                return "<span class='badge badge-danger'>Disabled</span>";
                            }else{
                                return "<span class='badge badge-success'>Active</span>";
                            }
                        }
                    },
                    {data: 'action', name: 'action', searchable: false ,orderable: false, className: "text-center",}
                ]
            });
            /* get city from state **/
            $(document).on('change','#state',function(){
                var stateId = $(this).val();
                $.ajax({
                    type: "POST",
                    url: "{!! route('cities') !!}" ,
                    data: {'stateId':stateId},
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        var select = $('.city');
                        select.empty().append(' <option >--Select City--</option>');
                        $.each(response, function(key, value) {
                            $('.city').append($("<option></option>")
                                    .attr("value", key)
                                    .text(value));
                        });
                    }
                });
            });

            $(document).on('change','#branch-name',function () {
                var branchName = $(this).val();
                $.ajax({
                    type: "POST",
                    url: "{!! route('check.branch') !!}" ,
                    data: {'branchName':branchName},
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if ( response.status == true ) {
                            $(this).addClass('error');
                            $('#branch-name').after('<label id="branch-name-error" class="error" for="branch-name">Branch name all ready exist.</label>');
                            $('#branch-name').val('');
                        }
                    }
                });
            })
        });
</script>
