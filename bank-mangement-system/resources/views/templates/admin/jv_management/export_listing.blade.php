<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
	<thead>
		<tr>
		<th>S/N</th>
		<th>Journal#</th>
		<th>Branch</th>
		<th>Reference Number</th>
<!-- 		<th>Status</th>
 -->		<th>Debit</th>
		<th>Credit</th>
		<th>Created</th> 
		</tr>
	</thead>
	<tbody>
		<?php
		if(count($data)){ for($x=0; $x<count($data); $x++){ ?>
			<tr>
				<td><?php echo $data[$x]["DT_RowIndex"]; ?></td>
				<td><?php echo $data[$x]["journal"]; ?></td>
				<td><?php echo $data[$x]["branch"]; ?></td>
				<td><?php echo $data[$x]["reference_number"]; ?></td>
<!-- 				<td><?php echo $data[$x]["status"]; ?></td>
 -->				<td><?php echo $data[$x]["debit"]; ?></td>
				<td><?php echo $data[$x]["credit"]; ?></td>
				<td><?php echo $data[$x]["created"]; ?></td>
			</tr>
		<?php } }?>
	</tbody>
</table>

