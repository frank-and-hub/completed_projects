 <table class="table datatable-show-all" id="update_g">
<thead>
    <tr>
        <th>S.N</th>
		<th>Company</th>
		<th>Member Name</th>							
		<th>Year</th>
		<th>File Name</th>
		<th>Status</th>
    </tr>
</thead>
<tbody>
    @foreach($data as $index =>$row)
        <tr>
            <td>{{$index+1}}</td>
			<td>{{$row->company->name}}</td>
            <td>{{$row->member_id ? $row->member->first_name.' '.$row->member->last_name : 'N/A'}}</td>
            <td>{{$row->year}}</td>
            <td>{{$row->file}}</td>
			<td>{{$row->status==1?'Active':'Inactive'}}</td>
        </tr>
    @endforeach
</tbody>
</table>