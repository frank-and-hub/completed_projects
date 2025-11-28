<!doctype html>

  <?php
    $id = Session::get('uId');
    $tokenSession = Session::get('branch_token');
    // those branch id's are for multi login allow
    $branchId = array(1,3,4,10,30,5,43); // this is not a branch id it's manager id
    $user_token = App\Models\User::where('id', $id)->first();
    $branch = getBranchDetail(Auth::user()->id);
    if(isset($user_token->branch_token))
    {
      $user_token = $user_token->branch_token;
    }else{
      $user_token ='';
    }
    if(!in_array($id,$branchId))
    {
      if($tokenSession != $user_token){
      Auth::logout();
      session()->flash('message', 'Just Logged Out !');
      header("Refresh:0");
      return redirect('/login');
      header("Refresh:0");
      }
    }

    ?>



<html class="no-js" lang="en">
    <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <base href="{{url('/')}}"/>
        <title>{{ $title }} | {{$set->site_name}}</title>

        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1" />
        <meta name="robots" content="index, follow">
        <meta name="apple-mobile-web-app-title" content="{{$set->site_name}}"/>
        <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
        <meta name="application-name" content="{{$set->site_name}}"/>
        <meta name="msapplication-TileColor" content="#ffffff"/>
        <meta name="description" content="{{$set->site_desc}}" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="shortcut icon" href="{{url('/')}}/asset/{{ $logo->image_link }}" />
        <link rel="apple-touch-icon" href="{{url('/')}}/asset/{{ $logo->image_link }}" />
        <link rel="apple-touch-icon" sizes="72x72" href="{{url('/')}}/asset/{{ $logo->image_link2 }}" />
        <link rel="apple-touch-icon" sizes="114x114" href="{{url('/')}}/asset/{{ $logo->image_link2 }}" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,600,700&display=swap">
        <link rel="stylesheet" href="{{url('/')}}/asset/dashboard/vendor/nucleo/css/nucleo.css" type="text/css">
        <link rel="stylesheet" href="{{url('/')}}/asset/dashboard/vendor/@fortawesome/fontawesome-free/css/all.min.css" type="text/css">
        <link rel="stylesheet" href="{{url('/')}}/asset/dashboard/vendor/datatables.net-bs4/css/dataTables.bootstrap4.min.css">
        <link rel="stylesheet" href="{{url('/')}}/asset/dashboard/vendor/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css">
        <link rel="stylesheet" href="{{url('/')}}/asset/dashboard/vendor/datatables.net-select-bs4/css/select.bootstrap4.min.css">
        <link rel="stylesheet" href="{{url('/')}}/asset/dashboard/css/argon.css?v=1.1.0" type="text/css">
        <link rel="stylesheet" href="{{url('/')}}/asset/css/sweetalert.css" type="text/css">
        <link rel="stylesheet" href="{{url('/')}}/asset/dashboard/vendor/select2/dist/css/select2.min.css">
        <link rel="stylesheet" href="{{url('/')}}/asset/dashboard/vendor/quill/dist/quill.core.css">
        <script src="{{url('/')}}/asset/global_assets/js/plugins/ui/moment/moment.min.js"></script>
        <style type="text/css">
          <style type="text/css">
          .preloader {
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          z-index: 9999;
          background-image: url("{{url('/')}}/asset/{{ $logo->image_link }}");
          background-repeat: no-repeat;
          background-color: #FFF;
          background-position: center;
          }

          .loader {
              position: fixed;
              left: 0px;
              top: 0px;
              width: 100%;
              height: 100%;
              z-index: 9999;
              background: url("{{url('/')}}/asset/images/loader.gif") 50% 50% no-repeat rgb(249,249,249,0);
          }

		  #cover {
		    position: fixed;
			left: 0px;
			top: 0px;
			width: 100%;
			height: 100%;
			z-index: 9999;
			background:#aaaaaa91;
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
			  background: url("{{url('/')}}/asset/images/spiners.gif") 50% 50% no-repeat rgb(249,249,249,0);
		}

        </style>
        </style>
         @yield('css')

    </head>
<!-- header begin-->
<body>
  <div class="preloader"></div>
  <div class="spiners" style="display: none;"></div>
  <div id="cover"> <p class="loaders"></p> </div>
  <!-- Sidenav -->
  <?php

  $branch_name = customBranchName();

  $getBranchId = $branch_name->id;
  $cash_in_hand = $branch_name->cash_in_hand;
  $show_module = false;
  $start_date = $branch_name->date;  // date("Y-m-d");
  $end_date = date("Y-m-d");
  // $authDetail = auth()->user()->getPermissionNames()->toArray();
  $authDetail = auth()
        ->user()
        ->permissions()
        ->where('status', 1) // Add your condition here
        ->get()
        ->pluck('name')
        ->toArray();
  $branch_id = $getBranchId;
  $getBranchOpening_cash = getBranchOpeningDetail($branch_id);
  $balance_cash = 0;
  $closing_balance = 0;
  $diff_balance = $branch_name->day_closing_amount - $cash_in_hand;
  $branchcash = App\Models\BranchCurrentBalance::orderBy('entry_date', 'desc')
  ->where('entry_date','<=',date("Y-m-d"))
  ->where('branch_id', $branch_id)->count('branch_id');

  if (isset($branch_name->first_login) && $branch_name->first_login == 0) {
      if ($branchcash) {
          if ($diff_balance > 0) {
              $show_module = false;
          } else {
              $show_module = true;
          }
      }
      else
      {
          if ($diff_balance > 0) {
              $show_module = false;
          } else {
              $show_module = true;
          }
      }
      if ($cash_in_hand == 0) {
          $show_module = true;
      }
  } else {
      $show_module = true;
      if ($cash_in_hand == 0) {
          $show_module = true;
      }
  }
