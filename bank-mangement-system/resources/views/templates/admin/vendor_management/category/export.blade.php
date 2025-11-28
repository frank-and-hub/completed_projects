<h6 class="card-title font-weight-semibold">Vendor  Management | Vendor Category List
</h6>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
 <tr>
                                <th>S/N</th>
                                    <th> Name</th>
                                    
                                    <th>Status</th>                                     
                                    <th>Created</th>  
                                    <th>Updated</th> 
                            </tr>
</thead>
<tbody>
@foreach($data as $index => $row)  
<?php
            $status = 'Active';
                if($row->status==1)
                {
                    $status = 'Active';
                }  if($row->status==0)
                {
                    $status = 'Inactive';
                }
                $created_at='';
                if($row->created_at!= NULL  )
                {
                    $created_at =  date("d/m/Y", strtotime($row->created_at));
                } 
                $updated_at='';
                if($row->updated_at!= NULL  )
                {
                    $updated_at =  date("d/m/Y", strtotime($row->updated_at));
                } 
                
?>
  <tr>
    <td>{{ $index+1 }}</td> 
    <td>{{ $row->name }}</td> 
    <td>{{ $status }} </td>
    <td>{{ $created_at }} </td> 
    <td>{{ $updated_at }} </td> 
    
    
    
  </tr>
@endforeach
</tbody>
</table>
