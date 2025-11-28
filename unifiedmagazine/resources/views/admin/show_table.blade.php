<?php 
$sno = 0; 
$status = [
    0 => 'pending',
    1 => 'send',
    2 => 'feild',
    3 => 'pending'
];
?>
@foreach($data as $value)
<?php $sno++; ?>
    <tr>
        <td>{{$sno}}</td>
        <td>{{$value['file']}}</td>
        <td>{{$value['name']}}</td>
        <td>{{$value['company_name']}}</td>
        <td>{{$status[$value['status']]}}</td>
    </tr>
@endforeach