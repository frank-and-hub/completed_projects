<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
  <tr>
    <th>S/N</th>
    <th>BR Name</th>
            <th>BR Code</th>
            <th>SO Name</th>
            <th>RO Name</th>
            <th>ZO Name</th>
    <th>Amount</th>
    <th>Transaction Date</th>
    <th>Cheque Date</th>
    <th>Cheque Number</th> 
    <th>Bank Name</th>
    <th>Branch Name</th>
    <th>Status</th> 
  </tr>
</thead>
<tbody>
@foreach($data as $index => $value)  
  <tr>
    <?php
      foreach ($value['investmentPayment'] as $key => $investmentValue) {
        $cheque_number = $investmentValue->cheque_number;
        $bank_name = $investmentValue->branch_name;
        $branch_name = $investmentValue->bank_name;
        $cheque_date = date("d/m/Y", strtotime($investmentValue->cheque_date));

        $transaction_date = date("d/m/Y", strtotime(convertDate($investmentValue->created_at))); 
        if($investmentValue->status==0)
        {
            $status = 'Approve';
        }
        elseif($investmentValue->status==1)
        {
            $status = 'Unapprove';
        }
      }
    ?>
    <td>{{ $index+1 }}</td>
    <td  >{{ $value['branch']->name }}</td>
    <td  >{{ $value['branch']->branch_code }}</td>
    <td  >{{ $value['branch']->sector }}</td>
    <td  >{{ $value['branch']->regan }}</td>
    <td  >{{ $value['branch']->zone }}</td>
    <td>{{ $value->deposite_amount }}</td>
    <td>{{ $transaction_date }}</td>
    <td>{{ $cheque_date }}</td>
    <td>{{ $cheque_number }}</td>
    <td>{{ $bank_name }}</td>
    <td>{{ $branch_name }}</td>   
    <td>{{ $status }}</td>
  </tr>
@endforeach
</tbody>
</table>
