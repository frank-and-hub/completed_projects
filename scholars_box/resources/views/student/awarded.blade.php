@extends('student.layout.app')
@section('title', 'Scholarship - Awarded Scholarship')
@section('content')

    <section class="main-inner-banner-one innerpage-banner">
        <div class="blur-1">
            <img src="{{asset('images/Blur_1.png')}}" alt="bg blur">
        </div>
        <div class="blur-2">
            <img src="{{asset('images/Blur_2.png')}}" alt="bg blur">
        </div>
        <div class="banner-one-shape1 animate-this wow fadeIn" data-wow-delay=".7s"></div>
        <div class="banner-one-shape2 animate-this wow fadeIn" data-wow-delay=".9s"></div>
        <div class="banner-one-shape3 animate-this wow fadeIn" data-wow-delay="1s"></div>
        <div class="banner-one-shape4">
            <img src="{{ asset('images/banner-inner-shape-one.png') }}" alt="shap">
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrum-title-one wow fadeInDown">
                        <h1 class="h1-title">Awarded Scholarship</h1>
                        <div class="breadcrum-one">
                            <ul>
                                <li>
                                    <a href="{{url('/')}}">Home</a>
                                </li>
                                <li>
                                    <i class="fa fa-chevron-right"></i>
                                </li>
                                <li>
                                    <a href="">Awarded Scholarship</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="mt-10 mb-10 stu-dashboard">
        <div class="container">
            <div class="row">
                <div class="col-xl-3 col-lg-4 order-1 order-lg-0">
                    <div class="profile-sidebar">
                        <div class="widget-profile pro-widget-content">
                            @include('student.profile_img')
                        </div>
                        <div class="dashboard-widget">
                            <nav class="dashboard-menu">
                                @include('student.sidebar')
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9 col-lg-8 order-2">
                    <div class="card profile-main">
                        <div class="card-body">

                            @if(count($amounts) > 0)
                            @foreach ($amounts as $val)
                                <div class="overflow-x">
                                    <table class="table table-border mt-3">
                                        <thead class="thead-dark">
                                            <tr class="text-center table-heading">
                                                <th colspan="6">Scholarship Disbursal</th>
                                            </tr>
                                            <tr>
                                                <th scope="col">Name of scholarship</th>
                                                <th scope="col">Disbursed Date</th>
                                                <th scope="col">Amount Disbursed</th>
                                                <th scope="col">Account Number</th>
                                                <th scope="col">Account Holder Name</th>
                                                <th scope="col">Receipt</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(count($val->distributionAmount) > 0)
                                                @foreach ($val->distributionAmount as $scholarship)
                                                @php
                                                $carbonDate = \Carbon\Carbon::parse($scholarship->created_at);
                                                $formattedDate = $carbonDate->format('d M Y');
                                                $formattedTime = $carbonDate->format('h:i A');
                                            @endphp
                                                    <tr>
                                                        <th>{{$val->scholarship_name}}</th>
                                                        <td>{{$formattedDate}} {{$formattedTime}}</td>
                                                        <td>Rs. {{$scholarship->amount}}</td>
                                                        <td>{{$scholarship->account_number}}</td>
                                                        <td>{{$scholarship->account_holder_name}}</td>
                                                        <td>
                                                            <span onclick="downloadFile('{{ url($scholarship->receipt) }}', '{{ pathinfo($scholarship->receipt, PATHINFO_EXTENSION) }}')" class="receipt-download">
                                                                <i class="fa fa-download"></i>
                                                            </span>
                                                            {{ str_replace('receipts/', '', $scholarship->receipt) }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                             
                                                        <td colspan="6">
                                                            <input type="checkbox" class="scholarship-checkbox" @if($scholarship->ack == 1) checked @endif data-id="{{$val->id}}" id="scholarship-{{$scholarship->id}}"> 
                                                            <label for="scholarship-{{$scholarship->id}}">
                                                                I'm happy to acknowledge that I have received the above scholarship amount to the mentioned account number and account holder name.
                                                            </label>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="6">No Awarded Scholarship yet</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        @else
                            <p>No Awarded Scholarship yet</p>
                        @endif                               
                        </div>
                        <div id="pat_prescriptions" class="tab-pane fade">
                           <button class="form-control">button</button>
                        </div>
                    </div>
                </div>
            </div>
    </section>
    <div class="modal fade" id="documentModal" tabindex="-1" role="dialog" aria-labelledby="documentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentModalLabel">10th Board Marksheet</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <img src="{{asset('images/document.jpg')}}">
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.scholarship-checkbox').change(function() {
                var scholarshipId = $(this).data('id');
                
                $.ajax({
                    url: '{{ route("Student.save.scholarship") }}', // Adjust the route name as needed
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}', // CSRF token for security
                        id: scholarshipId,
                        checked: $(this).is(':checked') // Check whether the checkbox is checked or not
                    },
                    success: function(response) {
                        toastr.success(response.message);

                        console.log(response.message);
                    },
                    error: function(xhr) {
                        // Handle errors
                        console.log(xhr.responseText);
                    }
                });
            });
        });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', (event) => {
                document.querySelectorAll('.scholarship-checkbox').forEach((checkbox) => {
                    // Disable the checkbox if it is already checked
                    if (checkbox.checked) {
                        checkbox.disabled = true;
                    }
                    // Add event listener to disable the checkbox when it is checked
                    checkbox.addEventListener('change', function() {
                        if (this.checked) {
                            this.disabled = true;
                        }
                    });
                });
            });
        </script>
        
        
@endsection