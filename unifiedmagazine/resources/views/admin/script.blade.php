<script>
    var send_email_list_table;
    $((e)=>{
        $('#edit_user_details_submit').on('click',function(e){
            var form = $('#edit_user_details').serializeArray();
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            $.post("{{route('users.store')}}",{'_token':csrfToken,'form':form},function(resource){
                send_email_list_table.draw();
                $('.dismiss_model').click();
                alert('Update successful');
            }),'JSON';
        });
        $('#filter').validate({
            rules: {
                users: {
                    required: true
                },
                date: {
                    required: true
                }
            },
            messages: {
                users: {
                    required: "Please select a user"
                },
                date: {
                    required: "Please pick a date"
                }
            }
        });
        $('#searchSubmit').on('click',function(e){
			e.preventDefault();
            if($('#filter').valid()){
                // send_email_list_table.draw();
                fetchUpdatedUserData();
            }
		});
    });
    $(document).on('click','.email_message',function(){
        var msg = $(this).data('message');
        $('#model_email').html(msg);
    });
    fetchUpdatedUserData();
    function fetchUpdatedUserData() {
        var formData = $('form#filter').serialize(); // Serialize the form data

        $.ajax({
            url: "{{ route('user.show.list') }}",
            method: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                $('#send_email_list_table tbody').html(response);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching updated data:', error);
            }
        });
    }

    function status_update(id,status){
        $.ajax({
            url:`{{route('user.all.update')}}`,
            type:'POST',
            data:{'id':id,'status':status,},
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function(e) {
                send_email_list_table.draw();
                alert('Update successful');
            },error: function(xhr, status, error) {
                send_email_list_table.draw();
                alert('Update failed', error);
            }
        });
    };
    function email_update(id,name,email){
        $('#user_id').val(id);
        $('#user_name').val(name);
        $('#user_email').val(email);
    }
    function view_user(id,name,email,pswd){
        $('#view_id').val(id);
        $('#view_name').val(name);
        $('#view_email').val(email);
        $('#pswd').val(pswd);
    }
    
</script>
