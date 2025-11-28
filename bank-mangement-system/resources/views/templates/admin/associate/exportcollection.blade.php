<h3 class="mb-0 text-dark">Associate's Total Collection</h3>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
  <tr>
    <th>S/N</th>
                                <th>Branch Name</th>
                                <th>Associate Name</th>
                                <th>Associate Code</th>
                                <th>Associate Carder</th>
                                <th>Total  Amount</th>                         
                                <th>Total Commission Amount</th>
                                <th>Senior Code</th>
                                <th>Senior Name</th>
                                <th>Senior Carder</th>
  </tr>
</thead>
<tbody>
@foreach($data as $index => $row)  
  <tr>
    <td>{{ $index+1 }}</td>
    <td>{{ $row['branch']->name }}</td>
    <td>{{ $row->first_name }} {{ $row->last_name }}</td>
    <td>{{ $row->associate_no }} </td>
    <td>{{ getCarderName($row->current_carder_id) }} </td>
    <td>{{ getAssociateTotalCommissionCollection($row->id,$startDate,$endDate,'total_amount')}} </td>
    <td>{{ getAssociateTotalCommissionCollection($row->id,$startDate,$endDate,'commission_amount')}} </td>
    <td>{{ $row->associate_senior_code }} </td>
    <td>{{ getSeniorData($row->associate_senior_id,'first_name') }} {{ getSeniorData($row->associate_senior_id,'last_name') }} </td>
    <td>{{ getCarderName(getSeniorData($row->associate_senior_id,'current_carder_id')) }} </td>
    

  </tr>
@endforeach
</tbody>
</table>
