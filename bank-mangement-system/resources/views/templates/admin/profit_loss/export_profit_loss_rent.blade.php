<table id="rent" class="table datatable-show-all">
    <thead>
        <tr>
             <th>S/N</th>

             <th>Date</th>

            <th>Owner Name</th>

            <th>Rent Type</th>

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
				<td><?php echo $data[$x]["owner_name"]; ?></td>
				<td><?php echo $data[$x]["rent_type"]; ?></td>
				<td><?php echo $data[$x]["cr"]; ?></td>
				<td><?php echo $data[$x]["dr"]; ?></td>
				<td><?php echo $data[$x]["balance"]; ?></td>
			</tr>
		<?php } }?>
    </tbody>
</table>
