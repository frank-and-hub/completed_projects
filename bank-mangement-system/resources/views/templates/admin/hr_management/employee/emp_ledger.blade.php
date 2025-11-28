@extends('templates.admin.master')



@section('content')

    <div class="content">

        <div class="row">

            

            <div class="col-md-12">

                <div class="card">

                    <div class="card-header header-elements-inline">

                        <h6 class="card-title font-weight-semibold">{{ $detail['company']->name}} - {{$detail->employee_name}}  - Ledger</h6>
                         <!--  <h6 class=" font-weight-semibold " style="float: right;margin-left:700px;">Current Balance- &#X20B9;{{ number_format((float)$detail->current_balance, 2, '.', '')}}</h6> -->
                    
               

                        <div class="">

                         <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">

                        @csrf



                        <input type="hidden" name="liability" id="liability" value="{{$emp}}"> 

                        </form>

                            <!--<button type="button" class="btn bg-dark legitRipple export_report ml-2" data-extension="0" style="float: right;">Export xslx</button>-->

                            

                        </div>

                    </div>

                    <table class="table datatable-show-all" id="emp_ledger">

                        <thead>

                            <tr>                                    

                                        <th >S.No</th>  

                                        <th >Date</th> 

                                        <th >Description</th>

                                        <th >Reference No</th>  

                                        <th >Amount</th>   

                                        <th >Payment Type</th>


                                        <th >Payment Mode</th>  



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

@include('templates.admin.hr_management.employee.emp_ledger_script')

@endsection

