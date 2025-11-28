 <table id="fixed_deposit" class="table datatable-show-all">

<thead>

    <tr>
        <th>S/N</th>
		<th>Date</th>
		<th>Amount Number</th>
        <th>Member Id</th>
        <th>Member Name</th>
		<th>Type</th>
		<th>Payment Mode</th>
		<th>CR</th>
		<th>DR</th>
		<th>Balance</th>
    </tr>
</thead>                    
<tbody>
	<?php
	    $m_id ='';
		if(count($data)){ for($x=0; $x<count($data); $x++){ ?>
			<tr>
				<td><?php echo $data[$x]["DT_RowIndex"]; ?></td>
				<td><?php echo $data[$x]["date"]; ?></td>
				<td><?php echo $data[$x]["account_number"]; ?></td>
				<td><?php if(isset($data[$x]["member_id"]))echo $data[$x]["member_id"]; else echo $m_id ;?></td>
				<td><?php if(isset($data[$x]["member_id"])) echo $data[$x]["member_name"]; else echo $m_id ; ?></td>
				<td><?php echo $data[$x]["type"]; ?></td>
				<td><?php echo $data[$x]["payment_mode"]; ?></td>
				<td><?php echo $data[$x]["cr"]; ?></td>
				<td><?php echo $data[$x]["dr"]; ?></td>
				<td><?php echo $data[$x]["balance"]; ?></td>
			</tr>
		<?php } }?>
</tbody>
</table>