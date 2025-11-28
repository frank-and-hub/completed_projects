<h6 class="card-title font-weight-semibold">Cheque Management | Cheque List</h6>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
 <tr>
                                
                                    <th>S/N</th>
                                    <th>COMPANY NAME</th>
                                    <th>BR Name</th>
                                    <th>BR Code</th>
                                    <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th>
                                    <th>Cheque Date </th>
                                    <th>Cheque Number</th>
                                    <th>Cheque Bank Name</th>
                                    <th>Cheque Bank Branch Name</th>
                                    <th>Cheque Account No.</th>
                                    <th>Cheque Account Holder Name</th>
                                    <th>Amount</th> 
                                    <th>Deposit  Date </th> 
                                    <th>Deposit Bank Name</th>
                                    <th>Deposit Account No.</th>
                                    <th>Clearing Date</th>
                                    <th>Status</th>
                                    <th>Remark</th>   
                            </tr>
</thead>
<tbody>
@foreach($data as $index => $row)  
<?php
                if($row->clearing_date)
                {
                    $clearing_date = date("d/m/Y", strtotime($row->clearing_date));
                }else{
                    $clearing_date = 'N/A';
                }

                $status = 'New';
                if($row->status==1)
                {
                    $status = 'Pending';
                }                
                if($row->status==2)
                {
                    $status = 'Apporved';
                }
                if($row->status==3)
                {
                    $status = 'cleared';
                }
                if($row->status==0)
                {
                    $status = 'Deleted';
                }
?>
  <tr>
    <td>{{ $index+1 }}</td> 
    <td  >{{ $row['receivedBranch']->name }}</td>
    <td  >{{ $row['receivedBranch']->branch_code }}</td>
    <td  >{{ $row['receivedBranch']->sector }}</td>
    <td  >{{ $row['receivedBranch']->regan }}</td>
    <td  >{{ $row['receivedBranch']->zone }}</td>
    <td>{{ date("d/m/Y  ", strtotime($row->cheque_create_date)) }}</td>
    <td>{{ $row->cheque_no }} </td>
    <td>{{ $row->bank_name }}</td>
    <td>{{ $row->branch_name }}</td>
    <td>{{ $row->cheque_account_no }}</td>
    <td>{{ $row->account_holder_name }} </td>
    <td>{{ $row->amount }}</td>
    <td>{{ date("d/m/Y  ", strtotime($row->cheque_deposit_date)) }}</td>    
    <td>{{ $row['receivedBank']->bank_name }} </td>
    <td>{{ $row['receivedAccount']->account_no }}</td>
    <td>{{ $clearing_date }} </td>
    <td>{{ $status }} </td> 
    <td>{{ $row->remark }} </td> 
    
    
    
  </tr>
@endforeach
</tbody>
</table>
