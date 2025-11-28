<h5 class="mb-0 text-dark">Associate Commission Detail - {{ $member['associate_no']}}({{$member['first_name']}} {{$member['last_name']}}) - {{ getCarderName($member['current_carder_id']) }}</h5>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
  <tr>
    <th>S/N</th>
    <th>Date</th>
                                <th>Investment Account</th>
                                <th>Plan Name</th>
                                <th>Total Amount</th>
                                <th>Commission Amount</th>                         
                                <th>Percentage</th>
                                <th>Carder Name</th>
                                <th>EMI No</th>
                                <th>Commission Type</th> 
                                <th>Associate Exists</th>
                                <th>Payment Type</th>
                                <th>Payment Distribute</th>   </tr>
</thead>
<tbody>
@foreach($data as $index => $row)  
  <tr>
    <td>{{ $index+1 }}</td>
    <td>{{ date("d/m/Y", strtotime($row->created_at)) }}</td>
    <td>{{ $row['investment']->account_number }}</td>
    <td>{{ transactionPlanName($row->type_id) }}</td>
    <td>{{ $row->total_amount }} </td>
    <td>{{ $row->commission_amount }} </td>
    <td>{{ $row->percentage }} </td>
    <td>{{ getCarderName($row->carder_id) }} </td>
    <td><?php $get_plan = $row['investment']->plan_id;
              if($get_plan==7)
              {
                if($row->month>1)
                {
                  $emi_no=$row->month.' Days';
                }
                else
                {
                  $emi_no=$row->month.' Day';
                }
              }
              else
              {
                if($row->month>1)
                {
                  $emi_no=$row->month.' Months';
                }
                else
                {
                  $emi_no=$row->month.' Month';
                }
              }
              ?>{{ $emi_no }} </td>
    <td>
      @if($row->commission_type==0)
        Self
      @else
        Team Member
      @endif 
    </td>

    <td>
      @if($row->associate_exist==0)
        Yes
      @else
        No
      @endif 
    </td>

    <td><?php
    if($row->pay_type==1){
                $pay_type = 'OverDue';
              }elseif($row->pay_type==2){
                $pay_type = 'Due Date';
              }
              else
              {
                $pay_type = 'Advance';

              }?>
      {{ $pay_type}}
    </td>
    <td>
      @if($row->is_distribute==1)
        Yes
      @else
        No
      @endif 
    </td>

  </tr>
@endforeach
</tbody>
</table>
