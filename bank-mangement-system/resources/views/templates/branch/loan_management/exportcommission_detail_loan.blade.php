<?php

                                if($loan->loan_type == 1){
                    $plan_name = 'Personal Loan';
                }elseif($loan->loan_type == 2){
                    $plan_name = 'Staff Loan(SL)';
                }elseif($loan->loan_type == 3){
                    $plan_name = 'Group Loan';        
                }elseif($loan->loan_type == 4){
                    $plan_name = 'Loan against Investment plan(DL) ';    
                }

                                ?>
<h5 class="mb-0 text-dark"><h5 class="mb-0 text-dark">Loan -- {{$loan->account_number}}({{$plan_name}})</h5></h5>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
  <tr>
    <th>S/N</th>
                                <th>Date</th>
                                <th>Associate Name</th>
                                <th>Associate Code</th>
                                <th>Total Amount</th>
                                <th>Commission Amount</th>                         
                                <th>Percentage</th>
                                <th>Carder Name</th> 
                                <th>Commission Type</th>  
                                <th>Payment Type</th>
                                <th>Payment Distribute</th> 
                            </tr>
</thead>
<tbody>
@foreach($data as $index => $row)  

<?php
 
if($row->type==4)
                {
                    $commission_for='Loan Commission';
                }
                if($row->type==6)
                {
                    $commission_for='Loan Collection';
                }
                if($row->type==7)
                {
                    $commission_for='Group Loan Commission';
                }
                if($row->type==8)
                {
                    $commission_for='Group Loan Collection';
                }
?>
  <tr>
    <td>{{ $index+1 }}</td>
    <td>{{ date("d/m/Y", strtotime($row->created_at)) }}</td>
    <td>{{ getSeniorData($row->member_id,'first_name').' '.getSeniorData($row->member_id,'last_name')}}</td>
    <td>{{ getSeniorData($row->member_id,'associate_no') }}</td>
    <td>{{ $row->total_amount }} </td>
    <td>{{ $row->commission_amount }} </td>
    <td>{{ $row->percentage }} </td>
    <td> {{ getCarderName($row->carder_id) }}</td>
    <td>{{ $commission_for }}</td> 

    <td><?php
     $pay_type='';
              if($row->pay_type==4){
                $pay_type = 'Loan Emi';
              }elseif($row->pay_type==5){
                $pay_type = 'Loan Panelty';
              }

    ?>
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
