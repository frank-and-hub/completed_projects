@extends('admin.layout.master')

@push('plugin-styles')
  <link href="{{ asset('admin/assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">

@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Company Management</a></li>
    <li class="breadcrumb-item active" aria-current="page">Comany permission List</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">

        <h6 class="card-title">Permission List</h6>
        
        
        <form name="permission" method="post" action="{{route('company.permission.store')}}" class="table-responsive">
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
                      <td><input name="v[{{$k}}]" type="checkbox" {{ $p ? isset($p[$k]) ? isset($p[$k]['view']) ? ($p[$k]['view'] == '1') ? 'checked' : '' : '' : '' : ''}} value="1" /></td>
                      <td><input name="a[{{$k}}]" type="checkbox" {{ $p ? isset($p[$k]) ? isset($p[$k]['add']) ? ($p[$k]['add'] == '1') ? 'checked' : '' : '' : '' : ''}} value="1" /></td>
                      <td><input name="e[{{$k}}]" type="checkbox" {{ $p ? isset($p[$k]) ? isset($p[$k]['edit']) ? ($p[$k]['edit'] == '1') ? 'checked' : '' : '' : '' : ''}} value="1" /></td>
                      <td><input name="d[{{$k}}]" type="checkbox" {{ $p ? isset($p[$k]) ? isset($p[$k]['delete']) ? ($p[$k]['delete'] == '1') ? 'checked' : '' : '' : '' : ''}} value="1" /></td>
                      <input name="m[{{$k}}]" type="hidden" value="{{$value->id}}" />
                  </tr>
              @endforeach
            </tbody>
          </table>
          <br>
          <input type="submit" name="submit" class="form-control" value="Submit" />
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