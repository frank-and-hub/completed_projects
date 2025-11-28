<script type="text/javascript">
        $(document).ready(function() {
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

        });
</script>
