<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
  <tr>
    <th>#</th>
    <th>Join Date</th> 
                                    <th>BR Name</th>
                                    <th>BR Code</th>
                                    <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th>
                                    <th>Associate ID</th>
                                    <th>Name</th> 
                                    <th>Email ID</th>
                                    <th>Mobile No</th> 
                                    <th>Senior Code</th>  
                                    <th>Senior Name</th>                              
                                    <th>Status</th>   
  </tr>
</thead>
<tbody>
@foreach($associateList as $index => $member)  
  <tr>
    <td>{{ $index+1 }}</td>
    <td>{{ date("d/m/Y", strtotime($member->associate_join_date)) }}</td>
    <td>{{ $member['associate_branch']->name }}</td>

    <td>{{ $member['associate_branch']->branch_code }}</td>
    <td>{{ $member['associate_branch']->sector }}</td>
    <td>{{ $member['associate_branch']->regan }}</td>
    <td>{{ $member['associate_branch']->zone }}</td>

    <td>{{ $member->associate_no }}</td> 
    <td>{{ $member->first_name }} {{ $member->last_name }}</td>
    <td>{{ $member->email }}</td>
    <td>{{ $member->mobile_no }}</td>
    <td>{{ $member->associate_senior_code }}</td>
    <td>{{ getSeniorData($member->associate_senior_id,'first_name').' '.getSeniorData($member->associate_senior_id,'last_name') }}</td>
    <td>
      @if($member->is_block)
        Block
      @else
        @if($member->associate_status==1)
          Active
        @else
          Inactive
        @endif
      @endif
    </td> 
  </tr>
@endforeach
</tbody>
</table>
