<script type="text/javascript">

           /** Ip Address Validation ******/
            $.validator.addMethod('IP4Checker', function(value) {
                var ip = /^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/;
                ip = /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/;
                return value.match(ip);
            }, 'Please enter valid Ip Address.');

            jQuery( "#update" ).submit(function( event ) {
                console.log("check", event);
                jQuery('#update').validate({ // initialize the plugin
                    rules: {
                        ip_address: {
                            required: true,
                            IP4Checker: true
                        }
                    },
                    messages: {
                        ip_address: {
                            required: 'Please enter Ip Address.',
                        },
                    }
                });
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

</script>
