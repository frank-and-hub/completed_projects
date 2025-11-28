<h3 class="card-title font-weight-semibold">Employee Application List
</h3>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
//echo "<pre>";print_r($data);die;
?>


<thead>
 <tr>
                                <th>S/N</th>
                                    <!--<th>Application Date</th>-->
                                    <th>Application Type</th>
                                    <th>Designation</th>
                                    <th>Category</th>
                                    <th>BR Name</th>
                                    <th>BR Code</th>
                                    <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th>
                                    <th>Recommendation Employee Name</th> 
                                    <th>Employee Name</th>
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
                                    <th>Application Status</th> 
                                    <!--<th>Approved Date</th>-->                                    
                                    <th>Created</th>
                            </tr>
</thead>
<tbody>
@foreach($data as $index => $row)  
<?php

$application_type = '';
                if($row->application_type==1)
                {
                    $application_type = 'Register';
                }
                else
                {
                    $application_type = 'Resign';
                }
                 $category = '';
                if($row['employeeget']->category==1)
                {
                    $category = 'On-rolled';
                }
                if($row['employeeget']->category==2)
                {
                   $category = 'Contract'; 
                }
                $status = 'Pending';
              
                    if($row->status==1)
                    {
                        $status = 'Approved';
                    }
                    if($row->status==2)
                    {
                        $status = 'Rejected';
                    }
                    $gender = 'Other';
                if($row['employeeget']->gender==1)
                {
                    $gender = 'Male';
                }                
                if($row['employeeget']->gender==0)
                {
                    $gender = 'Female';
                } 
               
?>
  <tr>
    <td>{{ $index+1 }}</td>
    <td>{{ $application_type }}</td>
    <td>{{ getDesignationData('designation_name',$row['employeeget']->designation_id)->designation_name }} </td>
    <td>{{ $category}} </td>
    <td>{{ $row['branch']->name }}</td>

    <td>{{ $row['branch']->branch_code }}</td>
    <td>{{ $row['branch']->sector }}</td>
    <td>{{ $row['branch']->regan }}</td>
    <td>{{ $row['branch']->zone }}</td>
    
    <td>{{ $row['employeeget']->recommendation_employee_name }}</td>
    <td>{{ $row['employeeget']->employee_name }} </td> 
    <td>{{ date("d/m/Y", strtotime($row['employeeget']->dob)) }}</td>

    <td>{{ $gender }}</td>
    <td>{{ $row['employeeget']->mobile_no }} </td>
    <td>{{ $row['employeeget']->email }} </td>
    <td>{{ $row['employeeget']->father_guardian_name }}</td>



    <td>{{ $row['employeeget']->father_guardian_number }} </td>
    <td>{{ $row['employeeget']->mother_name }} </td>
    <td>{{ $row['employeeget']->pen_card }}</td>

    <td>{{ $row['employeeget']->aadhar_card }} </td>
    <td>{{ $row['employeeget']->voter_id }} </td>
    <td>{{ $status }}</td>

    <td>{{ date("d/m/Y h:i:s a", strtotime($row->created_at)) }} </td>
    
    
    
  </tr>
@endforeach
</tbody>
</table>
