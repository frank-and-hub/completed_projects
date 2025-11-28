@extends('templates.super_admin.master')

@section('content')
<div class="content">
<div class="row">

  @if ( count( $status) > 0 )
    @foreach( $status as $contents )
      <div class="col-md-4">
        <div class="card border-left-3 border-left-violet rounded-left-0">
          <div class="card-body">
            <div class="d-sm-flex align-item-sm-center flex-sm-nowrap">
              <div>
                <h6 class="font-weight-semibold">{{ $contents['title'] }}</h6>
              </div>

              <div class="text-sm-right mb-0 mt-3 mt-sm-0 ml-auto">
                <h3 class="font-weight-semibold">₹ {{ $contents['total'] }}</h3>
              </div>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  @endif

  @stop