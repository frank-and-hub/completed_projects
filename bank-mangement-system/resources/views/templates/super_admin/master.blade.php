<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <base href="{{url('/')}}"/>
    <title>{{ $title }} | {{$set->site_name}}</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1" />
    <meta name="robots" content="index, follow">
    <meta name="apple-mobile-web-app-title" content="{{$set->site_name}}"/>
    <meta name="application-name" content="{{$set->site_name}}"/>
    <meta name="msapplication-TileColor" content="#ffffff"/>
    <meta name="description" content="{{$set->site_desc}}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{url('/')}}/asset/{{ $logo->image_link }}" />
    <link rel="apple-touch-icon" href="{{url('/')}}/asset/{{ $logo->image_link }}" />
    <link rel="apple-touch-icon" sizes="72x72" href="{{url('/')}}/asset/{{ $logo->image_link2 }}" />
    <link rel="apple-touch-icon" sizes="114x114" href="{{url('/')}}/asset/{{ $logo->image_link2 }}" />
    <link rel="stylesheet" href="{{url('/')}}/asset/css/sweetalert.css" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Ubuntu:400,300,100,500,700,900" rel="stylesheet" type="text/css">

     <link rel="stylesheet" href="{{url('/')}}/asset/dashboard/vendor/@fortawesome/fontawesome-free/css/all.min.css" type="text/css">
    <link href="{{url('/')}}/asset/global_assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">
    <link href="{{url('/')}}/asset/global_assets/css/icons/fontawesome/styles.min.css" rel="stylesheet" type="text/css">
    <link href="{{url('/')}}/asset/user/assets/css/bootstrap.css" rel="stylesheet" type="text/css">
    <link href="{{url('/')}}/asset/user/assets/css/bootstrap_limitless.css" rel="stylesheet" type="text/css">
    <link href="{{url('/')}}/asset/user/assets/css/layout.css" rel="stylesheet" type="text/css">
    <link href="{{url('/')}}/asset/user/assets/css/components.min.css" rel="stylesheet" type="text/css">
    <link href="{{url('/')}}/asset/user/assets/css/colors.css" rel="stylesheet" type="text/css">

    <link href="{{url('/')}}/asset/dashboard/vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"> 
    <link href="{{url('/')}}/asset/dashboard/vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker3.standalone.min.css" rel="stylesheet" type="text/css">
    <link href="{{url('/')}}/asset/css/admin_panel.css" rel="stylesheet" type="text/css">

    <script src="{{url('/')}}/asset/global_assets/js/main/jquery.min.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/main/bootstrap.bundle.min.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/plugins/loaders/blockui.min.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/plugins/ui/ripple.min.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/plugins/visualization/d3/d3.min.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/plugins/visualization/d3/d3_tooltip.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/plugins/forms/styling/switchery.min.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/plugins/forms/selects/bootstrap_multiselect.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/plugins/ui/moment/moment.min.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/plugins/pickers/daterangepicker.js"></script>

    <script src="{{url('/')}}/asset/dashboard/vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>


    <script src="{{url('/')}}/asset/global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/plugins/forms/selects/select2.min.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/plugins/ui/prism.min.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/plugins/editors/summernote/summernote.min.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/plugins/forms/styling/uniform.min.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/plugins/forms/validation/validate.min.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/plugins/forms/validation/additional_methods.min.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/plugins/forms/inputs/touchspin.min.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/plugins/forms/selects/select2.min.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/plugins/forms/styling/switch.min.js"></script>
    <script src="{{url('/')}}/asset/user/assets/js/app.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/demo_pages/dashboard.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/demo_pages/login.js"></script>
    <!--<script src="{{url('/')}}/asset/global_assets/js/demo_pages/datatables_advanced.js"></script>-->
    <script src="{{url('/')}}/asset/global_assets/js/demo_pages/datatables_basic.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/demo_pages/form_layouts.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/demo_pages/form_select2.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/demo_pages/form_checkboxes_radios.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/demo_pages/form_validation.js"></script>
    <script src="{{url('/')}}/asset/global_assets/js/demo_pages/datatables_responsive.js"></script>
    <script src="{{url('/')}}/asset/tinymce/tinymce.min.js"></script>
    <script src="{{url('/')}}/asset/tinymce/init-tinymce.js"></script>

    <script src="{{url('/')}}/asset/print.min.js"></script>
  <script src="{{url('/')}}/asset/js/jQuery.print.js"></script>

    @yield('css')
    <style type="text/css">
      .loader {
          position: fixed;
          left: 0px;
          top: 0px;
          width: 100%;
          height: 100%;
          z-index: 9999;
          background: url('{{url('/')}}/asset/images/loader.gif') 50% 50% no-repeat rgb(249,249,249,0);
      }
	  .invalid-feedback{ font-size: 100%;}
    </style>
    </head>

