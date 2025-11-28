@extends('templates.admin.master')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">All Active Branches</h6>
                </div>
                <form action="{!! route('admin.branchlimitupdate') !!}" method="post" enctype="multipart/form-data" id="filter" name="filter">
                    @csrf
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">S.NO.</th>
                                    <th scope="col">Branch</th>
                                    <th scope="col">Old Cash in Hand</th>
                                    <th scope="col">New Cash in Hand</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($branch))
                                @foreach($branch as $key => $branchName)
                                <tr>
                                    <td>
                                        {{$key+1}}
                                    </td>
                                <input type="hidden" name="branch_id[]" id="branch_id" value="{{$branchName->id}}">                                       
                                       
                                    <td>
                                        {{$branchName->name}}
                                    </td>
                                    <td>
                                        <input type="text" name="cash_in_hand_amount{{$key}}]" id="cash_in_hand_amount_{{$key}}" class="form-control cash_in_hand_amount" style="width: 100px" readonly value="{{ number_format((float)$branchName->cash_in_hand, 2, '.', '') }}">
                                    </td>
                                    <td>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="total_amount[{{$key}}]" id="total_amount_[{{$key}}]" class="form-control  total_amount" style="width:80%" pattern="[0-9 .]+" minlength="1" >
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 text-center">
                             
                                    <button type="submit" class=" btn bg-dark legitRipple">Submit</button>
                                </div>
                            </div>
                        </div>
                </form>
            </div>
        </div>
    </div>
</div>
@include('templates.admin.branch.partials.branch-cash-limit-script')
@stop