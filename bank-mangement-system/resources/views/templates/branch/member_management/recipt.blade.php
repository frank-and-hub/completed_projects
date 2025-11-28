@extends('layouts/branch.dashboard')

@section('content')
<div class="loader" style="display: none;"></div>
<div class="container-fluid mt--6">
  <div class="content-wrapper">

      <div class="row">
        
        <div class="col-lg-12" > 
          @if (session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
              
              <span class="alert-text"><strong>Success!</strong> {{ session('success') }} </span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div>
          @endif
        </div>
        <div class="col-lg-12" id="print_recipt"> 
            
          <div class="card bg-white" >
            <div class="card-body">
              <h3 class="card-title mb-3 text-center">Receipt Detail</h3>
              <table width="80%" border="0" cellspacing="0" cellpadding="5" style="margin-left: 170px;margin-right: 70px">
                  <tr>
                    <td>Branch Name :</td>
                    <td>{{ $recipt->branchReceipt->name }}</td>
                  </tr>
                  <tr>
                    <td>Branch Code :</td>
                    <td>{{ $recipt->branchReceipt->branch_code }}</td>
                  </tr>
                  <tr>
                    <td>Date :</td>
                    <td>{{ date('d/m/Y', strtotime($recipt->created_at)) }}</td>
                  </tr>
                  <tr>
                    <td>Receipt Number :</td>
                    <td>{{ $recipt->id }}</td>
                  </tr>

                  <tr>
                    <td>Form Number :</td>
                    <td>{{ $recipt->memberReceipt->form_no }}</td>
                  </tr>

                  <tr>
                    <td>Member Id Number:</td>
                    <td>{{ $recipt->memberReceipt->member_id }}</td>
                  </tr> 
                  <tr>
                    <td>Name :</td>
                    <td>{{ $recipt->memberReceipt->first_name }} @if(!is_null($recipt->memberReceipt->last_name)) {{ $recipt->memberReceipt->last_name }} @endif</td>
                  </tr>
                  <tr>
                    <td>Address :</td>
                    <td>{{ $recipt->memberReceipt->address }} </td>
                  </tr>
                  
                  <tr>
                    <td>Mobile Number :</td>
                    <td>{{ $recipt->memberReceipt->mobile_no}}</td>
                  </tr>
                  <tr>
                    <td>Amount :</td>
                    <td>
                       <table width="100%" border="0" cellspacing="0" cellpadding="0">
                       
                       
                       @foreach($recipt_amount as $val)
                          @if($val->type_label==1)
                            <tr>
                             <td>Member ID Number(Fee)   :</td>
                             <td>{{ number_format($val->amount, 2, '.', ',') }} <img src="{{url('/')}}/asset/images/rs.png" width="7"></td>
                            </tr>
                          @else
                          <tr>
                            <td>ST. Charge :</td>
                            <td>{{ number_format($val->amount, 2, '.', ',') }} <img src="{{url('/')}}/asset/images/rs.png" width="7"></td>
                          </tr>
                          @endif
                        
                        @endforeach
                       
                        </table>

                    
                    </td>
                  </tr>
                  
                </table>           
          

            </div>
          </div>

        </div> 

        <div class="col-lg-12">
          <div class="card bg-white" >            
            <div class="card-body">
              <div class="text-center">

                @if( in_array('Print Member Receipt', auth()->user()->getPermissionNames()->toArray() ) )
                  <button type="submit" class="btn btn-primary" onclick="printDiv('print_recipt');">Print<i class="icon-paperplane ml-2"></i></button>
                @endif
              <a href="{!! route('branch.member_list') !!}" class="btn btn-secondary">Back</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>




@stop


@section('script')

@include('templates.branch.member_management.partials.script')
@stop