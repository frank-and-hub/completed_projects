<h5 class="mb-0 text-dark">Associate Commission Detail - {{ $member['associate_no']}}({{$member['first_name']}} {{$member['last_name']}}) - {{ getCarderName($member['current_carder_id']) }}</h5>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
  <tr>
    <th>S/N</th>
                                <th>Date</th>
                                <th>Loan Account</th>
                                <th>Loan Type </th>
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
$account='N/A';
                  $loan_type='N/A';
                if($row->type==4 || $row->type==6)
                {
                  $loan_detail=getLoanDetail($row->type_id);
                  $account=$loan_detail->account_number;
                  $loan_type='Loan';
                  if($loan_detail->loan_type==1)
                  {
                     $loan_type='Personal Loan';
                  }
                  if($loan_detail->loan_type==2)
                  {
                     $loan_type='Staff Loan';
                  }
                  if($loan_detail->loan_type==4)
                  {
                     $loan_type='Loan Against Investment Plan(DL)';
                  }
                }
                if($row->type==7 || $row->type==8)
                {
                  $loan_detail=getGroupLoanDetail($row->type_id);
                  $account=$loan_detail->account_number;
                  $loan_type='Group Loan';
                }

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
    <td>{{ $account}}</td>
    <td>{{ $loan_type}}</td>
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
