<table id="bill_listing" class="table datatable-show-all">
    <thead>
        <tr>
            <th>S/N</th>
            <th>Date</th>
            <th>Company Name</th> 
            <th>Branch Name</th>
            <th>Bill Number</th>
            <th>Vendor Name</th>
            <th>Status</th>
            <th>Due Date</th>
            <th>Paid Amount</th>
            <th> Balance Due</th>
            <th> Bill Amount</th>
        </tr>
    </thead> 
    <tbody>
        @foreach($data as $index => $row)
        <tr>    
            <td>{{$index+1}}</td>
            <td>{{$row['date']}}</td>
            <td>{{$row['company_name']}}</td> 
             <td>{{$row['branch_name']. ' - '. $row['branch_code']}}</td>
            <td>{{$row['ref_number']}}</td>
            <td>{{$row['vendor_name']}}</td>
            <td>{{$row['status']}}</td>
            <td>{{$row['due_date']}}</td>
            <td>{{$row['amount']}}</td>
            <td>{{$row['due_balance']}}</td>
            <td>{{$row['bill_amount']}}</td>
        </tr>    

        @endforeach

    </tbody>   
</table>    