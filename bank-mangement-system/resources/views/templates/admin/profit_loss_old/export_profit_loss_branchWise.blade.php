<?php
    $info = 'head'.$data['label'];
?>

<table id="detailList" class="table datatable-show-all">
    <thead>
        <tr>
            <th><b>S/N</b></th>
            <th><b>Branch Code</b></th>
            <th><b>Branch Name</b></th>
            <th><b>Total Amount</b></th>
        </tr>
    </thead>   
    <tbody>
        @foreach($data['branches'] as $index =>$row)
        <tr>
            <td>{{$index+1}}</td>
            <td>{{$row->branch_code}}</td>
            <td>{{$row->name}}</td>
            <td>{{number_format((float)headTotalNew($data['head'], $data['date'],$data['to_date'], $row->id) , 2, '.', '')}}</td>
        </tr>







        @endforeach
    </tbody>
</table>