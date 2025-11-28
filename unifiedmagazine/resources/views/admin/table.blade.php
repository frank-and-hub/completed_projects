<?php $sno = 0; ?>
@foreach($data as $val)
<?php $sno++; ?>
    <tr>
        <td>{{$sno}}</td>
        <td><div style="cursor:pointer" class="email_message" data-bs-toggle="modal" data-bs-target="#largeModal" data-message="{{$val['message']}}">{{$val['subject']??''}}</div></td>
        <td>{{$val['email_count']??''}}</td>
        <td>{{$val['sent_email_count']??'0'}}</td>
        <td>{{$val['file_name']??''}}</td>
        @if(Auth::user()->role == '1')
        <th>{{$val['user_email']??''}}</th>
        @endif
    </tr>
@endforeach
