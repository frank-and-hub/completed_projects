<table id="interest_on_loan_taken" class="table datatable-show-all">
		<thead>
			<tr>
				<th>S/N</th>
				 <th>Date</th>
				 <th>Account Number</th>
				<th>Bank Name</th>
				<th>Description</th>
				<th>CR</th>
                <th>DR</th>
                <th>Balance</th>
			</tr>
		</thead>
	<tbody>
    <?php
	if(count($data)){ for($x=0; $x<count($data); $x++){?>
		<tr>
			<td><?php echo $data[$x]["DT_RowIndex"]; ?></td>
			<td><?php echo $data[$x]["date"]; ?></td>
			<td><?php echo $data[$x]["account_number"]; ?></td>
			<td><?php echo preg_replace("/[^A-Za-z0-9\-\']/", ' ', $data[$x]["loan_account_name"]); ?></td>
            <td><?php echo preg_replace("/[^A-Za-z0-9\-\']/", ' ', $data[$x]["description"]); ?></td>
			<td><?php echo $data[$x]["cr"]; ?></td>
			<td><?php echo $data[$x]["dr"]; ?></td>
			<td><?php echo $data[$x]["balance"]; ?></td>

		</tr>
	<?php } }?>

	</tbody>

   </table>
