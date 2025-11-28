<!--  <h6 class="card-title font-weight-semibold"></h6>  -->
 
 
 <table id="interest_on_deposit" class="table datatable-show-all">
	<thead>
		<tr>
			<th>S/N</th>
			<th>Owner/Employee Name</th>
			<th>Employee Code</th>
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
				<td><?php echo $data[$x]["name"]; ?></td>
				<td><?php echo $data[$x]["code"]; ?></td>
				<td><?php echo $data[$x]["cr"]; ?></td>
				<td><?php echo $data[$x]["dr"]; ?></td>
				<td><?php echo $data[$x]["amount"]; ?></td>
			</tr>
		<?php } }?>
	</tbody>


</table>