@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Property'))
<div class="content-wrapper">
    <div class="page-header">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">{{ ucwords($title) }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">List</li>
            </ol>
        </nav>
    </div>
    <x-adminsubuser.matched-property />
</div>

@endsection
