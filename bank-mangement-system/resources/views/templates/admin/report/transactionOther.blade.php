<h6 class="card-title font-weight-semibold">Investment Transaction</h6>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
 <tr>
    <th>S/N</th>
                                    <th style="font-weight: bold;">BR Name</th>
                                    <th style="font-weight: bold;">BR Code</th>
                                    <th style="font-weight: bold;">SO Name</th>
                                    <th style="font-weight: bold;">RO Name</th>
                                    <th style="font-weight: bold;">ZO Name</th> 
                                    <th style="font-weight: bold;">Member Id </th>                                    
                                    <th style="font-weight: bold;">Member Name</th>
                                    <th style="font-weight: bold;">Amount Type </th>
                                    <th style="font-weight: bold;">Amount</th>
                                    <th style="font-weight: bold;">Payment Mode</th>
                                    <th style="font-weight: bold;">Payment Type</th> 
                                    <th style="font-weight: bold;">Created</th> 
                             
                            </tr>
</thead>
<tbody>
@foreach($data as $index => $row)  
<?php
                $member_id=getSeniorData($row->member_id,'member_id');
                $name=getSeniorData($row->member_id,'first_name').' '.getSeniorData($row->member_id,'last_name'); 

                $amount= number_format((float)$row->amount, 2, '.', '');  
                $p_mode='Other';
                if($row->payment_mode==0)
                {
                    $p_mode='Cash';
                }
                if($row->payment_mode==1)
                {
                    $p_mode='Cheque';
                } 
                $p_type='CR';
                if($row->payment_type=='DR')
                {
                    $p_type='DR';
                   
                }  
                $account_number='Passbook Print';
                if($row->transaction_type==0)
                {
                     $account_number='Member Register';
                    if($row->amount==90 || $row->amount==90.00)
                    {

                     $account_number='Stn Charge';
                    }
                }
?>
  <tr>
    <td>{{ $index+1 }}</td> 
    <td >{{ getBranchDetail($row->branch_id)->name }}</td>
    <td >{{ getBranchDetail($row->branch_id)->branch_code }}</td>
    <td >{{ getBranchDetail($row->branch_id)->sector }}</td>
    <td >{{ getBranchDetail($row->branch_id)->regan }}</td>
    <td >{{ getBranchDetail($row->branch_id)->zone }}</td> 
    <td>{{ $member_id }}</td>
    <td>{{ $name }}</td>
    <td>{{ $account_number }}</td> 
    <td>{{ $amount}}</td>    
    <td>{{ $p_mode }} </td>
    <td>{{ $p_type }}</td> 
    <td>{{ date("d/m/Y", strtotime($row->created_at))}} </td>   
    
  </tr>
@endforeach
</tbody>
</table>
