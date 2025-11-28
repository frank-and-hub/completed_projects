<h6 class="card-title font-weight-semibold">Commission Transfer - Ledger List</h6>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
 <tr>
                                <th>S/N</th>
                                <th>Associate Code</th>
                                <th>Associate Name</th>
                                <th>Associate Carder</th>
                                <th>PAN No </th> 
                                <th>Total Amount </th>
                                <th>TDS Amount </th>
                                <th>Final Payable Amount</th>
                                <th>Total Collection </th>
                                <th>Fule Amount </th>
                                <th>SSB Account No</th>
                                <th>Status</th>
                                <th>Created</th> 
                            </tr>
</thead>
<tbody>
@foreach($data as $index => $row)  
  <tr>
    <td>{{ $index+1 }}</td>
    <td>{{ getSeniorData($row->member_id,'associate_no')}}</td>
    <td>{{ getSeniorData($row->member_id,'first_name').' '.getSeniorData($row->member_id,'last_name') }}</td>
    <td>{{ getCarderName(getSeniorData($row->member_id,'current_carder_id')) }}</td>
    <td>{{ get_member_id_proof($row->member_id,5) }}</td>
    <td>{{ $row->amount_tds}}</td>
    <td>{{ $row->total_tds}}</td>
    <td>{{ $row->amount }}</td>
    <td>{{ $row->collection }}</td>
    <td>{{ $row->fuel }}</td>
    <td>{{ getMemberSsbAccountDetail($row->member_id)->account_no }}</td>
    <td>
      @if($row->status==1)
        Transferred
      @elseif($row->status==0)
       Deleted
      
      @else
        Pending
      @endif

     
    </td>
    <td>{{ date("d/m/Y h:i:s a", strtotime($row->created_at)) }}</td>
    
    
  </tr>
@endforeach
</tbody>
</table>
