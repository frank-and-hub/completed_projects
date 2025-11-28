<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
    <thead>
    <tr>
        <th>#</th>
        <th> Date</th>
        <th> Member ID</th>
        <th> Member Name</th>
        <th> Total Amount</th>
        <th> Commission Amount</th>
        <th> Percentage</th>
        <th> Carder Name</th>
        <th>EMI No</th>
        <th>Commission Type</th>
        <th>Associate Exists</th>
        <th>Payment Type</th>
        <th>Payment Distribute</th>
    </tr>
    </thead>
<tbody>
@foreach($data as $index => $value)  
<?php
$member_name = getMemberDetails($value->member_id);
?>
  <tr>
    <td>{{ $index+1 }}</td>
    <td>{{ date("d/m/Y", strtotime($value->created_at)) }}</td>
   {{-- <td>{{ $value->day_book_id }}</td>--}}
    <td>{{ getApplicantid($value->member_id) }}</td>
    <td>{{ $member_name->first_name }}</td>
    <td>{{ $value->total_amount }}</td>
    <td>{{ $value->commission_amount }}</td>
    <td>{{ $value->percentage }}</td>
    <td>@if($value->type==5) Collection Charge @else {{ getCarderName($value->carder_id) }} @endif</td>
    <td><?php $get_plan =  getInvestmentDetails($value->type_id);
              if($get_plan->plan_id==7)
              {
                if($value->month>1)
                {
                  $emi_no=$value->month.' Days';
                }
                else
                {
                  $emi_no=$value->month.' Day';
                }
              }
              else
              {
                if($value->month>1)
                {
                  $emi_no=$value->month.' Months';
                }
                else
                {
                  $emi_no=$value->month.' Month';
                }
              }
              ?>{{ $emi_no }} </td>
    <td>
      @if($value->commission_type==0)
        Self
      @else
        Team Member
      @endif 
    </td>

    <td>
      @if($value->associate_exist==0)
        Yes
      @else
        No
      @endif 
    </td>

    <td><?php
    if($value->pay_type==1){
                $pay_type = 'OverDue';
              }elseif($value->pay_type==2){
                $pay_type = 'Due Date';
              }
              else
              {
                $pay_type = 'Advance';

              }?>
      {{ $pay_type}}
    </td>
    <td>
      @if($value->is_distribute==1)
        Yes
      @else
        No
      @endif 
    </td>
  </tr>
@endforeach
</tbody>
</table>
