<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
 //echo "<pre>";print_r($associateList);die;
?>


<thead>
  <tr>
    <th>#</th>
        <th>Join Date</th> 
        <th>BR Name</th>
        <th>BR Code</th>
        <th>SO Name</th>
        <th>RO Name</th>
        <th>ZO Name</th>
        <th>Member ID</th>
        <th>Associate ID</th>
        <th>Name</th> 
        <th>Associate DOB</th> 
        <th>Nominee Name</th>
        <th>Relation</th>
        <th>Nominee Age</th>
        <th>Email ID</th>
        <th>Mobile No</th> 
        <!--<th>Senior Code</th>  
        <th>Senior Name</th>   -->                           
        <th>Status</th>  
        <th>Is Uploaded</th>
        <!--                                     <th>Achieved Target</th>
        -->                                    
        <th>Address</th>
        <th>State</th>
        <th>District</th> 
        <th>City</th>
        <th>Village Name</th> 
        <th>Pin Code</th>  
        <th>First ID Proof</th>                               
        <th>Second ID Proof</th> 
  </tr>
</thead>
<tbody>
@foreach($rowReturn as $index => $row)
  <tr>
    <td>{{ $index+1 }}</td>
    <td>{{ $row['join_date'] }}</td>
    <td>{{ $row['branch'] }}</td>

    <td>{{ $row['branch_code'] }}</td>
    <td>{{ $row['sector_name'] }}</td>
    <td>{{ $row['region_name'] }}</td>
    <td>{{ $row['zone_name'] }}</td>
     <td>{{ $row['member_id'] }}</td> 
    <td>{{ $row['m_id'] }}</td> 
    <td>{{ $row['name']}}</td>
    <td  >{{$row['dob']}}</td>  
  <td>{{$row['nominee_name']}} </td>
  <td>{{$row['relation']}} </td>
  <td>{{$row['nominee_age']}} </td>
    <td>{{ $row['email'] }}</td>
    <td>{{ $row['mobile_no'] }}</td>
    <!--<td>{{ $row['associate_code']}}</td>
    <td>{{ $row['associate_name'] }}</td>-->
    <td>{{$row['status']}}</td>
    <td>{{$row['is_upload'] }}</td>  
    <td>{{$row['address']}}</td>
    <td>{{$row['state']}}</td>
    <td>{{$row['district']}}</td>
    <td>{{$row['city']}}</td>
    <td>{{$row['village']}}</td>
    <td>{{$row['pin_code']}}</td>
    <td>{{$row['firstId']}}</td>
    <td>{{$row['secondId']}}</td>
  </tr>
@endforeach</tbody>
</table>
