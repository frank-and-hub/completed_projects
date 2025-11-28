@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Dashboard')

<div class="container-fluid">
    <div class="page-content-wrapper">
        <h4 class="my-3">Dashboard</h4>
        <div class="row">
            <div class="col-lg-4 col-md-6">
                <div class="dash-item">
                    <a href="{{ route('admin.userIndex') }}">
                        <div class="dash-item-icon">
                            <img src="{{ asset('assets/images/total-users.svg')}}">
                        </div>
                        <div>
                            <h6>TOTAL USERS</h6>
                            <p class="mb-0">{{$total_users}}</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="dash-item">
                <a href="{{ route('admin.exerciseIndex') }}">
                        <div class="dash-item-icon">
                            <img src="{{ asset('assets/images/exerciesicon.svg') }}">
                        </div>
                        <div>
                            <h6>EXERCISES</h6>
                            <p class="mb-0">{{$total_exercises }}</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="dash-item">
                    <a href="#">
                        <div class="dash-item-icon">
                            <img src="{{ asset('assets/images/total-workout.svg')}}">
                        </div>
                        <div>
                            <h6>Total EXERCISE VIDEO VIEWS</h6>
                            <p class="mb-0">{{$total_exercise_video_views}}</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="dash-item">
                    <a href="#">
                        <div class="dash-item-icon">
                        <img src="{{ asset('assets/images/fitnes-chall.svg')}}">
                        </div>
                        <div>
                            <h6>Weekly Workout Completion Rate</h6>
                            <p class="mb-0">{{$total_weekly_workout_completion_rate}}</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="dash-item">
                    <a href="#">
                        <div class="dash-item-icon">
                        <img src="{{ asset('assets/images/fitnes-chall.svg')}}">
                        </div>
                        <div>
                            <h6>Total Subscription Revenue</h6>
                            <p class="mb-0">{{$total_subscription_revenue}}</p>
                        </div>
                    </a>
                </div>
        </div>
    </div>
    <div class="footer">
    </div>
</div>


@endsection
