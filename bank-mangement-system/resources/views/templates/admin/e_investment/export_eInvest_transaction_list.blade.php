<h3> Transaction List</h3>

<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">

<?php

  //echo "<pre>";print_r($reports);die;

?>





<thead>

  <tr> 

                                 <th>S/N</th>

                                    <th>Transaction ID.</th>

									<th>Date</th>

									<th>Description</th>

                                    <th>Cheque/Reference Number</th>

                                    <th>Withdrawal</th>

                                    <th>Deposit</th>

                                    <th>Balance</th> 

  </tr>

</thead>

<tbody>

<?php

$amnt = 0;

?>

@foreach($data as $index => $row)  

<?php

	$ref_no = '';

	if($row->payment_mode ==1)

	{

		$ref_no= $row->cheque_dd_no;

	}

	else if($row->payment_mode ==4 ||$row->payment_mode ==5){

		$ref_no = $row->reference_no;

	}

	else if($row->payment_mode==3){

		$ref_no =$row->online_payment_id;

	}	

	

		

?>

  <tr>

    <td>{{ $index+1 }}</td>

	 <td>{{ $row->transaction_id }}</td>

	<td>{{ date("d/m/Y", strtotime($row->created_at))}}</td>

	<td>{{ $row->description}}</td>

   

    <td>{{ $ref_no }}</td>

	 <td>@if( $row->withdrawal >0){{ $row->withdrawal }}@endif</td>

    <td>@if($row->deposit > 0){{ $row->deposit }}@endif</td>

    <td>{{ $row->opening_balance }}</td>

    

    



  </tr>



@endforeach

</tbody>

</table>