<body class="">
	<!-- Main navbar -->
	<div class="loader" style="display: none;"></div>
	<div class="navbar navbar-expand-md navbar-light navbar-static">
    <div class="navbar-header navbar-dark bg-blue d-none d-md-flex align-items-md-center">
        <div class="navbar-brand navbar-brand-md">
          <a href="{{ route('admin.dashboard')}}" class="d-inline-block">
		  	<img src="{{url('/')}}/asset/{{ $logo->image_link }}">
			 
          </a>
        </div>
        
        <div class="navbar-brand navbar-brand-xs">
          <a href="{{url('/')}}" class="d-inline-block">
		  	<img src="{{url('/')}}/asset/{{ $logo->image_link }}">
          </a>
        </div>
    </div>
		<div class="d-md-none">
			<button class="navbar-toggler sidebar-mobile-main-toggle" type="button">
				<i class="icon-paragraph-justify3"></i>
			</button>
		</div>
		<div class="collapse navbar-collapse" id="navbar-mobile">
			<ul class="navbar-nav">
				<li class="nav-item">
					<a href="#" class="navbar-nav-link sidebar-control sidebar-main-toggle d-none d-md-block">
          				<i class="icon-paragraph-justify3"></i>
					</a>
				</li>
			</ul>
			<span class="navbar-text ml-md-3 mr-md-auto">
				<span class="badge badge-mark border-orange-300 mr-2"></span>
				Welcome back, {{Auth::guard('superAdmin')->user()->username}}
			</span>

			<div class="">
	            {{--<select name="hbranchid" id="hbranchid" class="form-control" title="Please select something!">
                    @foreach( App\Models\States::pluck('name', 'id') as $key => $val )
	                    <option @if($key == 33) selected="" @endif value="{{ $key }}"  >{{ $val }}</option> 
	                @endforeach
                </select>--}}
	       	</div>

			<div class="">
	            <h6 class="h2 mb-0 text-success gdate"></h6>
	            <span class="gdatetime" style="display: none;"></span>
	       	</div>
			<ul class="navbar-nav">
				<li class="nav-item dropdown dropdown-user">
					<a href="#" class="navbar-nav-link dropdown-toggle" data-toggle="dropdown">
						<img src="{{url('/')}}/asset/profile/react.jpg" class="rounded-circle" alt="">
						<span>{{Auth::guard('superAdmin')->user()->username}}</span>
					</a>

					<div class="dropdown-menu dropdown-menu-right">
						{{--<a href="{{route('admin.account')}}" class="dropdown-item"><i class="icon-lock"></i> Account information</a>--}}
						<a href="{{route('Admin.logout')}}" class="dropdown-item"><i class="icon-switch2"></i> Logout</a>
					</div>
				</li>
			</ul>
		</div>
	</div>
	<div class="page-content">


		<!-- Main sidebar -->
		<div class="sidebar sidebar-dark bg-blue sidebar-main sidebar-expand-md">

			<!-- Sidebar mobile toggler -->
			<div class="sidebar-mobile-toggler text-center">
				<a href="#" class="sidebar-mobile-main-toggle">
					<i class="icon-arrow-left8"></i>
				</a>
				Navigation
				<a href="#" class="sidebar-mobile-expand">
					<i class="icon-screen-full"></i>
					<i class="icon-screen-normal"></i>
				</a>
			</div>
			<!-- /sidebar mobile toggler -->


			<!-- Sidebar content -->
			<div class="sidebar-content">
				
				<!-- User menu -->
				<div class="sidebar-user-material">
					<div class="sidebar-user-material-body">
						<div class="card-body text-center">
							<h6 class="mb-0 text-white text-shadow-dark">{{$set->site_name}}</h6>
							<span class="font-size-sm text-white text-shadow-dark">{{$set->title}}</span>
						</div>
					</div>
					{{--<div class="sidebar-user-material-footer">
							<a href="#user-nav" class="d-flex justify-content-between align-items-center text-shadow-dark dropdown-toggle" data-toggle="collapse"><span>My account</span></a>
						</div>--}}
				</div>
				{{--<div class="collapse" id="user-nav">
						<ul class="nav nav-sidebar">
							<li class="nav-item">
								<a href="{{route('admin.account')}}" class="nav-link">
									<i class="icon-lock"></i>
									<span>Account information</span>
								</a>
							</li>
							<li class="nav-item">
								<a href="{{route('admin.logout')}}" class="nav-link">
									<i class="icon-switch2"></i>
									<span>Logout</span>
								</a>
							</li>
						</ul>
					</div>--}}
				<!-- /user menu -->
	
				
				<!-- Main navigation -->
				<div class="card card-sidebar-mobile">
					<ul class="nav nav-sidebar" data-nav-type="accordion">

						<!-- Main -->
						<li class="nav-item">
							<a href="{{route('Admin.dashboard')}}" class="nav-link">
								<i class="icon-home4"></i>
								<span>
									Dashboard
								</span>
							</a>
						</li>
					</ul>
				</div>
				<!-- /main navigation -->

			</div>
			<!-- /sidebar content -->
			
		</div>
		<div class="content-wrapper">
			<div class="page-header page-header-light">
				<div class="page-header-content header-elements-md-inline">
					<div class="page-title d-flex">
						<h4><span class="font-weight-semibold">{{$title}}</span></h4>
					</div>
					@if ( Request::segment(2) == 'loans' || Request::segment(2) == 'dashboard' || Request::segment(2) == 'py-plans' || Request::segment(2) == 'branch'
					|| Request::segment(2) == 'permission' )
					@else
						<div class="text-right">
							<a href="{{ redirect()->back()->getTargetUrl() }}" style="float:right" class="btn bg-dark legitRipple {{ Request::segment(2) }}">Back</a>
						</div>
					@endif
				</div>
			</div>
