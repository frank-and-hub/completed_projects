@extends('admin.layout.master')

@push('plugin-styles')
  <link href="{{ asset('admin/assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">

@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Scholarship</a></li>
    <li class="breadcrumb-item active" aria-current="page">Scholarship Questions List</li>
  </ol>
</nav>


<div class="row">
  <div class="col-md-12 stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Scholarship Questions</h6>
      <a href="{{route('admin.scholarship.application',$id)}}"><h6 style="float:right;" class="card-title">Add New</h6></a>


        <h3>Questions</h3>
        <br>
        @if(count($questions) > 0)
        @foreach($questions as $key=>$value)

        <h5>Question {{++$key}}:- {{$value->question}}    ({{$value->type}})</h5>

        <ul>
    @foreach($value->options as $data)
     <li>{{ $data->keys_name }}:  {{$data->options}}  </li> 
    @endforeach
</ul>
       @endforeach
       @else

<h3><center>No Question Found</center></h3>
       @endif

</div>
</div>
</div>
</div>



@endsection