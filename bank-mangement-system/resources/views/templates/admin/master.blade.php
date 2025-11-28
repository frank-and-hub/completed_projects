<!DOCTYPE html>
@if(Auth::guard('admin')->user()->status == "0")
	<?php
	Auth::guard()->logout();
	session()->flash('message', 'Just Logged Out!');
	header("Refresh:0");
	return redirect('/admin');
	header("Refresh:0");
	?>
@endif
@if(Auth::guard('admin')->user()->logged_in != "1")
	<?php
	Auth::guard()->logout();
	session()->flash('message', 'Just Logged Out!');
	header("Refresh:0");
	return redirect('/admin');
	header("Refresh:0");
	?>
@endif
<?php
		$rid = Session::get('rid');
		$tokenSession = Session::get('token');
		$user_token = Auth::guard('admin')->user()->user_token;

		if($rid != 5){
			if($tokenSession != $user_token){
				Auth::guard()->logout();
				session()->flash('message', 'Just Logged Out!');
				header("Refresh:0");
				return redirect('/admin');
				header("Refresh:0");
			}
		}

		?>
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
	<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>


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


	  tr.child_inactive,tr.parent_inactive,.parent_inactive_li  {
		  background: #ee7979;
		  color: #fff;
		}

		child_inactive.a{color: #fff;}
		#cover {
		   position: absolute;
		   top: 0;
		   left: 0;
		   right: 0;
		   bottom: 0;
		   opacity: 0.80;
		   background: #aaa;
		   z-index: 10;
		   display: none;
		}

		.loaders{
			    margin: auto;
				position: fixed;
				padding: 10px;
				font-size: 100px;
				display: flex;
				height: 100vh;
				width: 100%;
				align-items: center;
				justify-content: center;
		}

		.spiners{
			  position: fixed;
			  left: 0px;
			  top: 0px;
			  width: 100%;
			  height: 100%;
			  z-index: 9999;
			  background: url('{{url('/')}}/asset/images/spiners.gif') 50% 50% no-repeat rgb(249,249,249,0);
		}
    </style>
	<script>
	$(function(){
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
    	// End Disable Developers Tool
	});
	// Show loading image
	$(document).ajaxStart(function() {
		$(".loader").show();
	});
	// Hide loading image
	$(document).ajaxComplete(function() {
		$(".loader").hide();
	});
	</script>
	@yield('css')
    </head>
