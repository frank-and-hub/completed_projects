<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<thead>
    <tr>
        <th width = "5%">#</th>
        <th width = "10%">State</th>
        <th width = "10%">Month</th>
        <th width = "10%">Date</th>
        <th width = "10%">Event Name</th>
    </tr>
</thead>
<tbody>
@foreach($data as $index => $holiday)  
  <tr>
    <td width = "5%">{{ $index+1 }}</td>
    <td width = "10%">{{ $holiday['state']->name }}</td>
    <td width = "10%">{{ date("F", strtotime($holiday->start_date)) }}</td>  
    <td width = "10%">{{ date("d/m/Y", strtotime($holiday->start_date)) }}</td>
    <td width = "10%">{{ $holiday->title }}</td>
  </tr>
@endforeach
</tbody>
</table>
