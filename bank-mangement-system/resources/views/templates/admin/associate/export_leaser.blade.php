<h6 class="card-title font-weight-semibold">Commission Transfer - Ledger List</h6>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
 <tr>
                                <th>S/N</th>
                                <th>Start Date Time</th>
                                <th>End Date Time</th>
                                <th>Total Amt. </th>
                                <th>Total Transfer Amt. </th>
                                <th>Total Refund Amt. </th>
                                <th>Total Fuel Transfer Amt. </th> 
                                <th>Total Fuel Refund </th>
                                <th>Status</th>
                                <th>Created</th>  
                            </tr>
</thead>
<tbody>
@foreach($data as $index => $row)  
  <tr>
    <td>{{ $index+1 }}</td>
    <td>{{ date("d/m/Y h:i:s a", strtotime($row->start_date)) }}</td>
    <td>{{ date("d/m/Y h:i:s a", strtotime($row->end_date)) }}</td>
    <td>{{ $row->total_amount }}</td>
    <td>{{ $row->ledger_amount }} </td>
    <td>{{ $row->credit_amount }}</td>
    <td>{{ $row->total_fuel }} </td>
    <td>{{ $row->credit_fuel }} </td>
    <?php 
if($row->status==1)
                {
                  $ledgerAmount = 0; 
                }
                else{
                    $ledgerAmount = $row->total_amount-$row->credit_amount; 
                }
    ?>
    
    <td>
      @if($row->status==1)
        Transferred
      @else
        Deleted
      @endif
    </td>
    <td>{{ date("d/m/Y h:i:s a", strtotime($row->created_at)) }}</td>
    
    
  </tr>
@endforeach
</tbody>
</table>
