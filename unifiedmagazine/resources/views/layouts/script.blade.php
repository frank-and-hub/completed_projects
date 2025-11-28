<script>
    $(document).ready(function () {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        var app_password = document.getElementById('app_password');
        if (app_password.value == '') {
            $('#modelapppassword').click();
        }
        $('#update_app_password_details_submit').on('click', function (e) {
            e.preventDefault();
            var form = $('#updateAppPassword').serializeArray();
            $.ajax({
                url: "{!!route('email.env')!!}",
                type: "POST",
                data: { 'form': form },
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function (r) {
                    if (r.result == '1') {
                        $('#app_password').val(r.app_password);
                        alert('App password updates sucessfully !');
                    } else {
                        alert('App password not updated yet, please try again !');
                    }
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>