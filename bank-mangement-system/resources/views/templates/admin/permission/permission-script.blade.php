<script type="text/javascript">
        $(document).ready(function() {

            $('.select2').select2();
            
         
            // Branch Form validations
            $('#role').validate({
                rule: {
                    name : 'required'
                },
                message: {
                    name:{
                        required: 'Please enter valid role name.'
                    }
                }
            });
            $('#permission').validate({ // initialize the plugin
                rules: {
                    name : 'required'
                },
                messages: {
                    name:{
                        required: 'Please enter valid permission name.'
                    }
                }
            });

            $(document).on('change','#branch-id',function(){
                var branchId = $(this).val();
                if ( branchId ) {
                    branchChange(branchId);
                } else {
                    $('.datatable-show-all').css('display','none');
                    $('#per-update').css('display','none');
                }

            });
            $( '#permissions' ).on( 'submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.permission') !!}" ,
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        swal("Success", response.success, "success");
                    },
                    error: function () {
                        swal("Sorry!", "Permission not updated!" , "error");
                    }
                });

            });

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

                // console.log("CCC", className, classArray, classIndex);
                /*if ( $(this).is(':checked')  ) {
                    $("input:checkbox."+className).prop('checked',this.checked);
                } else {
                    $("input:checkbox."+className).prop('checked',false);
                }*/

            });

        });
        /**
         * this function created by Sourab on 27-09-2023 for 
         * getting currect data from permission as per datatable
         * table name user_permission and all classes and there 
         * values are dunamic now.
         */
        function branchChange(branchId) {
                $.ajax({
                        type: "POST",
                        url: "{!! route('getPermission') !!}" ,
                        data: {'branchId':branchId},
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $.each( $('input[type=checkbox]'), function(key, value) {
                                $("input[type=checkbox][name='"+value.name+"']").prop('checked', false);
                                if ( response.indexOf( value.name ) !== -1 ) {
                                    $("input[type=checkbox][name='"+value.name+"']").prop('checked', true);
                                }
                                var mainStatus = true;
                                let m = value.title;
                                $('.main-per-'+m+'-child').each(function( index, element  ) {
                                    if ( element.checked == false) {
                                        mainStatus = false;
                                        return false;
                                    }
                                });
                                if ( mainStatus == true) {
                                    $('.main-per-'+m).prop('checked', true);
                                }else{
                                    $('.main-per-'+m).prop('checked', false);
                                }
                            });
                            $('.datatable-show-all').removeAttr('style');
                            $('#per-update').removeAttr('style');
                        }
                    });
            }
</script>
