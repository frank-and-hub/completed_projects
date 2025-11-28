<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<thead>
    <tr>
        <th>S/N</th>
        <th>Date</th>
        <th>BR Name</th>
        <th>BR Code</th>
        <th>SO Name</th>
        <th>RO Name</th>
        <th>ZO Name</th> 
        <th>Plan</th>
        <th>Amount</th>
        <th>Rate of Interest</th>
        <th>Maturity Amount</th>
        <th>Tenure</th>                                    
        <th>Associate Code</th>  
        <th>Associate Name</th> 
         
    </tr>
</thead>      
<tbody>
    @foreach($data as $index=>$row)
    <tr>
        <td>{{$index+1}}</td>
        <td>{{ date("d/m/Y", strtotime($row->created_at))}}</td>
        <td>{{$row['branch']->name}}</td>
        <td>{{$row['branch']->branch_code}}</td>
        <td>{{$row['branch']->sector}}</td>
         <td>{{$row['branch']->regan}}</td>
        <td>{{ $row['branch']->zone}}</td>
        <td>{{$row['plan']->name}}</td>
        <td>{{ $row->deposite_amount}}</td>
        <td>{{$row->interest_rate}}</td>
         <td>{{ $row->maturity_amount}}</td>
        <td>@if($row->tenure)
                    {{$row->tenure}} years @endif
               </td>
        <td>{{$row['associateMember']['associate_no']}}</td>
        <td>{{$row['associateMember']['first_name'].' '.$row['associateMember']['last_name']}}</td>
       
         
    </tr>

    @endforeach
</tbody>              
</table>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script>
window.onafterprint = window.close;
window.print();
</script>