<style type="text/css" media="print">

    @page 

    {

        size: auto;   /* auto is the initial value */

        margin: 0mm;  /* this affects the margin in the printer settings */

    }

	

</style>



<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">



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

    <td  >{{ getAcountHead($row->head4) }}</td>

    @php
    if($row->head4 == 62 || $row->head4 == 63){
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

    <td  >{{ getTdsDrAmount($row->daybook_ref_id,$row->head4) }}</td>

    <td  >{{ $row->amount }}</td>

    @php
    $balance = ($balance+$row->amount)-getTdsDrAmount($row->daybook_ref_id,$row->head4);
    @endphp

    <td>{{ $balance }}</td>

  </tr>

@endforeach

</tbody>

</table>





<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>



<script>

window.onafterprint = window.close;

window.print();

</script>