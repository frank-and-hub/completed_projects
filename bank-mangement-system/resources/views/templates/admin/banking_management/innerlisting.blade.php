@extends('templates.admin.master')

@section('content')

    <div class="content">
        <div class="row">

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
                    </div>
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="filter-inner-listing" name="filter-inner-listing">
                        @csrf
                        <input type="hidden" name="listingttype" id="listingttype" value="{{ $type }}">
                        <input type="hidden" name="listingttypeid" id="listingttypeid" value="{{ $id }}">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From  Date</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" class="form-control  " name="start_date" id="start_date" required=""> 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To  Date</label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" class="form-control  " name="end_date" id="end_date" required=""> 
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group text-right"> 
                                        <div class="col-lg-12 page">
                                            <input type="hidden" name="is_search" id="is_search" value="yes">
                                            <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-12">

                <div class="card">

                    <!-- <div class="card-header header-elements-inline">

                        <h6 class="card-title font-weight-semibold">Banking Listing</h6>

                    </div> -->

                    <div class="">

                        <table id="branch_banking_inner_listing" class="table datatable-show-all">

                            <thead>

                                <tr>

                                    <th>S/N</th>

                                    <th>Date</th>

                                    <th>Type</th>

                                    <th>Sub Type</th>

                                    <th>Amount</th>

									<?php if(check_my_permission( Auth::user()->id,"191") == "1" || check_my_permission( Auth::user()->id,"192") == "1"){ ?>
                                    <th>Action</th> 
									<?php } ?>

                                </tr>

                            </thead>   

                            <tbody>
                                <!-- @if(count($records) > 0)
                                    @foreach($records as $key => $record)
                                    <tr>
                                        <td>{{ $key+1 }}</td>
                                        <td>{{ date("d/m/Y", strtotime(convertDate($record->date))) }}</td>
                                        @if($record->banking_type == 1)
                                            @if($record->expense_account3)
                                            <td>Expense/{{ getAcountHead($record->expense_account3) }}</td>
                                            @elseif($record->expense_account2)
                                            <td>Expense/{{ getAcountHead($record->expense_account2) }}</td>
                                            @elseif($record->expense_account1)
                                            <td>Expense/{{ getAcountHead($record->expense_account1) }}</td>
                                            @elseif($record->expense_account)
                                            <td>Expense/{{ getAcountHead($record->expense_account) }}</td>
                                            @endif
                                        @elseif($record->banking_type == 2)
                                        <td>Payment</td>
                                        @elseif($record->banking_type == 3)
                                        <td>Card Payment</td>
                                        @elseif($record->banking_type == 4)
                                        <td>Receive</td>
                                        @elseif($record->banking_type == 5)
                                        <td>Income</td>
                                        @endif
                                        <td>{{ $record->amount }}</td>
                                        <td>
                                            <div class="list-icons">
                                                <div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">

                                                    <a class="dropdown-item" href="{{ URL::to('admin/banking/edit/'.$record->id.'') }}" title="Edit"><i class="icon-pencil7  mr-2"></i> Edit</a>  

                                                    <a class="dropdown-item delete-transaction" href="{{ URL::to('admin/banking/delete/'.$record->id.'') }}" title="Delete"><i class="icon-trash-alt  mr-2"></i> Delete</a>

                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="5" style="text-align: center;">No Records Found!</td></tr>
                                @endif -->
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

@include('templates.admin.banking_management.partials.create_script')

@endsection