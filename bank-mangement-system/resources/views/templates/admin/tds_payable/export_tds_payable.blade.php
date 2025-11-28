<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">

<?php

  //echo "<pre>";print_r($reports);die;

?>





<thead>

    <tr>

      <th>S/N</th>

      <th>Created Date</th>

      <th>TDS Head Name</th>

      <th>Vendor Name</th>

      <th>PAN Number</th>

      <th>DR</th>

      <th>CR</th>

      <th>Balance</th>  

    </tr>

</thead>

<tbody>

@php
$balance = 0;
@endphp

@foreach($data as $index => $row) 



  <tr>

    <td  >{{ $index+1 }}</td>

    <td  >{{ date("d/m/Y", strtotime(convertDate($row->created_at))) }}</td>

    <td  >{{ getAcountHead($row->head_id) }}</td>

    @php
    if($row->head_id == 62 || $row->head_id == 63){
       $vendor_name= getMemberData($row->member_id)->first_name.' '.getMemberData($row->member_id)->last_name; 
    }
	
    if(getMemberData($row->member_id)['memberIdProofs']){
        $mIdProof = getMemberData($row->member_id)['memberIdProofs'];
        if($mIdProof->first_id_type_id == 5){
            $pan_number=$mIdProof->first_id_no;
        }elseif($mIdProof->second_id_type_id == 5){
            $pan_number=$mIdProof->second_id_no;
        }
    }
    @endphp

    <td  >{{ $vendor_name }}</td>

	  <td>{{ $pan_number }}</td>

    <td  >{{ getTdsDrAmount($row->daybook_ref_id,$row->head_id) }}</td>

    <td  >{{ $row->amount }}</td>

    @php
    $balance = ($balance+$row->amount)-getTdsDrAmount($row->daybook_ref_id,$row->head_id);
    @endphp

    <td>{{ $balance }}</td>

  </tr>

@endforeach

</tbody>

</table>

