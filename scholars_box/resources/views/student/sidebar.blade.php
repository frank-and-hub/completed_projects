@php
    $slagment = request()->segment(2);
 

$scholarshipsCount = \App\Models\SaveScholorship::with('savescholorship')->where('userid',auth()->user()->id)->count();
$schCout =  \App\Models\ScholarshipApplication\ScholarshipApplication::where('user_id',auth()->user()->id)->count();
$schCoutamount =  \App\Models\AmountDistribution::where('user_id',auth()->user()->id)->count();
$schnotificationCoutamount =  \App\Models\Notification::where('user_id',auth()->user()->id)->where('author_name',1)->count();
@endphp
<ul>
  
    <li class="{{$slagment=='dashboard'?'active':''}}">
        <a href="{{route('Student.dashboard')}}">
            <i class="fa fa-id-badge"></i>
            <span>My Profile</span>
        </a>
    </li>
    <li class="{{$slagment=='applied'?'active':''}}">
        <a href="{{route('Student.applied')}}">
            <i class="fa fa-check"></i>
            <span>Applied Scholarships</span>
            <small class="unread-msg">{{$schCout}}</small>
        </a>
    </li>
    <li class="{{$slagment=='awarded'?'active':''}}">
        <a href="{{route('Student.awarded')}}">
            <i class="fa fa-trophy"></i>
            <span>Awarded Scholarships</span>
            <small class="unread-msg">{{$schCoutamount}}</small>
        </a>
    </li>
    <li class="{{$slagment=='saved'?'active':''}}">
        <a href="{{route('Student.saved')}}">
            <i class="fa fa-bookmark"></i>
            <span>Saved Scholarships</span>
            <small class="unread-msg">{{$scholarshipsCount}}</small>
        </a>
    </li>
    <li class="{{$slagment=='notification'?'active':''}}">
        <a href="{{route('Student.notification')}}">
            <i class="fa fa-bell"></i>
            <span>Notifications</span>
            <small class="unread-msg">{{$schnotificationCoutamount}}</small>
        </a>
    </li>
    <li class="{{$slagment=='resourse'?'active':''}}">
        <a href="{{route('Student.resourse')}}">
        <i class="fa fa-chain-broken" aria-hidden="true"> </i>
        <span>Resources and events</span>
            <!-- <small class="unread-msg">15</small> -->
        </a>
    </li>
    <li class="">
        <form id="logout-form" action="{{ route('logout') }}" method="POST"
            style="display: none;">
            @csrf
        </form>
        <a href="{{ route('logout') }}"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fa fa-sign-out"></i>
            {{ __('Logout') }}
        </a>
    </li>
</ul>
