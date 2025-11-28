<h3 class="card-title font-weight-semibold"> Employee  List
</h3>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
//echo "<pre>";print_r($data);die;
?>


<thead>
 <tr>
                                <th>S/N</th> 
                                    <th>Designation</th>
                                    <th>Category</th> 
                                    <th>BR Name</th>
                                    <th>BR Code</th>
                                    <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th>
                                    <th>Recommendation Employee Name</th> 
                                    <th>Employee Name</th>
                                    <th>Employee Code</th>
                                    <th>DOB</th>
                                    <th>Gender</th>
                                    <th>Number</th>
                                    <th>Email Id</th>
                                    <th>Guardian Name</th>
                                    <th>Guardian Number</th>
                                    <th>Mother Name</th>
                                    <th>Pen Card</th>
                                    <th>Aadhar Card</th>
                                    <th>Voter Id</th> 
                                    <th>Status</th> 
                                    <th>Is Resigned</th>  
                                    <th>Is Terminated</th>  
                                    <th>Is Tranfered</th>                                
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
                 $gender = 'Other';
                if($row->gender==1)
                {
                    $gender = 'Male';
                }                
                if($row->gender==0)
                {
                    $gender = 'Female';
                } 
                if($row->is_employee==0)
                {
                    $status = 'Pending';
                }
                else
                {
                        $status = 'Inactive';
              
                    if($row->status==1)
                    {
                        $status = 'Active';
                    }
                } 
    $resign = 'No';
    if($row->is_resigned==1 || $row->is_resigned==2)
    {
        $resign = 'Yes';
    } 
    $terminate = 'No';
    if($row->is_terminate==1)
    {
     $terminate = 'Yes';    
    } 
    $transfer = 'No';
              
                    if($row->is_transfer==1)
                    {
                        $transfer = 'Yes';
                    }
               
?>
  <tr>
    <td>{{ $index+1 }}</td>
    <td>{{ getDesignationData('designation_name',$row->designation_id)->designation_name }}</td>
    <td>{{ $category }} </td>
    <td>{{ $row['branch']->name}} </td>


    <td>{{ $row['branch']->branch_code }}</td>
    <td>{{ $row['branch']->sector }}</td>
    <td>{{ $row['branch']->regan }}</td>
    <td>{{ $row['branch']->zone }}</td>
    
    <td>{{ $row->recommendation_employee_name }}</td>
    <td>{{ $row->employee_name }} </td>
    <td>{{ $row->employee_code }} </td>
    <td>{{ date("d/m/Y", strtotime($row->dob)) }}</td>

    <td>{{ $gender }}</td>
    <td>{{ $row->mobile_no }} </td>
    <td>{{ $row->email }} </td>
    <td>{{ $row->father_guardian_name }}</td>



    <td>{{ $row->father_guardian_number }} </td>
    <td>{{ $row->mother_name }} </td>
    <td>{{ $row->pen_card }}</td>

    <td>{{ $row->aadhar_card }} </td>
    <td>{{ $row->voter_id }} </td>
    <td>{{ $status }}</td>

    <td>{{ $resign }} </td>
    <td>{{ $terminate}} </td>
    <td>{{ $transfer }}</td>

    <td>{{ date("d/m/Y h:i:s a", strtotime($row->created_at)) }} </td>
    
    
    
  </tr>
@endforeach
</tbody>
</table>
