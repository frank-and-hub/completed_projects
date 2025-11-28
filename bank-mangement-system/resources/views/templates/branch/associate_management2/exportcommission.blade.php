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
        <!--    <th>Total Collection Amount</th>-->
        <th>Senior Code</th>
        <th>Senior Name</th>
        <th>Senior Carder</th>
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
    <td><?php echo $row['getCarderNameCustom']->name.'('.$row['getCarderNameCustom']->short_name.')'; ?></td>
    <td>{{ number_format($row->associate_total_commission_count,2,'.','') }} </td>
    <!-- <td>{{ getTotalCollection($row->id,$startDate,$endDate)}} </td>   --> 
    <td>{{ $row->associate_senior_code }} </td>
    <td><?php echo $row['seniorData']->first_name.' '.$row['seniorData']->last_name; ?></td>
    <td><?php echo $row['seniorData']['getCarderNameCustom']->name.'('.$row['seniorData']['getCarderNameCustom']->short_name.')'; ?> </td>
    

  </tr>
@endforeach
</tbody>
</table>
