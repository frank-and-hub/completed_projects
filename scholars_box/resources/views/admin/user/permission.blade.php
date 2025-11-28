@extends('admin.layout.master')

@push('plugin-styles')
  <link href="{{ asset('admin/assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">User Management</a></li>
    <li class="breadcrumb-item active" aria-current="page">User's permission</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">

        <h6 class="card-title">Permission List</h6>
        
        
        <form name="permission" method="post" action="{{route('user.permission.store')}}" class="table-responsive">
            @csrf
          <table id="" class="table">
            <thead>
              <tr>
                <th>Menu Name</th>
                <th>View</th>
                <th>Add</th>
                <th>Edit</th>
                <th>Delete</th>
              </tr>
            </thead>
            <input name="user_id" value="{{$id}}" type="hidden" />
            <tbody>
                @foreach($menu as $k => $value)
              <tr>
                  <td>{{$value->name}}</td>
                  <td><input name="v[{{$k}}]" type="checkbox" {{ $p ? $p[$k]['view'] == '1' ? 'checked' : '' : ''}} value="1" /></td>
                  <td><input name="a[{{$k}}]" type="checkbox" {{ $p ? $p[$k]['add'] == '1' ? 'checked' : '' : ''}} value="1" /></td>
                  <td><input name="e[{{$k}}]" type="checkbox" {{ $p ? $p[$k]['edit'] == '1' ? 'checked' : '' : ''}} value="1" /></td>
                  <td><input name="d[{{$k}}]" type="checkbox" {{ $p ? $p[$k]['delete'] == '1' ? 'checked' : '' : ''}} value="1" /></td>
                  <input name="m[{{$k}}]" type="hidden" value="{{$value->id}}" />
              </tr>
              @endforeach
            </tbody>
          </table>
          <br>
              <input type="submit" name="submit" class="form-control btn btn-primary" value="Submit" />
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('plugin-scripts')
  <script src="{{ asset('admin/assets/plugins/datatables-net/jquery.dataTables.js') }}"></script>
  <script src="{{ asset('admin/assets/plugins/datatables-net-bs5/dataTables.bootstrap5.js') }}"></script>
@endpush

@push('custom-scripts')
  <script src="{{ asset('admin/assets/js/data-table.js') }}"></script>
@endpush