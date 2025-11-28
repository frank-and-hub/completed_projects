<h6 class="card-title font-weight-semibold">Assets Management | Assets Report</h6>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php
//echo "<pre>";print_r($data);
?>


<thead>
    <tr>
        <th>S/N</th>
                                    <th>Branch Name</th>
                                    <th>Account Head</th>
                                    <th>Sub-Account Head Name</th>
                                    <th>Demand Date</th>
                                    <th>Advice Date</th>
                                    <th>Amount</th>
                                    <th>Party Name</th>
                                    <th>Mobile no.</th>
                                    <th>Bill no.</th>
                                    <th>Bill copy</th>
                                    <th>Status </th> 
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
                if($row->status == 0){
                    $status = 'Working';
                }else{
                    $status = 'Damaged';        
                }   

  ?>
    <tr>
        <td>{{$index+1}}</td>
        <td>{{ getBranchDetail($row['advices']->branch_id)->name .'-'. getBranchDetail($row['advices']->branch_id)->branch_code }}</td>
        <td>{{getAcountHeadNameHeadId($row->assets_category)}}</td>
        <td>{{getAcountHeadNameHeadId($row->assets_subcategory)}}</td>
        <td>{{date("d/m/Y", strtotime($row['advices']->date))}}</td>
        <td>{{date("d/m/Y", strtotime($row->purchase_date))}}</td>
        <td>{{number_format((float)$row->amount, 2, '.', '')}}</td>
        
        <td>{{$row->party_name}}</td>
        <td>{{$row->mobile_number}}</td>
        <td>{{ $row->bill_number}}</td>
        <td>{{$bill_file_id}}</td>
         <td>{{$status}}</td>
        
    </tr>
     
  @endforeach
</tbody>
</table>
