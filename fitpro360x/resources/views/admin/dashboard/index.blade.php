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
                    <a href="{{ route('admin.workoutPlansIndex') }}">
                        <div class="dash-item-icon">
                            <img src="{{ asset('assets/images/total-workout.svg')}}">
                        </div>
                        <div>
                            <h6>TOTAL WORKOUTS</h6>
                            <p class="mb-0">{{$total_workouts}}</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="dash-item">
                    <a href="{{ route('admin.fitnessChallengeIndex') }}">
                        <div class="dash-item-icon">
                        <img src="{{ asset('assets/images/fitnes-chall.svg')}}">
                        </div>
                        <div>
                            <h6>FITNESS CHALLENGES</h6>
                           <p class="mb-0">{{$total_fitnessChallenges}}</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="dash-item">
                <a href="{{ route('admin.exerciseIndex') }}">
                        <div class="dash-item-icon">
                            <img src="{{ asset('assets/images/excercise.svg')}}">
                        </div>
                        <div>
                            <h6>EXERCISE</h6>
                           <p class="mb-0">{{$total_exercise}}</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="dash-item">
                    <a href="{{ route('admin.muscleIndex') }}">
                        <div class="dash-item-icon">
                        <img src="{{ asset('assets/images/muscles_type_icon.svg')}}" width="26px">
                        </div>
                        <div>
                            <h6>Muscles</h6>
                            <p class="mb-0">{{$total_musclemaster}}</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="dash-item">
                    <a href="{{ route('admin.bodyTypeIndex')}}">
                        <div class="dash-item-icon">
                        <img src="{{ asset('assets/images/body_type_icon.svg')}}" width="26px">
                        </div>
                        <div>
                            <h6>Body Type</h6>
                            <p class="mb-0">{{$total_bodytype}}</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="footer">
    </div>
</div>


@endsection
