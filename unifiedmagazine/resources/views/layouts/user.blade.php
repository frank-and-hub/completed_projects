@extends('layouts.app')
@section('content')
<div class="card">
    <div class="card-header">{{ __('User Listing') }}</div>
        <div class="card-body">
            <div class="row">
                <div class="form-row">
                    <div class="container">    
                    <table class="table" id="user_listing_table">
                        <thead>
                            <tr>
                                <th scope="col-2" class="text-center">S. No.</th>
                                <th scope="col-2" class="text-center">Name</th>
                                <th scope="col-2" class="text-center">Email</th>
                                <th scope="col-2" class="text-center">Created at</th>
                                <th scope="col-2" class="text-center">Status</th>
                                <th scope="col-2" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>                                       
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="verticalycenteredDelete" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Are You Sure Deleting this user</h5>
        </div>
        <div class="modal-body d-none">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
            <button type="button" class="btn btn-primary deletduser" id="deleteModelUser" data-bs-dismiss="modal" data-id="">Yes</button>
        </div>
        </div>
    </div>
</div>
<div class="modal fade" id="verticalycenteredView" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        {{ Form::open(['url'=>'#','method'=>'POST','id'=>'','class'=>'modal-content']) }}
            <div class="modal-header">
                <h5 class="modal-title">View User Details</h5>
                <button type="button" class="btn-close dismiss_model" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <label for="email" class="col-sm-2 col-form-label">User Id</label>
                    <div class="col-sm-10">
                        {{Form::text('id',null,['id'=>'view_id','class'=>'form-control','autocomplete'=>'off','readonly'=>true])}}
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="name" class="col-sm-2 col-form-label">Name</label>
                    <div class="col-sm-10">
                        {{Form::text('name',null,['id'=>'view_name','class'=>'form-control','autocomplete'=>'off','readonly'=>true])}}
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="email" class="col-sm-2 col-form-label">Email</label>
                    <div class="col-sm-10">
                        {{Form::text('email',null,['id'=>'view_email','class'=>'form-control','autocomplete'=>'off','readonly'=>true])}}
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="email" class="col-sm-2 col-form-label">Password</label>
                    <div class="col-sm-10">
                        {{Form::password('password',['id'=>'pswd','class'=>'form-control','autocomplete'=>'off','readonly'=>true])}}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
       {{Form::close()}}
    </div>
</div>
<div class="modal fade" id="verticalycenteredEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        {{ Form::open(['url'=>'#','method'=>'POST','id'=>'edit_user_details','class'=>'modal-content']) }}
            <div class="modal-header">
                <h5 class="modal-title">Edit User Details</h5>
                <button type="button" class="btn-close dismiss_model"  data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <label for="name" class="col-sm-2 col-form-label">Name</label>
                    <div class="col-sm-10">
                        {{Form::text('name',null,['id'=>'user_name','class'=>'form-control','placeholder'=>'Update User Name','autocomplete'=>'off','required'=>true])}}
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="email" class="col-sm-2 col-form-label">Email</label>
                    <div class="col-sm-10">
                        {{Form::email('email',null,['id'=>'user_email','class'=>'form-control','placeholder'=>'Update User Email','autocomplete'=>'off','required'=>true])}}
                    </div>
                </div>
                {{Form::hidden('id',null,['id'=>'user_id','class'=>'form-control'])}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                {{Form::button('submit',['id'=>'edit_user_details_submit','class'=>'btn btn-primary'])}}
            </div>
        {{Form::close()}}
    </div>
</div>
<script>
    var user_listing_table;
    $((e)=>{
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        user_listing_table = $('#user_listing_table').DataTable({
                processing: true,
                serverSide: true,
                searching:false,
                lengthChange: false,
                paging: true,
                ajax: {
                    url: "{{route('user.list')}}",
                    type: 'POST',
                    // data:function(d) {d.searchform=$('form#filter').serializeArray()},
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                },
                columns: [
                    {data:'s_no'},{data:'name'},{data:'email'},{data:'created_at'},{data:'status'},{data:'action'}
            ],ordering: false,
        });
        $(user_listing_table.table().container()).removeClass( 'form-inline' );
        $('#edit_user_details_submit').on('click',function(e){
            var form = $('#edit_user_details').serializeArray();            
            $.post("{{route('users.store')}}",{'_token':csrfToken,'form':form},function(resource){
                user_listing_table.draw();
                $('.dismiss_model').click();
                alert('Update successful');
            }),'JSON';
        });
        $('#deleteModelUser').on('click',function(){
            var id = $(this).data('id');
            $.post("{!!route('user.delete')!!}",{'id':id,'_token':csrfToken},function(e){
                user_listing_table.draw();
                alert(''+e+' user Deleted Successfully.');
            },"JSON");
        });
       
    });
    function status_update(id,status){
        $.ajax({
            url:`{{route('user.all.update')}}`,
            type:'POST',
            data:{
                'id':id,
                'status':status,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(e) {
                user_listing_table.draw();
                alert('Update successful');
            },
            error: function(xhr, status, error) {
                user_listing_table.draw();
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
    function delete_user(id){
        $('#deleteModelUser').attr('data-id',id);
    }
</script>

@endsection