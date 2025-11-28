<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
    <tr>
        <th width = "5%">#</th>
        <th width = "10%">Join Date</th>
        <th width = "10%">Branch</th>
        <th width = "10%">Member ID</th>
        <th width = "10%">Name</th>
        <th width = "10%">Email ID</th>
        <th width = "10%">Mobile No</th>
        <th width = "10%">Associate Code</th>
        <th width = "10%">Associate Name</th>
        <th width = "10%" >Status</th>
    </tr>
</thead>
<tbody>
@foreach($memberList as $index => $member)  
  <tr>
    <td width = "5%">{{ $index+1 }}</td>
    <td width = "10%">{{ date("d/m/Y", strtotime($member->re_date)) }}</td>
    <td width = "10%">{{ $member['branch']->name }}</td>
    <td width = "10%">{{ $member->member_id }}</td>
    <td width = "10%">{{ $member->first_name }} {{ $member->last_name }}</td>
    <td width = "10%">{{ $member->email }}</td>
    <td width = "10%">{{ $member->mobile_no }}</td>
    <td width = "10%">{{ $member->associate_code }}</td>
    <td width = "10%" >{{ getSeniorData($member->associate_id,'first_name').' '.getSeniorData($member->associate_id,'last_name') }}</td>
    <td width = "10%">
      @if($member->is_block)
        Block
      @else
        @if($member->status==1)
          Active
        @else
          Inactive
        @endif
      @endif
    </td>
  </tr>
@endforeach
</tbody>
</table>
