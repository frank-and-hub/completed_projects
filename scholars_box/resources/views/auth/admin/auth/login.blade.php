@extends('admin.layout.master2')

@section('content')
<div class="page-content d-flex align-items-center justify-content-center"> <div class="row w-100 mx-0 auth-page">
  <div class="col-md-8 col-xl-6 mx-auto"> <div class="card"> <div class="row">
    <div class="col-md-4 pe-md-0">
    <div class="auth-side-wrapper" style="background-image: url({{ url('https://via.placeholder.com/219x452') }})">

    </div>
    </div>
    <div class="col-md-8 ps-md-0">
      <div class="auth-form-wrapper px-4 py-5">
        <a href="#" class="noble-ui-logo d-block mb-2">Noble<span>UI</span></a>
        <h5 class="text-muted fw-normal mb-4">Welcome back! Log in to your account.</h5>
        <form method="post" action="{{route('admin.login')}}"class="forms-sample">
            @csrf
          <div class="mb-3">
            <label for="userEmail" class="form-label">Email address</label>
            <input type="email" name="email" class="form-control" id="userEmail" placeholder="Email">
          </div>
          <div class="mb-3">
            <label for="userPassword" class="form-label">Password</label>
            <input type="password" name="password" class="form-control" id="userPassword" autocomplete="current-password"
              placeholder="Password">
          </div>
          <input type="hidden" value="1" name="role">
          <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="authCheck">
            <label class="form-check-label" for="authCheck">
              Remember me
            </label>
          </div>
          <div>
            
            <button type="submit" class="btn btn-outline-primary btn-icon-text mb-2 mb-md-0">
             
              Login
            </button>
          </div>
        
        </form>
      </div>
    </div>
  </div>
</div>
</div>
</div>

</div>
@endsection