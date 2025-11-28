@extends('layouts/branch.dashboard')
@section('content')
<style type="text/css">
    @media print{
        .headId,.auth,.des{
            display: none;
        }
        .box2,.c_logo,.c_name,.c_address,.date,thead{
            display: none;
        }
        label .v_no,.m_id,.name,.ac_no {
             display: none;
        }
        .auth_main{
            border: none;
            margin-top: 80px;
            text-align: center;
        }

        
    } 

</style>
<div class="content">
    <div class="row" id="advice"> 
    <table width="90%" align="center" border="0" cellspacing="0" cellpadding="0" style="font-family:Arial, Helvetica, sans-serif;">
  <tr>
    <td>
     <table width="100%" border="0" cellspacing="0" cellpadding="15" style="background:#fff; border:#000000 solid 2px;">
      <tr>
        <td colspan="2" style=" text-align:center; padding:20px 0px 40px 0px; font-size:20px; font-weight:600">Expense Voucher</td>
      </tr>
      <tr>
        <td width="70%" style="text-align:center;">
         <img src="http://qa.samraddhbestwin.com/asset/images/logo_1587603901.png" width="200" alt="" />
         <h3 style="margin:0px; padding:10px 0px;"> @if(isset($bill_status['companyName']->name)) {{$bill_status['companyName']->name}} @else N/A @endif</h3>
         <p >{{$bill_status['companyName']->address ?? N/A}} Info.: Phone No.: +91 {{$bill_status['companyName']->mobile_no ?? N/A}} | Web: www.samraddhbestwin.com | Mail Us: {{$bill_status['companyName']->email ?? N/A}}</p>
         <div style="padding:15px; border:#000000 solid 1px;"><strong>Authorised Center Name And Code: {{getBranchDetail($branch_id)->name}} ({{getBranchDetail($branch_id)->branch_code}})</strong></div>
        </td>
        <td>
         <div class="" style="padding:0 0 0 50px;">
          <p><label>Bill.No:</label> <span>{{ $bill_no }}</span></p>
          <p><label>Bill Date:</label> <span>@if($pr_data) {{ date("d/m/Y", strtotime(convertDate($pr_data[0]['bill_date']))) }} @else {{ 'N/A' }} @endif</span></p>
         </div>
         
         <div style="padding:0 0 0 30px;"> 	
 	
         <table width="100%" border="1" cellspacing="0" cellpadding="10">
          <tr>
            <td>Party Name:</td>
            <td>@if($party_name){{ $party_name }}@else {{ 'N/A' }} @endif</td>
          </tr>
          <tr>
            <td>Bill Amount:</td>
            <td>{{ number_format((float)($total_amount), 2, '.', '') }}</td>
          </tr>
       
        </table>
        </div>
        </td>
      </tr>
    </table>
        <table width="100%" border="1" cellspacing="0" cellpadding="10">
         
          <tr>
            <td>Amount</td> 	 	 	
            <td>Bill Date</td>
            <td>Account Head</td>
            <td>Sub Head1</td>
            <td>Sub Head2</td>
            <td>Particulars</td>
            <td>Rupees In Words</td>
            
          </tr>
          
          <tbody>
          
            @foreach($pr_data as $row)
                <tr>
                    <td>{{$row['amount']}}</td>
                    <td >@if($row['bill_date']) {{$row['bill_date']}} @else {{ 'N/A' }} @endif</td>
                    <td>@if($row['account_head']) {{$row['account_head']}} @else {{ 'N/A' }} @endif</td>
                    <td>@if($row['sub_head1']) {{$row['sub_head1']}} @else {{ 'N/A' }} @endif</td> 
                    <td>@if($row['sub_head2']) {{$row['sub_head2']}} @else {{ 'N/A' }} @endif</td> 
                    <td>@if($row['particular']) {{$row['particular']}} @else {{ 'N/A' }} @endif</td>
                    <td>{{ amountINWord($row['amount']) }} Only</td>
                    
                </tr>
                @endforeach
         </tbody> 
        </table>
    
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding:20px 0 0 0; margin: 30px 0 30px 0;">
          <tr>
            <td>
             <table width="100%" border="0" cellspacing="0" cellpadding="10" style="border:#000000 solid 1px;">
              <tr>
                <td style="border-bottom:#000000 solid 1px;">Full Signature Of Authorised Person:</td>
                <td style="border-bottom:#000000 solid 1px;">Full Signature Of Cashier:</td>
              </tr>
              <tr>
                <td>Name Of Code Of Authorised Person: </td>
                <td>Name Of Emp Code Of Cashier:</td>
              </tr>
            </table>
            </td>
            <td style="padding:0 0 0 20px;">
            <table width="100%" border="0" cellspacing="0" cellpadding="10" style="border:#000000 solid 1px;">
              <tr>
                <td style="border-bottom:#000000 solid 1px;">RevenueStamp:</td>
                <td style="border-bottom:#000000 solid 1px;">&nbsp;</td>
              </tr>
              <tr>
                <td>Full Signature Of Applicant:</td>
                <td>&nbsp;</td>
              </tr>
            </table></td>
          </tr>
        </table>
        </td>
      </tr>
    </table>
    </div>
    

    
        <div class="row print-section">
                <div class="col-lg-12">
                  <div class="card bg-white" >            
                    <div class="card-body ">
                        <div class="row">
                            <div class="col-lg-12 text-center">
                                <button type="submit" class="btn btn-primary print-advice" onclick="printDiv('advice');" data-id="asdsa"> Print<i class="icon-paperplane ml-2" ></i></button>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
   
</div>
@stop
@section('script')
    @include('templates.branch.expense.partial.script')
@stop