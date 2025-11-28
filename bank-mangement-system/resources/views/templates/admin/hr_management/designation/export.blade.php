<h6 class="card-title font-weight-semibold">Designation Management | Designation List
</h6>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
 <tr>
                                <th>S/N</th>
                                    <th>Designation Name</th>
                                    <th>Category</th>
                                    <th>Gross Salary</th>
                                    <th>Basic Salary</th>
                                    <th>Daily Allowances</th> 
                                    <th>HRA</th>
                                    <th>HRA Metro City</th>
                                    <th>UMA</th>
                                    <th>Convenience Charges</th>
                                    <th>Maintenance Allowance</th>
                                    <th>Communication Allowance</th>
                                    <th>PRD</th>
                                    <th>IA</th>
                                    <th>CA</th>
                                    <th>FA</th>
                                    <th>PF</th>
                                    <th>TDS</th>
                                    <th>Status</th>                                     
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
                $status = 'Active';
                if($row->status==1)
                {
                    $status = 'Active';
                }                
                if($row->status==0)
                {
                    $status = 'Inactive';
                }
                $created_at='';
                if($row->created_at!= NULL  )
                {
                    $created_at =  date("d/m/Y", strtotime($row->created_at));
                } 
                $sum=$row->basic_salary+$row->daily_allowances+$row->hra+$row->hra_metro_city+$row->uma +$row->convenience_charges+$row->maintenance_allowance+$row->communication_allowance+$row->prd+$row->ia+$row->ca+$row->fa
                ;
                $deduction=$row->pf+$row->tds;
                $total=$sum-$deduction;
?>
  <tr>
    <td>{{ $index+1 }}</td> 
    <td>{{ $row->designation_name }}</td>
    <td>{{ $category }} </td>
    <td>{{ number_format((float)$total, 2, '.', '') }}</td>
    <td>{{ number_format((float)$row->basic_salary, 2, '.', '') }}</td>
    <td>{{ number_format((float)$row->daily_allowances, 2, '.', '') }}</td>
    <td>{{ number_format((float)$row->hra, 2, '.', '') }}</td>
    <td>{{ number_format((float)$row->hra_metro_city, 2, '.', '') }}</td>
    <td>{{ number_format((float)$row->uma, 2, '.', '') }}</td>
    <td>{{ number_format((float)$row->convenience_charges, 2, '.', '') }}</td>
    <td>{{ number_format((float)$row->maintenance_allowance, 2, '.', '') }}</td>
    <td>{{ number_format((float)$row->communication_allowance, 2, '.', '') }}</td>
    <td>{{ number_format((float)$row->prd, 2, '.', '') }}</td>
    <td>{{ number_format((float)$row->ia, 2, '.', '') }}</td>
    <td>{{ number_format((float)$row->ca, 2, '.', '') }}</td>
    <td>{{ number_format((float)$row->fa, 2, '.', '') }}</td>
    <td>{{ number_format((float)$row->pf, 2, '.', '') }}</td>
    <td>{{ number_format((float)$row->tds, 2, '.', '') }}</td>
    <td>{{ $status }} </td>
    <td>{{ $created_at }} </td> 
    
    
    
  </tr>
@endforeach
</tbody>
</table>
