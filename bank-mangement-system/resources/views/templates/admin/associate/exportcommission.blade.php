<h3 class="mb-0 text-dark">Associate's Total Commission</h3>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
  <tr>
    <th>S/N</th>
                                <th>BR Name</th>
                                    <th>BR Code</th>
                                    <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th>
                                <th>Associate Name</th>
                                <th>Associate Code</th>
                                <th>Associate Carder</th>                         
                                <th>Total Commission Amount</th>
                                <th>Total Collection Amount</th>
                                <th>Senior Code</th>
                                <th>Senior Name</th>
                                <th>Senior Carder</th>
                                <th>Total Collection Amount All</th>
  </tr>
</thead>
<tbody>
@foreach($data as $index => $row)  
  <tr>
    <td>{{ $index+1 }}</td> 
    <td  >{{ $row['associate_branch']->name }}</td>
    <td  >{{ $row['associate_branch']->branch_code }}</td>
    <td  >{{ $row['associate_branch']->sector }}</td>
    <td  >{{ $row['associate_branch']->regan }}</td>
    <td  >{{ $row['associate_branch']->zone }}</td>
    <td>{{ $row->first_name }} {{ $row->last_name }}</td>
    <td>{{ $row->associate_no }} </td>
    <td>{{ getCarderName($row->current_carder_id) }} </td>
    <td>{{ getAssociateTotalCommission($row->id,$startDate,$endDate,'commission_amount')}} </td>
    <td>{{ getTotalCollection($row->id,$startDate,$endDate)}} </td>
    <td>{{ $row->associate_senior_code }} </td>
    <td>{{ getSeniorData($row->associate_senior_id,'first_name') }} {{ getSeniorData($row->associate_senior_id,'last_name') }} </td>
    <td>{{ getCarderName(getSeniorData($row->associate_senior_id,'current_carder_id')) }} </td>
    <td>{{ getTotalCollection_all($row->id,$startDate,$endDate)}} </td>

  </tr>
@endforeach
</tbody>
</table>
