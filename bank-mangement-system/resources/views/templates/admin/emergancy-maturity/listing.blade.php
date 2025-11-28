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

        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">

            @csrf

            <div class="row">
            {{--@php
                            $dropDown = $company;
                            $filedTitle = 'Company';
                            $name = 'company_id';
                            @endphp
            @include('templates.GlobalTempletes.new_role_type',['dropDown'=>$dropDown,'filedTitle'=>$filedTitle,'name'=>$name,'value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch_id','apply_col_md'=>true,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch','selectedCompany'=>1])--}}
            @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true])
                <div class="col-md-12">

                    <div class="form-group row">

                        <div class="col-lg-12 text-right">

                            <input type="hidden" name="is_search" id="is_search" value="no">

                            <button type="button" class=" btn bg-dark legitRipple"
                                onClick="searchForm()">Submit</button>

                            <button type="button" class="btn btn-gray legitRipple" id="reset_form"
                                onClick="resetForm()">Reset </button>

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
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Emergency Maturity</h6>
                        <div class="">
                            <form action="{{route('admin.demandadvice.approveemergancy')}}" method="post" name="transfer-emergancy-maturity" id="transfer-emergancy-maturity">
                                @csrf
                                <input type="hidden" name="selected_records" id="selected_records">
                                <input type="hidden" name="pending_records" id="pending_records">
                                <button type="submit" class="btn bg-dark transfer-emergancy-button">Transfer<i class="icon-paperplane ml-2"></i></button>
                            </form>
                        </div>
                        
                    </div>
                    <table class="table datatable-show-all" id="emergancy-maturity-table">
                        <thead>
                        <tr>
                                <th >S/N</th>
                                <th ><input type="checkbox" name="select_all" id="select_all"></th>
                                <th >Company Name</th>
                                <th >Branch Name</th>
                                <th >Opening Date</th>
                                <th >Account Number</th>

                                <th >Plan Name</th>
                                <th >Tenure</th>
                                <th >Customer Id</th>
                                <th >Member Id</th>
                                <th >Account holder name</th>
                                <th >Deposit Amount</th>
                                <th >TDS Amount</th>
                                <th >Maturity Amount Till Date</th>
                                <th >Maturity Amount Payable</th>
                                <th >Final Amount</th>
                                <th >Mobile Number</th>
                                <th >SSB Account</th>
                                <th >Bank Name</th>
                                <th >Bank A/C No.</th>
                                <th >IFSC</th>
                                <th >Letter's Photo</th>
                                <th >Payment Date</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>
@include('templates.admin.emergancy-maturity.partials.script')
@endsection
