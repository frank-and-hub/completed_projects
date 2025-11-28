<h6 class="card-title font-weight-semibold">Investment Transaction</h6>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
 <tr>
                                <th style="font-weight: bold;">S/N</th>
                                    <th style="font-weight: bold;">BR Name</th>
                                    <th style="font-weight: bold;">BR Code</th>
                                    <th style="font-weight: bold;">SO Name</th>
                                    <th style="font-weight: bold;">RO Name</th>
                                    <th style="font-weight: bold;">ZO Name</th> 
                                    <th style="font-weight: bold;">Member Id </th>                                    
                                    <th style="font-weight: bold;">Member Name</th>
                                    <th style="font-weight: bold;">Account No </th>
                                    <th style="font-weight: bold;">Plan Name </th>
                                     <th style="font-weight: bold;">Tag </th>
                                    <th style="font-weight: bold;">Amount</th>
                                    <th style="font-weight: bold;">Payment Mode</th>
                                    <th style="font-weight: bold;">Payment Type</th> 
                                    <th style="font-weight: bold;">Is Eli</th> 
                                    <th style="font-weight: bold;">Created</th>  
                            </tr>
</thead>
<tbody>
@foreach($data as $index => $row)  
<?php
                $account_number = $row['investment']->account_number;
                if(str_starts_with($account_number,'R-'))
                {
                    $tag = 'R';
                }
                else{
                   $tag = 'N'; 
                }
                $member_id=getSeniorData($row['investment']->member_id,'member_id');
                $name=getSeniorData($row['investment']->member_id,'first_name').' '.getSeniorData($row['investment']->member_id,'last_name'); 
                
                $amount= number_format((float)$row->deposit, 2, '.', '');  
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
                    $amount= number_format((float)$row->withdrawal, 2, '.', ''); 
                }  
                $is_eli='No';
                if($row->is_eli==1)
                {
                    $is_eli='Yes'; 
                } 
?>
  <tr>
    <td>{{ $index+1 }}</td> 
    <td  >{{ $row['dbranch']->name }}</td>
    <td  >{{ $row['dbranch']->branch_code }}</td>
    <td  >{{ $row['dbranch']->sector }}</td>
    <td  >{{ $row['dbranch']->regan }}</td>
    <td  >{{ $row['dbranch']->zone }}</td> 
    <td>{{ $member_id }}</td>
    <td>{{ $name }}</td>
    <td>{{ $row['investment']->account_number }}</td>
    <td>{{ getPlanDetail($row['investment']->plan_id)->name }} </td>
    <td>{{$tag}}</td>
    <td>{{ $amount}}</td>    
    <td>{{ $p_mode }} </td>
    <td>{{ $p_type }}</td>
    <td>{{ $is_eli }} </td> 
    <td>{{ date("d/m/Y", strtotime($row->created_at))}} </td> 
    
    
    
  </tr>
@endforeach
</tbody>
</table>