<body class="">
	<!-- Main navbar -->
		<!-- Main navbar -->
	<script src="{{url('/')}}/core/public/js/global.js"></script>

	<div class="loader" style="display: none;"></div>
	<div class="spiners" style="display: none;"></div>
	<div id="cover"> <p class="loaders"></p> </div>
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
					Welcome-Back, {{Auth::guard('admin')->user()->username}}
				</span>
			<span class="navbar-text align-items-center  mr-md-auto  commission_process"> </span>
			<div class="">
	            <select name="hbranchid" id="hbranchid" class="form-control" title="Please select something!">
                    @foreach( App\Models\States::pluck('name', 'id') as $key => $val )
	                    <option @if($key == 33) selected="" @endif value="{{ $key }}"  >{{ $val }}</option>
	                @endforeach
                </select>
	       	</div>
			<div class="">
	            <h6 class="h2 mb-0 text-success gdate"></h6>
	            <span class="gdatetime" style="display: none;"></span>
	       	</div>
			<ul class="navbar-nav">
				<li class="nav-item dropdown dropdown-user">
					<a href="#" class="navbar-nav-link dropdown-toggle" data-toggle="dropdown">
						<img src="{{url('/')}}/asset/profile/react.jpg" class="rounded-circle" alt="">
						<span>{{Auth::guard('admin')->user()->username}}</span>
					</a>
					<div class="dropdown-menu dropdown-menu-right">
						<a href="{{route('admin.account')}}" class="dropdown-item"><i class="icon-lock"></i> Account information</a>
						<a href="{{route('admin.logout')}}" class="dropdown-item"><i class="icon-switch2"></i> Logout</a>
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
					<div class="sidebar-user-material-footer">
							<a href="#user-nav" class="d-flex justify-content-between align-items-center text-shadow-dark dropdown-toggle" data-toggle="collapse"><span>My Account</span></a>
						</div>
				</div>
				<div class="collapse" id="user-nav">
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
					</div>
				<!-- /user menu -->
				<!-- Main navigation -->


				<div class="card card-sidebar-mobile">
					<ul class="nav nav-sidebar" data-nav-type="accordion">
						<!-- Main -->
						<li class="nav-item">
							<a href="{{route('admin.dashboard')}}" class="nav-link">
								<i class="icon-home4"></i>
								<span>
									Dashboard
								</span>
							</a>
						</li>
						{{--<li class="nav-item nav-item-submenu">
							<a href="#" class="nav-link"><i class="icon-lan2"></i><span>Transfer</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="Transfer">
								<li class="nav-item"><a href="{{route('admin.ownbank')}}" class="nav-link"><i class="icon-office"></i>Own bank</a></li>
								<li class="nav-item"><a href="{{route('admin.otherbank')}}" class="nav-link"><i class="icon-city"></i>Other bank</a></li>
							</ul>
						</li>	--}}
						<!------------------------ Companies management Start ------------------------->
						<!--@if (check_my_permission(Auth::user()->id, '324') == '1' || check_my_permission(Auth::user()->id, '325') == '1' || check_my_permission(Auth::user()->id, '326') == '1' || check_my_permission(Auth::user()->id, '327') == '1' || check_my_permission(Auth::user()->id, '328') == '1')
                        <li
                            class="nav-item nav-item-submenu {{ set_active(['admin/company/register', 'admin/company/companies-list', 'admin/company/associate-setting', 'admin/companies/view/*', 'admin/companies/edit/*']) }}">
                            <a href="#" class="nav-link">
                                <i class="fas fa-city"></i><span>Company</span>
                            </a>
                            <ul class="nav nav-group-sub" data-submenu-title="company"
                                @if (set_active([
                                        'admin/company/register',
                                        'admin/company/companies-list',
                                        'admin/company/associate-setting',
                                        'admin/companies/view/*',
                                        'admin/companies/edit/*',
                                    ])) style="display:block" @endif>
                                @if (check_my_permission(Auth::user()->id, '328') == '1')
                                    <li class="nav-item" {{ set_active(['admin/company/register']) }} >
                                        <a href="{{ route('admin.companies.index') }}" class="nav-link">
                                            <i class="icon-add"></i>Company Register
                                        </a>
                                    </li>
                                @endif
                                @if (check_my_permission(Auth::user()->id, '324') == '1')
                                    <li class="nav-item" {{ set_active(['admin/company/companies-list']) }} >
                                        <a href="{{ route('admin.companies.show') }}"  class="nav-link">
                                            <i class="fa fa-list"></i>Companies List
                                        </a>
                                    </li>
                                @endif
                                @if (check_my_permission(Auth::user()->id, '325') == '1')
                                    <li class="nav-item" {{ set_active(['admin/company/associate-setting']) }} >
                                        <a href="{{ route('admin.companies.associateSetting') }}" class="nav-link" style="background-color:pink">
                                            <i class="icon-users"></i>Associate Setting
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                        @endif-->
						<!------------------------ Companies management End ------------------------->

                 <!-------------- User Manangement Start ------------------->
					 @if(check_my_permission( Auth::user()->id,"75") == "1" || check_my_permission( Auth::user()->id,"76") == "1")
						<li class="nav-item nav-item-submenu {{ set_active(['admin/usermanagement', 'admin/usermanagement-register']) }}" >
							<a href="#" class="nav-link"><i class="icon-user"></i> <span>User  Manangement</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="user Manangement"    @if(set_active(['admin/usermanagement', 'admin/usermanagement-register', 'admin/usermanagement-register/*', 'admin/usermanagement-permission/*',]))
							style="display:block" @endif>
								@if(check_my_permission( Auth::user()->id,"75") == "1")
								<li class="nav-item"><a href="{{route('admin.usermanagement.register')}}" class="nav-link"><i class="icon-user-plus"></i> Users Register</a></li>
								@endif
								@if(check_my_permission( Auth::user()->id,"76") == "1")
								<li class="nav-item"><a href="{{route('admin.usermanagement.usermanagementdetails')}}" class="nav-link"><i class="fas fa-users"></i> Users List</a></li>
								@endif
							</ul>
						</li>
					@endif
				<!-------------- User Manangement end ------------------->

				<!-------------- Member Manangement Start ------------------->

					@if(check_my_permission( Auth::user()->id,"2") == "1" || check_my_permission( Auth::user()->id,"3") == "1")
						<li class="nav-item nav-item-submenu {{ set_active([
							'admin/member',
							'admin/member-detail/*',
							'admin/member-account/*',
							'admin/member-loan/*',
							'admin/member-edit/*',
							'admin/member-investment/*',
							'admin/member-register',
							'admin/member-receipt/*',
							'admin/member-transactions/*',
							'admin/member-payment',
							'admin/blacklist-members-on-loan',
							'admin/add-blacklist-member-on-loan',
							'admin/add-blacklistmember-on-loan',
							'admin/customer',
							'admin/member-registration'
						])}}" >
							<a href="#" class="nav-link"><i class="icon-user"></i> <span>Member  Manangement</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="Member Manangement"    @if(set_active([
								'admin/member',
								'admin/member-detail/*',
								'admin/member-account/*',
								'admin/member-loan/*',
								'admin/member-edit/*',
								'admin/member-investment/*',
								'admin/member-register',
								'admin/member-receipt/*',
								'admin/member-transactions/*',
								'admin/member-payment',
								'admin/blacklist-members-on-loan',
								'admin/add-blacklist-member-on-loan',
								'admin/add-blacklistmember-on-loan',
								'admin/form_g/*',
								'admin/customer',
								'admin/member-registration'
							]))
							style="display:block" @endif>
							{{--
							@if(check_my_permission( Auth::user()->id,"2") == "1")
							<li class="nav-item">
								<a href="{{route('admin.member.register')}}" class="nav-link">
									<i class="icon-user-plus"></i> Members Registration
								</a>
							</li>
							@endif
							--}}
							@if (check_my_permission(Auth::user()->id, '2') == '1')
							<li class="nav-item {{ set_active('admin/member-registration') }}">
								<a href="{{ route('admin.member.registration') }}" class="nav-link">
									<i  class="icon-user-plus"></i>Members Registration
								</a>
							</li>
                            @endif
							@if(check_my_permission( Auth::user()->id,"3") == "1")
								<li class="nav-item"><a href="{{route('admin.member')}}" class="nav-link"><i class="fas fa-users"></i> Members List</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"200") == "1")
							<li class="nav-item"><a href="{{route('admin.customer_list')}}" class="nav-link"><i class="fas fa-users"></i> Customer List</a></li>
							@endif
							@if (check_my_permission(Auth::user()->id, '233') == '1')
                                <li class="nav-item {{set_active(['admin/blacklist-members-on-loan','admin/add-blacklist-member-on-loan'])}}"><a href="{{ route('admin.blacklist-members-on-loan') }}"
                                class="nav-link"><i class="icon-user-plus"></i>Manage Blacklist Members For Loan </a></li>
                            @endif
							</ul>
						</li>
					@endif
				<!-------------- Member Manangement end ------------------->
				<!-------------- Associate Manangement Start ------------------->
					@if(check_my_permission( Auth::user()->id,"5") == "1" || check_my_permission( Auth::user()->id,"6") == "1" || check_my_permission( Auth::user()->id,"7") == "1" || check_my_permission( Auth::user()->id,"8") == "1" || check_my_permission( Auth::user()->id,"9") == "1" || check_my_permission( Auth::user()->id,"10") == "1" || check_my_permission( Auth::user()->id,"11") == "1" || check_my_permission( Auth::user()->id,"12") == "1" || check_my_permission( Auth::user()->id,"13") == "1" || check_my_permission( Auth::user()->id,"14") == "1" || check_my_permission( Auth::user()->id,"15") == "1" || check_my_permission( Auth::user()->id,"279") == "1" || check_my_permission( Auth::user()->id,"277") == "1")
						<li class="nav-item nav-item-submenu {{ set_active(['admin/associate', 'admin/associate-detail/*', 'admin/associate-edit/*', 'admin/associate-register', 'admin/associate-receipt/*','admin/associate-tree','admin/associate-upgrade','admin/associate-status','admin/associate-downgrade','admin/associatecommission','admin/associatebusinessreport','admin/associate-idcard/*','admin/associate-commission-transfer','admin/associate-commission-transfer-list','admin/associate-commission-transfer-detail/*','admin/associate-senior','admin/associate-commission-create','admin/associate-collection-report','admin/associate-branch-transfer','admin/associate-log/*','admin/daily-account-setting','admin/commission/*','admin/associate/commision/month-end-comission-list','admin/associate-exception','admin/associate/commission/month-end-comission-create','admin/commision/exception-list','admin/daily-account-setting','admin/new-associate-commission-list']) }}" >
							<a href="#" class="nav-link"><i class="icon-users"></i> <span>Associate Manangement</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="Member Manangement"    @if(set_active(['admin/associate', 'admin/associate-detail/*', 'admin/associate-edit/*', 'admin/associate-register', 'admin/associate-receipt/*','admin/associate-tree','admin/associate-upgrade','admin/associate-status','admin/associate-downgrade','admin/associatecommission','admin/associatebusinessreport','admin/associate-idcard/*','admin/associate-commission-transfer','admin/associate-commission-transfer-list','admin/associate-commission-transfer-detail/*','admin/associate-senior','admin/associate-commission-create','admin/associate-collection-report','admin/associate-branch-transfer','admin/associate-log/*','admin/daily-account-setting','admin/commission/*','admin/associate/commision/month-end-comission-list','admin/associate-exception','admin/associate/commission/month-end-comission-create','admin/commision/exception-list','admin/daily-account-setting','admin/new-associate-commission-list']))
							style="display:block" @endif>
							@if(check_my_permission( Auth::user()->id,"5") == "1")
								<li class="nav-item"><a href="{{route('admin.associate.register')}}" class="nav-link"><i class="icon-user-plus"></i> Associate Registration</a></li>
							@endif

							@if(check_my_permission( Auth::user()->id,"6") == "1")
								<li class="nav-item"><a href="{{route('admin.associate')}}" class="nav-link"><i class="fas fa-list"></i> Associate List</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"7") == "1")
								<li class="nav-item"><a href="{{route('admin.associate.tree')}}" class="nav-link"><i class="fas fa-tree"></i> Associate Tree</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"8") == "1")
								<li class="nav-item"><a href="{{route('admin.associate.senior_change')}}" class="nav-link"><i class="icon-arrow-resize7"></i> Associate Senior Change</a></li>

							@endif

							@if(check_my_permission( Auth::user()->id,"277") == "1")
								<li class="nav-item"><a href="{{route('admin.associate.branchtransfer_change')}}" class="nav-link"><i class="icon-arrow-resize7"></i> Associate Branch Transfer</a></li>
	                          @endif

							@if(check_my_permission( Auth::user()->id,"9") == "1")
								<li class="nav-item"><a href="{{route('admin.associate.upgrade')}}" class="nav-link"><i class="fa fa-level-up"></i> Associate Upgrade</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"10") == "1")
							<li class="nav-item"><a href="{{route('admin.associate.downgrade')}}" class="nav-link"><i class="fa fa-level-down"></i> Associate Downgrade</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"11") == "1")
								<li class="nav-item"><a href="{{route('admin.associate.status')}}" class="nav-link"><i class="fas fa-ban"></i> Associate Deactivate or Activate </a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"12") == "1")
								<li class="nav-item"><a href="{{route('admin.associate.commission')}}" class="nav-link"><i class="fa fa-percent"></i> Associate Commission</a></li>
							@endif
							<!-- @if(check_my_permission( Auth::user()->id,"13") == "1")
								<li class="nav-item"><a href="{{route('admin.quotabusiness.index')}}" class="nav-link">  <i class="icon-library2"></i>Quota Business Report</a></li>
							@endif -->
							<!-- @if(check_my_permission( Auth::user()->id,"14") == "1")
								<li class="nav-item"><a href="{{route('admin.associate.commissionTransferNew')}}" class="nav-link">  <i class="icon-lan2"></i>Commission Transfer (Ledger Create)  </a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"15") == "1")
								<li class="nav-item"><a href="{{route('admin.associate.commissionTransferList')}}" class="nav-link">  <i class="fas fa-list-alt"></i>Commission Transfer (Ledger List)  </a></li>
							@endif -->

							 @if(check_my_permission( Auth::user()->id,"279") == "1")
                               <li class="nav-item"><a href="{{route('admin.associate.associatecollectionreport')}}" class="nav-link">  <i class="fas fa-list-alt"></i>Associate Collection Report  </a></li>
                               @endif
							   @if(check_my_permission( Auth::user()->id,"14") == "1" || check_my_permission( Auth::user()->id,"15") == "1" || check_my_permission( Auth::user()->id,"315") == "1" || check_my_permission( Auth::user()->id,"316") == "1" || check_my_permission( Auth::user()->id,"317") == "1" || check_my_permission( Auth::user()->id,"318") == "1" || check_my_permission( Auth::user()->id,"319") == "1" || check_my_permission( Auth::user()->id,"295") == "1"  || check_my_permission( Auth::user()->id,"320") == "1" || check_my_permission( Auth::user()->id,"329") == "1")
							   <li class="nav-item nav-item-submenu {{ set_active(['admin/daily-account-setting','admin/commission/*','admin/associate/commision/month-end-comission-list','admin/associate-exception','admin/associate/commission/month-end-comission-create','admin/commision/exception-list','admin/daily-account-setting','admin/new-associate-commission-list']) }}">
                                    <a href="#" class="nav-link"><i class="icon-user-tie"></i> <span>Commision</span></a>
                                    <ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/daily-account-setting','admin/commission/*','admin/associate/commision/month-end-comission-list','admin/associate-exception','admin/associate/commission/month-end-comission-create','admin/commision/exception-list','admin/daily-account-setting','admin/new-associate-commission-list']))
                                    style="display:block" @endif >
									@if(check_my_permission( Auth::user()->id,"315") == "1")
										  <li class="nav-item"><a href="{{route('admin.associatecommision.monthList')}}" class="nav-link" ><i class="icon-add"></i>Month End</a></li>
									@endif
									@if(check_my_permission( Auth::user()->id,"329") == "1")
										<li class="nav-item"><a href="{{route('admin.associatecommision.exceptionList')}}" class="nav-link" ><i class="icon-list"></i>Associate Exception</a></li>
									@endif
									@if(check_my_permission( Auth::user()->id,"15") == "1")
										<li class="nav-item"><a href="{{route('admin.associate.commission.commissionTransfer')}}" class="nav-link" ><i class="icon-lan2"></i>Ledger Create</a></li>
									@endif
									@if(check_my_permission( Auth::user()->id,"317") == "1")
										<li class="nav-item"><a href="{{route('admin.associate.commission.ledgerList')}}" class="nav-link" ><i class="fas fa-list-alt"></i>Ledger List</a></li>
									@endif
									@if(check_my_permission( Auth::user()->id,"318") == "1")
										<li class="nav-item"><a href="{{route('admin.dailyacc.setting')}}" class="nav-link" ><i class="fas fa-list-alt"></i>Daily Account Setting</a></li>
									@endif
									@if(check_my_permission( Auth::user()->id,"319") == "1")

										<li class="nav-item"><a href="{{route('admin.associate.commission.new')}}" class="nav-link" ><i class="fas fa-list-alt"></i> Commission Ledger Detail Company Wise</a></li>
									@endif
									@if(check_my_permission( Auth::user()->id,"295") == "1" || check_my_permission( Auth::user()->id,"320") == "1")

										<li class="nav-item nav-item-submenu {{ set_active(['admin/associate-commission-create','associate-commission-transfer-list']) }}">
											<a href="#" class="nav-link"><i class="icon-user-tie"></i> <span>OLd Commision</span></a>
											<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/associate-commission-create','associate-commission-transfer-list']))
											style="display:block" @endif >
											@if(check_my_permission( Auth::user()->id,"320") == "1")
												<li class="nav-item"><a href="{{route('admin.associate.commissionTransferList')}}" class="nav-link">  <i class="fas fa-list-alt"></i>Ledger List  </a></li>
											@endif
											</ul>
										</li>
									@endif
                                    </ul>
                                </li>
								@endif


							</ul>
						</li>
					@endif
				<!-------------- Associate Manangement end ------------------->

						{{--<li class="nav-item nav-item-submenu">
							<a href="#" class="nav-link"><i class="icon-cogs spinner"></i> <span>System configuration</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="System configuration">
								<li class="nav-item"><a href="{{route('admin.setting')}}" class="nav-link"><i class="icon-hammer-wrench"></i>Settings</a></li>
								<li class="nav-item"><a href="{{route('admin.email')}}" class="nav-link"><i class="icon-envelope"></i>Email</a></li>
								<li class="nav-item"><a href="{{route('admin.sms')}}" class="nav-link"><i class="icon-bubble"></i>Sms</a></li>
								<li class="nav-item"><a href="{{route('admin.account')}}" class="nav-link"><i class="icon-user"></i>Account information</a></li>
							</ul>
						</li>
						--}}
							<!-------------- SSB Account Setting Start ------------------->
				@if(check_my_permission( Auth::user()->id,"250") == "1" || check_my_permission( Auth::user()->id,"251") == "1" || check_my_permission( Auth::user()->id,"340") == "1")
					<li class="nav-item nav-item-submenu {{ set_active(['admin/ssbaccount-register', 'admin.ssbaccount.ssbaccountdetails','admin/ssbaccountstatus']) }}" >
						<a href="#" class="nav-link"><i class="icon-book"></i> <span>SSB Account Setting</span></a>
						<ul class="nav nav-group-sub" data-submenu-title="SSB Account Setting"

						@if(set_active(['admin/ssbaccount-register', 'admin/ssbaccountdetails','admin/ssbaccountstatus']))
						style="display:block"
						@endif >
							@if(check_my_permission( Auth::user()->id,"250") == "1")
							<li class="nav-item"><a href="{{route('admin.ssbaccount.save')}}" class="nav-link"><i class="icon-wallet"></i> Create Setting</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"251") == "1")
							<li class="nav-item"><a href="{{route('admin.ssbaccount.ssbaccountdetails')}}" class="nav-link"><i class="fas fa-users"></i>Activate Setting</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"340") == "1")
							<li class="nav-item {{set_active(['admin/ssbaccountstatus'])}}"><a href="{{route('admin.investment.ssbaccountstatus')}}" class="nav-link"><i class="fas fa-check"></i> SSB Account Status</a></li>
							@endif
						</ul>
					</li>

				@endif
					<!-------------- SSB Account Setting End ------------------->

				<!---------------- transaction serch Start ------------------->
				<!-- @if(check_my_permission( Auth::user()->id,"253") == "1")
				<li class="nav-item nav-item-submenu {{ set_active(['admin/ssbaccount-register']) }}" >
					<a href="#" class="nav-link"><i class="icon-book"></i> <span>Transcation search</span></a>
					<ul class="nav nav-group-sub" data-submenu-title="Transcation search"

					@if(set_active(['admin/transcationdetails']))
					style="display:block"
					@endif >
						@if(check_my_permission( Auth::user()->id,"253") == "1")
						<li class="nav-item"><a href="{{route('admin.transcation.transcationdetails')}}" class="nav-link"><i class="icon-wallet"></i> Transcation search</a></li>
						@endif
					</ul>
				</li>
				@endif -->


				

					@if(
						check_my_permission( Auth::user()->id,"17") == "1" ||
						check_my_permission( Auth::user()->id,"18") == "1" ||
						check_my_permission( Auth::user()->id,"19") == "1" ||
						check_my_permission( Auth::user()->id,"20") == "1" ||
						check_my_permission( Auth::user()->id,"21") == "1" ||
						check_my_permission( Auth::user()->id,"22") == "1" ||
						check_my_permission( Auth::user()->id,"256") == "1" ||
						check_my_permission( Auth::user()->id,"257") == "1" ||
						check_my_permission( Auth::user()->id,"278") == "1" ||
						check_my_permission( Auth::user()->id,"301") == "1"
						)

						<li class="nav-item nav-item-submenu {{ set_active(['admin/plan-categories','admin/py-plan-create','admin/investment/*','admin/investment-associate','admin/renewplan','admin/renewaldetails','admin/renew/recipt','admin/daily/report','admin/monthly/report','admin/investment-branch-transfer','admin/investment-log/*','admin/investment_management/collector-change','admin/renewplan/new']) }}" >
							<a href="#" class="nav-link"><i class="icon-pulse2"></i> <span>Investment Manangement</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="PY scheme" @if(set_active(['admin/plan-categories','admin/registerplan','admin/investment/*', 'admin/investments','admin/investment-associate','admin/renewplan','admin/renewaldetails','admin/renew/recipt','admin/renew/recipt','admin/daily/report','admin/monthly/report','admin/investment-branch-transfer','admin/investment-log/*','admin/investment_management/collector-change','admin/renewplan/new']))
							style="display:block" @endif>
								<!-- <li class="nav-item"><a href="{{route('admin.planCategory')}}" class="nav-link"><i class="icon-quill4"></i>Plan Category</a></li> -->
									{{-- <li class="nav-item"><a href="{{route('admin.py.completed')}}" class="nav-link"><i class="icon-cup2"></i>Completed</a></li>
									<li class="nav-item"><a href="{{route('admin.py.pending')}}" class="nav-link"><i class="icon-spinner2 spinner"></i>Pending</a></li> --}}

								{{-- @if(check_my_permission( Auth::user()->id,"18") == "1")
									<li class="nav-item"><a href="{{route('admin.register.plan')}}" class="nav-link"><i class="icon-puzzle4"></i>New Investment</a></li>
								@endif --}}
								@if(check_my_permission( Auth::user()->id,"18") == "1")
									 <li class="nav-item"><a href="{{route('investment.create')}}" class="nav-link"><i class="icon-puzzle4"></i>New Investment</a></li>
								@endif
								@if(check_my_permission( Auth::user()->id,"19") == "1")
									<li class="nav-item"><a href="{{route('admin.investment.plans')}}" class="nav-link"><i class="fas fa-users"></i> Investment Plan Details</a></li>
								@endif
								@if(check_my_permission( Auth::user()->id,"20") == "1")
									<li class="nav-item"><a href="{{route('admin.investment.associate_change')}}" class="nav-link"><i class="icon-arrow-resize7"></i> Investment Associate Change</a></li>
								@endif

								@if(check_my_permission( Auth::user()->id,"278") == "1")
								<li class="nav-item"><a href="{{route('admin.investment.investementbranch_change')}}" class="nav-link"><i class="icon-arrow-resize7"></i> Investment Branch Transfer</a></li>
								@endif


								@if(check_my_permission( Auth::user()->id,"21") == "1")
									<!-- <li class="nav-item"><a href="{{route('admin.renew')}}" class="nav-link"><i class="fa fa-refresh"></i> Renewal Investment</a></li> -->
									<li class="nav-item"><a href="{{route('admin.renew.new')}}" class="nav-link"><i class="fa fa-refresh"></i> Renewal Investment</a></li>
								@endif
								@if(check_my_permission( Auth::user()->id,"22") == "1")
									<li class="nav-item"><a href="{{route('admin.investment.renewaldetails')}}" class="nav-link"><i class="icon-redo"></i> Renewal List</a></li>
								@endif
								<!-- @if(check_my_permission( Auth::user()->id,"139") == "1")
								<li class="nav-item"><a href="{{route('admin.renew.updaterenewal')}}" class="nav-link"><i class="icon-redo"></i> Update Renewal</a></li>
								@endif -->
								@if(check_my_permission( Auth::user()->id,"255") == "1")
								<li class="nav-item"><a href="{{route('common.investment.daily.report')}}" class="nav-link"><i class="fas fa-money-bill-wave-alt"></i> Daily Investment Plan Due Report</a></li>
								@endif
								@if(check_my_permission( Auth::user()->id,"256") == "1")
								<li class="nav-item"><a href="{{route('common.investment.monthly.report')}}" class="nav-link"><i class="fas fa-money-bill-wave-alt"></i> Monthly Investment Plan Due Report</a></li>
								@endif

								@if(check_my_permission( Auth::user()->id,"301") == "1")
								<li class="nav-item"><a href="{{route('admin.investment_management.investmentcollector.collectorchangeindex')}}" class="nav-link" ><i class="icon-add"></i>Investment Collector Change </a></li>
								@endif

							</ul>
						</li>
					@endif

					@if(check_my_permission( Auth::user()->id,"25") == "1" || check_my_permission( Auth::user()->id,"26") == "1" || check_my_permission( Auth::user()->id,"27") == "1" || check_my_permission( Auth::user()->id,"28") == "1" || check_my_permission( Auth::user()->id,"29") == "1" || check_my_permission( Auth::user()->id,"283") == "1" || check_my_permission( Auth::user()->id,"284") == "1" || check_my_permission( Auth::user()->id,"285") == "1" || check_my_permission( Auth::user()->id,"302") == "1" || check_my_permission( Auth::user()->id,"13") == "1" ||check_my_permission( Auth::user()->id,"344") == "1"||check_my_permission( Auth::user()->id,"254") == "1" || check_my_permission( Auth::user()->id,"357") == "1" || check_my_permission( Auth::user()->id,"366") == "1")

						<li class="nav-item nav-item-submenu {{ set_active(['admin/loans', 'admin/loan-create','admin/loan-requests','admin/group-loan-requests','admin/loan-transactions','admin/loan/*','admin/loans/*','admin/loan-emi-delete-form','admin/loan-branch-transfer','admin/account-log','admin/loan/loancollector/collector-change','admin/common_loan_emi_payment','admin/loan/ecs_change','loan/updates/emi_due_date/correction','admin/loan/update/emioutstanding','admin/loan/ecs/bounce_charges/current_status']) }}" >
							<a href="#" class="nav-link"><i class="icon-snowflake"></i > <span>Loan management</span></a>
							<ul class="nav nav-group-sub"  data-submenu-title="Loan management" @if(set_active(['admin/loans', 'admin/loan-pending','admin/group-loan-requests','admin/loan-create','admin/loan-requests','admin/loan-transactions','admin/loan/*','admin/loans/*','admin/loan-emi-delete-form','admin/loan/loancollector/collector-change','admin/common_loan_emi_payment','admin/loan/ecs_change','loan/updates/emi_due_date/correction','admin/loan/update/emioutstanding','admin/loan/ecs/bounce_charges/current_status']))
							style="display:block" @endif>
							{{-- 
							<li class="nav-item"><a href="{{route('admin.loan.create')}}" class="nav-link"><i class="icon-quill4"></i>Create loan</a></li>
							@if(check_my_permission( Auth::user()->id,"25") == "1")
								<li class="nav-item"><a href="{{route('admin.loan.loans')}}" class="nav-link"><i class="fa fa-money"></i>Loans</a></li>
							@endif 
							--}}
							@if(check_my_permission( Auth::user()->id,"26") == "1")
								<li class="nav-item"><a href="{{route('admin.loan.request')}}" class="nav-link"><i class="fa fa-list"></i>Loan Registration Details</a></li>
							@endif
							{{--
							@if(check_my_permission( Auth::user()->id,"27") == "1")
								<li class="nav-item"><a href="{{route('admin.grouploan.request')}}" class="nav-link"><i class="icon-puzzle4"></i>Group Loan Registration Details</a></li>
							@endif
							--}}
							@if(check_my_permission( Auth::user()->id,"28") == "1")
								<li class="nav-item"><a href="{{route('admin.loan.recovery')}}" class="nav-link"><i class="fa fa-undo"></i>Loan Recovery</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"29") == "1")
								<li class="nav-item"><a href="{{route('admin.grouploan.recovery')}}" class="nav-link"><i class="fas fa-recycle"></i>Group Loan Recovery</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"254") == "1" )
							<li class="nav-item {{ set_active(['admin/common_loan_emi_payment']) }}">
								<a href="{{ route('admin.common.LoanEmiPayment') }}" class="nav-link"><i class="fas fa-money-bill"></i> Emi Payment</a>
							</li>
							@endif
							@if(check_my_permission( Auth::user()->id,"265") == "1")
							<li class="nav-item"><a href="{{route('admin.loan.transaction')}}" class="nav-link"><i class="fa fa-list"></i>Loan Transaction Listing</a></li>
							@endif
							<li class="nav-item"><a href="{{route('admin.memberLoans.outStanding')}}" class="nav-link"><i class="fas fa-recycle"></i> Loan Outstanding Report</a></li>

							{{-- <li class="nav-item"><a href="{{route('admin.memberGroupLoans.outStanding')}}" class="nav-link"><i class="fas fa-recycle"></i> Group Loan Outstanding Report</a></li> --}}
							@if(check_my_permission( Auth::user()->id,"344") == "1"  || check_my_permission( Auth::user()->id,"345") == "1" || check_my_permission( Auth::user()->id,"346") == "1" )
							<li class="nav-item nav-item-submenu {{ set_active(['admin/loan/bank-ecs-import','admin/loan/ecs_deduction','admin/loan/ecs_change','admin/loan/ecs/bounce_charges/current_status']) }}">
								<a href="#" class="nav-link"><i class="fa fa-pencil-square-o"></i>
									<span>ECS</span></a>
								<ul class="nav nav-group-sub" data-submenu-title="News Section"
									@if (set_active(['admin/loan/bank-ecs-import','admin/loan/ecs_deduction','admin/loan/ecs/ecs_transactions','admin/loan/ecs_change','admin/loan/ecs/bounce_charges/current_status']))  style="display:block" @endif>
									@if(check_my_permission( Auth::user()->id,"347") == "1")
										<li
										class="nav-item  {{ set_active(['admin/loan/ecs_deduction']) }}">
											<a class="nav-link" href="{{route('admin.loan.ecsDeduction')}}"><i class="fas fa-clipboard-list"></i>ECS Deduction Listing</a>
									</li>@endif
									@if(check_my_permission( Auth::user()->id,"345") == "1")
									<li class="nav-item {{ set_active(['admin/loan/bank-ecs-import']) }} "><a href="{{ route('admin.loan.importView') }}" class="nav-link"><i class="fas fa-upload"></i>Bank ECS Import</a>
									</li>
									@endif
									@if(check_my_permission( Auth::user()->id,"346") == "1")<li
										class="nav-item  {{ set_active(['admin/loan/ecs/ecs_transactions']) }}">
										<a href="{{ route('admin.ecs.ecs.transactions_list') }}"class="nav-link"><i class="fas fa-recycle"></i>ECS Transaction</a>
									</li>
									@endif
									@if(check_my_permission( Auth::user()->id,"344") == "1")
										<li class="nav-item {{ set_active(['admin/loan/ecs_change']) }}">
											<a href="{!! route('admin.loan.loancollector.ecschangeindex') !!}" class="nav-link"><i class="icon-add"></i>ECS Type Change </a>
										</li>
									@endif
									@if(check_my_permission( Auth::user()->id,"366") == "1")
										<li class="nav-item {{ set_active(['admin/loan/ecs/bounce_charges/current_status']) }}">
											<a href="{!! route('admin.loan.ecs.bounce_charge.status') !!}" class="nav-link"><i class="icon-add"></i>Bounce ECS Current Status </a>
										</li>
									@endif
								</ul>
							</li>
							@endif
							

							<!-- <li class="nav-item"><a href="{{route('admin.loan.hold')}}" class="nav-link"><i class="icon-watch"></i>On hold</a></li>
							<li class="nav-item"><a href="{{route('admin.loan.pending')}}" class="nav-link"><i class="icon-spinner2 spinner"></i>Awaiting payback</a></li>
							<li class="nav-item"><a href="{{route('admin.loan.completed')}}" class="nav-link"><i class="icon-cup2"></i>Completed</a></li> -->

							@if(check_my_permission( Auth::user()->id,"283") == "1" || check_my_permission( Auth::user()->id,"284") == "1" || check_my_permission( Auth::user()->id,"285") == "1")
								<li class="nav-item nav-item-submenu {{ set_active(['admin/loan-branch-transfer','admin/account-log']) }}">
									<a href="#" class="nav-link"><i class="icon-user-tie"></i> <span>Loan Branch Transfer</span></a>
									<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/loan-branch-transfer','admin/account-log']))
									style="display:block" @endif >
										@if(check_my_permission( Auth::user()->id,"284") == "1")
											<li class="nav-item">
												<a href="{{route('admin.loan.loanbranchtransfer_change')}}" class="nav-link" ><i class="icon-add"></i>Branch Transfer</a>
											</li>
										@endif
										@if(check_my_permission( Auth::user()->id,"285") == "1")
											<li class="nav-item">
												<a href="{{route('admin.loan.account_log')}}" class="nav-link" ><i class="icon-list"></i>Account Logs</a>
											</li>
										@endif
									</ul>
								</li>
							@endif

							@if(check_my_permission( Auth::user()->id,"302") == "1")
							<li class="nav-item {{ set_active(['admin/loan/loancollector/collector-change']) }}">
								<a href="{{route('admin.loan.loancollector.collectorchangeindex')}}" class="nav-link" >
									<i class="icon-add"></i>Loan Collector Change 
								</a>
							</li>
							@endif
							@if(check_my_permission( Auth::user()->id,"138") == "1")
							<li class="nav-item"><a href="{{route('admin.loan.emi_deleteForm')}}" class="nav-link"><i class="fas fa-recycle"></i>Delete Loan EMI</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"357") == "1")
							<li class="nav-item"><a href="{{route('admin.loan.emi_due_date.change')}}" class="nav-link"><i class="fas fa-recycle"></i>Emi Due Date & Emi Amount Correction </a></li>
							@endif

							@if(check_my_permission( Auth::user()->id,"365") == "1")
							<li class="nav-item"><a href="{{route('admin.loan.emioutstanding.update')}}" class="nav-link"><i class="fas fa-upload"></i>Emi Outstanding update </a></li>
							@endif
						</ul>
					</li>

					
					@endif

					@if(check_my_permission( Auth::user()->id,"133") == "1" || check_my_permission( Auth::user()->id,"84") == "1" || check_my_permission( Auth::user()->id,"85") == "1" || check_my_permission( Auth::user()->id,"86") == "1" || check_my_permission( Auth::user()->id,"299") == "1" || check_my_permission( Auth::user()->id,"87") == "1")

						<li class="nav-item nav-item-submenu {{ set_active(['admin/demand-advices','admin/demand-advice-approve','admin/demand-advice/*']) }}">
							<a href="#" class="nav-link"><i class="fas fa-tasks"></i> <span>Demand Advice</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/demand-advices','admin/demand-advice-approve','admin/demand-advice/*']))
							style="display:block" @endif >
							@if(check_my_permission( Auth::user()->id,"133") == "1")
								<li class="nav-item"><a href="{{route('admin.demand.addadvice')}}" class="nav-link" ><i class="far fa-plus-square"></i>Add Demand Advice</a></li>
							@endif
								<!-- <li class="nav-item"><a href="{{route('admin.demand.advices')}}" class="nav-link"><i class="fas fa-users"></i>Demand Advices</a></li> -->
							@if(check_my_permission( Auth::user()->id,"84") == "1")
								<li class="nav-item"><a href="{{route('admin.demand.report')}}" class="nav-link"><i class="icon-chart"></i>Demand Advice Report</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"85") == "1")
								<li class="nav-item"><a href="{{route('admin.demand.advices.demand_advice_maturity')}}" class="nav-link"><i class="fas fa-calculator"></i>Demand Advice Maturity</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"86") == "1")
								<li class="nav-item"><a href="{{route('admin.demand.application')}}" class="nav-link"><i class="fa fa-files-o" aria-hidden="true"></i>Demand Advice Application</a></li>
							@endif
							<!-- @if(check_my_permission( Auth::user()->id,"87") == "1")
								<li class="nav-item"><a href="{{route('admin.damandadvice.viewtaadvanced')}}" class="nav-link"><i class="fas fa-eye"></i>View TA advance and Imprest Advice</a></li>
							@endif -->
							@if(check_my_permission( Auth::user()->id,"299") == "1")
							<li class="nav-item"><a href="{{route('admin.demand.report_reject')}}" class="nav-link"><i class="fas fa-eye"></i> Demand Advice Reject Report</a></li>
							@endif
							</ul>
						</li>
					@endif

					{{--<li class="nav-item nav-item-submenu">
						<a href="#" class="nav-link"><i class="icon-pulse2"></i> <span>Manage assets</span></a>
						<ul class="nav nav-group-sub" data-submenu-title="Manage assets">
							<li class="nav-item"><a href="{{route('admin.asset.create')}}" class="nav-link"><i class="icon-quill4"></i>Create asset</a></li>
							<li class="nav-item"><a href="{{route('admin.asset.plans')}}" class="nav-link"><i class="icon-puzzle4"></i>assets</a></li>
							<li class="nav-item"><a href="{{route('admin.asset.buy')}}" class="nav-link"><i class="icon-spinner2 spinner"></i>Buying log</a></li>
							<li class="nav-item"><a href="{{route('admin.asset.sell')}}" class="nav-link"><i class="icon-spinner2 spinner"></i>Selling log</a></li>
							<li class="nav-item"><a href="{{route('admin.asset.exchange')}}" class="nav-link"><i class="icon-spinner2 spinner"></i>Exchange log</a></li>
						</ul>
					</li>--}}

					<!--Bank Management Menu Start-->
							<li class="nav-item nav-item-submenu {{ set_active(['admin/bank','admin/bank-accounts']) }}" >
								<a href="#" class="nav-link"><i class="icon-pulse2"></i> <span>Bank Manangement</span></a>
								<ul class="nav nav-group-sub" data-submenu-title="PY scheme" @if(set_active(['admin/bank','admin/bank-accounts']))
								style="display:block" @endif>
									<li class="nav-item"><a href="{{route('admin.bank')}}" class="nav-link"><i class="fas fa-landmark"></i>Banks</a></li>
									<li class="nav-item"><a href="{{route('admin.bank-accounts')}}" class="nav-link"><i class="fas fa-money-check"></i>Bank Accounts</a></li>
								</ul>
							</li>
					<!--Bank Management Menu End-->

					


						{{--<li class="nav-item nav-item-submenu">
							<a href="#" class="nav-link"><i class="icon-box"></i> <span>Save 4 me</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="Save 4 me">
								<li class="nav-item"><a href="{{route('admin.save.completed')}}" class="nav-link"><i class="icon-cup2"></i>Completed</a></li>
								<li class="nav-item"><a href="{{route('admin.save.pending')}}" class="nav-link"><i class="icon-spinner2 spinner"></i>On hold</a></li>
							</ul>
						</li>--}}
						{{--<li class="nav-item nav-item-submenu">
							<a href="#" class="nav-link"><i class="icon-credit-card"></i><span>Deposit system</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="Deposit">
								<li class="nav-item"><a href="{{route('admin.deposit.method')}}" class="nav-link"><i class="icon-puzzle4"></i>Payment gateways</a></li>
								<li class="nav-item"><a href="{{route('admin.banktransfer')}}" class="nav-link"><i class="icon-share2"></i>Bank transfer & logs</a></li>
								<li class="nav-item"><a href="{{route('admin.deposit.log')}}" class="nav-link"><i class="icon-list-unordered"></i>Deposit log</a></li>
								<li class="nav-item"><a href="{{route('admin.deposit.pending')}}" class="nav-link"><i class="icon-spinner2 spinner"></i>Pending deposit</a></li>
								<li class="nav-item"><a href="{{route('admin.deposit.approved')}}" class="nav-link"><i class="icon-thumbs-up2"></i>Approved deposit</a></li>
								<li class="nav-item"><a href="{{route('admin.deposit.declined')}}" class="nav-link"><i class="icon-thumbs-down2"></i>Declined deposit</a></li>
							</ul>
						</li>--}}
						{{--<li class="nav-item nav-item-submenu">
							<a href="#" class="nav-link"><i class="icon-credit-card"></i><span>Merchant system</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="Deposit">
								<li class="nav-item"><a href="{{route('transfer.log')}}" class="nav-link"><i class="icon-list-unordered"></i>Transfer logs</a></li>
								<li class="nav-item"><a href="{{route('merchant.log')}}" class="nav-link"><i class="icon-list-unordered"></i>Merchant logs</a></li>
								<li class="nav-item"><a href="{{route('pending.merchant')}}" class="nav-link"><i class="icon-spinner2 spinner"></i>Pending merchant</a></li>
								<li class="nav-item"><a href="{{route('approved.merchant')}}" class="nav-link"><i class="icon-thumbs-up2"></i>Approved merchant</a></li>
								<li class="nav-item"><a href="{{route('declined.merchant')}}" class="nav-link"><i class="icon-thumbs-down2"></i>Declined merchant</a></li>
							</ul>
						</li>--}}
					@if(
					check_my_permission( Auth::user()->id,"31") == "1"
					|| check_my_permission( Auth::user()->id,"32") == "1"
					|| check_my_permission( Auth::user()->id,"33") == "1"
					|| check_my_permission( Auth::user()->id,"34") == "1"
					|| check_my_permission( Auth::user()->id,"275") == "1"
					|| check_my_permission( Auth::user()->id,"276") == "1"
					|| check_my_permission( Auth::user()->id,"354") == "1"
					|| check_my_permission( Auth::user()->id,"355") == "1"
					|| check_my_permission( Auth::user()->id,"356") == "1"
					|| check_my_permission( Auth::user()->id,"353") == "1")

						<li class="nav-item nav-item-submenu {{ set_active(['admin/fund-transfer','admin/edit/branch_to_ho/*','admin/fund-transfer/*','admin/fund-transfer-detail/*']) }}" >
							<a href="#" class="nav-link"><i class="icon-share2"></i> <span>Fund Transfer</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="Fund Transfer" @if(set_active(['admin/fund-transfer','admin/fund-transfer/*','admin/edit/branch_to_ho/*','admin/fund-transfer-detail/*','admin/admin-fund-transfer-bankTobank','admin/bank-ledger-report']))
							style="display:block" @endif>
							@if(check_my_permission( Auth::user()->id,"31") == "1")
								<li class="nav-item text-default">
									<a href="{{route('admin.fund-transfer.branchToHo')}}" class="nav-link"><i class="fas fa-piggy-bank"></i>Branch To HO</a>
								</li>
							@endif
							@if(check_my_permission( Auth::user()->id,"32") == "1")
								<li class="nav-item text-default">
									<a href="{{route('admin.fund.transfer')}}" class="nav-link"><i class="fa fa-exchange" aria-hidden="true"></i>Bank To Bank</a>
								</li>
							@endif
							@if(check_my_permission( Auth::user()->id,"33") == "1")
							<li class="nav-item text-default">
									<a href="{{route('admin.bank-ledger.report')}}" class="nav-link"><i class="fas fa-list-alt" aria-hidden="true"></i>Bank Ledger</a>
								</li>
							@endif
							@if(check_my_permission( Auth::user()->id,"34") == "1")
								<li class="nav-item text-default">
									<a class="nav-link" href="{{route('admin.fund-transfer.report')}}"><i class="fa fa-list" aria-hidden="true"></i>Report</a>
								</li>
							@endif
							<!-- @if(check_my_permission( Auth::user()->id,"275") == "1")
							 <li class="nav-item text-default">
									<a href="{{route('admin.bank-balance.update')}}" class="nav-link"><i class="fas fa-list-alt" aria-hidden="true"></i> Bank Update</a>
								</li>
							@endif
							@if(check_my_permission( Auth::user()->id,"276") == "1")
								<li class="nav-item text-default">
									<a href="{{route('admin.cash-balance.update')}}" class="nav-link"><i class="fas fa-list-alt" aria-hidden="true"></i> Cash Update</a>
								</li>
							@endif	 -->
							</ul>
						</li>
					@endif
					@if(check_my_permission( Auth::user()->id,"35") == "1")
						<li class="nav-item">
							<a href="{{route('admin.cash-in-hand')}}" class="nav-link">
								<i class="icon-cash4"></i>
								<span>
									Cash In Hand Report
								</span>
							</a>
						</li>
					@endif
				 <!-- @if(check_my_permission( Auth::user()->id,"36") == "1")
						<li class="nav-item">
							<a href="{{route('admin.profit&loss')}}" class="nav-link">
								<i class="fas fa-chart-line"></i>
								<span>
									Profit & Loss
								</span>
							</a>
						</li>
					@endif

					@if(check_my_permission( Auth::user()->id,"37") == "1")
						<li class="nav-item">
							<a href="{{route('admin.balance.sheet')}}" class="nav-link">
								<i class="icon-balance"></i>
								<span>
									Balance Sheet
								</span>
							</a>
						</li>
					@endif

					 @if(check_my_permission( Auth::user()->id,"37") == "1")
						<li class="nav-item">
							<a href="{{route('admin.trail_balance')}}" class="nav-link">
								<i class="icon-balance"></i>
								<span>
									Trial Balance Sheet
								</span>
							</a>
						</li>
					 @endif -->

					 @if (check_my_permission(Auth::user()->id, '37') == '1' || check_my_permission(Auth::user()->id, '36') == '1')
                            <li
                                class="nav-item nav-item-submenu {{ set_active(['admin/balance-sheet', 'admin/balance-sheet/*', 'admin/profit-loss', 'admin/trail_balance', 'admin/head/closing-listing', 'admin/head-closing-save', 'admin/trail_balance/sub_head/*', 'admin/trail_balance/sub_head']) }}">
                                <a href="#"
                                    class="nav-link  nav-item-submenu {{ set_active(['admin/balance-sheet', 'admin/balance-sheet/*', 'admin/profit-loss', 'admin/trail_balance', 'admin/head/closing-listing', 'admin/head-closing-save', 'admin/trail_balance/sub_head/*', 'admin/trail_balance/sub_head']) }}"><i
                                        class="icon-user"></i> <span>Financial Reports</span></a>
                                <ul class="nav nav-group-sub" data-submenu-title="Financial Reports"
                                    @if (set_active([
                                            'admin/balance-sheet',
                                            'admin/profit-loss',
                                            'admin/trail_balance',
                                            'admin/head/closing-listing',
                                            'admin/head-closing-save',
                                            'admin/trail_balance/sub_head/*',
                                            'admin/trail_balance/sub_head',
                                        ])) style="display:block" @endif>
                                    @if (check_my_permission(Auth::user()->id, '36') == '1')
                                        <li class="nav-item {{ set_active(['admin/profit-loss']) }}"><a
                                                href="{{ route('admin.profit&loss') }}" class="nav-link"><i
                                                    class="fa fa-list-alt"></i>Profit & Loss</a></li>
                                    @endif
                                    @if (check_my_permission(Auth::user()->id, '37') == '1')
                                        <li
                                            class="nav-item {{ set_active(['admin/balance-sheet', 'admin/balance-sheet/*']) }}">
                                            <a href="{{ route('admin.balance.sheet') }}" class="nav-link"><i
                                                    class="fa fa-list-alt"></i>Balance Sheet</a>
                                        </li>
                                        <li
                                            class="nav-item {{ set_active(['admin/trail_balance', 'admin/trail_balance/sub_head/*', 'admin/trail_balance/sub_head']) }}">
                                            <a href="{{ route('admin.trail_balance') }}" class="nav-link"><i
                                                    class="fas fa-hand-holding-usd"></i>Trial Balance Sheet</a>
                                        </li>


                                    <li
                                        class="nav-item nav-item-submenu {{ set_active(['admin/head/closing-listing', 'admin/head-closing-save']) }}">
                                        <a href="{{ route('admin.head.closing_list') }}" class="nav-link"> <i
                                                class="icon-balance"></i> Head Closing Balance </a>
                                        <ul class="nav  nav-group-sub" data-submenu-title="Loan From Banks"
                                            @if (set_active(['admin/head/closing-listing', 'admin/head-closing-save'])) style="display:block" @endif>
                                            <li class="nav-item {{ set_active(['admin/head/closing-listing']) }}"><a
                                                    href="{{ route('admin.head.closing_list') }}"
                                                    class="nav-link"><i class="icon-list"></i>View Head Closing Amount</a></li>
                                            <li class="nav-item {{ set_active(['admin/head-closing-save']) }}"><a
                                                    href="{{ route('admin.head.closing_save') }}"
                                                    class="nav-link"><i class="icon-add"></i> Add Head Closing Amount
                                                </a></li>
                                        </ul>
                                    </li>
									@endif
                                </ul>
                            </li>
                        @endif


						<!-- End Balance Sheet -->
						<!-- BRS -->
					@if(check_my_permission( Auth::user()->id,"39") == "1" || check_my_permission( Auth::user()->id,"40") == "1")
						<li class="nav-item nav-item-submenu {{ set_active(['admin/brs/report','admin/brs/bank_charge']) }}" >
							<a href="#" class="nav-link  nav-item-submenu"><i class="icon-user"></i> <span>BRS  Management</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="BRS" @if(set_active(['admin/brs/report','admin/brs/bank_charge']))
							style="display:block" @endif>
							@if(check_my_permission( Auth::user()->id,"39") == "1")
								<li class="nav-item"><a href="{{route('admin.brs.report')}}" class="nav-link"><i class="fa fa-list-alt"></i>BRS List</a></li>
							@endif

							@if(check_my_permission( Auth::user()->id,"40") == "1")
								<li class="nav-item"><a href="{{route('admin.brs.bank_charge')}}" class="nav-link"><i class="fas fa-hand-holding-usd"></i>Bank Charge Management</a></li>
							@endif

							</ul>
						</li>
					@endif
					<!-- @if(check_my_permission( Auth::user()->id,"140") == "1")
					<li class="nav-item nav-item-submenu {{ set_active(['admin/e-investment-maturity']) }}" >
							<a href="#" class="nav-link  nav-item-submenu"><i class="icon-user"></i> <span>Reinvest Money Back Plan Maturity</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="BRS" @if(set_active(['admin/e-investment-maturity'])) style="display:block" @endif>
									@if(check_my_permission( Auth::user()->id,"139") == "1")
								<li class="nav-item"><a href="{{route('admin.e_investment_maturity')}}" class="nav-link"><i class="fa fa-list-alt"></i>ELI Money Back</a></li>
								@endif
							</ul>
						</li>
					@endif	 -->

					@if(check_my_permission( Auth::user()->id,"42") == "1" || check_my_permission( Auth::user()->id,"23") == "1" )
						<li class="nav-item nav-item-submenu {{ set_active(['admin/withdrawal','admin/savingaccountreport']) }}">
							<a href="#" class="nav-link"><i class="icon-share2"></i><span>Payment Management</span></a>

							<ul class="nav nav-group-sub" data-submenu-title="Withdraw" @if(set_active(['admin/withdrawal','admin/savingaccountreport']))
							style="display:block" @endif>
							@if(check_my_permission( Auth::user()->id,"42") == "1")
							<li class="nav-item"><a href="{{route('admin.withdraw.ssb')}}" class="nav-link"><i class="icon-puzzle4"></i>SSB Withdraw</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"23") == "1")
								<li class="nav-item"><a href="{{route('admin.investment.savingaccountreport')}}" class="nav-link"><i class="fas fa-money-bill-wave-alt"></i> Saving Listing</a></li>
							@endif
								<!-- <li class="nav-item"><a href="{{route('admin.withdraw.log')}}" class="nav-link"><i class="icon-list-unordered"></i>Withdraw log</a></li>
								<li class="nav-item"><a href="{{route('admin.withdraw.unpaid')}}" class="nav-link"><i class="icon-spinner2 spinner"></i>Unpaid withdrawal</a></li>
								<li class="nav-item"><a href="{{route('admin.withdraw.approved')}}" class="nav-link"><i class="icon-thumbs-up2"></i>Approved withdrawal</a></li>
								<li class="nav-item"><a href="{{route('admin.withdraw.declined')}}" class="nav-link"><i class="icon-accessibility"></i>Declined withdrawal</a></li> -->
							</ul>
						</li>
					@endif
						{{--<li class="nav-item nav-item-submenu">
							<a href="#" class="nav-link"><i class="icon-share2"></i><span>Withdraw system</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="Withdraw">
								<li class="nav-item"><a href="{{route('admin.withdraw.method')}}" class="nav-link"><i class="icon-puzzle4"></i>Withdraw methods</a></li>
								<li class="nav-item"><a href="{{route('admin.withdraw.log')}}" class="nav-link"><i class="icon-list-unordered"></i>Withdraw log</a></li>
								<li class="nav-item"><a href="{{route('admin.withdraw.unpaid')}}" class="nav-link"><i class="icon-spinner2 spinner"></i>Unpaid withdrawal</a></li>
								<li class="nav-item"><a href="{{route('admin.withdraw.approved')}}" class="nav-link"><i class="icon-thumbs-up2"></i>Approved withdrawal</a></li>
								<li class="nav-item"><a href="{{route('admin.withdraw.declined')}}" class="nav-link"><i class="icon-accessibility"></i>Declined withdrawal</a></li>
							</ul>
						</li>
						<li class="nav-item nav-item-submenu">
							<a href="#" class="nav-link"><i class="icon-magazine"></i> <span>News Section</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="News Section">
								<li class="nav-item"><a href="{{route('blog.create')}}" class="nav-link"><i class="icon-quill4"></i>New Post</a></li>
								<li class="nav-item"><a href="{{route('admin.blog')}}" class="nav-link"><i class="icon-newspaper"></i>Articles</a></li>
								<li class="nav-item"><a href="{{route('admin.cat')}}"class="nav-link"><i class="icon-clipboard6"></i>Category</a></li>
							</ul>
						</li>--}}
							<!-- Samraddh FD Bank -->
						@if(check_my_permission( Auth::user()->id,"257") == "1" || check_my_permission( Auth::user()->id,"258") == "1" || check_my_permission( Auth::user()->id,"260") == "1" )
						<li class="nav-item nav-item-submenu {{ set_active(['admin/create/samraddh/bank','admin/samraddh/fd/list', 'admin/company_bound/interest/*','admin/samraddh/fd/close/*','admin/samraddh/interest/transaction/*']) }}">
							<a href="#" class="nav-link"><i class="icon-cogs spinner"></i> <span>Samraddh Bank FDR</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="Samraddh bank" @if(set_active(['admin/create/samraddh/bank','admin/samraddh/fd/list', 'admin/company_bound/interest/*','admin/samraddh/fd/close/*','admin/samraddh/interest/transaction/*']))style="display:block" @endif >
								 @if(check_my_permission( Auth::user()->id,"258") == "1")
								<li class="nav-item"><a href="{{route('admin.create.fd')}}" class="nav-link"><i class="icon-hammer-wrench"></i>Create FDR</a></li>
								@endif
								 @if(check_my_permission( Auth::user()->id,"260") == "1")
								<li class="nav-item"><a href="{{route('admin.company.fd.list')}}" class="nav-link"><i class="icon-envelope"></i>FDR List</a></li>
								@endif
							</ul>
						</li>
						@endif
					<!-- Smnaraddh Bank End -->
						<!-- AccountHead Management  -->
					@if(
						check_my_permission( Auth::user()->id,"44") == "1" ||
						check_my_permission( Auth::user()->id,"46") == "1" ||
						check_my_permission( Auth::user()->id,"47") == "1" ||
						check_my_permission( Auth::user()->id,"48") == "1" ||
						check_my_permission( Auth::user()->id,"49") == "1" ||
						check_my_permission( Auth::user()->id,"50") == "1" ||
						check_my_permission( Auth::user()->id,"51") == "1" ||
						check_my_permission( Auth::user()->id,"52") == "1" ||
						check_my_permission( Auth::user()->id,"54") == "1" ||
						check_my_permission( Auth::user()->id,"55") == "1" ||
						check_my_permission( Auth::user()->id,"56") == "1" ||
						check_my_permission( Auth::user()->id,"57") == "1" ||
						check_my_permission( Auth::user()->id,"58") == "1" ||
						check_my_permission( Auth::user()->id,"59") == "1" ||
						check_my_permission( Auth::user()->id,"60") == "1" ||
						check_my_permission( Auth::user()->id,"136") == "1" ||
						check_my_permission( Auth::user()->id,"142") == "1" ||
						check_my_permission( Auth::user()->id,"143") == "1" ||
						check_my_permission( Auth::user()->id,"144") == "1"
						)
						<li class="nav-item nav-item-submenu {{ set_active([
							'admin/loanFromBank',
							'admin/loan_emi',
							'admin/accountHead',
							'admin/shareholder',
							'admin/fixed_asset',
							'admin/eli-loan',
							'admin/loanFromBank',
							'admin/bank_account',
							'admin/indirect_expense',
							'admin/edit/holder_director/*',
							'admin/eli_loan/edit/*',
							'admin/loan_from_bank/edit/*',
							'admin/fixed_asset/edit/*',
							'admin/bank_account/edit/*',
							'admin/indirect_expense/edit/*',
							'admin/shareholder',
							'admin/director/deposit_payment',
							'admin/director/withdrawal_payment',
							'admin/share_holder/deposit_payment',
							'admin/share-holder/transfer_share',
							'admin/create/fixed_asset',
							'admin/create/bank_account',
							'admin/create/indirect_expense',
							'admin/loanFromBank/*',
							'admin/shareholder_director/*',
							'admin/shareholder_director',
							'admin/create/eli_Loan',
							'admin/account_head_ledger/transaction/*',
							'admin/head/closing-listing',
							'admin/head-closing-save',
							'admin/head-grouping'
							]) }}" >
							<a href="#" class="nav-link"><i class="icon-accessibility"></i> <span>Account Head Management</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="Account Head Management" @if(set_active([
								'admin/loanFromBank',
								'admin/loan_emi',
								'admin/accountHead',
								'admin/shareholder',
								'admin/fixed_asset',
								'admin/eli-loan',
								'admin/loanFromBank',
								'admin/bank_account',
								'admin/indirect_expense',
								'admin/edit/holder_director/*',
								'admin/eli_loan/edit/*',
								'admin/loan_from_bank/edit/*',
								'admin/fixed_asset/edit/*',
								'admin/bank_account/edit/*',
								'admin/indirect_expense/edit/*',
								'admin/shareholder',
								'admin/director/deposit_payment',
								'admin/director/withdrawal_payment',
								'admin/share_holder/deposit_payment',
								'admin/share-holder/transfer_share',
								'admin/create/fixed_asset',
								'admin/create/bank_account',
								'admin/create/indirect_expense',
								'admin/loanFromBank/*',
								'admin/shareholder_director/*',
								'admin/shareholder_director',
								'admin/head',
								'admin/create_head',
								'admin/edit/head/*',
								'admin/create/eli_Loan',
								'admin/account_head_ledger/transaction/*',
								'admin/head-grouping',
								'admin/head/closing-listing',
								'admin/head-closing-save',
								'admin/account_head_ledger/*'
								]))
							style="display:block" @endif>
							<!--
							@if(check_my_permission( Auth::user()->id,"44") == "1")
								<li class="nav-item"><a href="{{route('admin.accountHead')}}" class="nav-link" ><i class="icon-accessibility"></i>Account Head</a></li>
    						@endif
							-->

							@if(
								check_my_permission( Auth::user()->id,"46") == "1" ||
								check_my_permission( Auth::user()->id,"47") == "1" ||
								check_my_permission( Auth::user()->id,"48") == "1" ||
								check_my_permission( Auth::user()->id,"49") == "1" ||
								check_my_permission( Auth::user()->id,"50") == "1" ||
								check_my_permission( Auth::user()->id,"51") == "1"
								)
								<li class="nav-item nav-item-submenu {{ set_active([
									'admin/shareholder_director/*',
									'admin/shareholder_director'
									]) }}">
								<a href="" class="nav-link" >
									<i class="fas fa-handshake"></i>Share holder/Director</a>
								<ul class="nav nav-group-sub" data-submenu-title="Account Head Management" @if(set_active([
									'admin/shareholder_director/*',
									'admin/director/deposit_payment',
									'admin/director/withdrawal_payment',
									'admin/share_holder/deposit_payment',
									'admin/share-holder/transfer_share',
									'admin/shareholder_director',
									'admin/account_head_ledger/*',
									]))
								style="display:block" @endif>
									@if (check_my_permission(Auth::user()->id, '46') == '1')
                                        <li class="nav-item {{set_active(['admin/shareholder_director/1'])}}"><a href="{{ route('admin.shareholder.create') }}" class="nav-link"><i class="icon-add"></i>Share holder/Director Create</a></li>
                                    @endif
                                    @if (check_my_permission(Auth::user()->id, '47') == '1')
                                        <li class="nav-item {{set_active(['admin/shareholder_director'])}}"><a href="{{ route('admin.shareholder') }}" class="nav-link"><i class="icon-list"></i>Share holder/Director  List</a></li>
                                    @endif
                                    @if (check_my_permission(Auth::user()->id, '48') == '1')
                                        <li class="nav-item {{set_active(['admin/shareholder_director/deposit_payment'])}}"><a href="{{ route('admin.director_deposit_payment') }}" class="nav-link"><i class="icon-arrow-resize7"></i>Director Deposit</a></li>
                                    @endif
                                    @if (check_my_permission(Auth::user()->id, '49') == '1')
                                        <li class="nav-item {{set_active(['admin/shareholder_director/withdrawal_payment'])}}"><a href="{{ route('admin.director_withdrawal_payment') }}" class="nav-link"><i class="fas fa-handshake"></i>Director Withdrwal</a></li>
                                    @endif
                                    @if (check_my_permission(Auth::user()->id, '50') == '1')
                                        <li class="nav-item {{set_active(['admin/shareholder_director/director_deposit_payment'])}}"><a href="{{ route('admin.share_holder_deposit_payment') }}" class="nav-link"><i class="icon-arrow-resize7"></i>Share holder Deposit </a></li>
                                    @endif
                                    @if (check_my_permission(Auth::user()->id, '51') == '1')
                                        <li class="nav-item {{set_active(['admin/shareholder_director/transfer_share'])}}"><a href="{{ route('admin.share_holder.transfer_share') }}" class="nav-link"><i class="icon-share2"></i>Transfer Share holder </a></li>
                                    @endif
								</ul>
							</li>
							@endif
								<!-- @if(check_my_permission( Auth::user()->id,"52") == "1")
								<li class="nav-item {{ set_active(['admin/create/eli_Loan']) }}"><a href="{{route('admin.eli-loan')}}" class="nav-link" ><i class="icon-snowflake"></i>Eli Loan </a></li>
    						    @endif
 -->

								@if(check_my_permission( Auth::user()->id,"54") == "1" || check_my_permission( Auth::user()->id,"55") == "1" || check_my_permission( Auth::user()->id,"56") == "1" || check_my_permission( Auth::user()->id,"57") == "1")
    						    <li class="nav-item nav-item-submenu {{ set_active(['admin/loanFromBank','admin/loan_emi','admin/loan_from_bank/edit/*','admin/loanFromBank/*']) }}">
    						    	<a href="#" class="nav-link" ><i class="fas fa-handshake"></i>Loan From Banks </a>
    						    	<ul class="nav  nav-group-sub" data-submenu-title="Loan From Banks" @if(set_active(['admin/loanFromBank','admin/loan_emi','admin/loan_from_bank/edit/*','admin/loanFromBank/*'])) style="display:block" @endif>
										@if(check_my_permission( Auth::user()->id,"54") == "1")
    						    		<li class="nav-item {{ set_active(['admin/loanFromBank/create']) }}"><a href="{{route('admin.create.loan-from-bank')}}" class="nav-link" ><i class="icon-add"></i>Loan From Bank Create</a></li>
										@endif
										@if(check_my_permission( Auth::user()->id,"55") == "1")
										<li  class="nav-item {{ set_active(['admin/loanFromBank']) }}"><a href="{{route('admin.loan_from_bank')}}" class="nav-link" ><i class="icon-list"></i>Loan From Bank List</a></li>
    						    		@endif
										@if(check_my_permission( Auth::user()->id,"56") == "1")
										<li class="nav-item {{ set_active(['admin/loanFromBank/loan_emi']) }}"><a href="{{route('admin.loan_emi')}}" class="nav-link" ><i class="icon-share2"></i>Loan Emi Payment</a></li>
    						    		@endif
										@if(check_my_permission( Auth::user()->id,"57") == "1")
										<li  class="nav-item {{ set_active(['admin/loanFromBank/loan_emi_report']) }}"><a href="{{route('admin.loan_emi_report')}}" class="nav-link" ><i class="icon-list"></i>Loan Emi List</a></li>
										@endif
									</ul>
    						    </li>
								@endif

							<!-- 	@if(check_my_permission( Auth::user()->id,"58") == "1")
    						    <li class="nav-item"><a href="{{route('admin.fixed_asset')}}" class="nav-link" ><i class="icon-add"></i>Fixed Assets </a></li>
								@endif -->
								<!-- @if(check_my_permission( Auth::user()->id,"59") == "1")
								<li class="nav-item"><a href="{{route('admin.bank_account')}}" class="nav-link" ><i class="fa fa-exchange"></i>Bank Accounts </a></li>
    						    @endif -->
								@if (check_my_permission(Auth::user()->id, '144') == '1')
									<li class="nav-item {{ set_active(['admin/head-grouping']) }}"
										>
										<a href="{{ route('admin.head-grouping') }}" class="nav-link">
											<i class="icon-balance"></i>
											<span>
												Head Grouping
											</span>
										</a>
									</li>
								@endif
								@if (check_my_permission(Auth::user()->id, '142') == '1')
                                <li class="nav-item {{ set_active(['admin/head-logs']) }}"><a
                                        href="{{ route('admin.head_logs') }}"
                                        class="nav-link"><i class="fa fa-history" aria-hidden="true"></i>Head Logs</a>
                                </li>
                            @endif

								<!-- @if(Auth::user()->id == 16 || Auth::user()->id == 14 || Auth::user()->id == 1)

								<li class="nav-item nav-item-submenu {{ set_active(['admin/head/closing-listing','admin/head-closing-save']) }}">
									<a href="{{route('admin.head.closing_list')}}" class="nav-link"> <i class="icon-balance"></i> Head Closing Balance </a>

									<ul class="nav  nav-group-sub" data-submenu-title="Loan From Banks" @if(set_active(['admin/head/closing-listing','admin/head-closing-save'])) style="display:block" @endif>
										<li class="nav-item {{ set_active(['admin/head/closing-listing']) }}"><a href="{{route('admin.head.closing_list')}}" class="nav-link" ><i class="icon-list"></i>View Head Closing Amount</a></li>
										<li class="nav-item {{ set_active(['admin/head-closing-save']) }}"><a href="{{route('admin.head.closing_save')}}" class="nav-link" ><i class="icon-add"></i>  Add Head Closing Amount </a></li>

									</ul>

								</li>
								@endif  -->


								<!-- @if(check_my_permission( Auth::user()->id,"60") == "1")
								<li class="nav-item"><a href="{{route('admin.indirect_expense')}}" class="nav-link" ><i class="icon-add"></i>Indirect Expenses </a></li>
								@endif -->
								@if(check_my_permission( Auth::user()->id,"142") == "1" || check_my_permission( Auth::user()->id,"143") == "1"  )
                   					<li class="nav-item nav-item-submenu {{ set_active(['admin/loanFromBank','admin/loan_emi','admin/loan_from_bank/edit/*','admin/loanFromBank/*']) }}">
    						    	<a href="#" class="nav-link" ><i class="fas fa-handshake"></i>Head Create </a>
									<ul class="nav  nav-group-sub" data-submenu-title="Loan From Banks" @if(set_active(['admin/loanFromBank','admin/loan_emi','admin/loan_from_bank/edit/*','admin/loanFromBank/*','admin/create_head','admin/head','admin/edit/head/*'])) style="display:block" @endif>
    						    @if(check_my_permission( Auth::user()->id,"142") == "1"  )

    						    	<li class="nav-item {{ set_active(['admin/create_head']) }}"><a href="{{route('admin.create_head')}}" class="nav-link" ><i class="icon-add"></i>Head Create</a></li>
    						    	@endif
    						    @if(check_my_permission( Auth::user()->id,"143") == "1"  )
    						    		<li class="nav-item {{ set_active(['admin/head']) }}"><a href="{{route('admin.head')}}" class="nav-link" ><i class="icon-add"></i>Head List</a></li>
    						    	@endif
    						   </ul>
    						  @endif
							</ul>
						</li>
						@endif
						{{-- @if (check_my_permission(Auth::user()->id, '142') == '1')
                                <li class="nav-item {{ set_active(['admin/head-logs']) }}"><a
                                        href="{{ route('admin.head_logs') }}"
                                        class="nav-link"><i class="fa fa-history" aria-hidden="true"></i>Head Logs</a>
                                </li>
                            @endif --}}
						<!-- Asset updated on 09-oct-2023 from shahid -->

					@if(check_my_permission( Auth::user()->id,"62") == "1" || check_my_permission( Auth::user()->id,"63") == "1")
						<li class="nav-item nav-item-submenu {{ set_active(['admin/asset','admin/depreciation/','admin/asset/*','admin/depreciation/*']) }}" >

							<a href="#" class="nav-link"><i class="fas fa-plus"></i> <span>Assets</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="asset" @if(set_active(['admin/asset','admin/depreciation','admin/depreciation/*','admin/asset/*']))
							style="display:block" @endif>
							@if(check_my_permission( Auth::user()->id,"62") == "1")
								<li class="nav-item"><a href="{{route('admin.asset')}}" class="nav-link"><i class="fas fa-users"></i>Assets Management</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"63") == "1")
								<li class="nav-item"><a href="{{route('admin.depreciation')}}" class="nav-link"><i class="fas fa-users"></i>Depreciation Management</a></li>
							@endif
							</ul>
						</li>
					@endif
						<!--  -->
					@if(check_my_permission( Auth::user()->id,"65") == "1" || check_my_permission( Auth::user()->id,"66") == "1")
						<li class="nav-item nav-item-submenu {{ set_active(['admin/branch', 'admin/branch-create', 'admin/branch-edit/*','admin/branch/change-password/*','admin/branch/company_view/*','admin/branch-log/*','admin/branch-log','admin/branch/branch_limit_change']) }}">
							<a href="#" class="nav-link"><i class="icon-home4"></i> <span>Branch</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/branch','admin/branch-create','admin/branch-edit/*','admin/branch/change-password/*','admin/branch/company_view/*','admin/branch-log/*','admin/branch-log','admin/branch/branch_limit_change']))
							style="display:block" @endif >

							@if(check_my_permission( Auth::user()->id,"65") == "1")
								<li class="nav-item"><a href="{{route('admin.branch')}}" class="nav-link" ><i class="icon-home2"></i>Branches</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"66") == "1")
								<li class="nav-item"><a href="{{ route('branch.create') }}" class="nav-link"><i class="icon-file-plus mr-2"></i>Create Branches</a></li>
							@endif
							@if (check_my_permission(Auth::user()->id, '313') == '1')
								<li class="nav-item"><a href="admin/branch/branch_limit_change" class="nav-link"><i class="fas fa-exchange-alt mr-3"></i>Branch Limit Change</a></li>
							@endif
							@if (check_my_permission(Auth::user()->id, '314') == '1')
								<li class="nav-item"><a href="admin/branch-log" class="nav-link"><i class="fas fa-code-branch"></i>Branch Log Detail</a></li>
							@endif
							</ul>
						</li>
					@endif
					<!-- @if(Auth::user()->id!= "13")
					<li class="nav-item">
							<a href="{{route('admin.e_investment_maturity')}}" class="nav-link">
								<i class="icon-cash4"></i>
								<span>
									Reinvest Maturity
								</span>
							</a>
						</li>
					@endif -->

					@if(
						check_my_permission( Auth::user()->id,"68") == "1" ||
						check_my_permission( Auth::user()->id,"69") == "1" ||
						check_my_permission( Auth::user()->id,"70") == "1" ||
						check_my_permission( Auth::user()->id,"71") == "1" ||
						check_my_permission( Auth::user()->id,"311") == "1" ||
						check_my_permission( Auth::user()->id,"312") == "1" ||
						check_my_permission( Auth::user()->id,"342") == "1"
						)
						<li class="nav-item nav-item-submenu {{ set_active([
							'admin/member/corrections',
							'admin/associate/corrections',
							'admin/printpassbook/corrections',
							'admin/printcertificate/corrections',
							'admin/memberinvestment/corrections',
							'admin/renew/corrections',
							'admin/correction/requests'
							]) }}" >
							<a href="#" class="nav-link"><i class="icon-user"></i> <span>Correction Manangement</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="Member Manangement" @if(set_active([
								'admin/member/corrections',
								'admin/associate/corrections',
								'admin/memberinvestment/corrections',
								'admin/renew/corrections',
								'admin/printpassbook/corrections',
								'admin/printcertificate/corrections',
								'admin/memberinvestment/corrections',
								'admin/correction/requests'
								])) style="display:block" @endif>
							<!--
							@if (check_my_permission(Auth::user()->id, '67') == '1')
								<li class="nav-item {{set_active(['admin/correction/requests'])}}"><a href="{{ route('admin.correctionrequest.view') }}" class="nav-link"><i class="fas fa-users"></i>Correction Requests</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"68") == "1")
								<li class="nav-item"><a href="{{route('admin.member.correctionrequest')}}" class="nav-link"><i class="fas fa-users"></i>Member Correction Request</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"69") == "1")
								<li class="nav-item"><a href="{{route('admin.associate.correctionrequest')}}" class="nav-link"><i class="fas fa-check-circle"></i>Associate Correction Request</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"70") == "1")
								<li class="nav-item"><a href="{{route('admin.investment.correctionrequest')}}" class="nav-link"><i class="fas fa-money-bill-alt"></i>Investment Correction Request</a></li>
							@endif
							-->
							@if(check_my_permission( Auth::user()->id,"342") == "1")
								<li class="nav-item"><a href="{{route('admin.renew.correctionrequest')}}" class="nav-link"><i class="fa fa-refresh"></i>Renew Correction Request</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"311") == "1")
								<li class="nav-item"><a href="{{route('admin.printpassbook.correctionrequest')}}" class="nav-link"><i class="fa fa-refresh"></i>Print Passbook Correction Request</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"312") == "1")
								<li class="nav-item"><a href="{{route('admin.printcertificate.correctionrequest')}}" class="nav-link"><i class="fa fa-refresh"></i>Print Certificate Correction Request</a></li>
							@endif
							</ul>
						</li>
					@endif
					<!-- @if(check_my_permission( Auth::user()->id,"73") == "1")
						<li class="nav-item nav-item-submenu {{ set_active(['admin/reinvest','admin/edit-reinvestment/*','admin/reinvest/*']) }}" >
							<a href="#" class="nav-link"><i class="icon-user"></i> <span>Reinvest</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="Reinvest"    @if(set_active(['admin/reinvest','admin/edit-reinvestment/*','admin/reinvest/*']))
							style="display:block" @endif>
							@if(check_my_permission( Auth::user()->id,"73") == "1")
								<li class="nav-item"><a href="{{route('admin.reinvest')}}" class="nav-link"><i class="fas fa-users"></i>Reinvest</a></li>
							@endif
							</ul>
						</li>
					@endif -->


					 @if(check_my_permission( Auth::user()->id,"78") == "1" || check_my_permission( Auth::user()->id,"79") == "1" || check_my_permission( Auth::user()->id,"80") == "1" || check_my_permission( Auth::user()->id,"81") == "1" || check_my_permission( Auth::user()->id,"82") == "1")
						<li class="nav-item nav-item-submenu {{ set_active(['admin/rentliabilities','admin/rent/*']) }}">
							<a href="#" class="nav-link"><i class="fa fa-user"></i> <span>Rent Management</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/rentliabilities','admin/rent/*','admin/rent/payment/ledger-report']))
							style="display:block" @endif >
							@if(check_my_permission( Auth::user()->id,"78") == "1")
								<li class="nav-item"><a href="{{route('admin.rent.addliability')}}" class="nav-link" ><i class="icon-stack-plus"></i>Add Owner</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"79") == "1")
								<li class="nav-item"><a href="{{route('admin.rent.liabilities')}}" class="nav-link"><i class="fas fa-balance-scale"></i>Owner List</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"80") == "1")
								<li class="nav-item"><a href="{{route('admin.rent.ledger-create')}}" class="nav-link"><i class="icon-add"></i>Create Ledger</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"81") == "1")
								<li class="nav-item"><a href="{{route('admin.rent.rent-ledger')}}" class="nav-link"><i class="fas fa-ellipsis-v"></i>Rent Ledger List</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"82") == "1")
								<li class="nav-item"><a href="{{route('admin.rent.payment-ledger-report')}}" class="nav-link"><i class="icon-list"></i>Rent Payment List</a></li>
							@endif
								{{--<li class="nav-item"><a href="{{route('admin.rent.payable')}}" class="nav-link"><i class="icon-share2"></i>Rent Payable</a></li>
								<li class="nav-item"><a href="{{route('admin.rent.report')}}" class="nav-link"><i class="icon-list"></i>Rent Report</a></li>--}}

							</ul>
						</li>
					@endif				

					@if(check_my_permission( Auth::user()->id,"89") == "1" || check_my_permission( Auth::user()->id,"90") == "1")
						<li class="nav-item nav-item-submenu {{ set_active(['admin/emergancy-maturity','admin/emergancy-maturity/*']) }}">
							<a href="#" class="nav-link"><i class="fa fa-user"></i> <span>Emergency Maturity</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/emergancy-maturity','admin/emergancy-maturity/*'])) style="display:block" @endif >
								@if(check_my_permission( Auth::user()->id,"89") == "1")
								<li class="nav-item"><a href="{{route('admin.emergancymaturity.add')}}" class="nav-link" ><i class="icon-user-plus"></i>Add Emergency Maturity</a></li>
								@endif
								@if(check_my_permission( Auth::user()->id,"90") == "1")
								<li class="nav-item"><a href="{{route('admin.emergancymaturity.index')}}" class="nav-link"><i class="fas fa-users"></i>Emergency Maturity</a></li>
								@endif
							</ul>
						</li>
					@endif
					 @if(check_my_permission( Auth::user()->id,"92") == "1" || check_my_permission( Auth::user()->id,"334") == "1" || check_my_permission( Auth::user()->id,"335") == "1" || check_my_permission( Auth::user()->id,"93") == "1" || check_my_permission( Auth::user()->id,"336") == "1" || check_my_permission( Auth::user()->id,"337") == "1" || check_my_permission( Auth::user()->id,"338") == "1" || check_my_permission( Auth::user()->id,"339") == "1" )
						<li class="nav-item nav-item-submenu {{ set_active(['admin/permission', 'admin/viewEvent','admin/py-plans','admin/py-plan/*','admin/plan_account_management','admin/cron_management','admin/cron_management/money_back_amount_transfer_cron','admin/cron_management/monthly_income_scheme_interest_transfer_cron']) }}">
							<a href="#" class="nav-link"><i class="icon-cogs spinner"></i> <span>Setting</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active([
								'admin/permission',
								'admin/viewEvent',
								'admin/py-plans',
								'admin/py-plan/*',
								'admin/plan_account_management',
								'admin/cron_management',
								'admin/cron_management/money_back_amount_transfer_cron',
								'admin/cron_management/monthly_income_scheme_interest_transfer_cron',
								]))
							style="display:block" @endif >
							@if(check_my_permission( Auth::user()->id,"92") == "1")
								<li class="nav-item"><a href="{{route('admin.permission')}}" class="nav-link" ><i class="icon-accessibility"></i>Permission</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"93") == "1")
								<li class="nav-item"><a href="{{route('admin.viewevent')}}" class="nav-link" ><i class="fa fa-calendar"></i>Holiday Calendar</a></li>
							@endif
							@if (check_my_permission(Auth::user()->id, '352') == '1')
							<li class="nav-item {{ set_active('admin/settings/allholiday/crons') }}"><a
									href="{{ route('admin.allholiday.crons') }}" class="nav-link"><i
										class="fa fa-calendar"></i>All Holiday Cron Settings</a></li>
							@endif
							@if(check_my_permission(Auth::user()->id, "335") == "1" || check_my_permission(Auth::user()->id, "332") == "1")
								<li class="nav-item nav-item-submenu {{ set_active(['admin/loan/plan/*']) }}">
									<a href="#" class="nav-link"><i class="fa fa-money"></i> <span>Loan Setting</span></a>
									<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/loan/loansettings/*','admin/loan/loansettings'])) style="display:block" @endif>
										@if(check_my_permission(Auth::user()->id, "335") == "1")
											<li class="nav-item {{ set_active(['admin/loan/plan/*']) }}">
												<a href="{{ route('admin.loan.plan_listing') }}" class="nav-link">
													<i class="icon-list"></i> Loan Plans
												</a>
											</li>
										@endif
									</ul>
								</li>
							@endif

							@if(check_my_permission( Auth::user()->id,"334") == "1" || check_my_permission( Auth::user()->id,"333") == "1")
							<li class="nav-item nav-item-submenu {{ set_active(['admin/py-plans','admin/py-plan/*']) }}">
								<a href="#" class="nav-link"><i class=" fa fa-money"></i> <span>Plan Setting</span></a>
								<ul class="nav nav-group-sub" data-submenu-title="News Section"
								@if (set_active(['admin/py-plans','admin/py-plan/*'])) style="display:block" @endif>
								@if(check_my_permission( Auth::user()->id,"334") == "1")
									<li class="nav-item {{ set_active(['admin/py-plans','admin/py-plan/*']) }}"><a href="{{ route('admin.py.plans') }}" class="nav-link"><i class="fa fa-list"></i>Plans</a></li>
									@endif
								</ul>
							</li>
							@endif
							@if(check_my_permission( Auth::user()->id,"336") == "1" )
							<li class="nav-item nav-item-submenu {{ set_active(['admin/plan_account_management']) }}">
								<li class="nav-item {{ set_active(['admin/plan_account_management']) }}"><a href="{{ route('admin.planLog.detail') }}"
										class="nav-link"><i class="	fa fa-eye"></i>Loan/Investment Logs</a></li>
							</li>
							@endif
							@if(check_my_permission( Auth::user()->id,"337") == "1" )
							<li class="nav-item {{ set_active(['admin/cron_management']) }}" >
									<a href="{{ route('admin.cron.index') }}" class="nav-link">
										<i class="fas fa-credit-card"></i>
										<span>
											Cron Log Management
										</span>
									</a>
								</li>
							@endif
							@if (check_my_permission(Auth::user()->id, '339') == '1')
                                <li class="nav-item {{ set_active(['admin/cron_management/money_back_amount_transfer_cron']) }}" >
                                    <a href="{{ route('admin.cron.money_back_amount_transfer_cron') }}" class="nav-link">
                                        <i class="fas fa-piggy-bank "></i>
                                        <span>
                                            Money Back Cron
                                        </span>
                                    </a>
                                </li>
							@endif
							@if (check_my_permission(Auth::user()->id, '338') == '1')
                                <li class="nav-item {{ set_active(['admin/cron_management/monthly_income_scheme_interest_transfer_cron']) }}" >
                                    <a href="{{ route('admin.cron.monthly_income_scheme_interest_transfer_cron') }}" class="nav-link">
                                        <i class="fas fa-money-bill-wave-alt"></i>
                                        <span>
                                            MIS Cron
                                        </span>
                                    </a>
                                </li>
                            @endif
							</ul>
						</li>
					@endif
					<!----------------- Duties and Taxes--------------------->
						@if (check_my_permission(Auth::user()->id, '266') == '1' ||
							check_my_permission(Auth::user()->id, '267') == '1' ||
							check_my_permission(Auth::user()->id, '268') == '1' ||
							check_my_permission(Auth::user()->id, '269') == '1' ||
							check_my_permission(Auth::user()->id, '270') == '1' ||
							check_my_permission(Auth::user()->id, '271') == '1' ||
							check_my_permission(Auth::user()->id, '272') == '1' ||							
							check_my_permission(Auth::user()->id, '166') == '1' ||
							check_my_permission(Auth::user()->id, '167') == '1' ||
							check_my_permission(Auth::user()->id, '168') == '1' ||
							check_my_permission(Auth::user()->id, '212') == '1' ||
							check_my_permission(Auth::user()->id, '215') == '1'	||					
							check_my_permission(Auth::user()->id, '349') == '1'	||					
							check_my_permission(Auth::user()->id, '351') == '1'	||					
							check_my_permission(Auth::user()->id, '214') == '1'	||					
							check_my_permission(Auth::user()->id, '350') == '1' ||
							check_my_permission(Auth::user()->id, '364') == '1'
							)
						<li
							class="nav-item nav-item-submenu {{ set_active(['admin/duties_taxes/*']) }}">
							<a href="#" class="nav-link"><i class="fas fa-tasks"></i> <span>Duties and Taxes</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="News Section"
								@if (set_active(['admin/duties_taxes/*'])) style="display:block" @endif>
								@if (
									check_my_permission(Auth::user()->id, '267') == '1' ||
									check_my_permission(Auth::user()->id, '268') == '1' ||
									check_my_permission(Auth::user()->id, '270') == '1' ||
									check_my_permission(Auth::user()->id, '271') == '1' ||
									check_my_permission(Auth::user()->id, '272') == '1' ||
									check_my_permission(Auth::user()->id, '349') == '1' ||
									check_my_permission(Auth::user()->id, '166') == '1' ||
									check_my_permission(Auth::user()->id, '167') == '1' ||
									check_my_permission(Auth::user()->id, '214') == '1'
								)
								<li class="nav-item nav-item-submenu {{ set_active(['admin/duties_taxes/gst/*']) }}">
									<a href="#" class="nav-link"><i class="fas fa-hand-holding-usd"></i>
										<span>GST</span>
									</a>
									<ul class="nav nav-group-sub" data-submenu-title="GST"
										@if (set_active(['admin/duties_taxes/gst/setting/*','admin/duties_taxes/gst/report/*','admin/duties_taxes/gst/customer_transactions'])) style="display:block" @endif>
										@if (
											check_my_permission(Auth::user()->id, '267') == '1' ||
											check_my_permission(Auth::user()->id, '268') == '1' 
										)
										<li class="nav-item nav-item-submenu {{ set_active(['admin/duties_taxes/gst/setting/*']) }}">
											<a href="#" class="nav-link">
												<i class="fas fa-cog"></i><span>setting</span>
											</a>
											<ul class="nav nav-group-sub" data-submenu-title="setting"
												@if (set_active(['admin/duties_taxes/gst/setting/*'])) style="display:block" @endif>
												@if (check_my_permission(Auth::user()->id, '267') == '1')											
												<li class="nav-item {{ set_active(['admin/duties_taxes/gst/setting/company_settings']) }}">
													<a href="{{ route('admin.duties_taxes.gst.setting.company_settings') }}" class="nav-link">
														<i class="fa fa-list"></i>Company Settings 
													</a>
												</li>
												@endif
												@if (check_my_permission(Auth::user()->id, '268') == '1')
												<li class="nav-item {{ set_active(['admin/duties_taxes/gst/setting/head_settings']) }}">
													<a href="{{ route('admin.duties_taxes.gst.setting.head_settings') }}" class="nav-link">
														<i class="fas fa-tasks"></i>Head  settings  
													</a>
												</li>
												@endif
											</ul>
										</li>
										@endif
										@if (
											check_my_permission(Auth::user()->id, '270') == '1' ||
											check_my_permission(Auth::user()->id, '271') == '1' ||
											check_my_permission(Auth::user()->id, '272') == '1'
										)
										<li style="background-color: #014a01;" class="nav-item nav-item-submenu {{ set_active(['admin/duties_taxes/gst/report/*']) }}">
											<a href="#" class="nav-link">
												<i class="icon-file-xml2"></i><span>report</span>
											</a>
											<ul class="nav nav-group-sub" data-submenu-title="report"
												@if (set_active(['admin/duties_taxes/gst/report/*'])) style="display:block" @endif>		
												@if (check_my_permission(Auth::user()->id, '270') == '1')
												<li class="nav-item {{ set_active(['admin/duties_taxes/gst/report/outward_supply']) }}">
													<a href="{{ route('admin.duties_taxes.gst.report.outward_supply') }}" class="nav-link">
														<i class="fas fa-list-alt"></i>Outward Supply
													</a>
												</li>
												@endif
												@if (check_my_permission(Auth::user()->id, '271') == '1')
												<li class="nav-item {{ set_active(['admin/duties_taxes/gst/report/cr_dr_note']) }}">
													<a href="{{ route('admin.duties_taxes.gst.report.cr_dr_note') }}" class="nav-link">
														<i class="icon-cash"></i>CR DR Note
													</a>
												</li>
												@endif
												@if (check_my_permission(Auth::user()->id, '272') == '1')
												<li class="nav-item {{ set_active(['admin/duties_taxes/gst/report/summary_supply']) }}">
													<a href="{{ route('admin.duties_taxes.gst.report.summary_supply') }}" class="nav-link">
														<i class="icon-chart"></i>Summary supply
													</a>
												</li>
												@endif
												{{--
												@if (check_my_permission(Auth::user()->id, '364') == '1')
												<li class="nav-item {{ set_active(['admin/duties_taxes/gst/report/collection']) }}">
													<a href="{{ route('admin.duties_taxes.gst.report.collection') }}" class="nav-link">
														<i class="fas fa-list-alt"></i>collection List
													</a>
												</li>
												@endif
												--}}
											</ul>
										</li>
										@endif
										@if (check_my_permission(Auth::user()->id, '349') == '1')
										<li class="nav-item {{ set_active(['admin/duties_taxes/gst/customer_transactions']) }}">
											<a href="{{ route('admin.duties_taxes.gst.customer_transactions') }}" class="nav-link">
												<i class="fas fa-list-alt"></i><span>Customer transaction </span>
											</a>
										</li>
										@endif
									</ul>
								</li> 
								@endif
								@if (
									check_my_permission(Auth::user()->id, '168') == '1' ||
									check_my_permission(Auth::user()->id, '216') == '1' ||
									check_my_permission(Auth::user()->id, '350') == '1'
								)
								<li class="nav-item nav-item-submenu {{ set_active(['admin/duties_taxes/tds/*']) }}">
									<a href="#" class="nav-link"><i class="fas fa-file-invoice-dollar"></i>
										<span>TDS</span>
									</a>
									<ul class="nav nav-group-sub" data-submenu-title="TDS"
										@if (set_active(['admin/duties_taxes/tds/*'])) style="display:block" @endif>
										@if (
											check_my_permission(Auth::user()->id, '168') == '1' ||
											check_my_permission(Auth::user()->id, '216') == '1' ||
											check_my_permission(Auth::user()->id, '350') == '1'
										)								
										<li class="nav-item nav-item-submenu {{ set_active(['admin/duties_taxes/tds/setting/*']) }}">
											<a href="#" class="nav-link">
												<i class="fas fa-cog"></i><span>setting</span>
											</a>
											<ul class="nav nav-group-sub" data-submenu-title="setting"
												@if (set_active(['admin/duties_taxes/tds/setting/*'])) style="display:block" @endif>	
												@if (check_my_permission(Auth::user()->id, '168') == '1')
												<li class="nav-item {{ set_active(['admin/duties_taxes/tds/setting/tds_settings']) }}">
													<a href="{{ route('admin.duties_taxes.tds.setting.tds_settings') }}" class="nav-link">
														<i class="icon-chart"></i>TDS Setting 
													</a>
												</li>
												@endif
												@if (check_my_permission(Auth::user()->id, '350') == '1')
												<li class="nav-item {{ set_active(['admin/duties_taxes/tds/setting/customer_transactions']) }}">
													<a href="{{ route('admin.duties_taxes.tds.setting.customer_transactions') }}" class="nav-link">
														<i class="fas fa-list-alt"></i>Customer Transaction 
													</a>
												</li>
												@endif
											</ul>
										</li>
										@endif
									</ul>
								</li>
								@endif
								@if (check_my_permission(Auth::user()->id, '166') == '1')
								<li class="nav-item {{ set_active(['admin/duties_taxes/transfer']) }}">
									<a href="{{ route('admin.duties_taxes.transfer') }}" class="nav-link">
										<i class="icon-share2"></i><span>Transfer </span>
									</a>
								</li>
								@endif
								@if (check_my_permission(Auth::user()->id, '351') == '1')
								<li class="nav-item {{ set_active(['admin/duties_taxes/transfer_list']) }}">
									<a href="{{ route('admin.duties_taxes.transfer_list') }}" class="nav-link">
										<i class="fa fa-list"></i><span>Transfer List</span>
									</a>
								</li>
								@endif
								@if (check_my_permission(Auth::user()->id, '214') == '1')
								<li class="nav-item {{ set_active(['admin/duties_taxes/payable']) }}">
									<a href="{{ route('admin.duties_taxes.tds.payable') }}" class="nav-link">
										<i class="fas fa-hand-holding-usd"></i><span>Payable </span>
									</a>
								</li>
								@endif
								@if (check_my_permission(Auth::user()->id, '167') == '1')
								<li class="nav-item {{ set_active(['admin/duties_taxes/payable_list']) }}">
									<a href="{{ route('admin.duties_taxes.payable_list') }}" class="nav-link">
										<i class="fas fa-tasks"></i><span>Payable Listing </span>
									</a>
								</li>
								@endif
							</ul>
						</li>
					@endif
					@if(check_my_permission( Auth::user()->id,"95") == "1" || check_my_permission( Auth::user()->id,"96") == "1" || check_my_permission( Auth::user()->id,"97") == "1" || check_my_permission( Auth::user()->id,"98") == "1")
						<li class="nav-item nav-item-submenu {{ set_active(['admin/cheque', 'admin/cheque/add', 'admin/cheque/list', 'admin/cheque/cancel', 'admin/cheque/delete','admin/cheque/view/*']) }}">
							<a href="#" class="nav-link"><i class="icon-cash4"></i> <span>Cheque Management</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/cheque', 'admin/cheque/add', 'admin/cheque/cancel', 'admin/cheque/delete','admin/cheque/view/*']))
							style="display:block" @endif >
							@if(check_my_permission( Auth::user()->id,"95") == "1")
								<li class="nav-item"><a href="{{route('admin.cheque_add')}}" class="nav-link" ><i class="icon-add"></i>Add Cheque</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"96") == "1")
								<li class="nav-item"><a href="{{route('admin.cheque_list')}}" class="nav-link" ><i class="icon-list"></i>Cheque Lists</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"97") == "1")
								<li class="nav-item"><a href="{{route('admin.cheque_delete')}}" class="nav-link" ><i class="icon-trash-alt"></i>Cheque Delete</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"98") == "1")
								{{-- <li class="nav-item"><a href="{{route('admin.cheque_cancel')}}" class="nav-link" ><i class="icon-cancel-circle2 "></i>Cheque Cancel & Re-issue</a></li> --}}
							@endif
							</ul>
						</li>
					@endif
					@if(check_my_permission( Auth::user()->id,"100") == "1" || check_my_permission( Auth::user()->id,"101") == "1")
						<li class="nav-item nav-item-submenu {{ set_active(['admin/received/cheque', 'admin/received/cheque/add', 'admin/received/cheque/list', 'admin/received/cheque/approve/*', 'admin/received/cheque/edit/*','admin/received/cheque/view/*']) }}">
							<a href="#" class="nav-link"><i class="icon-cash4"></i> <span>Received Cheque Management</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/received/cheque', 'admin/received/cheque/add', 'admin/received/cheque/list', 'admin/received/cheque/approve/*', 'admin/received/cheque/edit/*','admin/received/cheque/view/*']))
							style="display:block" @endif >
							@if(check_my_permission( Auth::user()->id,"100") == "1")
								<li class="nav-item"><a href="{{route('admin.received.cheque_add')}}" class="nav-link" ><i class="icon-add"></i>Add Cheque</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"101") == "1")
								<li class="nav-item"><a href="{{route('admin.received.cheque_list')}}" class="nav-link" ><i class="icon-list"></i>Cheque Lists</a></li>
							@endif
							</ul>
						</li>
					@endif
					 @if(check_my_permission( Auth::user()->id,"103") == "1")
						<li class="nav-item nav-item-submenu {{ set_active(['admin/notice-board', 'admin/notice-board-create']) }}">
							<a href="#" class="nav-link"><i class="icon-cash4"></i> <span>Notice Board</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/notice-board', 'admin/notice-board-create']))
							style="display:block" @endif >

							@if(check_my_permission( Auth::user()->id,"103") == "1")
								<li class="nav-item"><a href="{{route('admin.noticeboard')}}" class="nav-link" ><i class="icon-add"></i>Notice Board</a></li>
							@endif
							</ul>
						</li>
					@endif
					@if(check_my_permission( Auth::user()->id,"308") == "1" || check_my_permission( Auth::user()->id,"309") == "1" || check_my_permission( Auth::user()->id,"310") == "1" )
					<!----------------- Advance Payment Start ---------------->
                   <li class="nav-item nav-item-submenu {{ set_active(['admin/advancePayment', 'admin/addRequest', 'admin/requestList', 'admin/paymentList','admin/advancePayment/*', 'admin/addAdjestment/*']) }}">
                        <a href="#" class="nav-link"><i class="icon-cash4"></i> <span>Advance Payment</span></a>
                        <ul class="nav nav-group-sub" data-submenu-title="News Section"
                            @if (set_active([ 'admin/advancePayment', 'admin/addRequest', 'admin/requestList', 'admin/paymentList', 'admin/advancePayment/*', 'admin/addAdjestment/*'])) style="display:block" @endif>
                           @if(check_my_permission( Auth::user()->id,"308") == "1")
								<li class="nav-item {{ set_active(['admin/addRequest']) }}"><a href="{{ route('admin.advancePayment.add_request') }}" class="nav-link"><i class="icon-add"></i>Advance Request</a></li>
							@endif
                           @if(check_my_permission( Auth::user()->id,"309") == "1")
							<li class="nav-item {{ set_active(['admin/requestList']) }}"><a href="{{ route('admin.advancePayment.requestList') }}" class="nav-link"><i class="fas fa-list"></i>Request List</a></li>
							@endif
                           @if(check_my_permission( Auth::user()->id,"310") == "1")
							<li class="nav-item {{ set_active(['admin/paymentList']) }}"><a href="{{ route('admin.advancePayment.paymentList') }}" class="nav-link"><i class="fas fa-list"></i>Payment Listing</a></li>
							@endif
                        </ul>
                    </li>
                    <!----------------- Advance Payment End  ---------------->
					@endif
					 @if(check_my_permission( Auth::user()->id,"106") == "1" || check_my_permission( Auth::user()->id,"107") == "1" || check_my_permission( Auth::user()->id,"109") == "1" || check_my_permission( Auth::user()->id,"110") == "1" || check_my_permission( Auth::user()->id,"111") == "1" || check_my_permission( Auth::user()->id,"112") == "1" || check_my_permission( Auth::user()->id,"113") == "1" || check_my_permission( Auth::user()->id,"114") == "1" || check_my_permission( Auth::user()->id,"115") == "1" || check_my_permission( Auth::user()->id,"117") == "1" || check_my_permission( Auth::user()->id,"118") == "1" || check_my_permission( Auth::user()->id,"119") == "1" || check_my_permission( Auth::user()->id,"304") == "1")
						<li class="nav-item nav-item-submenu {{ set_active(['admin/hr/designation', 'admin/hr/designation/add', 'admin/hr/designation/edit/*','admin/hr/designation/detail/*','admin/hr/employee', 'admin/hr/employee/register', 'admin/hr/employee/edit/*','admin/hr/employee/detail/*','admin/hr/employee/application','admin/hr/employee/resign_letter/*', 'admin/hr/employee/resign_request','admin/hr/employee/termination_letter/*','admin/hr/employee/transfer_letter/*','admin/hr/employee/terminate','admin/hr/employee/transfer','admin/hr/employee/transfer-request','admin/hr/employee/transfer/detail/*','admin/hr/employee/application_print/*','admin/hr/employee/application_approve/*','admin/hr/employee/application_edit/*','admin/hr/salary/payable','admin/hr/salary/transfer/*','admin/hr/salary','admin/hr/salary/salary_generate','admin/hr/salary/transfer_next','admin/hr/salary/list/*','admin/hr/salary/advice/*','admin/hr/employ/*','admin/hr/employee_status/*','admin/hr/employee_status']) }}">
							<a href="#" class="nav-link"><i class="icon-users4"></i> <span>HR Management</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/hr/designation', 'admin/hr/designation/add', 'admin/hr/designation/edit/*','admin/hr/designation/detail/*','admin/hr/employee', 'admin/hr/employee/register', 'admin/hr/employee/edit/*','admin/hr/employee/detail/*','admin/hr/employee/application','admin/hr/employee/resign_letter/*', 'admin/hr/employee/resign_request','admin/hr/employee/termination_letter/*','admin/hr/employee/transfer_letter/*','admin/hr/employee/terminate','admin/hr/employee/transfer','admin/hr/employee/transfer-request','admin/hr/employee/transfer/detail/*','admin/hr/employee/application_print/*','admin/hr/employee/application_approve/*','admin/hr/employee/application_edit/*','admin/hr/salary/payable','admin/hr/salary/transfer/*','admin/hr/salary','admin/hr/salary/salary_generate','admin/hr/salary/transfer_next','admin/hr/salary/list/*','admin/hr/salary/advice/*','admin/hr/salary/employ_leaser','admin/hr/employ/*','admin/hr/employee_status/*','admin/hr/employee_status']))
							style="display:block" @endif >
								@if(check_my_permission( Auth::user()->id,"106") == "1" || check_my_permission( Auth::user()->id,"107") == "1")
								<li class="nav-item nav-item-submenu {{ set_active(['admin/hr/designation', 'admin/hr/designation/add', 'admin/hr/designation/edit/*','admin/hr/designation/view/*']) }}">
									<a href="#" class="nav-link"><i class="icon-user-tie"></i> <span>Designation Management</span></a>
									<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/hr/designation', 'admin/hr/designation/add', 'admin/hr/designation/edit/*','admin/hr/designation/detail/*']))
									style="display:block" @endif >
										@if(check_my_permission( Auth::user()->id,"106") == "1")
										<li class="nav-item"><a href="{{route('admin.hr.designation_add')}}" class="nav-link" ><i class="icon-add"></i>Add Designation</a></li>
										@endif
										@if(check_my_permission( Auth::user()->id,"107") == "1")
										<li class="nav-item"><a href="{{route('admin.hr.designation_list')}}" class="nav-link" ><i class="icon-list"></i>Designation Lists</a></li>
										@endif
									</ul>
								</li>
								@endif
								@if(check_my_permission( Auth::user()->id,"109") == "1" || check_my_permission( Auth::user()->id,"110") == "1" || check_my_permission( Auth::user()->id,"111") == "1" || check_my_permission( Auth::user()->id,"112") == "1" || check_my_permission( Auth::user()->id,"113") == "1" || check_my_permission( Auth::user()->id,"114") == "1" || check_my_permission( Auth::user()->id,"115") == "1")
								<li class="nav-item nav-item-submenu {{ set_active(['admin/hr/employee', 'admin/hr/employee/register', 'admin/hr/employee/edit/*','admin/hr/employee/detail/*','admin/hr/employee/application','admin/hr/employee/resign_letter/*', 'admin/hr/employee/resign_request','admin/hr/employee/termination_letter/*','admin/hr/employee/transfer_letter/*','admin/hr/employee/terminate','admin/hr/employee/transfer','admin/hr/employee/transfer-request','admin/hr/employee/transfer/detail/*','admin/hr/employee/application_print/*','admin/hr/employee/application_approve/*','admin/hr/employee/application_edit/*','admin/hr/employ/*']) }}">
									<a href="#" class="nav-link"><i class="icon-users"></i> <span>Employee Management</span></a>
									<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active([
										'admin/hr/employee',
										'admin/hr/employee/register',
										'admin/hr/employee/edit/*',
										'admin/hr/employee/detail/*',
										'admin/hr/employee/application',
										'admin/hr/employee/resign_letter/*',
										'admin/hr/employee/resign_request',
										'admin/hr/employee/termination_letter/*',
										'admin/hr/employee/transfer_letter/*',
										'admin/hr/employee/terminate',
										'admin/hr/employee/transfer',
										'admin/hr/employee/transfer-request',
										'admin/hr/employee/transfer/detail/*',
										'admin/hr/employee/application_print/*',
										'admin/hr/employee/application_approve/*',
										'admin/hr/employee/application_edit/*',
										'admin/hr/employ/*'
										]))
									style="display:block" @endif >

										{{--
										@if(check_my_permission( Auth::user()->id,"109") == "1")
										<li class="nav-item"><a href="{{route('admin.hr.employee_add')}}" class="nav-link" ><i class="icon-add"></i>Register Employee</a></li>
										@endif
										--}}
										@if (check_my_permission(Auth::user()->id, '109') == '1')
										<li class="nav-item"><a href="{{ route('admin.employee_add') }}"
												class="nav-link"><i class="icon-add"></i>Employee
												Register </a>
										</li>
										@endif
										@if(check_my_permission( Auth::user()->id,"110") == "1")
										<li class="nav-item"><a href="{{route('admin.hr.employee_resign_request')}}" class="nav-link" ><i class="icon-add"></i>Resign Request</a></li>
										@endif
										@if(check_my_permission( Auth::user()->id,"111") == "1")
										<li class="nav-item"><a href="{{route('admin.hr.employee_application_list')}}" class="nav-link" ><i class="icon-list"></i>Employee Application Lists</a></li>
										@endif
										@if(check_my_permission( Auth::user()->id,"112") == "1")
										<li class="nav-item"><a href="{{route('admin.hr.employee_list')}}" class="nav-link" ><i class="fas fa-ellipsis-v"></i>Employee Lists</a></li>
										@endif
										@if(check_my_permission( Auth::user()->id,"113") == "1")
										<li class="nav-item"><a href="{{route('admin.hr.employee_terminate_request')}}" class="nav-link" ><i class="icon-add"></i>Employee Termination</a></li>
										@endif
										@if(check_my_permission( Auth::user()->id,"114") == "1")
										<li class="nav-item"><a href="{{route('admin.hr.employee_transfer_list')}}" class="nav-link" ><i class="fas fa-stream"></i>Employee Transfer Lists</a></li>
										@endif
										@if(check_my_permission( Auth::user()->id,"115") == "1")
										<li class="nav-item"><a href="{{route('admin.hr.employee_transfer_request')}}" class="nav-link" ><i class="fas fa-exchange-alt"></i>Employee Transfer</a></li>
										@endif
									</ul>
								</li>
								@endif

								@if(check_my_permission( Auth::user()->id,"116") == "1" || check_my_permission( Auth::user()->id,"117") == "1" || check_my_permission( Auth::user()->id,"118") == "1" || check_my_permission( Auth::user()->id,"119") == "1")
								<li class="nav-item nav-item-submenu {{ set_active(['admin/hr/salary/payable','admin/hr/salary/transfer/*','admin/hr/salary','admin/hr/salary/salary_generate','admin/hr/salary/transfer_next','admin/hr/salary/list/*','admin/hr/salary/advice/*','admin/hr/salary/employ_leaser']) }}">
									<a href="#" class="nav-link"><i class="icon-cash2"></i> <span>Salary Management</span></a>
									@if(check_my_permission( Auth::user()->id,"116") == "1")
										<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/hr/salary/payable','admin/hr/salary/transfer/*','admin/hr/salary','admin/hr/salary/salary_generate','admin/hr/salary/transfer_next','admin/hr/salary/list/*','admin/hr/salary/advice/*','admin/hr/salary/employ_leaser']))
										style="display:block" @endif >
										@if(check_my_permission( Auth::user()->id,"117") == "1")
											<li class="nav-item"><a href="{{route('admin.hr.salary_payable')}}" class="nav-link" ><i class="icon-add"></i>Salary Payable</a></li>
											@endif
											@if(check_my_permission( Auth::user()->id,"118") == "1")
											<li class="nav-item"><a href="{{route('admin.hr.salary_leaser')}}" class="nav-link" ><i class="icon-list"></i>Salary Ledger </a></li>
											@endif
											@if(check_my_permission( Auth::user()->id,"119") == "1")
											<li class="nav-item"><a href="{{route('admin.hr.employ_salary_leaser')}}" class="nav-link" ><i class="icon-list"></i>Salary List</a></li>
											@endif
										</ul>
									@endif
								</li>
								@endif
								@if(check_my_permission( Auth::user()->id,"304") == "1")
								<li class="nav-item {{ set_active(['admin/hr/employee_status/*','admin/hr/employee_status']) }}"><a href="{{route('admin.hr.employeestatus')}}" class="nav-link"><i class="fas fa-check"></i> Employee Status</a></li>
								@endif
							</ul>
						</li>
					@endif

	<!-------- Report Manangement Start  ----------------->
					@if(check_my_permission( Auth::user()->id,"121") == "1" || check_my_permission( Auth::user()->id,"122") == "1" || check_my_permission( Auth::user()->id,"123") == "1" || check_my_permission( Auth::user()->id,"124") == "1" || check_my_permission( Auth::user()->id,"125") == "1" || check_my_permission( Auth::user()->id,"126") == "1" || check_my_permission( Auth::user()->id,"127") == "1" || check_my_permission( Auth::user()->id,"128") == "1" || check_my_permission( Auth::user()->id,"129") == "1" || check_my_permission( Auth::user()->id,"140") == "1" || check_my_permission( Auth::user()->id,"291") == "1" || check_my_permission( Auth::user()->id,"292") == "1" || check_my_permission( Auth::user()->id,"293") == "1" || check_my_permission( Auth::user()->id,"294") == "1" || check_my_permission( Auth::user()->id,"300") == "1" || check_my_permission( Auth::user()->id,"303") == "1" || check_my_permission( Auth::user()->id,"305") == "1"  || check_my_permission( Auth::user()->id,"306") == "1" ||check_my_permission( Auth::user()->id,"348") == "1")
						<li style="background-color: #014a01;" class="nav-item nav-item-submenu {{ set_active(['admin/report', 'admin/report/daybook', 'admin/report/associate_business_report', 'admin/report/associate_business_compare', 'admin/report/associate_business_summary', 'admin/report/cash_report', 'admin/report/transaction', 'admin/report/maturity_report','admin/report/maturity','admin/report/day_book','admin/report/branch_business','admin/report/admin_business','admin/report/loan','admin/report/day_business','admin/report/day_book_duplicate','print/report/day_book_duplicate','admin/report/maturity_demand','admin/report/maturity_payment','admin/report/maturity_over_due','admin/report/maturity_upcoming','admin/report/loan_application','admin/report/loan_issued','admin/report/loan_closed','admin/report/cash_in_hand','admin/report/deposit_amount_report','admin/report/npa','admin/report/npa/*','admin/report/mother_branch_business','admin/report/day_business_report']) }}">
							<a href="#" class="nav-link"><i class="icon-file-xml2"></i> <span>Report Manangement</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/report', 'admin/report/daybook', 'admin/report/associate_business_report', 'admin/report/associate_business_compare', 'admin/report/associate_business_summary', 'admin/report/cash_report', 'admin/report/transaction', 'admin/report/maturity_report','admin/report/maturity','admin/report/day_book','admin/report/branch_business','admin/report/admin_business','admin/report/loan','admin/report/day_business','admin/report/day_book_duplicate','print/report/day_book_duplicate','admin/report/maturity_demand','admin/report/maturity_payment','admin/report/maturity_over_due','admin/report/maturity_upcoming','admin/report/loan_application','admin/report/loan_issued','admin/report/loan_closed','admin/report/cash_in_hand','admin/report/deposit_amount_report','admin/report/npa','admin/report/npa/*','admin/report/mother_branch_business','admin/report/day_business_report']))
							style="display:block" @endif >
							@if(check_my_permission( Auth::user()->id,"121") == "1")
								<li class="nav-item"><a href="{{route('admin.common.associate_busniss_report')}}" class="nav-link" ><i class="icon-chart"></i>Associate Business Report</a></li>
							@endif
							{{--@if(check_my_permission( Auth::user()->id,"122") == "1")
								<li class="nav-item"><a href="{{route('admin.report.associate_business_summary_report')}}" class="nav-link" ><i class="fas fa-list"></i>Associate Business Summary Report</a></li>
							@endif--}}
							@if(check_my_permission( Auth::user()->id,"123") == "1")
								<li class="nav-item"><a href="{{route('admin.common.associate_busniss_compare')}}" class="nav-link" ><i class="fas fa-compress-arrows-alt"></i>Associate Business Compare Report</a></li>
							@endif
							{{--@if(check_my_permission( Auth::user()->id,"124") == "1")
								<li class="nav-item  {{ set_active([ 'admin/report/transaction' ]) }}"><a href="{{route('admin.report.transaction')}}" class="nav-link" ><i class="icon-cash4"></i>Transactions Detail</a></li>
							@endif --}}
							 @if(check_my_permission( Auth::user()->id,"125") == "1")
								<li class="nav-item  {{ set_active([ 'admin/report/transaction' ]) }}" ><a href="{{route('admin.report.loan')}}" class="nav-link" ><i class="fas fa-hand-holding-usd"></i>Loan Report</a></li>
							@endif

							<!-- loan report new start -->



							 @if(check_my_permission( Auth::user()->id,"286") == "1" && check_my_permission( Auth::user()->id,"287") == "1" || check_my_permission( Auth::user()->id,"288") == "1" || check_my_permission( Auth::user()->id,"289") == "1")
							<li class="nav-item nav-item-submenu {{ set_active(['admin/report/loan_application','admin/report/loan_issued','admin/report/loan_closed']) }}" >
								<a href="#" class="nav-link"><i class="icon-user-tie"></i> <span>Loan Reports</span></a>
								<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/report/loan_application','admin/report/loan_issued','admin/report/loan_closed']))
								style="display:block" @endif >
								 @if(check_my_permission( Auth::user()->id,"287") == "1")
									<li class="nav-item"><a href="{{route('admin.report.loanapplication')}}" class="nav-link" ><i class="icon-add"></i>Loan Applications</a></li>
								 @endif
								  @if(check_my_permission( Auth::user()->id,"288") == "1")
									<li class="nav-item"><a href="{{route('admin.report.loanissue')}}" class="nav-link" ><i class="icon-list"></i>Loan Issued</a></li>
								 @endif
								 @if(check_my_permission( Auth::user()->id,"289") == "1")
								   <li class="nav-item"><a href="{{route('admin.report.loanclosed')}}" class="nav-link" ><i class="icon-list"></i>Loan Closed</a></li>
								  @endif
								</ul>

							</li>
							@endif

							<!-- loan report new end -->

							<!-- maturity report new start -->
							@if(check_my_permission( Auth::user()->id,"290") == "1")

							<li class="nav-item nav-item-submenu {{ set_active(['admin/report/maturity_demand','admin/report/maturity_payment','admin/report/maturity_over_due','admin/report/maturity_upcoming']) }}">
							<a href="#" class="nav-link"><i class="icon-user-tie"></i> <span>Maturity Reports</span></a>
							   <ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/report/maturity_demand','admin/report/maturity_payment','admin/report/maturity_over_due','admin/report/maturity_upcoming']))
							   style="display:block" @endif >
									@if(check_my_permission( Auth::user()->id,"291") == "1")
									<li class="nav-item"><a href="{{route('admin.report.maturity_report_demand')}}" class="nav-link" ><i class="icon-add"></i>Maturity Demand</a></li>
									 @endif
									@if(check_my_permission( Auth::user()->id,"292") == "1")
								   <li class="nav-item"><a href="{{route('admin.report.maturity_report_payment')}}" class="nav-link" ><i class="icon-list"></i>Maturity Payment</a></li>
									@endif
								  @if(check_my_permission( Auth::user()->id,"293") == "1")
								  <li class="nav-item"><a href="{{route('admin.report.maturity_report_overdue')}}" class="nav-link" ><i class="icon-list"></i>Maturity Over Due </a></li>
								 @endif
								  @if(check_my_permission( Auth::user()->id,"294") == "1")
								  <li class="nav-item"><a href="{{route('admin.report.maturity_reportupcoming')}}" class="nav-link" ><i class="icon-list"></i>Maturity Upcoming </a></li>
								  @endif
							   </ul>
						   </li>
						   @endif

							<!-- maturity report new end -->

							@if(check_my_permission( Auth::user()->id,"126") == "1")
								<li class="nav-item  {{ set_active([ 'admin/report/maturity' ]) }}"><a href="{{route('admin.report.maturity')}}" class="nav-link" ><i class="fas fa-list-alt"></i>Maturity Report</a></li>
							@endif
							<!-- @if(check_my_permission( Auth::user()->id,"127") == "1")
								<li class="nav-item  {{ set_active([ 'admin/report/day_business' ]) }}"><a href="{{route('admin.report.day_business')}}" class="nav-link" ><i class="fas fa-business-time"></i>Daily Business Report</a></li>
							@endif -->
							@if (check_my_permission(Auth::user()->id, '348') == '1')
                                    <li class="nav-item  {{ set_active(['admin/report/day_business_report']) }}"><a
                                            href="{{ route('admin.bussiness.report') }}" class="nav-link"><i
                                                class="fas fa-business-time"></i>Day Business Report</a></li>
                                @endif
							<!-- @if(check_my_permission( Auth::user()->id,"128") == "1")
								<li class="nav-item  {{ set_active([ 'admin/report/day-book' ]) }}"><a href="{{route('admin.report.day_book')}}" class="nav-link" ><i class="icon-snowflake"></i>Day Book Report</a></li>
							@endif -->
							@if(check_my_permission( Auth::user()->id,"129") == "1")
								<li class="nav-item  {{ set_active([ 'admin/report/branch-business' ]) }}"><a href="{{route('admin.report.branch_business')}}" class="nav-link" ><i class="fas fa-code-branch"></i>Branch Business  Report</a></li>
							@endif
							<!-- mahesh -->
							@if(check_my_permission( Auth::user()->id,"306") == "1")
							<li class="nav-item  {{ set_active(['admin/report/mother_branch_business']) }}"><a href="{{ route('admin.report.mother_branch_business') }}" class="nav-link"><i class="fas fa-code-branch"></i>Mother Branch Business Report</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"141") == "1")
								<li class="nav-item  {{ set_active([ 'admin/report/day_book_duplicate','print/report/day_book_duplicate' ]) }}"><a href="{{route('admin.report.day_book_dublicate')}}" class="nav-link" ><i class="icon-snowflake"></i>Duplicate Day Book Report</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"300") == "1")
								<li class="nav-item  {{ set_active([ 'admin/report/cash_in_hand','print/report/cash_in_hand' ]) }}"><a href="{{route('admin.report.cashinhand')}}" class="nav-link" ><i class="icon-cash"></i>Branch Cash In Hand Report</a></li>
							@endif
							 @if(check_my_permission( Auth::user()->id,"303") == "1")
								<li class="nav-item  {{ set_active([ 'admin/report/deposit_amount_report']) }}"><a href="{{route('admin.report.deposit_amount_report')}}" class="nav-link" ><i class="fas fa-list-alt"></i>Deposit Amount Report</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"305") == "1")
								<li class="nav-item  {{ set_active([ 'admin/report/npa']) }}"><a href="{{route('admin.report.npa')}}" class="nav-link" ><i class="fas fa-list-alt"></i>Loan - NPA</a></li>
							@endif
							</ul>
						</li>
					@endif
<!-------- Report Manangement End  ----------------->
					@if(check_my_permission( Auth::user()->id,"173") == "1" || check_my_permission( Auth::user()->id,"201") == "1" || check_my_permission( Auth::user()->id,"202") == "1")
						<li class="nav-item nav-item-submenu {{ set_active([
							'admin/view-ledger-listing',
							'admin/view-ledger-records'
							]) }}">
							<a href="#" class="nav-link"><i class="fas fa-money-bill-alt"></i></i> <span>Ledger listing</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active([
								'admin/view-ledger-listing',
								'admin/view-ledger-records'
								])) style="display:block" @endif >
								@if(check_my_permission( Auth::user()->id,"201") == "1" )
									<li class="nav-item {{ set_active(['admin/view-ledger-listing']) }}"><a href="{{route('admin.view-ledger-listing')}}" class="nav-link" ><i class="icon-list"></i>Head Ledger Report</a></li>
								@endif
								@if(check_my_permission( Auth::user()->id,"202") == "1" )
									<li class="nav-item {{ set_active(['admin/view-ledger-records']) }}"><a href="{{route('admin.view-ledger-records')}}" class="nav-link" ><i class="icon-list"></i>Ledger Report</a></li>
								@endif

							</ul>
						</li>
					@endif

						<!-- @if(check_my_permission( Auth::user()->id,"245") == "1" || check_my_permission( Auth::user()->id,"246") == "1" || check_my_permission( Auth::user()->id,"247") == "1" || check_my_permission( Auth::user()->id,"248") == "1" )
							<li class="nav-item nav-item-submenu {{ set_active(['admin/debit-card', 'admin/debit-card/create', 'admin/debit-card/card-history', 'admin/debit-card/edit/*', 'admin/debit-card/card-history/*', 'admin/debit-card/ssb-history/*']) }}">
								<a href="#" class="nav-link">
									<i class="fas fa-credit-card"></i> <span>Debit Card Management</span>
								</a>

								<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/debit-card', 'admin/debit-card/create', 'admin/debit-card/card-history', 'admin/debit-card/edit/*', 'admin/debit-card/card-history/*', 'admin/debit-card/ssb-history/*'])) style="display:block" @endif >
									@if(check_my_permission( Auth::user()->id,"246") == "1" )
										<li class="nav-item {{ set_active(['admin/debit-card']) }}">
											<a href="{{route('admin.debit-card')}}" class="nav-link" >
												<i class="icon-list"></i>Debit Card Listing
											</a>
										</li>
									@endif
									@if(check_my_permission( Auth::user()->id,"247") == "1" )
										<li class="nav-item {{ set_active(['admin/debit-card/create']) }}">
											<a href="{{route('admin.debit-card.create')}}" class="nav-link" >
												<i class="icon-list"></i>Create Debit Card
											</a>
										</li>
									@endif
									@if(check_my_permission( Auth::user()->id,"248") == "1" )
										<li class="nav-item {{ set_active(['admin/debit-card/card-history']) }}">
											<a href="{{route('admin.debit-card.card_history')}}" class="nav-link" >
												<i class="icon-list"></i>Card Payment History
											</a>
										</li>
									@endif
								</ul>
							</li>
							{{--<li class="nav-item ">
								<a href="{{route('admin.debit-card')}}" class="nav-link">
									<i class="fas fa-credit-card"></i><span>Debit Card Management</span>
								</a>
							</li>--}}
						@endif -->
						<!-- @if(check_my_permission( Auth::user()->id,"36") == "1")
						<li class="nav-item">
							<a href="{{route('admin.activityLogs')}}" class="nav-link">
								<i class="fas fa-chart-line"></i>
								<span>Activity Logs</span>
							</a>
						</li>
					@endif
					</ul> -->
<!--------------------Voucher  Start------------------------------>
					@if(check_my_permission( Auth::user()->id,"131") == "1" || check_my_permission( Auth::user()->id,"132") == "1")
						<li class="nav-item nav-item-submenu {{ set_active(['admin/rentliabilities','admin/rent/*']) }}">
							<a href="#" class="nav-link"><i class="fa fa-tag fa-lg"></i> <span>Receive Voucher (INFLOW)</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/voucher','admin/voucher/*']))
							style="display:block" @endif >
							@if(check_my_permission( Auth::user()->id,"131") == "1")
								<li class="nav-item"><a href="{{route('admin.voucher.create')}}" class="nav-link" ><i class="icon-stack-plus"></i>Voucher Request</a></li>
							@endif
							@if(check_my_permission( Auth::user()->id,"132") == "1")
								<li class="nav-item"><a href="{{route('admin.voucher')}}" class="nav-link"><i class="icon-list"></i>Voucher List</a></li>
							@endif
							</ul>
						</li>
					@endif
	<!--------------------Voucher  End------------------------------>

	<!--------------------Associate App  start------------------------------>
				@if(check_my_permission( Auth::user()->id,"149") == "1" || check_my_permission( Auth::user()->id,"150") == "1" || check_my_permission( Auth::user()->id,"151") == "1" || check_my_permission( Auth::user()->id,"152") == "1")
					<li class="nav-item nav-item-submenu {{ set_active(['admin/associate-app-status','admin/associate-app-transaction','admin/associate-app-permission']) }}">
						<a href="#" class="nav-link"><i class="icon-mobile2"></i> <span>Associate App Manangement</span></a>
						<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/associate-app-status','admin/associate-app-transaction','admin/associate-app-permission'])) style="display:block" @endif >
								@if(check_my_permission( Auth::user()->id,"150") == "1")
								<li class="nav-item"><a href="{{route('admin.associate.status_app')}}" class="nav-link" ><i class="fas fa-ban"></i>Associate App Status</a></li>
								@endif
								@if(check_my_permission( Auth::user()->id,"151") == "1")
								<!--<li class="nav-item"><a href="{{route('admin.associate.app_transaction')}}" class="nav-link"><i class="icon-list"></i>Associate  Transaction Detail</a></li> -->
								@endif
									@if(check_my_permission( Auth::user()->id,"152") == "1")
								<!--<li class="nav-item"><a href="{{route('admin.associate.app_permission')}}" class="nav-link"><i class="icon-accessibility"></i>Associate Permission </a></li>-->
								@endif
							</ul>
						</li>

						@endif
<!--------------------Associate App   End------------------------------>
    <!--------------------Vendor  start ------------------------------>
	@if (Auth::user()->id == 1 || Auth::user()->id == 14 || Auth::user()->id == 16  || Auth::user()->id == 23 ||  Auth::user()->id == 6 ||  Auth::user()->id == 7)
    @if(check_my_permission( Auth::user()->id,"159") == "1" || check_my_permission( Auth::user()->id,"160") == "1" || check_my_permission( Auth::user()->id,"161") == "1" || check_my_permission( Auth::user()->id,"162") == "1" || check_my_permission( Auth::user()->id,"163") == "1" || check_my_permission( Auth::user()->id,"164") == "1" || check_my_permission( Auth::user()->id,"165") == "1")
                     <li class="nav-item nav-item-submenu {{ set_active(['admin/vendor/category','admin/vendor/category/add','admin/vendor/category/edit/*','admin/vendor','admin/vendor/add','admin/vendor/edit/*','admin/vendor/detail/*','admin/bill/create/*','admin/bill/payment/*','admin/bill_listing','admin/bill/create','admin/bill/edit/*','admin/vendor-credit/create/*']) }}">
                        <a href="#" class="nav-link"><i class="icon-users4"></i> <span>Vendor Management</span></a>
                            <ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/vendor/category','admin/vendor/category/add','admin/vendor/category/edit/*','admin/vendor','admin/vendor/add','admin/vendor/edit/*','admin/vendor/detail/*','admin/bill/create/*','admin/bill/payment/*','admin/bill_listing','admin/bill/create','admin/bill/edit/*','admin/vendor-credit/create/*']))  style="display:block" @endif >
                            	@if(check_my_permission( Auth::user()->id,"160") == "1" || check_my_permission( Auth::user()->id,"161") == "1" || check_my_permission( Auth::user()->id,"162") == "1" )
                                <li class="nav-item nav-item-submenu {{ set_active(['admin/vendor/category','admin/vendor/category/*']) }}">
                                    <a href="#" class="nav-link"><i class="icon-user-tie"></i> <span>Vendor Category</span></a>
                                    <ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/vendor/category','admin/vendor/category/*']))
                                    style="display:block" @endif >
                                   @if( check_my_permission( Auth::user()->id,"161") == "1")
                                        <li class="nav-item"><a href="{{route('admin.vendor.category.add')}}" class="nav-link" ><i class="icon-add"></i>Add Category</a></li>
                                   @endif
                                  @if( check_my_permission( Auth::user()->id,"162") == "1")
                                        <li class="nav-item"><a href="{{route('admin.vendor.category')}}" class="nav-link" ><i class="icon-list"></i>Category Lists</a></li>
                                  @endif
                                    </ul>
                                </li>
                                @endif
                                @if(check_my_permission( Auth::user()->id,"163") == "1" || check_my_permission( Auth::user()->id,"164") == "1" || check_my_permission( Auth::user()->id,"165") == "1" )
                                <li class="nav-item nav-item-submenu {{ set_active(['admin/vendor','admin/vendor/add','admin/vendor/edit/*','admin/vendor/detail/*','admin/bill/payment/*']) }}">
                                    <a href="#" class="nav-link"><i class="icon-user-tie"></i> <span>Vendor</span></a>
                                    <ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/vendor','admin/vendor/add','admin/vendor/edit/*','admin/vendor/detail/*','admin/bill/create/*','admin/bill/payment/*']))
                                    style="display:block" @endif >
                                     @if( check_my_permission( Auth::user()->id,"164") == "1")
                                        <li class="nav-item"><a href="{{route('admin.vendor.add')}}" class="nav-link" ><i class="icon-add"></i>Create Vendor</a></li>
                                      @endif
                                     @if( check_my_permission( Auth::user()->id,"165") == "1")  				    <li class="nav-item"><a href="{{route('admin.vendor')}}" class="nav-link" ><i class="icon-list"></i>Vendors List</a></li>
                                     @endif
                                    </ul>
                                </li>
                                @endif
								@if(check_my_permission( Auth::user()->id,"153") == "1" || check_my_permission( Auth::user()->id,"154") == "1")
									<li class="nav-item nav-item-submenu {{ set_active(['admin/bill_listing','admin/bill/edit/*','admin/bill/create','admin/bill/edit/*','admin/vendor-credit/create/*']) }}">
										<a href="#" class="nav-link"><i class="fa fa-tag fa-lg"></i> <span>Bill Management</span></a>
										<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/bill_listing','admin/bill/create','admin/bill/edit/*','admin/vendor-credit/create/*']))
										style="display:block" @endif >
										@if(check_my_permission( Auth::user()->id,"154") == "1")
											<li class="nav-item"><a href="{{route('admin.bill_management.bill')}}" class="nav-link" ><i class="icon-list"></i>Bill Listing</a></li>
											<li class="nav-item"><a href="{{route('admin.bill.create')}}" class="nav-link" ><i class="icon-add"></i>Bill Create </a></li>
										@endif
										</ul>
									</li>
								@endif
                            </ul>
                        </li>

     @endif
	 @endif
    <!--------------------Vendor  End------------------------------>

    <!--------------------TDS  start ------------------------------>
	<!--
    				@if(
						check_my_permission( Auth::user()->id,"166") == "1" ||
						check_my_permission( Auth::user()->id,"167") == "1" ||
						check_my_permission( Auth::user()->id,"168") == "1" ||
						check_my_permission( Auth::user()->id,"212") == "1" ||
						check_my_permission( Auth::user()->id,"215") == "1"
						)
                    <li class="nav-item nav-item-submenu {{ set_active(['admin/tds-payable','admin/add-tds-payable','admin/tds_deposit','admin/tds_deposit/create']) }}">
                        <a href="#" class="nav-link"><i class="fas fa-file-invoice-dollar"></i> <span>TDS Management</span></a>
                            <ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active([
								'admin/tds-payable',
								'admin/add-tds-payable',
								'admin/tds_deposit',
								'admin/tds_deposit/create',
								'admin/tds_deposit'
								]))  style="display:block" @endif >
                    @if (check_my_permission(Auth::user()->id, '212') == '1')
						 <li
							 class="nav-item {{ set_active(['admin/tds-payable']) }}">
							 <a href="{{ route('admin.tds-payable') }}" class="nav-link">
								 <i class="fas fa-tasks"></i>
								 <span>
									 Duties and Taxes List
								 </span>
							 </a>
						 </li>
					 @endif
					 @if (check_my_permission(Auth::user()->id, '212') == '1')
						 <li class="nav-item {{ set_active(['admin/add-tds-payable', 'admin/add-tds-payable/*,admin/tds_transfer_pay','admin/tds_transfer_pay/*']) }}">
							 <a href="{{ route('admin.add-tds-payable') }}" class="nav-link">
								 <i class="fas fa-hand-holding-usd"></i>
								 <span>
									 TDS Transfer & Payable
								 </span>
							 </a>
						 </li>
					@endif
						@if( check_my_permission( Auth::user()->id,"215") == "1"  )
								<li  class="nav-item {{ set_active(['admin/tds_deposit', 'admin/tds_deposit/create']) }}">
									<a href="{{route('admin.tds_deposit')}}" class="nav-link" >
										<i class="icon-share2"></i>TDS Deduction Setting
									</a>
								</li>
						@endif

                            </ul>
                        </li>
          @endif
							-->
    <!--------------------TDS  End------------------------------>

    <!---------------------------- Expense ------------------------->
						 @if(check_my_permission( Auth::user()->id,"169") == "1" || check_my_permission( Auth::user()->id,"170") == "1" || check_my_permission( Auth::user()->id,"171") == "1" )
							<li class="nav-item nav-item-submenu {{ set_active(['admin/expense', 'admin/report/expense']) }}" >
							<a href="#" class="nav-link"><i class="fas fa-money-bill-alt"></i></i> <span class="exp">Expense Booking Manangement</span></a>

							<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/expense', 'admin/report/expense','admin/report/bill_expense','admin/report/expense/*','admin/expense/edit/*']))
							style="display:block" @endif >
							 @if(check_my_permission( Auth::user()->id,"170") == "1" )
								<li class="nav-item {{ set_active(['admin/expense']) }}"><a href="{{route('admin.expense')}}" class="nav-link" ><i class="icon-add"></i>Add Expense Booking</a></li>
								@endif
							 @if( check_my_permission( Auth::user()->id,"171") == "1" )
								<li class="nav-item {{ set_active(['admin/report/expense']) }}"><a href="{{route('admin.expense.expense_bill')}}" class="nav-link" ><i class="icon-list"></i>Expense Booking Report</a></li>
							@endif
							</ul>
						</li>
						@endif
		<!-----------------------End Expense ------------------------------>

	<!-- JV Management Start-->
					@if(check_my_permission( Auth::user()->id,"149") == "1" || check_my_permission( Auth::user()->id,"150") == "1"  )
						<li class="nav-item nav-item-submenu {{ set_active(['admin/jv', 'admin/jv/create']) }}">
							<a href="#" class="nav-link"><i class="fas fa-money-bill-alt"></i></i> <span>Journal Voucher Management</span></a>

							<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/jv', 'admin/jv/create','admin/jv/edit/*']))
							style="display:block" @endif >
						@if(check_my_permission( Auth::user()->id,"150") == "1"  )
						<li class="nav-item nav-item-submenu {{ set_active(['admin/jv', 'admin/jv/create']) }}">
								<li class="nav-item {{ set_active(['admin/jv']) }}"><a href="{{route('admin.jv.list')}}" class="nav-link" ><i class="icon-list"></i>Journal Voucher List</a></li>
						@endif
							@if(check_my_permission( Auth::user()->id,"149") == "1"  )
								<li class="nav-item {{ set_active(['admin/jv/create']) }}"><a href="{{route('admin.jv.create')}}" class="nav-link" ><i class="icon-add"></i>Create New Journal Voucher</a></li>
							@endif
							</ul>
						</li>
					@endif
						 <!-- @if(check_my_permission( Auth::user()->id,"174") == "1" || check_my_permission( Auth::user()->id,"175") == "1" || check_my_permission( Auth::user()->id,"182") == "1" || check_my_permission( Auth::user()->id,"183") == "1" || check_my_permission( Auth::user()->id,"184") == "1" || check_my_permission( Auth::user()->id,"185") == "1" || check_my_permission( Auth::user()->id,"186") == "1" || check_my_permission( Auth::user()->id,"187") == "1" || check_my_permission( Auth::user()->id,"188") == "1" || check_my_permission( Auth::user()->id,"189") == "1" || check_my_permission( Auth::user()->id,"190") == "1" || check_my_permission( Auth::user()->id,"191") == "1" || check_my_permission( Auth::user()->id,"192") == "1")
						<li class="nav-item nav-item-submenu {{ set_active(['admin/banking', 'admin/banking/create']) }}">
							<a href="#" class="nav-link"><i class="fas fa-money-bill-alt"></i></i> <span>Banking Management</span></a>

							<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/banking', 'admin/banking/create']))
							style="display:block" @endif >
								@if(check_my_permission( Auth::user()->id,"175") == "1"  )
									<li class="nav-item {{ set_active(['admin/banking']) }}"><a href="{{route('admin.banking.index')}}" class="nav-link" ><i class="icon-list"></i>List</a></li>
								@endif
							</ul>
						</li>
						@endif -->

						<!-- @if(check_my_permission( Auth::user()->id,"166") == "1" || check_my_permission( Auth::user()->id,"177") == "1" || check_my_permission( Auth::user()->id,"178") == "1" || check_my_permission( Auth::user()->id,"179") == "1" || check_my_permission( Auth::user()->id,"180") == "1" || check_my_permission( Auth::user()->id,"181") == "1")
						<li class="nav-item ">
							<a href="{{route('admin.credit-card')}}" class="nav-link">
								<i class="fas fa-credit-card"></i>
								<span>
									Credit Card Management
								</span>
							</a>
						</li>
						@endif -->
						<!-- @if(check_my_permission( Auth::user()->id,"203") == "1" || check_my_permission( Auth::user()->id,"204") == "1" || check_my_permission( Auth::user()->id,"206") == "1" || check_my_permission( Auth::user()->id,"207") == "1")
						<li class="nav-item nav-item-submenu {{ set_active(['admin/payment_list', 'admin/edit/payment_bill']) }}">
							<a href="#" class="nav-link"><i class="fas fa-money-bill-alt"></i></i> <span>Payment History Management</span></a>

							<ul class="nav nav-group-sub" data-submenu-title="News Section" @if(set_active(['admin/payment_list', 'admin/edit/payment_bill']))
							style="display:block" @endif >
								@if(check_my_permission( Auth::user()->id,"204") == "1" )
									<li class="nav-item {{ set_active(['admin/payment_list']) }}"><a href="{{route('admin.payment.list')}}" class="nav-link" ><i class="icon-list"></i>List</a></li>
								@endif
							</ul>
						</li>
						@endif -->


					<!-- @if(check_my_permission( Auth::user()->id,"166") == "1" || check_my_permission( Auth::user()->id,"177") == "1" || check_my_permission( Auth::user()->id,"178") == "1" || check_my_permission( Auth::user()->id,"179") == "1" || check_my_permission( Auth::user()->id,"180") == "1" || check_my_permission( Auth::user()->id,"181") == "1")
						<li class="nav-item ">
							<a href="{{route('admin.credit-card')}}" class="nav-link">
								<i class="fas fa-credit-card"></i>
								<span>
									Credit Card Management
								</span>
							</a>
						</li>
						@endif -->
						<!--<li class="nav-item ">
							<a href="{{route('admin.view-ledger-listing')}}" class="nav-link">
								<i class="icon-balance"></i>
								<span>
									Ledger listing
								</span>
							</a>
						</li>
						-->

						<!--<li class="nav-item nav-item-submenu {{ set_active(['admin/banking', 'admin/add-banking/*']) }}">
							<a href="#" class="nav-link"><i class="icon-user"></i> <span>Bnaking</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="Member Manangement"    @if(set_active(['admin/banking', 'admin/add-banking/*'])) style="display:block" @endif>

								<li class="nav-item"><a href="{{route('admin.member.register')}}" class="nav-link"><i class="icon-user-plus"></i> Listing</a></li>

								<li class="nav-item"><a href="{{route('admin.add-banking')}}" class="nav-link"><i class="fas fa-users"></i> Add Banking</a></li>

							</ul>
						</li>-->

	<!-- JV Management End -->
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
							{{-- <a href="{{ redirect()->back()->getTargetUrl() }}" style="float:right" class="btn bg-dark legitRipple {{ Request::segment(2) }}">Back</a> --}}
					@if (strpos($title, 'Trial Balance') === false)
							<button onclick="goBack()" style="float:right" class="btn bg-dark legitRipple {{ Request::segment(2) }}">Back</button>
					@endif
						</div>
					@endif
				</div>
			</div>
@yield('content')
<!-- footer begin -->
<script type="text/javascript">
	function goBack() {
        if (window.history.length > 1) {
            // If the browser history is available, go back using JavaScript
            window.history.back();
        } else {
            // If the browser history is not available, redirect using PHP
            <?php
            if (isset($_SERVER['HTTP_REFERER'])) {
                echo 'window.location.href = "' . $_SERVER['HTTP_REFERER'] . '";';
            }
            ?>
        }
    }
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
<style>
	.exp{
		color: red;
	}
	.swal-footer {
		text-align: center !important;
	}
</style>
@include('sweetalert::alert')
@yield('script')

<script>

</script>
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
	$('.loader').fadeOut();
	let branchid = $( "#hbranchid option:selected" ).val();
	$('.commission_process').html('');
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
				console.log(response.globalDate);
        		$('.gdate').html(response.globalDate);
        		$('.renew_date').html(response.globalDate);
        		$('.gdatetime').html(response.globalDateTime);

				if(response.commissionProcess==1)
                    {
                        $('.commission_process').html('<span style="color:red"> The commission process is Pending. Please initiate the Commission <br>Month End process by clicking on the  <a href="{{ route('admin.associatecommision.monthList') }}">Commission Month END</a></span>');


                    }
                    if(response.commissionProcess==2){
                            $('.commission_process').html('<span style="color:red">The commission process is in progress</a></span>');
                    }
					/** ankerTag variable created and modify by sourab on 03-10-2023 for adding a condication that if user have a permission only when can redirect to associate commission transfer */
                    var ankerTag = `@if(check_my_permission(Auth::user()->id, '14') == '1')  <a href="{{ route('admin.associate.commission.commissionTransfer') }}" >Create Ledger</a> @endif`;
                    if(response.commissionProcess==3){
                            $('.commission_process').html('<span style="color:red">The commission process has been finalized. To generate<br> the commission ledger, clicking on the '+ankerTag+'</span>');
                    }


        		$('.create_application_date').val(response.globalDate);
        		$('.withdrawal_date').val(response.globalDate);
  				$('.created_at').val(response.globalDateTime);
  				$('.fundtransferdate').val(response.globalDate);
        	}else{
        		swal('Warning!',''+response.view+'','warning');
        	}
        }
    });
});

