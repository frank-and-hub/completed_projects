<table >
	<thead>
		<tr>
			<th>S/N</th>
			<th>Date</th>
			<th>Branch</th>
			<th>V.No/M.Id/Account Number</th>
			
			<th>Name</th>
			<th>Payment Mode</th>
			<th>Type</th>
			<th>CR</th>
			<th>DR</th>
			<th>Amount</th>
		</tr>
	</thead>   
	<tbody>
	<?php
    if(count($data)){ for($x=0; $x<count($data); $x++){ ?>
        <tr>
            <td><?php echo $data[$x]["DT_RowIndex"]; ?></td>
            <td><?php echo $data[$x]["date"]; ?></td>
			<td><?php echo $data[$x]["branch"]; ?></td>
			<td><?php echo $data[$x]["voucher_number"]; ?></td>
            <td><?php echo preg_replace("/[^A-Za-z0-9\-\']/", ' ', $data[$x]["owner_name"]) ; ?></td>
			<td><?php echo $data[$x]["payment_type"]; ?></td>
            <td><?php echo $data[$x]["type"]; ?></td>
            <td><?php echo $data[$x]["cr"]; ?></td>
            <td><?php echo $data[$x]["dr"]; ?></td>
            <td><?php echo $data[$x]["balance"]; ?></td>
        </tr>
    <?php } }?>
	</tbody>


</table>