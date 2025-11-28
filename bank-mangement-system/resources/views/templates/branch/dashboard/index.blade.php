@extends('layouts/branch.dashboard')

@section('content')
<div class="container-fluid mt--6">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <div class="card bg-white">
          <div class="card-body">
            Dashboard Hello
            
          </div>
        </div>
      </div>
      {{-- Created by mahesh on 22 dec 2023 --}}
      <div class="ml-3"><a href="https://eportal.incometax.gov.in/iec/foservices/#/pre-login/link-aadhaar-status" target="_blank" >
        <div class="card bg-white">
          <div class="card-body">
          पैन आधार स्टेटस चेक करने के लिए कृपया यहां क्लिक करें 
          </div>
        </div></a>
      </div>

      <div class="ml-3"><a href="https://nupaybiz.com/autonach/Home" target="_blank" >
        <div class="card bg-white">
          <div class="card-body">
          ECS Registration
          </div>
        </div></a>
      </div>
    </div> 
  </div>
</div> 
@stop