?>
  <nav class="sidenav navbar navbar-vertical fixed-left navbar-expand-xs navbar-light bg-white" id="sidenav-main">
    <div class="scrollbar-inner">
      <!-- Brand -->
      <div class="sidenav-header d-flex align-items-center">
        <a class="navbar-brand" href="{{route('login')}}">
          <img src="{{url('/')}}/asset/{{ $logo->image_link }}" class="navbar-brand-img" alt="...">
        </a>
          <div>
              {{ $branch_name->name }}
              <br>
              {{ $branch_name->branch_code }}
          </div>
        <div class="ml-auto">
          <!-- Sidenav toggler -->
          <div class="sidenav-toggler d-none d-xl-block" data-action="sidenav-unpin" data-target="#sidenav-main">
            <div class="sidenav-toggler-inner">
              <i class="sidenav-toggler-line"></i>
              <i class="sidenav-toggler-line"></i>
              <i class="sidenav-toggler-line"></i>
            </div>
          </div>
        </div>
      </div>
      <span class="p-4">Cash Limit = {{number_format((float)$cash_in_hand, 2, '.', '')}}</span>
      @php
      foreach(\App\Models\Companies::get(['id','short_name']) as $company){
      echo ('<small class="px-4"><span>' . $company->short_name .' = '. getbranchbankbalanceamounthelper(customBranchName()->id,$company->id) . '</span></small><br>');
      }
      @endphp
      @if($show_module)
      <div class="navbar-inner">
        <!-- Collapse -->
        <div class="collapse navbar-collapse" id="sidenav-collapse-main">
          <!-- Nav items -->
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" href="{{route('branch.dashboard')}}">
                <i class="ni ni-shop text-primary"></i>
                <span class="nav-link-text text-dark">Home</span>
              </a>
            </li>
            {{-- {{pd($authDetail)}} --}}
            @if( in_array('Member View', $authDetail ) || in_array('Member Create', $authDetail ) || in_array('Passbook view', $authDetail ) )
              <li class="nav-item" >
                      <a class="nav-link" href="#member_management" data-toggle="collapse" role="button" aria-expanded="{{ set_active_branch([
                        'branch/member/registration',
                        'branch/member',
                        'branch/member/detail/*',
                        'branch/member/account/*',
                        'branch/member/transactions/*',
                        'branch/member/account/detail/*',
                        'branch/member/account/statement/*',
                        'branch/member/account/printpassbook/*',
                        'branch/member/loan/*',
                        'branch/member/investment/*',
                        'branch/member/passbook',
                        'branch/member/passbook/*',
                        'branch/member/recipt/*',
                        'branch/customer',
                        'branch/blacklist-members-on-loan',
                        'branch/add-blacklist-member-on-loan',
                        'branch/member-registration'
                      ])}}"
                         aria-controls="navbar-examples">
                          <!--For modern browsers-->
                          <i class="ni ni-single-02 text-primary"></i>
                          <span class="nav-link-text text-dark">Member Management</span>
                      </a>
                      <div class="collapse @if(set_active_branch([
                        'branch/member/registration',
                        'branch/member',
                        'branch/member/detail/*',
                        'branch/member/account/*',
                        'branch/member/transactions/*',
                        'branch/member/account/detail/*',
                        'branch/member/account/statement/*',
                        'branch/member/account/printpassbook/*',
                        'branch/member/loan/*',
                        'branch/member/investment/*',
                        'branch/member/passbook',
                        'branch/member/passbook/*',
                        'branch/member/recipt/*',
                        'branch/form_g/*',
                        'branch/customer',
                        'branch/blacklist-members-on-loan',
                        'branch/add-blacklist-member-on-loan',
                        'branch/member-registration'
                      ]) == 'true') show @endif" id="member_management">
                          <ul class="nav nav-sm flex-column" >
                              {{--
                              @if( in_array('Member Create', $authDetail ) )
                                  <li class="nav-item text-default">
                                      <a href="{{route('branch.member_register')}}" class="nav-link">Member Registration</a>
                                  </li>
                              @endif
                              --}}
                              @if (in_array('Member Create', $authDetail))
                              <li class="nav-item text-default">
                                <a href="{{ route('branch.member_registration') }}" class="nav-link">Member Registration</a>
                              </li>
                              @endif
                              @if( in_array('Member View', $authDetail ) )
                                  <li class="nav-item text-default">
                                      <a href="{{route('branch.member_list')}}" class="nav-link">Member Details</a>
                                  </li>
                              @endif
                              @if( in_array('Passbook view', $authDetail ) )
                                  <li class="nav-item text-default">
                                      <a href="{{route('branch.passbook')}}" class="nav-link">Passbook</a>
                                  </li>
                              @endif
                              @if( in_array('Customer View', $authDetail ) )
                              <li class="nav-item text-default">
                                    <a href="{{route('branch.customer_list')}}" class="nav-link">Customer Detail</a>
                              </li>
                              @endif
                              @if (in_array('Manage Blacklist Members For Loan', $authDetail))
                              <li class="nav-item text-default">
                                <a href="{{ route('branch.blacklist-members-on-loan') }}" class="nav-link">Manage Blacklist Members For Loan</a>
                              </li>
                              @endif
                          </ul>
                      </div>
                  </li>
          @endif
          @if( in_array('Associate View', $authDetail) ||
              in_array('Associate Create', $authDetail) ||
              in_array('Associate Profile View', $authDetail) ||
              in_array('Associate Details', $authDetail) ||
              in_array('Associate Commission', $authDetail) ||
              in_array('Associate Upgrade', $authDetail) ||
              in_array('Associate Downgrade', $authDetail) ||
              in_array('Associate Deactivate or Activate', $authDetail) ||
              in_array('Associate Collection Report', $authDetail) )
            <li class="nav-item">
              <a class="nav-link" href="#associate_management" data-toggle="collapse" role="button" aria-expanded="{{ set_active_branch
              (['branch/associate/registration','branch/associate/registration/company', 'branch/associate','branch/associate/detail/*','branch/associate/receipt/*','branch/associate-upgrade','branch/associate-downgrade','branch/associate-status','branch/associate/commission','branch/associate/commission-detail/*','branch/associate/associatecollectionreport']) }}" aria-controls="navbar-examples">
                <!--For modern browsers-->
                <i class="ni ni-single-02 text-primary"></i>
                <span class="nav-link-text text-dark">Associate Management</span>
              </a>
              <div class="collapse @if(set_active_branch(['branch/associate/registration/company','branch/associate/registration', 'branch/associate','branch/associate/detail/*','branch/associate/receipt/*','branch/associate-upgrade','branch/associate-downgrade','branch/associate-status','branch/associate/commission','branch/associate/commission-detail/*','branch/associate/associatecollectionreport']) == 'true') show @endif" id="associate_management">
                <ul class="nav nav-sm flex-column">
                    @if( in_array('Associate Create', $authDetail ) )
                        <li class="nav-item text-default">
                            <a href="{{route('branch.associateregistercompany.index')}}" class="nav-link">Associate Registration</a>
                        </li>
                    @endif
                        @if( in_array('Associate Details', $authDetail ) )
                        <li class="nav-item text-default">
                            <a href="{{route('branch.associate_list')}}" class="nav-link">Associate Details</a>
                        </li>
                    @endif
            @if( in_array('Associate Commission', $authDetail ) )
                    <li class="nav-item text-default">
                            <a href="{{route('branch.associate.commission')}}" class="nav-link">Associate Commission</a>
                    </li>
            @endif

                <!-- @if( in_array('Associate Upgrade', $authDetail ) )
                   <li class="nav-item text-default">
                            <a href="{{route('branch.associate.upgrade')}}" class="nav-link">Associate Upgrade</a>
                    </li>
                 @endif
                  @if( in_array('Associate Downgrade', $authDetail ) )
                    <li class="nav-item text-default">
                            <a href="{{route('branch.associate.downgrade')}}" class="nav-link">Associate Downgrade</a>
                    </li>
                   @endif
                 @if( in_array('Associate Deactivate or Activate', $authDetail ) )
                    <li class="nav-item text-default">
                            <a href="{{route('branch.associate.status')}}" class="nav-link">Associate Deactivate or Activate</a>
                    </li>
                 @endif-->

					 @if( in_array('Associate Collection Report', $authDetail ) )
                    <li class="nav-item text-default">
                            <a href="{{route('branch.associate.associatecollectionreport')}}" class="nav-link">Associate Collection Report</a>
                    </li>
                    @endif
                </ul>
              </div>
            </li>
          @endif
         
          @if( in_array('Investment Plan View', $authDetail ) || in_array('Investment Plan Registration', $authDetail ) || in_array('Renewal Investment', $authDetail ) || in_array('Renewal List', $authDetail ) ||  in_array('Daily Due Report', $authDetail ) || in_array('Monthly Due Report', $authDetail ) )
            <li class="nav-item">
              <a class="nav-link" href="#investment_management" data-toggle="collapse" role="button" aria-expanded="{{ set_active_branch(['branch/registerplan',
              'branch/investments','branch/investment/*','branch/renewplan','branch/renewaldetails','branch/renew/recipt','branch/investment/commission/*','branch/update-renewal','branch/daily/report','branch/monthly/report','branch/renewplan/new']) }}" aria-controls="navbar-examples">
                <!--For modern browsers-->
                <i class="ni ni-chart-bar-32 text-primary"></i>
                <span class="nav-link-text text-dark">Investment Management</span>
              </a>
              <div class="collapse @if(set_active_branch(['branch/registerplan', 'branch/investments','branch/investment/*','branch/renewplan','branch/renewaldetails','branch/renew/recipt','branch/investment/commission/*','branch/update-renewal','branch/daily/report','branch/monthly/report','branch/renewplan/new']) ==
               'true') show @endif"
                   id="investment_management">
                <ul class="nav nav-sm flex-column">
                    @if( in_array('Investment Plan Registration', $authDetail ) )
                        <li class="nav-item text-default">
                            <a href="{{route('register.plan')}}" class="nav-link">New Investment</a>
                        </li>
                    @endif
                    @if( in_array('Investment Plan View', $authDetail ) )
                        <li class="nav-item text-default">
                            <a href="{{route('investment.plans')}}" class="nav-link">Investments Details</a>
                        </li>
                    @endif
                    <!-- @if( in_array('Renewal Investment', $authDetail ) )
                        <li class="nav-item text-default">
                            <a href="{{route('investment.renew')}}" class="nav-link">Renewal Investment</a>
                        </li>
                    @endif -->
                    @if( in_array('Renewal Investment', $authDetail ) )
                        <li class="nav-item text-default">
                            <a href="{{route('branch.renew.new')}}" class="nav-link"> Renewal Investment</a>
                        </li>
                    @endif

                    @if( in_array('Renewal List', $authDetail ) )
                        <li class="nav-item text-default">
                            <a href="{{route('investment.renewaldetails')}}" class="nav-link">Renewal List</a>
                        </li>
                   @endif


                     <!-- <li class="nav-item text-default">
                            <a href="{{route('branch.renew.updaterenewal')}}" class="nav-link">Update Renewal Transaction </a>
                        </li> -->


                        @if (in_array('Daily Due Report', $authDetail))

                        <li class="nav-item"><a href="{{ route('branch.investment.daily.report') }}" class="nav-link"> Daily Investment Plan Due Report</a></li>
                        @endif
                        @if (in_array('Monthly Due Report', $authDetail))

                        <li class="nav-item"><a href="{{ route('branch.investment.monthly.report') }}" class="nav-link"> Monthly Investment Plan Due Report</a></li>
                        @endif

                </ul>
              </div>
            </li>
          @endif
          @if( in_array('Loan View', $authDetail ) || in_array('Register Loan', $authDetail ) || in_array('Group Loans Details', $authDetail ) ||   in_array('Loan Transaction', $authDetail ) ||   in_array('Group Loan Pay EMI', $authDetail ) ||   in_array('Loan Pay EMI', $authDetail ))
            <li class="nav-item">
              <a class="nav-link" href="#loan_management" data-toggle="collapse" role="button" aria-expanded="{{ set_active_branch(['branch/registerloan','branch/loans', 'branch/loan', 'branch/requests', 'branch/loan/view/*', 'branch/loan/*','branch/loan-transactions','branch/branch_loan_emi_payment_common']) }}" aria-controls="navbar-examples">
                <!--For modern browsers-->
                <i class="ni ni-atom text-primary"></i>
                <span class="nav-link-text text-dark">Loan Management</span>
              </a>
              <div class="collapse @if(set_active_branch(['branch/registerloan','branch/requests', 'branch/loan/view/*', 'branch/loan' ,'branch/loans','branch/loan/*', 'branch/loan-transactions','branch/branch_loan_emi_payment_common']) == 'true') show @endif" id="loan_management">
                <ul class="nav nav-sm flex-column">

                      @if( in_array('Register Loan', $authDetail ) )
                        <li class="nav-item text-default">
                            <a href="{{route('loan.create')}}" class="nav-link">Register </a>
                        </li>
                    @endif

                    @if( in_array('Loan View', $authDetail ) )
                        <li class="nav-item text-default">
                            <a href="{{route('loan.loans')}}" class="nav-link">Loans Details</a>
                        </li>
                    @endif
                    @if( in_array('Group Loans Details', $authDetail ) )
                        <li class="nav-item text-default">
                            <a href="{{route('loan.grouploan')}}" class="nav-link">Group Loan Details</a>
                        </li>
                    @endif
                    @if( in_array('Loan Pay EMI', $authDetail ) || in_array('Group Loan Pay EMI', $authDetail ))
                    <li class="nav-item text-default">
                      <a href="{{ route('branch.common.LoanEmiPayment') }}" class="nav-link">Emi Payment</a>
                    </li>
                    @endif
                     @if(in_array('Loan Transaction', $authDetail ))
                      <li class="nav-item text-default">
                          <a href="{{route('branch.loan.transaction')}}" class="nav-link">Loan Transaction</a>
                      </li>
                      @endif
                      <li class="nav-item text-default">
                        <a href="{{ route('branch.ecs.ecs.transactions_list') }}" class="nav-link">Ecs Transaction</a>
                      </li>
                  <!--<li class="nav-item text-default">
                    <a href=" " class="nav-link">Loan Application</a>
                  </li>
                  <li class="nav-item text-default">
                    <a href="" class="nav-link">Loan Recovery</a>
                  </li>
                  <li class="nav-item text-default">
                    <a href="" class="nav-link">Loan Approval</a>
                  </li>-->
                </ul>
              </div>
            </li>
          @endif
          @if( in_array('Add Demand Advice', $authDetail ) || in_array('Demand Advice Application', $authDetail ) || in_array('Demand Advice Report', $authDetail ) || in_array('View TA advance and Imprest Advice', $authDetail ) )
          <li class="nav-item">
              <a class="nav-link" href="#demand_advice_management" data-toggle="collapse" role="button" aria-expanded="{{ set_active_branch(['branch/demand-advices','branch/demand-advice/*']) }}"  aria-controls="navbar-examples">
                <!--For modern browsers-->
                <i class="fas fa-tasks text-primary"></i>
                <span class="nav-link-text text-dark">Demand Advice Management</span>
              </a>
              <div class="collapse @if(set_active_branch(['branch/demand-advices','branch/demand-advice/*']) =='true') show @endif" id="demand_advice_management">
                <ul class="nav nav-sm flex-column">
                @if( in_array('Add Demand Advice', $authDetail ) )
                   <li class="nav-item"><a href="{{route('branch.demand.addadvice')}}" class="nav-link" ><i class="far fa-plus-square"></i>Add Demand Advice</a></li>
                 @endif

                 @if( in_array('Demand Advice Application', $authDetail ) )
                  <li class="nav-item"><a href="{{route('branch.demand.application')}}" class="nav-link"><i class="fa fa-file"></i>Demand Advice Application</a></li>
                 @endif

                 @if( in_array('Demand Advice Report', $authDetail ) )
                  <li class="nav-item"><a href="{{route('branch.demand.report')}}" class="nav-link"><i class="fa fa-list" aria-hidden="true"></i>Demand Advice Report</a></li>
                 @endif

                 <!-- @if( in_array('View TA advance and Imprest Advice', $authDetail ) )
                  <li class="nav-item"><a href="{{route('branch.damandadvice.viewtaadvanced')}}" class="nav-link">
                  <i class="fas fa-eye"></i>View TA advance and Imprest Advice</a>
                  </li>
                  @endif -->

                </ul>
              </div>
          </li>
      @endif
          @if( in_array('Branch To Ho', $authDetail ) || in_array('Report', $authDetail ) )
          <li class="nav-item">
              <a class="nav-link" href="#fund_transfer_management" data-toggle="collapse" role="button" aria-expanded="{{ set_active_branch(['branch/fund-transfer/*']) }}"  aria-controls="navbar-examples">
                <!--For modern browsers-->
                <i class="ni ni-chart-bar-32 text-primary"></i>
                <span class="nav-link-text text-dark">Fund Transfer</span>
              </a>
              <div class="collapse @if(set_active_branch(['branch/fund-transfer/*']) ==
               'true') show @endif"
                   id="fund_transfer_management">
                <ul class="nav nav-sm flex-column">
          @if( in_array('Branch To Ho', $authDetail ) )
                    <li class="nav-item text-default">
                        <a href="{{route('branch.fundtransfer.branchtoho')}}" class="nav-link"><i class="fas fa-piggy-bank"></i>Branch To Ho</a>
                    </li>
          @endif
          @if( in_array('Report', $authDetail ) )
                    <li class="nav-item text-default">
                        <a href="{{route('branch.fundtransfer.report')}}" class="nav-link"><i class="fa fa-list" aria-hidden="true"></i>Report</a>
                    </li>
          @endif
                </ul>
              </div>
          </li>
      @endif
      

      <!--------  -------------------- Expense ------------------------->
          @if( in_array('Expense Booking Manangement', $authDetail ) || in_array('Add Expense Booking', $authDetail ) || in_array('Expense Booking Report', $authDetail ) )
           <li class="nav-item">
              <a class="nav-link" href="#expense_booking" data-toggle="collapse" role="button" aria-expanded="{{ set_active_branch(['branch/expense', 'branch/report/expense','branch/report/bill_expense','branch/report/expense/*','branch/expense/edit/*']) }}"  aria-controls="navbar-examples">
                <!--For modern browsers-->
                <i class="fas fa-tasks text-primary"></i>
                <span class="nav-link-text text-dark">Expense Booking Manangement</span>
              </a>
               <div class="collapse @if(set_active_branch(['branch/expense', 'branch/report/expense','branch/report/bill_expense','branch/report/expense/*','branch/expense/edit/*']) =='true') show @endif" id="expense_booking">

                <ul class="nav nav-sm flex-column">
                 @if( in_array('Add Expense Booking', $authDetail ) )
               <li class="nav-item text-default"><a href="{{route('branch.expense')}}" class="nav-link" ><i class="far fa-plus-square"></i>Add Expense Booking</a></li>
               @endif
                @if( in_array('Expense Booking Report', $authDetail ) )
                <li class="nav-item text-default"><a href="{{route('branch.expense.expense_bill')}}" class="nav-link" ><i class="fa fa-list"></i>Expense Booking Report</a></li>
              @endif

                </ul>
              </div>
          </li>
          @endif
          @if( in_array('Advance Request List', $authDetail ) || in_array('Advance Payment Add Request', $authDetail ) || in_array('Advance Payment', $authDetail ) )
          <!----------------- Advance Payment Start ---------------->
          <li class="nav-item">
            <a class="nav-link" href="#advance_payment" data-toggle="collapse" role="button" aria-expanded="{{ set_active(['branch/advancePayment', 'branch/addRequest','branch/requestList','branch/paymentList']) }}" aria-controls="navbar-examples">
              <i class="far fa-money-bill-alt text-primary"></i>
              <span class="nav-link-text text-dark">Advance Payment</span>
            </a>
            <div class="collapse @if (set_active_branch(['branch/advancePayment', 'branch/addRequest' ,'branch/requestList','branch/advancePayment/*','branch/addAdjestment/*',]) == 'true') show @endif" id="advance_payment">
              <ul class="nav nav-sm flex-column">
                @if(in_array('Advance Payment Add Request',$authDetail))
                <li class="nav-item  text-default"><a href="{{route('branch.advancePayment.add_request')}}" class="nav-link"><i class="far fa-money-bill-alt"></i></i>Advance Request</a></li>
                @endif
                @if(in_array('Advance Request List',$authDetail))
                  <li class="nav-item text-default"><a href="{{route('branch.advancePayment.requestList')}}" class="nav-link"><i class="fas fa-list"></i>Request List</a></li>
                @endif
                  {{-- <li class="nav-item  text-default"><a href="{{route('branch.advancePayment.paymentList')}}" class="nav-link"><i class="fas fa-list"></i>Payment Listing</a> --}}
              </ul>
          </li>
         @endif
      <!-----------------------End Expense ------------------------------>
        @if( in_array('SSB Withdraw', $authDetail ) || in_array('SSB Deposit List', $authDetail ) )
            <li class="nav-item">
                <a class="nav-link" href="#saving_management" data-toggle="collapse" role="button" aria-expanded="{{ set_active_branch(['branch/withdrawal','branch/savingaccountreport']) }}"  aria-controls="navbar-examples">

                  <i class="fas fa-hand-holding-usd text-primary"></i>
                  <span class="nav-link-text text-dark">Payment Management</span>
                </a>
                <div class="collapse @if(set_active_branch(['branch/withdrawal','branch/savingaccountreport']) == 'true') show @endif"
                    id="saving_management">
                  <ul class="nav nav-sm flex-column">
                    @if( in_array('SSB Withdraw', $authDetail ) )
                    <li class="nav-item text-default">
                      <a href="{{route('branch.withdraw.ssb')}}" class="nav-link"><i class="fas fa-piggy-bank"></i>SSB Withdraw</a>
                    </li>
                    @endif
                    @if( in_array('SSB Deposit List', $authDetail ) )
                        <li class="nav-item text-default">
                            <a href="{{route('investment.savingaccountreport')}}" class="nav-link"><i class="fas fa-list"></i>Saving Listing</a>
                        </li>
                     @endif
                  </ul>
                </div>
            </li>
        @endif
      <!-- @if( in_array('View Member Correction Request List', $authDetail ) || in_array('View Associate Correction Request List', $authDetail ) || in_array('View Investment Correction Request List', $authDetail ) || in_array('View Renewal Correction Request List', $authDetail ))
            <li class="nav-item">
              <a class="nav-link" href="#correction_management" data-toggle="collapse" role="button" aria-expanded="{{ set_active_branch(['branch/member/corrections',
              'branch/associate/corrections','branch/investment/corrections','branch/renewal/corrections','branch/printpassbook/corrections','branch/printcertificate/corrections']) }}"  aria-controls="navbar-examples"> -->
                <!--For modern browsers-->
                <!-- <i class="ni ni-chart-bar-32 text-primary"></i>
                <span class="nav-link-text text-dark">Corrections Management</span>
              </a>
              <div class="collapse @if(set_active_branch(['branch/member/corrections',
              'branch/associate/corrections','branch/investment/corrections','branch/renewal/corrections','branch/printpassbook/corrections','branch/printcertificate/corrections']) ==
               'true') show @endif"
                   id="correction_management">
                <ul class="nav nav-sm flex-column">
          @if( in_array('View Member Correction Request List', $authDetail ) )
                    <li class="nav-item text-default">
                        <a href="{{route('branch.member.correctionrequest')}}" class="nav-link"><i class="fas fa-users"></i>Member Correction Requests</a>
                    </li>
          @endif
          @if( in_array('View Associate Correction Request List', $authDetail ) )
                    <li class="nav-item text-default">
                        <a href="{{route('branch.associate.correctionrequest')}}" class="nav-link"><i class="fas fa-users"></i>Associate Correction Requests</a>
                    </li>
          @endif
          @if( in_array('View Investment Correction Request List', $authDetail ) )
                    <li class="nav-item text-default">
                        <a href="{{route('branch.investment.correctionrequest')}}" class="nav-link"><i class="fas fa-users"></i>Investment Correction Requests</a>
                    </li>
          @endif
          @if( in_array('View Renewal Correction Request List', $authDetail ) )
                    <li class="nav-item text-default">
                        <a href="{{route('branch.renewal.correctionrequest')}}" class="nav-link"><i class="fas fa-users"></i>Renew Correction Requests</a>
                    </li>
                    <li class="nav-item text-default">
                        <a href="{{route('branch.printpassbook.correctionrequest')}}" class="nav-link"><i class="fas fa-users"></i>Print Passbook Correction Requests</a>
                    </li>
                    <li class="nav-item text-default">
                        <a href="{{route('branch.printcertificate.correctionrequest')}}" class="nav-link"><i class="fas fa-users"></i>Print Certificate Correction Requests</a>
                    </li>
          @endif
                </ul>
              </div>
          </li>
      @endif -->
      <!----------------- Correction Management Start ---------------->
      @if (
      in_array('Correction Management', $authDetail) ||
      in_array('Renewal Correction Request', $authDetail)
      )
        <li class="nav-item">
          <a class="nav-link" href="#correction_management" data-toggle="collapse" role="button" aria-expanded="{{ set_active([
            'branch/correctionmanagement/add',
            'branch/correction/view',
            'branch/correctionmanagement/renewal'
            ]) }}" aria-controls="navbar-examples">

          <i class="fas fa-tools text-primary"></i>
            <span class="nav-link-text text-dark">Correction Management</span>
          </a>
          <div class="collapse @if (set_active_branch([
                'branch/correctionmanagement/add',
                'branch/correction/view',
                'branch/correctionmanagement/renewal'
                ]) == 'true') show @endif" id="correction_management">

            <ul class="nav nav-sm flex-column">
              {{--
              @if (in_array('Correction Management Request', $authDetail))
              <li class="nav-item  text-default"><a href="{{route('branch.correctionmanagement.index')}}" class="nav-link"><i class="far fa-plus-square"></i></i>Correction Request</a></li>
              @endif
              @if (in_array('Correction Management Request List', $authDetail))
              <li class="nav-item text-default"><a href="{{route('branch.correctionmanagement.request')}}" class="nav-link"><i class="fas fa-clipboard-list"></i>Correction List</a></li>
              @endif--}}
              @if (in_array('Renewal Correction Request', $authDetail))
              <li class="nav-item text-default"><a href="{{route('branch.correctionmanagement.renewal')}}" class="nav-link"><i class="fas fa-clipboard-list"></i>Renewal Correction List</a></li>
              @endif
        </ul>
      </div>
      </li>
      @endif
      <!----------------- Correction Management End  ---------------->
        @if( in_array('Holidays', $authDetail ) )
          <li class="nav-item">
              <a class="nav-link" href="#holiday_calendar" data-toggle="collapse" role="button" aria-expanded="{{ set_active_branch(['branch/events']) }}"  aria-controls="navbar-examples">
                <!--For modern browsers-->
                <i class="ni ni-calendar-grid-58 text-primary"></i>
                <span class="nav-link-text text-dark">Holiday Calendar</span>
              </a>
              <div class="collapse @if(set_active_branch(['branch/events']) == 'true') show @endif" id="holiday_calendar">
                <ul class="nav nav-sm flex-column">
                  @if( in_array('Holidays', $authDetail ) )
                    <li class="nav-item text-default">
                        <a href="{{route('branch.events')}}" class="nav-link"><i class="fas fa-plus-circle"></i>Holidays</a>
                    </li>
                  @endif
                </ul>
              </div>
          </li>
      @endif
      @if( in_array('View Notice Board', $authDetail ) )
          <li class="nav-item">
              <a class="nav-link" href="#notice_board" data-toggle="collapse" role="button" aria-expanded="{{ set_active_branch
              (['branch/noticeboard'])
          }}"
                 aria-controls="navbar-examples">
                  <!-- For modern browsers -->
                  <i class="ni ni-chart-bar-32 text-primary"></i>
                  <span class="nav-link-text text-dark">Notice Board</span>
              </a>
              <div class="collapse @if(set_active_branch(['branch/noticeboard']) == 'true') show @endif"
                   id="notice_board">
                  <ul class="nav nav-sm flex-column">
          @if( in_array('View Notice Board', $authDetail ) )
                      <li class="nav-item text-default">
                          <a href="{{route('branch.noticeboard')}}" class="nav-link"><i class="fas fa-plus-circle"></i>Notice Board</a>
                      </li>
          @endif
                  </ul>
              </div>
          </li>
      @endif        
    @if( in_array('Add Cheque', $authDetail ) || in_array('Cheque List', $authDetail ))
          <li class="nav-item">
              <a class="nav-link" href="#received_cheque_management" data-toggle="collapse" role="button" aria-expanded="{{ set_active_branch(['branch/received/cheque','branch/received/cheque/add','branch/received/cheque/view/*']) }}"  aria-controls="navbar-examples">
                <i class="ni ni-chart-bar-32 text-primary"></i>
                <span class="nav-link-text text-dark">Received Cheque Management</span>
              </a>
              <div class="collapse @if(set_active_branch(['branch/received/cheque','branch/received/cheque/add','branch/received/cheque/view/*']) ==
               'true') show @endif"
                   id="received_cheque_management">
                <ul class="nav nav-sm flex-column">
          @if( in_array('Add Cheque', $authDetail ))
                    <li class="nav-item text-default">
                        <a href="{{route('branch.received.cheque_add')}}" class="nav-link"><i class="fas fa-plus-circle"></i>Add Cheque</a>
                    </li>
          @endif
          @if( in_array('Cheque List', $authDetail ))
                    <li class="nav-item text-default">
                        <a href="{{route('branch.received.cheque_list')}}" class="nav-link"><i class="fas fa-list"></i>Cheque List</a>
                    </li>
                    @endif
                </ul>
              </div>
          </li>
    @endif

    @if( in_array('Register Employee', $authDetail ) || in_array('Employee Application', $authDetail ) || in_array('Employee List', $authDetail ) || in_array('Employee Transfer List', $authDetail ) || in_array('Resign Request', $authDetail ) )
          <li class="nav-item">
              <a class="nav-link" href="#hr_management" data-toggle="collapse" role="button" aria-expanded="{{ set_active_branch([
                'branch/hr/employee',
                'branch/hr/employee/application',
                'branch/hr/employee/register',
                'branch/hr/edit/*',
                'branch/hr/employee/detail/*',
                'branch/hr/employee/resign_request',
                'branch/hr/employee/transfer_letter/*',
                'branch/hr/employee/transfer/detail/*',
                'branch/hr/employee/transfer',
                'branch/hr/employee/application_print/*',
                'branch/hr/employ/*',
                'branch/employee/register'
                ]) }}"  aria-controls="navbar-examples">
                <!--For modern browsers-->
                 <i class="fas fa-users text-primary"></i>
                <span class="nav-link-text text-dark">HR Management</span>
              </a>
              <div class="collapse @if(set_active_branch([
                'branch/hr/employee',
                'branch/hr/employee/application',
                'branch/hr/employee/register',
                'branch/hr/edit/*',
                'branch/hr/employee/detail/*',
                'branch/hr/employee/resign_request',
                'branch/hr/employee/transfer_letter/*',
                'branch/hr/employee/transfer/detail/*',
                'branch/hr/employee/transfer',
                'branch/hr/employee/application_print/*',
                'branch/hr/employ/*',
                'branch/employee/register'
                ]) ==
               'true') show @endif"
                   id="hr_management">
                <ul class="nav nav-sm flex-column">
                  <li class="nav-item">
                    <a class="nav-link" href="#employee" data-toggle="collapse" role="button" aria-expanded="{{ set_active_branch([
                      'branch/hr/employee',
                      'branch/hr/employee/application',
                      'branch/hr/employee/register',
                      'branch/hr/edit/*',
                      'branch/hr/employee/detail/*',
                      'branch/hr/employee/resign_request',
                      'branch/hr/employee/transfer_letter/*',
                      'branch/hr/employee/transfer/detail/*',
                      'branch/hr/employee/transfer',
                      'branch/hr/employee/application_print/*',
                      'branch/hr/employ/*',
                      'branch/employee/register'
                      ]) }}"  aria-controls="navbar-examples">
                              <i class="fas fa-user-friends "></i><span class="nav-link-text text-dark">Employee Management</span>
                    </a>
                    <div class="collapse @if(set_active_branch([
                      'branch/hr/employee',
                      'branch/hr/employee/application',
                      'branch/hr/employee/register',
                      'branch/hr/edit/*',
                      'branch/hr/employee/detail/*',
                      'branch/hr/employee/resign_request',
                      'branch/hr/employee/transfer_letter/*',
                      'branch/hr/employee/transfer/detail/*',
                      'branch/hr/employee/transfer',
                      'branch/hr/employee/application_print/*',
                      'branch/hr/employ/*',
                      'branch/employee/register'
                      ]) == 'true') show @endif" id="employee">
                        <ul class="nav nav-sm flex-column">
            {{--
            @if( in_array('Register Employee', $authDetail ))
                          <li class="nav-item text-default">
                            <a href="{{route('branch.hr.employee_add')}}" class="nav-link">  <i class="fas fa-plus-circle"></i>Register Employee</a>
                          </li>
            @endif
            --}}
            @if (in_array('Register Employee', $authDetail))
            <li class="nav-item text-default">
              <a href="{{ route('branch.employee_add') }}" class="nav-link"> <i class="fas fa-plus-circle"></i>Employee Register</a>
            </li>
            @endif
            @if( in_array('Employee Application', $authDetail ) )
                          <li class="nav-item text-default">
                            <a href="{{route('branch.hr.employee_application_list')}}" class="nav-link"> <i class="fas fa-list"></i>Employee Application</a>
                          </li>
            @endif
            @if( in_array('Employee List', $authDetail ))
                          <li class="nav-item text-default">
                            <a href="{{route('branch.hr.employee_list')}}" class="nav-link"> <i class="fas fa-list"></i>Employee List</a>
                          </li>
            @endif
            @if( in_array('Employee Transfer List', $authDetail ))
                          <li class="nav-item text-default">
                            <a href="{{route('branch.hr.employee_transfer_list')}}" class="nav-link"> <i class="fas fa-list"></i>Employee Transfer List</a>
                          </li>
            @endif
            @if( in_array('Resign Request', $authDetail ))
                          <li class="nav-item text-default">
                            <a href="{{route('branch.hr.employee_resign_request')}}" class="nav-link"><i class="fas fa-plus-circle"></i>Resign Request </a>
                          </li>
            @endif
                        </ul>
                    </div>
                  </li>
                </ul>
              </div>
          </li>
      @endif
  <!-------- Report Manangement Start  ----------------->
    @if( in_array('Associate Business Report', $authDetail ) || in_array('Associate Business Summary Report', $authDetail ) || in_array('Associate Business Compare Report', $authDetail ) || in_array('Maturity Report', $authDetail)  || in_array('Loan Report', $authDetail) || in_array('Day Book Report', $authDetail) || in_array('Daily Business Report', $authDetail) || in_array('Mother Branch Business Report', $authDetail))
            <li class="nav-item">
              <a class="nav-link" href="#report_menu" data-toggle="collapse" role="button" aria-expanded="{{ set_active_branch([ 'branch/report/associate_business', 'branch/report/associate_business_compare', 'branch/report/associate_business_summary','branch/report/maturity','branch/report/day_book','branch/report/loan','branch/report/day_business' ,'branch/report/mother_branch_business','branch/report/day_business_report']) }}"  aria-controls="navbar-examples">
                <i class="far fa-file-excel text-primary"></i>
                <span class="nav-link-text text-dark">Report Management</span>
              </a>
              <div class="collapse @if(set_active_branch(['branch/report/associate_business_report', 'branch/report/associate_business_compare', 'branch/report/associate_business_summary','branch/report/maturity','branch/report/day_book' ,'branch/report/loan','branch/report/day_business','branch/report/mother_branch_business','branch/report/day_business_report']) ==
               'true') show @endif"
                   id="report_menu">
                <ul class="nav nav-sm flex-column">
          @if( in_array('Associate Business Report', $authDetail ))
                    <li class="nav-item"><a href="{{route('branch.common.associate_busniss_report')}}" class="nav-link" ><i class="fas fa-chart-bar"></i>Associate Business Report</a></li>
          @endif
          <!-- @if( in_array('Associate Business Summary Report', $authDetail ))
          <li class="nav-item"><a href="{{route('branch.report.associate_business_summary_report')}}" class="nav-link" ><i class="fas fa-chart-bar"></i>Associate Business Summary Report</a></li>
          @endif -->
          @if( in_array('Associate Business Compare Report', $authDetail ))
          <li class="nav-item"><a href="{{route('branch.report.associate_business_compare_report')}}" class="nav-link" ><i class="fas fa-chart-bar"></i></i>Associate Business Compare Report</a></li>
          @endif

                      <!-- Durgesh -->

                    @if( in_array('Loan Report', $authDetail ))
                    <li class="nav-item" ><a href="{{route('branch.report.loan')}}" class="nav-link" ><i class="fas fa-chart-bar"></i></i>Loan Report</a></li>
                    @endif



                    @if( in_array('Maturity Report', $authDetail ))
            <li class="nav-item"><a href="{{route('branch.report.maturity')}}" class="nav-link" ><i class="fas fa-chart-bar"></i></i>Maturity Report </a></li>
                    @endif
                     <!-- @if( in_array('Day Book Report', $authDetail ))
            <li class="nav-item"><a href="{{route('branch.report.day_book')}}" class="nav-link" ><i class="fas fa-list"></i>Day Book Report</a></li>
                    @endif -->
                    <li class="nav-item"><a href="{{route('branch.report.day_book_dublicate')}}" class="nav-link" ><i class="fas fa-list"></i> Day Book Report</a></li>
                    @if( in_array('Daily Business Report', $authDetail ))
                    <li class="nav-item {{ set_active(['branch/report/day_business_report']) }}"><a href="{{ route('branch.bussiness.report') }}" class="nav-link"><i class="fas fa-list"></i> Daily Bussiness Report</a></li>
                    @endif
                      <!-- mahesh -->
                      @if( in_array('Mother Branch Business Report', $authDetail ))
                    <li class="nav-item  {{ set_active(['branch/report/mother_branch_business']) }}"><a href="{{ route('branch.report.mother_branch_business') }}" class="nav-link"><i class="fa-code-branch"></i>Mother Branch Business Report</a></li>
                    @endif
                     <!-- @if( in_array('Daily Business Report', $authDetail ))
                    <li class="nav-item"><a href="{{route('branch.report.day_business')}}" class="nav-link" ><i class="fas fa-list"></i>Daily Business Report</a></li>
                    @endif -->

                </ul>
              </div>
          </li>
    @endif

