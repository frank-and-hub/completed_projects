<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<thead>
    <tr>
        <th>S/N</th>
        <th>Date</th> 
        <th>Loan Name</th>
        <th>Transfer Amount</th> 
		 <th>Loan Amount</th> 
		 <th>File Charge</th> 
        <th>Branch Name</th> 
        <th>Associate Code</th>  
        <th>Associate Name</th> 
        <th>Approved Date</th> 
        <th>Status</th> 
        
    </tr>
</thead>   
<tbody>
        @foreach($data as $index=>$row)
        <?php $member =  App\Models\Member::where('id',$row->applicant_id)->first(['id','associate_code','first_name','last_name']);
        $branch = App\Models\Branch::where('id',$row->branch_id)->first()->name ?>
    <tr>
        <td>{{$index+1}}</td>
        <td>{{ date("d/m/Y", strtotime($row->created_at))}}</td>
        <td>{{ $row['loan']->name}}</td>
        <td>{{ $row->amount}}</td>
        <td>{{$row->amount + $row->file_charges}}</td>
         <td>{{ $row->file_charges}}</td>
        <td>{{ $branch}}</td>
        <td>{{$member->associate_code}}</td>
        <td>{{ $member->first_name.' '.$member->last_name}}</td>
        <td>@if($row->approve_date){{date("d/m/Y", strtotime($row->approve_date))}}@endif</td>
         <td>{{ $row->maturity_amount}}</td>
        <td>@if($row->status == 0)
                    Pending
                @elseif($row->status == 1)
                     Approved
                @elseif($row->status == 2)
                    Rejected
                @elseif($row->status == 3){
                    Clear
                @elseif($row->status == 4)
                    Due
                @endif
               </td>
        
       
         
    </tr>

    @endforeach
</tbody>                 
</table>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script>
window.onafterprint = window.close;
window.print();
</script>