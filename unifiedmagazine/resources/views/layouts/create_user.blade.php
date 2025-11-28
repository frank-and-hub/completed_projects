@extends('layouts.app')
@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title">{{ __('Create New User') }}</h5>
        {{Form::open(['url'=>route('user.store'),'method'=>'post','id'=>'userregistrationForm','class'=>'','enctype'=>'multipart/form-data','files'=>'true'])}}
        <div class="row mb-3">
            <label for="name" class="col-sm-2 col-form-label">User Name</label>
            <div class="col-sm-10">
            {{Form::text('name','',['id'=>'name','class'=>'form-control','autocomplete'=>'off'])}}
            </div>
        </div>
        <div class="row mb-3">
            <label for="email" class="col-sm-2 col-form-label">Email</label>
            <div class="col-sm-10">
            {{Form::email('email','',['id'=>'email','class'=>'form-control','required'=>'true','autocomplete'=>'off'])}}  
            </div>
        </div>
        <div class="row mb-3">
            <label for="password" class="col-sm-2 col-form-label">Password</label>
            <div class="col-sm-10">
            {{Form::password('password',['id'=>'password','class'=>'form-control','required'=>'true','autocomplete'=>'off'])}}
            </div>
        </div>
        <div class="row mb-3">
            <label for="cpassword" class="col-sm-2 col-form-label">Confirm Password</label>
            <div class="col-sm-10">
            {{Form::password('cpassword',['id'=>'cpassword','class'=>'form-control','required'=>'true','autocomplete'=>'off'])}}
            </div>
        </div>
        <div class="text-center">
            {{Form::hidden('role','1')}}
            {{Form::submit('Save',['class'=>'btn btn-primary '])}}
        </div>
        </form>
    </div>
</div>
@endsection
