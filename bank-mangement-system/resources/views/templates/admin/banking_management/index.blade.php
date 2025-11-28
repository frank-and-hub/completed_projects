@extends('templates.admin.master')

@section('content')

<style>
.ul-listing {
    padding: 0 0 0 15px;
    line-height: normal;
    margin: 15px 0;
}


.ul-listing li {
    line-height: normal;
    padding: 6px 0;
}

.ul-listing li a {
    padding: 0;
    color: #000;
    text-decoration: none;
    cursor: pointer;
    display: inline;
}

.ul-listing li a:hover {
    color: #2196F3;
    text-decoration: underline;
}

.money-out-title, .money-in-title {
    background: #e6e4e4;
    min-width: 250px;
    border-left: 3px solid #bbbaba;
    padding: 2px 20px;
    font-size: 18px;
    text-transform: uppercase;
    font-weight: 600;
}
</style>

    <div class="content">
        <div class="row">
		<?php if(check_my_permission( Auth::user()->id,"185") == "1" || check_my_permission( Auth::user()->id,"186") == "1" || check_my_permission( Auth::user()->id,"187") == "1" || check_my_permission( Auth::user()->id,"188") == "1" || check_my_permission( Auth::user()->id,"189") == "1" || check_my_permission( Auth::user()->id,"190") == "1"){ ?>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Mode of Operation</h6>
                    </div>
                    <div class="card-body">
					
                        <div class="row">
							<?php if(check_my_permission( Auth::user()->id,"185") == "1" || check_my_permission( Auth::user()->id,"186") == "1" || check_my_permission( Auth::user()->id,"187") == "1" || check_my_permission( Auth::user()->id,"188") == "1"){ ?>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <div class="col-lg-12 error-msg">
									
                                        <div class="input-group">
                                            <h3 class="money-out-title">Money OUT</h3> 
                                        </div>
                                        <ul class="ul-listing">
										<?php if(check_my_permission( Auth::user()->id,"185") == "1"){ ?>
                                            <li><a href="{{route('admin.banking.create')}}?banking_type=Expense" class="nav-link" target="_blank">Expense</a></li>
										<?php } ?>
										<?php if(check_my_permission( Auth::user()->id,"186") == "1"){ ?>
											<li><a href="{{route('admin.banking.create')}}?banking_type=Payment" class="nav-link" target="_blank">Payment</a></li>
										<?php } ?>
										<?php if(check_my_permission( Auth::user()->id,"187") == "1"){ ?>	
											<li><a href="{{route('admin.banking.create')}}?banking_type=Card" class="nav-link" target="_blank">Card Payment</a></li>
										<?php } ?>
										<?php if(check_my_permission( Auth::user()->id,"188") == "1"){ ?>	
											<li><a href="{{route('admin.fund-transfer.branchToHo')}}" class="nav-link" target="_blank">Payment deposit to bank A/C</a></li>
                                        </ul>
										<?php } ?>
                                    </div>
                                </div>
                            </div>
							<?php } ?>	

							<?php if(check_my_permission( Auth::user()->id,"189") == "1" || check_my_permission( Auth::user()->id,"190") == "1"){ ?>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <h3 class="money-in-title">Money IN</h3> 
                                        </div>
                                        <ul class="ul-listing">
										<?php if(check_my_permission( Auth::user()->id,"189") == "1"){ ?>
											<li><a href="{{route('admin.banking.create')}}?banking_type=Receive" class="nav-link" target="_blank">Receive Payment</a></li>
										<?php } ?>	
										<?php if(check_my_permission( Auth::user()->id,"190") == "1"){ ?>
											<li><a href="{{route('admin.banking.create')}}?banking_type=Income" class="nav-link" target="_blank">Other Income</a></li>
                                        </ul>
										<?php } ?>	
                                    </div>
                                </div>
                            </div>
							<?php } ?>	
                        </div>
                    </div>
                </div>
            </div>
			<?php } ?>

            <div class="col-md-12">

                <div class="card">

                    <div class="card-header header-elements-inline">

                        <h6 class="card-title font-weight-semibold">Bank/Branch List</h6>

                    </div>

                    <div class="">

                        <table id="banking_listing" class="table datatable-show-all">

                            <thead>

                                <tr>

                                    <th>S/N</th>

                                    <th>Bank/Branch Name</th>

                                    <th>Account Number/Branch Code</th>

                                    <th>Balance</th>
									<?php if(check_my_permission( Auth::user()->id,"182") == "1"){ ?>
                                    <th>Action</th> 
									<?php } ?>
                                </tr>

                            </thead>   

                            <tbody>
                                @php
                                    $no = 0;
                                @endphp
                                @foreach($banks as $key => $bank)
                                @php
                                    $no = $no+1;
                                    $bankAccount = \App\Models\SamraddhBankAccount::where('bank_id',$bank->id)->first('account_no');
                                    $balance = \App\Models\SamraddhBankClosing::select('id','balance')->where('bank_id',$bank->id)->orderBy('id','desc')->first();
                                @endphp    
                                <tr>
                                    <td>{{ $no }}</td>
                                    <td>{{ $bank->bank_name }}</td>
                                    <td>{{ $bankAccount->account_no }}</td>
                                    <td>{{ round($balance->balance) }}</td>
									<?php if(check_my_permission( Auth::user()->id,"182") == "1"){ ?>
                                    <td><a class="fa fa-file " href="{{ URL::to('admin/banking/innerlisting?type=bank&id='.$bank->id.'') }}" target="_blank" title="View Transcations"><i class="   mr-2"></i></a></td>
									<?php } ?>
                                </tr>
                                @endforeach

                                @foreach($branches as $key => $branch)
                                @php
                                    $no = $no+1;
                                    $branchBalance = \App\Models\BranchClosing::select('id','balance')->where('branch_id',$branch->id)->orderBy('id','desc')->first();
                                @endphp 
                                <tr>
                                    <td>{{ $no }}</td>
                                    <td>{{ $branch->name }}</td>
                                    <td>{{ $branch->branch_code }}</td>
                                    @if($branchBalance)
                                        <td>{{ round($branchBalance->balance) }}</td>
                                    @else
                                        <td>0</td>
                                    @endif
									<?php if(check_my_permission( Auth::user()->id,"182") == "1"){ ?>
										<td><a class="fa fa-file " href="{{ URL::to('admin/banking/innerlisting?type=branch&id='.$branch->id.'') }}" target="_blank" title="View Transcations"><i class="   mr-2"></i></a></td>
									<?php } ?>
                                </tr>
                                @endforeach
                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

@stop

@section('script')

<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>

@include('templates.admin.banking_management.partials.listing_script')

@endsection