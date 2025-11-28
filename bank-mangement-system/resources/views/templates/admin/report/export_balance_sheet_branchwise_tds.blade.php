<!--  <h6 class="card-title font-weight-semibold"></h6>  -->
 
 
 <table id="interest_on_deposit" class="table datatable-show-all">
	<thead>
		<tr>
			<th>S/N</th>
			<th>Date</th>
			<th>Branch</th>
			<th>V.NO./AC.NO./M.ID</th>
			<th>Name</th>
			<th>Payment Mode</th>
			<th>Type</th>
			<th>CR</th>
			<th>DR</th>
			<th>Balance</th>
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
				<td><?php echo $data[$x]["owner_name"]; ?></td>
				<td><?php echo $data[$x]["payment_type"]; ?></td>
				<td><?php echo $data[$x]["type"]; ?></td>
				<td><?php echo $data[$x]["cr"]; ?></td>
				<td><?php echo $data[$x]["dr"]; ?></td>
				<td><?php echo $data[$x]["balance"]; ?></td>
			</tr>
		<?php } }?>
	</tbody>


</table>