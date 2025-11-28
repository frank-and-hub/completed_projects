<h6 class="card-title font-weight-semibold">Cheque Management | Cheque List</h6>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
 <tr>
                                <th>S/N</th>
                                    <th>Cheque Date</th>
                                    <th>Bank Name</th>
                                    <th>Account No.</th>
                                    <th>Cheque No</th> 
                                    <th>Is Used</th>
                                    <th>Status</th>
                                    <th>Delete Date</th> 
                                    <th>Cancel Date</th>                                
                                    <th>Cancel Remark</th>    
                            </tr>
</thead>
<tbody>
@foreach($data as $index => $row)  
<?php
                if($row->is_use==1)
                {
                    $use = 'Yes';
                }
                else
                {
                    $use = 'No';     
                } 
                $status = 'New';
                if($row->status==1)
                {
                    $status = 'New';
                }                
                if($row->status==2)
                {
                    $status = 'Pending';
                }
                if($row->status==3)
                {
                    $status = 'cleared';
                }
                if($row->status==4)
                {
                    $status = 'Canceled & Re-issued';
                }
                if($row->status==0)
                {
                    $status = 'Deleted';
                }
                $cheque_delete_date='';
                if($row->cheque_delete_date!= NULL  )
                {
                    $cheque_delete_date =  date("d/m/Y", strtotime($row->cheque_delete_date));
                }
                $cheque_cancel_date='';
                if($row->cheque_cancel_date!= NULL)
                {
                    $cheque_cancel_date =  date("d/m/Y", strtotime($row->cheque_cancel_date));
                }
?>
  <tr>
    <td>{{ $index+1 }}</td>
    <td>{{ date("d/m/Y  ", strtotime($row->cheque_create_date)) }}</td>
    
    <td>{{ $row['samrddhBank']->bank_name }}</td>
    <td>{{ $row['samrddhAccount']->account_no }} </td>
    <td>{{ $row->cheque_no }}</td>
    <td>{{ $use }} </td>
    <td>{{ $status }} </td>
    <td>{{ $cheque_delete_date }} </td>
    <td>{{ $cheque_cancel_date }}</td>
    <td>{{ $row->remark_cancel }} </td>
    
    
    
  </tr>
@endforeach
</tbody>
</table>
