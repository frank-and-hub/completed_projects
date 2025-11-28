<h3 class="card-title font-weight-semibold">Employee Transfer List
</h3>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
//echo "<pre>";print_r($data);die;
?>


<thead>
 <tr>
                                <th>S/N</th>
                                    <th>Apply Date</th> 
                                    <th>Employee Code</th>
                                    <th>Employee Name</th>
                                    <th>Designation</th>
                                    <th>Category</th> 
                                    <th>BR Name</th>
                                    <th>BR Code</th>
                                    <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th>
                                    <th>Recommendation Employee Name</th>  
                                    <th>Transferred Date</th> 
                                    <th>Transferred To BR Name</th>
                                    <th>Transferred To BR Code</th>
                                    <th>Transferred To SO Name</th>
                                    <th>Transferred To RO Name</th>
                                    <th>Transferred To ZO Name</th>
                                    <th>Transferred Designation</th>
                                    <th>Transferred Category</th>
                                    <th>Recommendation Employee Name</th> 
                                    <th>File Link</th>  
                                    <th>Created</th>   
                            </tr>
</thead>
<tbody>
@foreach($data as $index => $row)  
<?php
 $category = '';
                if($row->category==1)
                {
                    $category = 'On-rolled';
                }
                if($row->category==2)
                {
                   $category = 'Contract'; 
                } 
$old_category = '';
                if($row->old_category==1)
                {
                    $old_category = 'On-rolled';
                }
                if($row->old_category==2)
                {
                   $old_category = 'Contract'; 
                }
               
?>
  <tr>
    <td>{{ $index+1 }}</td>
    <td>{{ date("d/m/Y", strtotime($row->apply_date)) }}</td>
    <td>{{ $row['transferEmployee']->employee_code }} </td>
    <td>{{ $row['transferEmployee']->employee_name }}</td>
    <td>{{ getDesignationData('designation_name',$row->old_designation_id)->designation_name }} </td>
    <td>{{ $old_category }} </td>
    <td>{{ $row['transferBranchOld']->name }}</td>

    <td>{{ $row['transferBranchOld']->branch_code }}</td>
    <td>{{ $row['transferBranchOld']->sector }}</td>
    <td>{{ $row['transferBranchOld']->regan }}</td>
    <td>{{ $row['transferBranchOld']->zone }}</td>

    <td>{{ $row->old_recommendation_name }} </td>
    <td>{{ date("d/m/Y", strtotime($row->transfer_date)) }} </td>
    <td>{{ $row['transferBranch']->name }}</td>

    <td>{{ $row['transferBranch']->branch_code }}</td>
    <td>{{ $row['transferBranch']->sector }}</td>
    <td>{{ $row['transferBranch']->regan }}</td>
    <td>{{ $row['transferBranch']->zone }}</td>
    
    
    <td>{{ getDesignationData('designation_name',$row->designation_id)->designation_name }} </td>
    <td>{{ $category }} </td>
    <td>{{ $row->recommendation_name }} </td>
    <td><a href="{{url('/')}}/asset/employee/transfer/{{$row->file}}"  target='_blank'  >{{$row->file}} </a></td>
    <td>{{ date("d/m/Y h:i:s a", strtotime($row->created_at)) }} </td>
    
    
    
  </tr>
@endforeach
</tbody>
</table>
