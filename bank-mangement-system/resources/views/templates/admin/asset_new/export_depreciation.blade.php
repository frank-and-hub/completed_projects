<h6 class="card-title font-weight-semibold">Depreciation Management | Depreciation Report</h6>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php
//echo "<pre>";print_r($data);die;
?>


<thead>
    <tr>
        <th>S/N</th>
                                    <th>Branch Name</th>
                                    <th>Account Head</th>
                                    <th>Sub-Account Head Name</th>
                                    <th>Assets Purchase Date</th>
                                    <th>Party Name</th>
                                    <th>Mobile no.</th>
                                    <th>Bill no.</th>                                    
                                    <th>Total Value of Asset</th>
                                    <th>Current Assets value</th>
                                    <th>Depreciation % </th>
                                    <th>Bill copy</th>
    </tr>
</thead>

<tbody>
  @foreach($data as $index => $row)  
   <?php
        if($row->bill_file_id)
                {
                   $res = \App\Models\Files::where('id',$row->bill_file_id)->first();
                $url=URL('core/storage/images/demand-advice/expense/'.$res->file_name.'');
                $bill_file_id='<a href="'.$url.'" target="blank">'.$res->file_name.'</a>';
                $bill_file_id=$res->file_name;
                }
                else
                {
                    $bill_file_id ='N/A'; 
                }
         
       
        

  ?>
    <tr>
        <td>{{$index+1}}</td>
        <td>{{ getBranchDetail($row['advices']->branch_id)->name .'-'. getBranchDetail($row['advices']->branch_id)->branch_code }}</td>
        <td>{{getAcountHeadNameHeadId($row->assets_category)}}</td>
        <td>{{getAcountHeadNameHeadId($row->assets_subcategory)}}</td> 
        <td>{{date("d/m/Y", strtotime($row->purchase_date))}}</td>        
        <td>{{$row->party_name}}</td>
        <td>{{$row->mobile_number}}</td>
        <td>{{ $row->bill_number}}</td>
        <td>{{number_format((float)$row->amount, 2, '.', '')}}</td>
        <td>{{number_format((float)$row->current_balance, 2, '.', '')}}</td>
        <td>{{number_format((float)$row->depreciation_per, 2, '.', '')}}</td>
        <td>{{$bill_file_id}}</td>
    </tr>
     
  @endforeach
</tbody>
</table>