<!-------- Report Manangement End  ----------------->
<!--------------------Voucher  Start------------------------------>
@if( in_array('Voucher Request', $authDetail ) || in_array('Voucher List', $authDetail ) )
            <li class="nav-item">

              <a class="nav-link" href="#voucher_menu" data-toggle="collapse" role="button" aria-expanded="{{ set_active_branch([ 'branch/voucher', 'branch/voucher/*' ]) }}"  aria-controls="navbar-examples">
                <i class="far fa-file-excel text-primary"></i>
                <span class="nav-link-text text-dark">Receive Voucher (INFLOW)</span>
              </a>
              <div class="collapse @if(set_active_branch(['branch/voucher', 'branch/voucher/*'  ]) ==
               'true') show @endif"
                   id="voucher_menu">
                <ul class="nav nav-sm flex-column">
              @if( in_array('Voucher Request', $authDetail ))
                    <li class="nav-item"><a href="{{route('branch.voucher.create')}}" class="nav-link" ><i class="fas fa-plus-circle"></i>Voucher Request</a></li>
                   @endif
                @if( in_array('Voucher List', $authDetail ))
                <li class="nav-item"><a href="{{route('branch.voucher')}}" class="nav-link" ><i class="fas fa-list"></i>Voucher List</a></li> </a></li>
               @endif


                </ul>
              </div>
          </li>
       @endif
