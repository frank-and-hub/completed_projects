@extends('layouts/branch.dashboard')

@section('content')
<style type="text/css">
  #table_amount{width: 100%}
  #print_recipt_table{width: 100%}
</style>
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
              <h3 class="card-title mb-3 text-center title_recepit" >Receipt Detail</h3>
              
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
                    <td>Member Id Number:</td>
                    <td>{{ $recipt->memberReceipt->member_id }}</td>
                  </tr>
                  <tr>
                    <td>Associate Id Number :</td>
                    <td>{{ $recipt->memberReceipt->associate_no }}</td>
                  </tr>
                  <tr>
                    <td>Name :</td>
                    <td>{{ $recipt->memberReceipt->first_name }} @if(!is_null($recipt->memberReceipt->last_name)) {{ $recipt->memberReceipt->last_name }} @endif</td>
                  </tr>
                  <tr>
                    <td>Address :</td>
                    <td>{{ $recipt->memberReceipt->address }} </td>
                  </tr>
                  
                  
                  @if($recipt->memberReceipt->rd_account!='' )
                  
                  <tr>
                    <td>RD Account Number :</td>
                    <td>{{ $recipt->memberReceipt->rd_account }}</td>
                  </tr>
                  <tr>
                    <td>RD Tenure  :</td>
                    <td>{{ getInvestmentAccount($recipt->memberReceipt->id,$recipt->memberReceipt->rd_account)->tenure*12 }} Months</td>
                  </tr>
                  @endif
                  
                  @if($recipt->memberReceipt->ssb_account!='' || !is_null($recipt->memberReceipt->ssb_account))
                  <tr>
                    <td>SSB Account Number :</td>
                    <td>{{ $recipt->memberReceipt->ssb_account }}</td>
                  </tr>
                  @endif
                  
                  <tr>
                    <td>Deposit Amount  :</td>
                    @if($recipt->memberReceipt->ssb_account!='' && $recipt->memberReceipt->rd_account)
                    <td>{{ number_format($total_amount, 2, '.', ',') }}  <img src="{{url('/')}}/asset/images/rs.png" width="7"></td>
                    @else
                    @if($ssb_recipt_amount)
                      <td>{{ number_format($ssb_recipt_amount->amount, 2, '.', ',') }}  <img src="{{url('/')}}/asset/images/rs.png" width="7"></td>
                    
                  @else
                  <td>{{ number_format($total_amount, 2, '.', ',') }}  <img src="{{url('/')}}/asset/images/rs.png" width="7"></td>
                  @endif

                    @endif
                  </tr>
                  
                  <tr>
                    <td>Mobile Number :</td>
                    <td>{{ $recipt->memberReceipt->mobile_no}}</td>
                  </tr>
                  <tr>
                    <td>Amount :</td>
                    <td>
                       <table  border="0" cellspacing="0" cellpadding="0" id="table_amount">
                       
                       
                       @foreach($recipt_amount as $val)
                          @if($val->type_label==1)
                            <tr>
                             <td>SSB Account Amount :</td>
                             <td>{{ number_format($val->amount, 2, '.', ',') }} <img src="{{url('/')}}/asset/images/rs.png" width="7"></td>
                            </tr>
                          @elseif($recipt->memberReceipt->rd_account)
                          <tr>
                            <td>RD Account Amount :</td>
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
                @if( in_array('Print Associate Receipt', auth()->user()->getPermissionNames()->toArray() ) )
                  <button type="submit" class="btn btn-primary avoid-this" onclick="printDiv('print_recipt');">Print<i class="icon-paperplane ml-2"></i></button>
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

@include('templates.branch.associate_management.partials.print_script')
@stop