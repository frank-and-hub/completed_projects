
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
    <thead>
    <tr>
    <th colspan="8"><h3> Salary Ledger List</h3></th>
    </tr>
    </thead>
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
  <tr>
    <th >S.No</th>
                                <th >Month</th>
                                <th >Year</th>
                                <th >Total Amount</th>
                                <th >Transferred  Amount</th>
                                <th >Pending Amount</th> 
                                <th >NEFT Charge</th> 

                                <th >Status</th>
                                <th >Created</th> 
  </tr>
</thead>
<tbody>
@foreach($data as $index => $row)  
<?php
$pending=$row->total_amount-$row->transfer_amount;
$transfer_charge = number_format((float)$pending, 2, '.', '');
$status = 'Pending ';
                if($row->status == 1){
                    $status = 'Transferred';
                }
                if($row->status == 2){
                    $status = 'Partial Transfer';
                }
                $neft=\App\Models\EmployeeSalary::where('leaser_id',$row->id)->sum('neft_charge');
                $transfer_charge = number_format((float)$neft, 2, '.', '');
?>
  <tr>
    <td>{{ $index+1 }}</td>
    <td>{{ $row->month_name }}</td>
    <td>{{ $row->year }}</td>
    <td>{{ number_format((float)$row->total_amount, 2, '.', '') }}</td>
    <td>{{ number_format((float)$row->transfer_amount, 2, '.', '') }}</td>
    <td>{{ $transfer_charge }}</td> 
    <td>{{ $transfer_charge }}</td>  
    <td>{{ $status }}</td>  
    <td>{{ date("d/m/Y", strtotime(convertDate($row->created_at))) }}</td>  
  </tr>

@endforeach
</tbody>
</table>