@yield('content')


<!-- footer begin -->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/{{$set->tawk_id }}/default';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
  <script src="{{url('/')}}/asset/js/sweetalert.js"></script>
	</div>
	<!-- /page content -->

</body>
</html>
@include('sweetalert::alert')
@yield('script')
@if (session('success'))
    <script>
      "use strict";
        $(document).ready(function () {
            swal("Success!", "{{ session('success') }}", "success");
        });
    </script>
@endif

@if (session('alert'))
    <script>
      "use strict";
        $(document).ready(function () {
            swal("Sorry!", "{{ session('alert') }}", "error");
        });
    </script>
@endif
<script>
@if(Session::has('message'))
"use strict";
var type = "{{Session::get('alert-type','info')}}";
switch (type) {
    case 'info':
        toastr.info("{{Session::get('message')}}");
        break;
    case 'warning':
        toastr.warning("{{Session::get('message')}}");
        break;
    case 'success':
        toastr.success("{{Session::get('message')}}");
        break;
    case 'error':
        toastr.error("{{Session::get('message')}}");
        break;
}
@endif

$(window).bind("load", function() {
	let branchid = $( "#hbranchid option:selected" ).val();  
    $.ajax({   
        type: "POST", 
        url: "{!! route('admin.getglobaldate') !!}",
        dataType: 'JSON',
        data: {'branchid':branchid},
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) { 
        	if(response.msg_type == 'success'){
        		$('.gdate').html(response.globalDate);
        		$('.gdatetime').html(response.globalDateTime);
        		$('.create_application_date').val(response.globalDate);
        		$('.withdrawal_date').val(response.globalDate);
  				$('.created_at').val(response.globalDateTime);
        	}else{
        		swal('Warning!',''+response.view+'','warning');
        	}
        }
    });
});

$(document).on('change', '#hbranchid', function(){
	let branchid = $( "#hbranchid option:selected" ).val();
    $.ajax({   
        type: "POST", 
        url: "{!! route('admin.getglobaldate') !!}",
        dataType: 'JSON',
        data: {'branchid':branchid},
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) { 
        	if(response.msg_type == 'success'){
        		$('.gdate').html(response.globalDate);
        		$('.gdatetime').html(response.globalDateTime);
        		$('.create_application_date').val(response.globalDate);
        		$('.withdrawal_date').val(response.globalDate);
  				$('.created_at').val(response.globalDateTime);
        	}else{
        		swal('Warning!',''+response.view+'','warning');
        	}
        }
    });
});

</script>
