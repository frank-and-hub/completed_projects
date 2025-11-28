@extends('layouts/branch.dashboard')

@section('content')
<style>
    .search-table-outter {
        overflow-x: scroll;
    }

    .frm {
        min-width: 200px;
    }

    #bill_date {
        min-width: 100px;
    }
</style>
<div class="loader" style="display: none;"></div>

<div class="content">
    <div class="row">
        @if ($errors->any())
        <div class="col-md-12">
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif



        <div class="col-md-12">
            <!-- Basic layout-->
            <div class="card">

                <div class="card-body">

                    <h3 class="card-title mb-3 maintital">Adjustment Request</h3>


                </div>


                <form id="form">
                @csrf
                    <input type="hidden" class="form-control created_at " name="created_at" id="created_at">

                    <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date">
                    <div class="card-header header-elements-inline">
                        <div class="container">
                            <div class=" form-group row">
                                <div class="col-lg-6">
                                    <label for="EmployeeName">Employee Name</label>
                                    <input type="text" name="employeename" readonly value="{{$data['employee']['employee_name']}}" class="form-control">
                                    <input type="hidden" name="employeeid" value="{{$data['employee']['id']}}" class="form-control">
                                    <input type="hidden" name="id" value="{{last(explode('/', url()->current()))}}" class="form-control">
                                </div>
                                <div class="col-lg-6">
                                    <label for="Employeecode">Employee Code</label>
                                    <input type="text" name="employeecode" readonly value="{{$data['employee']['employee_code']}}" class="form-control">
                                </div>
                            </div>
                            <div class=" form-group row">
                                <div class="col-lg-6">
                                    <label for="Amount">Amount</label>
                                    <input type="number" name="approveAmount" readonly value="{{$data['advancePayment']['amount'] - $data['advancePayment']['used_amount']}}" class="form-control">
                                </div>
                                <div class="col-lg-6">
                                    <label for="adjestmentdate">Adjustment Date</label>
                                    <input type="text" name="adjestmentdate" id="date" readonly value="{{date('d/m/Y',strtotime($data['advancePayment']['status_date']))}}" class="form-control">
                                </div>
                            </div>
                            <div class=" form-group row">
                                <div class="col-lg-6">
                                    <label for="Amount">Branch Name</label>
                                    <select class="form-control" id="branch_id" name="branch_id">
                                        <option value="">Select Branch</option>
                                        @foreach ($data['branches'] as $val)
                                        <option value="{{ $val['id'] }}">{{ $val['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="search-table-outter wrapper py-4">

                        <table class="table table-flush" id="expense1">
                            <thead>
                                <tr>
                                    <th>Account Head</th>
                                    <th>Account Sub Head1</th>
                                    <th>Account Sub Head2</th>
                                    <th>Account Sub Head3</th>
                                    <th>Account Sub Head4</th>
                                    <th>Account Sub Head5</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody id="myTable">
                                <tr>
                                    <td>
                                        <div class="col-lg-12 error-msg">

                                            <select name="account_head" id="account_head" class="form-control valid frm">
                                                <option value="">Select Account Head</option>
                                                <option value="1" data-name="expence">Expense</option>
                                                <option value="2" data-name="fixedasset">Fixed Assets</option>
                                            </select>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="error-msg">
                                            <select name="sub_head1" id="sub_head1" class="form-control frm">

                                            </select>
                                        </div>
                                    </td>

                                    <td>
                                        <div class=" error-msg">
                                            <select name="sub_head2" id="sub_head2" class="form-control frm">

                                            </select>
                                        </div>
                                    </td>

                                    <td>
                                        <div class=" error-msg">
                                            <select name="sub_head3" id="sub_head3" class="form-control frm">

                                            </select>
                                        </div>
                                    </td>

                                    <td>
                                        <div class=" error-msg">
                                            <select name="sub_head4" id="sub_head4" class="form-control frm">

                                            </select>
                                        </div>
                                    </td>

                                    <td>
                                        <div class=" error-msg">
                                            <select name="sub_head5" id="sub_head5" class="form-control frm">

                                            </select>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" autocomplete="off" name="amount" class="form-control t_amount frm" />
                                        </div>
                                    </td>
                                    <td>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" autocomplete="off" name="description" id="description" class="form-control t_description frm" />
                                        </div>
                                    </td>
                                    <td>
                                      
                                             <?php 
                                             
                                                $created_at = session()->get('created_at');
                                                $formate_date = date("d/m/Y",strtotime($created_at));
                                                ?>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" autocomplete="off" name="adjdate" id="adjdate" value="{{ date('d/m/Y') }}" class="form-control gdate t_date frm"/>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="col-lg-12 error-msg">
                                            <button type="button" class="btn btn-primary ml-2" id="add_row"><i class="icon-add "></i></button>
                                        </div>
                                    </td>

                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <hr />
                    <div class=" form-group row">
                        <div class=" col-lg-1">
                        </div>
                        {{--<div class=" col-lg-5">
                            <button type="button" class="btn btn-primary ml-2" id="add_row"><i class="icon-add ">ADD</i></button>
                        </div>--}}
                        <div class="  col-lg-6 row">
                            <label class="col-form-label col-lg-4">Total Amount<sup>*</sup></label>
                            <div class="col-lg-6">
                                <input type="text" name="total_amount" id="total_amount" class="form-control" readonly />
                            </div>

                        </div>
                    </div>


                    <input type="hidden" name="is_od" id="is_od" class="is_od_value">
                    <div class="col-lg-12 mb-4">
                        <div class="text-center">
                            <button type="button" id="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@include('templates.branch.AdvancePayment.partials.adjestment_script')

@stop