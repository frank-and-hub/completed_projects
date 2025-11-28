<!-- <h6 class="card-title font-weight-semibold">Account Head Report</h6> -->

<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
	<thead>
		<tr>
			<th>S/N</th>
	        <th>Date</th>
	        <th>Member Name</th>
	        <th>Member Id</th>
	        <th>Account Number</th>
	        <th>Payment Mode</th>
	        <th>CR</th>
	        <th>DR</th>
	        <th>Type</th>
	        <th>Balance</th>
		</tr>
	</thead>
	<tbody>
	
		<?php
		if(count($data)){ for($x=0; $x<count($data); $x++){ ?>
			<tr>
				<td><?php echo $data[$x]["DT_RowIndex"]; ?></td>
				<td><?php echo $data[$x]["date"]; ?></td>
				<td><?php echo $data[$x]["member_id"]; ?></td>
				<td><?php echo $data[$x]["member_name"]; ?></td>
				<td><?php echo $data[$x]["account_number"]; ?></td>
				<td><?php echo $data[$x]["payment_mode"]; ?></td>
				<td><?php echo $data[$x]["cr"]; ?></td>
				<td><?php echo $data[$x]["dr"]; ?></td>
				<td><?php echo $data[$x]["type"]; ?></td>
				<td><?php echo $data[$x]["balance"]; ?></td>
			</tr>
		<?php } }?>
	</tbody>
</table>

