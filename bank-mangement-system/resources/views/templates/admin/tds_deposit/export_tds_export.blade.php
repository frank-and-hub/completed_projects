 <table id="tds_deposite_listing" class="table datatable-show-all">
<thead>
    <tr>
        <th>S.N</th>
        <th>Date</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>TDS Percentage</th>
		<th>TDS Amount</th> 
		<th>Type</th> 
    </tr>
</thead>      
    @foreach($data as $index =>$row)
    <tr>
        <td>{{$index+1}}</td>
        <td>{{date("d/m/Y", strtotime( $row->created_at))}}</td>
        <td>{{date("d/m/Y", strtotime( $row->start_date))}}</td>
        <td>{{date("d/m/Y", strtotime( $row->end_date))}}</td>
        <td>{{$row->tds_per}}</td>
        <td>{{number_format((float)$row->tds_amount, 2, '.', '')}}</td>
        <td>@if($row->type == 1)
                Interest On Deposite With Pencard';
                
                @elseif($row->type == 2)
               Interest On Deposite Senior Citizen
                         
                @elseif($row->type == 3)
                Interest On Commission With Pencard
                
                @elseif($row->type == 4)
               
                    Interest On Commission WithOut Pencard
               
                @elseif($row->type == 5)
                Interest On Deposite Without Pencard
                @endif
                </td>
    </tr>

    @endforeach              
</table>