$.validator.addMethod("checkIfsc",function(value,element,p){
    if(this.optional(element) || /^[A-Z]{4}0[A-Z0-9]{6}$/.test(value)== true)
    {
        $.validator.messages.checkIfsc = "";
        result = true;
    }else{
        $.validator.messages.checkIfsc = "Please enter valid ifsc.";
        result = false;
      }
    return result;
},"");

$(document).on('change', '#hbranchid', function(){
	let branchid = $( "#hbranchid option:selected" ).val();
	$('.commission_process').html('');
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
        		$('.renew_date').html(response.globalDate);
        		$('.gdatetime').html(response.globalDateTime);

				if(response.commissionProcess==1)
                    {
                        $('.commission_process').html('<span style="color:red"> The commission process is Pending. Please initiate the Commission <br>Month End process by clicking on the  <a href="{{ route('admin.associatecommision.monthList') }}">Commission Month END</a></span>');


                    }
                    if(response.commissionProcess==2){
                            $('.commission_process').html('<span style="color:red">The commission process is in progress</a></span>');
                    }
					/** ankerTag variable created and modify by sourab on 03-10-2023 for adding a condication that if user have a permission only when can redirect to associate commission transfer */
                    var ankerTag = `@if(check_my_permission(Auth::user()->id, '14') == '1')  <a href="{{ route('admin.associate.commission.commissionTransfer') }}" >Create Ledger</a> @endif`;
                    if(response.commissionProcess==3){
                            $('.commission_process').html('<span style="color:red">The commission process has been finalized. To generate<br> the commission ledger, clicking on the '+ankerTag+'</span>');
                    }

        		$('.create_application_date').val(response.globalDate);
        		$('.withdrawal_date').val(response.globalDate);
  				$('.created_at').val(response.globalDateTime);
  				$('.fundtransferdate').val(response.globalDate);
        	}else{
        		swal('Warning!',''+response.view+'','warning');
        	}
        }
    });
});

	$.ajaxSetup({
		headers:
		{ 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
	});
	{{--@if(Auth::user()->id != 14)--}}
		$('label, title, h1, h2, h3, h4, h5, h6, span, tr, div, p, small, center, sub, sup, select, input, a, option').on('cut', function(e) {
		  e.preventDefault();
		});
		$(document).click(function (e) {
		// Check if the Control key (Ctrl) is pressed and the left mouse button is clicked
			if (e.ctrlKey || e.shiftKey) {
			// Prevent the default behavior (opening the link in a new tab)
			e.preventDefault();
			}
		});
		function checkDevTools() {
			if (window.innerHeight < 500) {
				// Developer tools might be open
				$("#cover").fadeIn(100);
				$("#cover").css("z-index", 500);
			}else {
				$("#cover").fadeOut(100);
			}
		}
		$(document).on("contextmenu", function(e) {
			e.preventDefault();
		});
		$(document).keydown(function (e) {
			if ((e.ctrlKey && e.keyCode === 85) || (e.ctrlKey && e.shiftKey && e.keyCode === 73) || (e.keyCode === 123 )) {
				return false;
			}
		});
		$(document).ready(function() {
            // Disable right-click on the entire document
            $(document).on("contextmenu", function(e) {
                e.preventDefault(); // Prevent the default context menu from appearing
            });
        });
		// window.addEventListener("resize", checkDevTools);
		// setInterval(checkDevTools, 5000);
	{{--@endif--}}
  $('.multiselect').select2({ width: '100%', placeholder: "Select an Option", allowClear: true });
  $(document).on('input', '.removeSpaceInput', function() {
    // Get the input value
    var inputValue = $(this).val();

    // Remove spaces from the middle
    var trimmedValue = inputValue.replace(/\s+/g, '');

    // Update the input field with the result
    $(this).val(trimmedValue);
});
</script>