<!--------------------Voucher  End------------------------------>
          </ul>
        </div>
        @else
          <?php
            $routeName = \Request::route()->getName();
          ?>
          <div class="navbar-inner">
        <!-- Collapse -->
            <div class="collapse navbar-collapse" id="sidenav-collapse-main">
          <!-- Nav items -->
            <ul class="navbar-nav">
                <li class="nav-item">
                <a href="{{route('branch.withdraw.ssb')}}" class="nav-link"><i class="fas fa-piggy-bank"></i>SSB Withdraw</a>

                </li>
                <li class="nav-item">
                <a href="{{route('branch.fundtransfer.branchtoho')}}" class="nav-link"><i class="fas fa-piggy-bank"></i>Branch To Ho</a>
                </li>

            </ul>
          </div>
        </div>
          @endif
      </div>
    </div>
  </nav>
   <div class="main-content" id="panel">
    <!-- Topnav -->
    <nav class="navbar navbar-top navbar-expand navbar-dark border-bottom">
      <div class="container-fluid">
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <!-- Search form -->

          <!-- Navbar links -->
          <ul class="navbar-nav align-items-center ml-md-auto">
            <li class="nav-item d-xl-none">
              <!-- Sidenav toggler -->
              <div class="pr-3 sidenav-toggler sidenav-toggler-light" data-action="sidenav-pin" data-target="#sidenav-main">
                <div class="sidenav-toggler-inner">
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                </div>
              </div>
            </li>
          </ul>
          @if(!$show_module)
            @php
                \App\Models\Branch::where('id',$branch_name->id)->update(['transferrabel_amount'=>$branch_name->day_closing_amount - $branch_name->cash_in_hand]);
            @endphp

            <h6 class="h2 mb-0"><span class="p-4 text-danger">Transferable Amount = {{$branch_name->day_closing_amount - $branch_name->cash_in_hand}}</span></h6>
        @endif
          <div class="">
            <h6 class="h2 mb-0 text-success">
              @php
                $stateid = getBranchStateByManagerId(Auth::user()->id);


              @endphp
              {{ Auth::user()->username }}

              {{ headerMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}

              <input type="hidden" name="gdatetime" id="gdatetime" value="{{ checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}">

            </h6>
          </div>
          <ul class="navbar-nav align-items-center ml-auto ml-md-0">
            <li class="nav-item dropdown">
              <a class="nav-link pr-0" href="javascript:void;" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <div class="media align-items-center">
                  <span class="avatar avatar-sm rounded-circle">
                    <!-- <img alt="Image placeholder" src="{{url('/')}}/asset/profile/react.jpg"> -->
                    <img alt="Image placeholder" src="{{ ImageUpload::generatePreSignedUrl('profile/react.jpg') }}">
                  </span>
                </div>
              </a>
             {{-- <div class="dropdown-menu dropdown-menu-right">
                <div class="dropdown-header noti-title">
                  <h6 class="text-overflow m-0">Welcome!</h6>
                </div>
                <a href="{{route('user.profile')}}" class="dropdown-item">
                  <i class="ni ni-single-02"></i>
                  <span>My profile</span>
                </a>
                <a href="{{route('user.password')}}" class="dropdown-item">
                  <i class="ni ni-key-25"></i>
                  <span>Password</span>
                </a>
                <a href="{{route('user.pin')}}" class="dropdown-item">
                  <i class="ni ni-lock-circle-open"></i>
                  <span>Transfer pin</span>
                </a>
              </div>--}}
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link" href="{{route('branch.logout')}}" role="button" aria-haspopup="true" aria-expanded="false">
                <i class="ni ni-button-power text-danger"></i>
              </a>
            </li>
          </ul>

        </div>
      </div>
    </nav>
   <?php

      $noticeIds = App\Models\AssignNoticeToBranch::where('branch_id', $branch_id)->pluck('notice_id')->toArray();
      if( count($noticeIds) > 0 ) {
        $noticeLists = App\Models\Noticeboard::orderBy('created_at','desc')->where('status',1)->first(['id','start_date','title']);
      } else {
        $noticeLists = '';
        $data['noticeLists'] = [];
          }
    ?>
    @if( in_array('View Notice Board', $authDetail ) )
    @if(isset($noticeLists->start_date))
    <marquee style="color:red;font-weight:bold;  " onmouseleave="this.start();" onMouseOver="this.stop();">
      <dl>
        <dt>
          <span class="notice" data-id="{{ $noticeLists->id }}" style="color: blue;">
            <strong style="color: red;">{{ \Carbon\Carbon::parse($noticeLists->start_date)->format('d M Y')}}</strong> | {{$noticeLists->title}}</span>
          </dt>
      </dl>
     </marquee>
     @endif
      @endif
    <div class="header pb-6">
      <div class="container-fluid">
        <div class="header-body">
        </div>
      </div>
    </div>
<!-- header end -->
@yield('content')
<!-- footer begin -->
<footer class="footer pt-0">
      </footer>
    </div>
  </div>
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
//s1.src='https://embed.tawk.to/{{$set->tawk_id }}/default';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
  <!-- Argon Scripts -->
  <!-- Core -->
  <script src="{{url('/')}}/asset/dashboard/vendor/jquery/dist/jquery.min.js"></script>
  <script src="{{url('/')}}/asset/dashboard/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{url('/')}}/asset/dashboard/vendor/js-cookie/js.cookie.js"></script>
  <script src="{{url('/')}}/asset/dashboard/vendor/jquery.scrollbar/jquery.scrollbar.min.js"></script>
  <script src="{{url('/')}}/asset/dashboard/vendor/jquery-scroll-lock/dist/jquery-scrollLock.min.js"></script>

  <script src="{{url('/')}}/asset/dashboard/vendor/datatables.net/js/jquery.dataTables.min.js"></script>
  <script src="{{url('/')}}/asset/dashboard/vendor/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
  <script src="{{url('/')}}/asset/dashboard/vendor/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
  <script src="{{url('/')}}/asset/dashboard/vendor/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
  <script src="{{url('/')}}/asset/dashboard/vendor/datatables.net-buttons/js/buttons.html5.min.js"></script>
  <script src="{{url('/')}}/asset/dashboard/vendor/datatables.net-buttons/js/buttons.flash.min.js"></script>
  <script src="{{url('/')}}/asset/dashboard/vendor/datatables.net-buttons/js/buttons.print.min.js"></script>
  <script src="{{url('/')}}/asset/dashboard/vendor/datatables.net-select/js/dataTables.select.min.js"></script>

  <script src="{{url('/')}}/asset/dashboard/vendor/select2/dist/js/select2.min.js"></script>
  <script src="{{url('/')}}/asset/dashboard/vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>

  <script src="{{url('/')}}/asset/dashboard/vendor/quill/dist/quill.min.js"></script>

  <script src="{{url('/')}}/asset/dashboard/vendor/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js"></script>
  <!-- Argon JS -->
  <script src="{{url('/')}}/asset/dashboard/js/argon.js?v=1.1.0"></script>

  <script src="{{url('/')}}/asset/js/sweetalert.js"></script>
  <script src="{{url('/')}}/asset/global_assets/js/plugins/forms/validation/validate.min.js"></script>
  <script src="{{url('/')}}/asset/global_assets/js/plugins/forms/validation/additional_methods.min.js"></script>
  <script src="{{url('/')}}/asset/print.min.js"></script>
  <script src="{{url('/')}}/asset/js/jQuery.print.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.js"></script>
  <script src="{{url('/')}}/core/public/js/global.js"></script>

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
</script>
@php

@endphp
<script type="text/javascript">

$('.preloader').fadeOut(1000);

$(window).bind("load", function() {
  let created_at = $( "#gdatetime" ).val();
    $('#created_at').val(created_at);
    $('#saving_created_at').val(created_at);
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

			$(function(){
				$.ajaxSetup({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					}
				});
			});
      /** this code is modify by Manhesh on 06-10-2023 for disabling right click
     * on hole project as per new client feedback.
     */
    {{--@if(Auth::user()->id != 1)--}}
    $( document ).ready(function() {
        $(document).on("contextmenu", function(e) {
          e.preventDefault();
        });
        $(document).keydown(function (e) {
          if ((e.ctrlKey && e.keyCode === 85 || e.ctrlKey && e.shiftKey && e.keyCode === 73) || (e.keyCode === 123 ) ) {
            return false;
          }
        });
    });
    document.addEventListener("DOMContentLoaded", function() {
    var links = document.querySelectorAll("a");
        links.forEach(function(link) {
            link.addEventListener("click", function(e) {
                if ((e.ctrlKey || e.shiftKey)) {
                    e.preventDefault(); // Prevent the default behavior of opening in a new tab
                }
            });
        });
    });
    $(document).click(function (e) {
		// Check if the Control key (Ctrl) is pressed and the left mouse button is clicked
			if (e.ctrlKey || e.shiftKey) {
			// Prevent the default behavior (opening the link in a new tab)
			e.preventDefault();
			}
		});
    $(document).ready(function() {
            // Disable right-click on the entire document
            $(document).on("contextmenu", function(e) {
                e.preventDefault(); // Prevent the default context menu from appearing
            });
        });
    $('label, title, h1, h2, h3, h4, h5, h6, span, td, tr, div, p, small, center, sub, sup, select, input, a, option').on('cut', function(e) {
      e.preventDefault();
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
	// window.addEventListener("resize", checkDevTools);
	// setInterval(checkDevTools, 5000);
  {{--@endif--}}
  $(document).on('input', '.removeSpaceInput', function() {
    // Get the input value
    var inputValue = $(this).val();

    // Remove spaces from the middle
    var trimmedValue = inputValue.replace(/\s+/g, '');

    // Update the input field with the result
    $(this).val(trimmedValue);
});
</script>
