<!-- detail.blade.php -->

@extends('admin.layout.master')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        html {
            line-height: 1;
        }

        body {
            background-color: #fff;
            color: white;
            font: 16px arial, sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
        }

        *,
        *:after,
        *:before {
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }

        .container {
            margin: 0 auto;
            max-width: 1024px;
            padding: 0 20px;
            overflow: auto;
        }

        .container table {
            margin: 15px 0 0;
        }

        table {

            border-collapse: collapse;
            //border-spacing: 4px; /* border-spacing works only if border-collapse is separate */
            color: white;
            font: 15px/1.4 "Helvetica Neue", Helvetica, Arial, Sans-serif;
            width: 100%;
        }

        thead {
            background: #395870;
            -webkit-background: linear-gradient(#49708f, #293f50);
            -moz-background: linear-gradient(#49708f, #293f50);
            background: linear-gradient(#49708f, #293f50);
            color: white;
        }

        tbody tr:nth-child(even) {
            background: #f0f0f2;
        }

        /* borders cannot be applied to tr elements or table structure elements. we should follow like below code. */
        tfoot tr:last-child td {
            //border-bottom: 0;
        }

        th,
        td {
            padding: 6px 4px;
            vertical-align: middle;
        }

        td {
            border-bottom: 1px solid #cecfd5;
            border-right: 1px solid #cecfd5;
        }

        td:first-child {
            border-left: 1px solid #cecfd5;
        }

        .book-title {
            color: white;
        }

        .item-stock,
        .item-qty {
            text-align: center;
        }

        .item-price {
            text-align: right;
        }

        .item-multiple {
            display: block;
        }

        /* task */
        .task table {
            margin-bottom: 44px;
        }

        .task a {
            color: white;
            //text-decoration: none;
        }

        .task thead {
            background-color: #f5f5f5;
            -webkit-background: transparent;
            -moz-background: transparent;
            background: transparent;
            color: white;
        }

        .task table th,
        .task table td {
            border-bottom: 0;
            border-right: 0;
        }

        .task table td {
            border-bottom: 1px solid #ddd;
        }

        .task table th,
        .task table td {
            padding-bottom: 22px;
            vertical-align: top;
        }

        .task tbody tr:nth-child(even) {
            background: transparent;
        }

        .task table:last-child {
            //margin-bottom: 0;
        }

        .value {
            color: gray;
        }
    </style>
    <style>
        /* Style for the modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
        }

        /* Style for the modal content */
        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 20px;
            text-align: center;
        }

        /* Style for the close button */
        .close {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
        }
    </style>
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Applicant</a></li>
            <li class="breadcrumb-item active" aria-current="page">Applicant Details</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-md-12 stretch-card">
            <div class="card">

                <div class="col-4" style="padding:30px;">
                    {{-- <select id="application_status" name="application_status" class="form-control" style="float: right;"
                    onchange="handleStatusChange(this.value, {{ $scholarship_id }}, {{ $applicantsDetail->id }})">
                    <option value="" >Select Mark Application Status</option>
                    @foreach (\App\Models\ScholarshipApplication\ScholarshipApplication::STATUS_APPLICATION as $statusKey => $statusValue)
                    <option value="{{ $statusKey }}" {{ $applicantsDetails->status == $statusKey ? 'selected' : '' }}>
                        {{ $statusValue }}
                    </option>
                    @endforeach
                </select> --}}
                    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Enter Custom Status</h5>
                                    {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button> --}}
                                </div>
                                <div class="modal-body">
                                    <textarea id="custom_status_modal" name="custom_status_modal" class="form-control" placeholder="Enter custom status"></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary"
                                        onclick="saveCustomStatus()">Save</button>
                                    {{-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- <h6 class="card-title">Applicant Details</h6> -->

                    <div class="mx-2 w-100">


                        <section class="task">
                            <table class="w-100" >



                                <tbody>

                                    <tr>
                                        <h4 class="card-title">Applicant Personel Details - #{{ $applicantsDetail->id }}</h4>
                                        <th scope="row">
                                                                      
                                        </th>

                                    </tr>
                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>

                                        <td>

                                        </td>

                                        <td>

                                        </td>
                                        <td>

                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>First Name : </h4>
                                        </td>
                                        <td>
                                            <h4 class="value">{{ $applicantsDetail->first_name }}</h4>
                                        </td>

                                        <td>
                                            <h4>Last Name :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">{{ $applicantsDetail->last_name }}</h4>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>Email Address :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">{{ $applicantsDetail->email }}</h4>
                                        </td>
                                        <td>
                                            <h4>Phone Number :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">{{ $applicantsDetail->phone_number }}</h4>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>Date of Birth :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ \Carbon\Carbon::parse($applicantsDetail->date_of_birth)->format('d-m-Y') }}
                                            </h4>

                                        </td>

                                        <td>
                                            <h4>Gender :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">{{ $applicantsDetail->gender }}</h4>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>State :</h4>
                                        </td>
                                        <td>
                                            {{-- <h4 class="value">{{ $applicantsDetail->state }}</h4> --}}
                                            <h4 class="value">{{ ($applicantsDetail->state)? $applicantsDetail->state :$applicantsDetail->student->addressDetails[0]->state  }}</h4>

                                        </td>
                                        <td>
                                            <h4>User type :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">{{ $applicantsDetail->user_type }}</h4>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>Looking For :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">{{ $applicantsDetail->looking_for }}</h4>
                                        </td>
                                        <td>
                                            <h4>Whatsapp Number :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">{{ $applicantsDetail->whatsapp_number }}</h4>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>Aadhar Card Number :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">{{ $applicantsDetail->aadhar_card_number ?? 'Null' }}</h4>
                                        </td>
                                        <td>
                                            <h4>User type :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">{{ $applicantsDetail->user_type }}</h4>
                                        </td>
                                    </tr>


                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>Minority :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">{{ $applicantsDetail->student->is_minority ?? 0 }}</h4>
                                        </td>
                                        <td>
                                            <h4>Minority Group :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                @isset($applicantsDetail->student->is_minority)
                                                    {{ $applicantsDetail->student->is_minority == 0 ? 'Not Applicable' : $applicantsDetail->student->minority_group ?? 'Not Applicable' }}
                                                @endisset
                                            </h4>

                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>Category :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">{{ $applicantsDetail->student->category ?? 'Null' }}</h4>
                                        </td>
                                        <td>
                                            <h4>Pwd Category :</h4>
                                        </td>
                                        <td>
                                            @isset($applicantsDetail->student->is_pwd_category)
                                                <h4 class="value">{{ $applicantsDetail->student->is_pwd_category }}</h4>
                                            @endisset
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>Pwd Percentage :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                @isset($applicantsDetail->student->is_pwd_category)
                                                    {{ $applicantsDetail->student->is_pwd_category == 0 ? 'Not Applicable' : $applicantsDetail->student->pwd_percentage ?? 'Not Applicable' }}
                                                @endisset

                                            </h4>
                                        </td>
                                        <td>
                                            <h4>Profession :</h4>
                                        </td>
                                        <td>
                                            @isset($applicantsDetail->student->occupation)
                                                <h4 class="value">{{ $applicantsDetail->student->occupation }}</h4>
                                            @endisset

                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>veteran category :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                @isset($applicantsDetail->student->is_army_veteran_category)
                                                    {{ $applicantsDetail->student->is_army_veteran_category == 1 ? 'Yes' : 'No' }}
                                                @endisset

                                            </h4>
                                        </td>
                                        <td>
                                            <h4>Army Veteran :</h4>
                                        </td>
                                        <td>
                                            @isset($applicantsDetail->student->is_army_veteran_category)
                                                <h4 class="value">
                                                    {{ $applicantsDetail->student->is_army_veteran_category == 0 ? 'Not Applicable' : $applicantsDetail->student->is_army_veteran_category ?? 'Not Applicable' }}
                                                </h4>
                                            @endisset

                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot></tfoot>
                            </table>
                        </section>


                        <section class="task">
                            <table class="w-100" >



                                <tbody>

                                    <tr>
                                        <h4 class="card-title">Applicant Family Details</h4>
                                        <th scope="row">
                                                                      
                                        </th>

                                    </tr>
                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>

                                        <td>

                                        </td>

                                        <td>

                                        </td>
                                        <td>

                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>Principal/Guardian Name : </h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->guardianDetails->name }}</h4>
                                        </td>

                                        <td>
                                            <h4>Relationship :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->guardianDetails->relationship }}</h4>

                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>Principal/Guardian Occupation:</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->guardianDetails->occupation }}</h4>

                                        </td>
                                        <td>
                                            <h4>Principal/Guardian Mobile:</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->guardianDetails->phone_number }}</h4>

                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>No Of Siblings:</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->guardianDetails->number_of_siblings }}</h4>


                                        </td>
                                        <td>
                                            <h4>Family Anual Income :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->guardianDetails->annual_income }}</h4>


                                        </td>


                                    </tr>

                                </tbody>
                            </table>
                        </section>


                        <section class="task">
                            <table class="w-100" >



                                <tbody>

                                    <tr>
                                        <h4 class="card-title">Applicant Current Address Details</h4>
                                        <th scope="row">
                                                                      
                                        </th>

                                    </tr>
                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>

                                        <td>

                                        </td>

                                        <td>

                                        </td>
                                        <td>

                                        </td>
                                    </tr>
                                 
                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>House Type : </h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->addressDetails[0]->house_type }}</h4>
                                        </td>

                                        <td>
                                            <h4>Address :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->addressDetails[0]->address }}</h4>

                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>State :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->addressDetails[0]->state }}</h4>

                                        </td>
                                        <td>
                                            <h4>District :</h4>
                                        </td>
                                    
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->addressDetails[0]->district?? 'Null' }}</h4>

                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>Pin Code:</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->addressDetails[0]->pincode }}</h4>


                                        </td>



                                    </tr>


                                </tbody>
                            </table>
                        </section>
                        <section class="task">
                            <table class="w-100" >



                                <tbody>

                                    <tr>
                                        <h4 class="card-title">Applicant Permanent Address Details</h4>
                                        <th scope="row">
                                                                      
                                        </th>

                                    </tr>
                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>

                                        <td>

                                        </td>

                                        <td>

                                        </td>
                                        <td>

                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>House Type : </h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->addressDetails[1]->house_type }}</h4>
                                        </td>

                                        <td>
                                            <h4>Address :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->addressDetails[1]->address }}</h4>

                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>Satate :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->addressDetails[1]->state }}</h4>

                                        </td>
                                        <td>
                                            <h4>District :</h4>
                                        </td>
                                
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->addressDetails[0]->district ?? 'Null' }}</h4>

                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>Pin Code:</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->addressDetails[1]->pincode }}</h4>


                                        </td>
                                        <td>
                                            <h4>Citizenship :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->current_citizenship == 1 ? 'Indian' : 'not mentioned' }}
                                            </h4>


                                        </td>



                                    </tr>


                                </tbody>
                            </table>
                        </section>



                        <section class="task">
                            <table class="w-100" >



                                <tbody>

                                    <tr>
                                        <h4 class="card-title">Applicant Employment Details</h4>
                                        <th scope="row">
                                                                      
                                        </th>

                                    </tr>
                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>

                                        <td>

                                        </td>

                                        <td>

                                        </td>
                                        <td>

                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>Employment Type : </h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->employmentDetails->employment_type??'' }}</h4>
                                        </td>

                                        <td>
                                            <h4>Designation :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->employmentDetails->designation ?? '' }}</h4>

                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>Joining Date :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->employmentDetails->joining_date?? 'N/A' }}</h4>

                                        </td>
                                        <td>
                                            <h4>End Date :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->employmentDetails->end_date??'' }}</h4>

                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>Job Role :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->employmentDetails->job_role?? '' }}</h4>


                                        </td>
                                        <td>
                                            <h4>Type :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">Null</h4>


                                        </td>


                                    </tr>


                                </tbody>
                                <tfoot></tfoot>
                            </table>
                        </section>

                        <section class="task">
                            <table class="w-100" >



                                <tbody>

                                    <tr>
                                        <h4 class="card-title">Applicant Guardian Details</h4>
                                        <th scope="row">
                                                                      
                                        </th>

                                    </tr>
                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>

                                        </td>

                                        <td>

                                        </td>
                                        <td>

                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>Name : </h4>
                                        </td>
                                        <td>
                                            <h4 class="value">{{ $applicantsDetail->student->guardianDetails->name }}
                                            </h4>
                                        </td>

                                        <td>
                                            <h4>Relationship :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->guardianDetails->relationship }}</h4>


                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>Profession :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->guardianDetails->occupation }}</h4>


                                        </td>
                                        <td>
                                            <h4>Phone Number :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->guardianDetails->phone_number }}</h4>


                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>Number of Siblings :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->guardianDetails->number_of_siblings }}</h4>



                                        </td>

                                        <td>
                                            <h4>annual_income :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->guardianDetails->annual_income }}</h4>



                                        </td>
                                    </tr>


                                </tbody>
                                <tfoot></tfoot>
                            </table>
                        </section>
                        @php
    $educationDetails = $applicantsDetail->student->educationDetails;
    $totalCount = count($educationDetails);
@endphp

@foreach ($educationDetails as $index => $data)
    @if ($index < $totalCount - 1)
                            <section class="task">

                                <table class="w-100" >



                                    <tbody>

                                        <tr>
                                            <h4 class="card-title">Applicant Education Details</h4>
                                            <th scope="row">

                                            </th>

                                        </tr>
                                        <tr>
                                            <th scope="row">

                                            </th>
                                            <td>

                                            </td>

                                            <td>

                                            </td>
                                            <td>

                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">

                                            </th>
                                            <td>
                                                <h4>Profession : </h4>
                                            </td>
                                            <td>
                                                <h4 class="value">{{ $data->level }}
                                                </h4>
                                            </td>

                                            <td>
                                                <h4>Institute/University :</h4>
                                            </td>
                                            <td>
                                                <h4 class="value">
                                                    {{ $data->institute_name }}</h4>

                                            </td>
                                        </tr>

                                        <tr>
                                            <th scope="row">

                                            </th>
                                            <td>
                                                <h4>Type of Institute :</h4>
                                            </td>
                                            <td>
                                                <h4 class="value">
                                                    {{ $data->institute_type }}</h4>


                                            </td>
                                            <td>
                                                <h4>State :</h4>
                                            </td>
                                            <td>
                                                <h4 class="value">
                                                    {{ $data->state }}</h4>


                                            </td>
                                        </tr>

                                        <tr>
                                            <th scope="row">

                                            </th>
                                            <td>
                                                <h4>District :</h4>
                                            </td>
                                            <td>
                                                <h4 class="value">
                                                    {{ $data->district }}</h4>



                                            </td>

                                            <td>
                                                <h4>Course Name :</h4>
                                            </td>
                                            <td>
                                                <h4 class="value">
                                                    {{ $data->course_name }}</h4>



                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">

                                            </th>
                                            <td>
                                                <h4>Specialisation :</h4>
                                            </td>
                                            <td>
                                                <h4 class="value">
                                                    {{ $data->specialisation }}</h4>



                                            </td>

                                            <td>
                                                <h4>Grading System :</h4>
                                            </td>
                                            <td>
                                                <h4 class="value">
                                                    {{ $data->grade_type }}</h4>



                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">

                                            </th>
                                            <td>
                                                <h4>Percentage scored/CGPA :</h4>
                                            </td>
                                            <td>
                                                <h4 class="value">
                                                    {{ $data->grade }}</h4>



                                            </td>

                                            <td>
                                                <h4>Course Name :</h4>
                                            </td>
                                            <td>
                                                <h4 class="value">
                                                    {{ $data->course_name }}</h4>



                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">

                                            </th>
                                            <td>
                                                <h4>From :</h4>
                                            </td>
                                            <td>
                                                <h4 class="value">
                                                    {{ $data->start_date }}</h4>



                                            </td>

                                            <td>
                                                <h4>to :</h4>
                                            </td>
                                            <td>
                                                <h4 class="value">
                                                    {{ $data->end_date }}</h4>



                                            </td>
                                        </tr>


                                    </tbody>
                                    <tfoot></tfoot>
                                </table>
                            </section>
                            @endif
                        @endforeach
                        <section class="task">
                            <table class="w-100" >



                                <tbody>

                                    <tr>
                                        <h4 class="card-title">Applicant Work Experience Details</h4>
                                        <th scope="row">
                                                                      
                                        </th>

                                    </tr>
                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>

                                        </td>

                                        <td>

                                        </td>
                                        <td>

                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>Employment Type : </h4> 
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->employmentDetails->employment_type?? '' }}
                                            </h4>
                                        </td>

                                        <td>
                                            <h4>Company Name :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->employmentDetails->company_name??'' }}</h4>

                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>Designation :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->employmentDetails->designation?? '' }}</h4>


                                        </td>
                                        <td>
                                            <h4>Job Profile :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->employmentDetails->job_role ??'' }}</h4>


                                        </td>
                                    </tr>

                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>
                                            <h4>Joining Date :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->employmentDetails->joining_date ??'' }}</h4>



                                        </td>

                                        <td>
                                            <h4>Worked Till :</h4>
                                        </td>
                                        <td>
                                            <h4 class="value">
                                                {{ $applicantsDetail->student->employmentDetails->end_date ?? ''}}</h4>



                                        </td>
                                    </tr>


                                </tbody>
                            </table>
                        </section>
                        <section class="task">
                            <table class="w-100" >



                                <tbody>

                                    <tr>
                                        <h4 class="card-title">Applicant Question And Answer Details</h4>
                                        <th scope="row">
                                                                      
                                        </th>

                                    </tr>
                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>

                                        </td>

                                        <td>

                                        </td>
                                        <td>

                                        </td>
                                    </tr>

                                    <?php 
                                    $e = 1;
                                    ?>

                                    @forelse($applicantsDetail->student->documents as $k => $v)
                                        <tr>
                                            @if ($v && $v->scholarship_id == $scholarship_id && $v->document_type == 'que')
                                                <th scope="row">

                                                </th>
                                                <td>
                                                    <h4>{{ ucwords($v->document_type) }} :</h4>
                                                </td>
                                                <td>
                                                    <h4 class="value ">
                                                        {{ $v->document_type == 'que' ? getUploadedQuestions($v->other_document_name) : '' }}
                                                    </h4>
                                                </td>

                                                <td>
                                                    <h4> :</h4>
                                                </td>
                                                <td>
                                                    <h4 class="value">
                                                        <a href="{{ asset('storage/' . $v->document) }}"
                                                            target="_blank">View</a>


                                                    </h4>

                                                </td>
                                                <td>
                                                <input type="checkbox" id="selectAllCheckbox" @if($v->verified == 1) checked @endif onclick="verifydoc({{ $v->id }})"> Verified
                                                    

                                                </td>
                                            @endif
                                        </tr>
                                    @empty

                                    @endforelse


                                    @forelse($applicantsDetails->scholarship->scholarshipQuestionApplication as $k => $v)
                                        <tr>
                                            @if ($v && $v->scholarship_id == $scholarship_id && $v->type != 'document')
                                                <th scope="row">

                                                </th>
                                                <td>
                                                    <h4>{{ ucwords($v->type) }} :</h4>
                                                </td>
                                                <td>
                                                    <h4 class="value ">
                                                        {{ $v->document_type != 'document' ? ucwords($v->question) : '' }} :
                                                    </h4>
                                                </td>

                                                <td>
                                                </td>
                                                <td>
                                                    <h4 class="value">
                                                        <p>
                                                            {{ $v->document_type != 'document' ? getAnswersByQuestions($applicantsDetail->student->id,$scholarship_id ,$v->id) : '' }}
                                                        </p>
                                                    </h4>

                                                </td>
                                                <td>
                                                <input type="checkbox" id="selectAllCheckbox" @if($v->verified == 1) checked @endif onclick="verifydoc({{ $v->id }})"> Verified
                                                </td>
                                            @endif
                                        </tr>
                                    @empty

                                    @endforelse
                                        



                                </tbody>
                            </table>
                        </section>

                        <section class="task">
                            <table class="w-100" >



                                <tbody>

                                    <tr>
                                        <h4 class="card-title">Applicant Document Details</h4>
                                        <th scope="row">
                                                                      
                                        </th>

                                    </tr>
                                    <tr>
                                        <th scope="row">
                                                                      
                                        </th>
                                        <td>

                                        </td>

                                        <td>

                                        </td>
                                        <td>

                                        </td>
                                    </tr>


                                    <?php $e = 1;
                                    
                                    // $d = \App\Models\ScholarshipApplication\ScholarshipApplication::where('user_id',176)->delete();
                                    // echo "<pre>"; print_r($d); echo"</pre>"; die();
                                    // dd($applicantsDetail->student->id);
                                    $jd = scholarshipDocCheck($scholarship_id);
                                    ?>

                                    @forelse($applicantsDetail->student->documents as $k => $v)
                                        {{-- @forelse($applicantsDetails->scholarship->scholarshipQuestionApplication as $k => $v) --}}
                                        <tr>
                                            @if ($v && $v->document_type != 'que' && ((!empty($jd) && in_array($v->document_type, $jd)) || ($v->extra == 'extra')))


                                                <th scope="row">

                                                </th>
                                                <td>
                                                    <h4>{{ ucwords($v->document_type) }} :</h4>
                                                </td>
                                                <td>

                                                    {{-- getAnswersByQuestions($applicantsDetail->student->id,$scholarship_id,$v->id) --}}

                                                </td>

                                                <td>
                                                    <h4> :</h4>
                                                </td>
                                                <td>
                                                    <h4 class="value">
                                                        <a href="{{ asset('storage/' . $v->document) }}"
                                                            target="_blank">View</a>
                                                    </h4>

                                                </td>
                                                <td>
                                                <input type="checkbox" id="selectAllCheckbox" @if($v->verified == 1) checked @endif onclick="verifydoc({{ $v->id }})"> Verified



                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                    @endforelse




                                </tbody>
                            </table>
                        </section>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="pdfModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <!-- Include an iframe for PDF rendering -->
            <iframe id="pdfIframe" width="100%" height="500px" frameborder="0"></iframe>
            <!-- Buttons for the modal -->

            <div class="row">
                <select class="form-control">
                    <option>Select Status</option>
                    <option>Blur</option>
                    <option>Rejected</option>
                    <option>Image not clear</option>
                    <option>Not eligible</option>
                </select>
                <button class="btn btn-primary" onclick="closeModal()">Close</button>
                <button class="btn btn-info" onclick="downloadPDF()">Download PDF</button>
            </div>
        </div>
        <script>
            function openModal(pdfUrl) {
                // Get the modal and iframe elements
                var modal = document.getElementById('pdfModal');
                var iframe = document.getElementById('pdfIframe');

                // Set the source of the iframe to the PDF URL
                iframe.src = 'https://docs.google.com/gview?url=' + encodeURIComponent(pdfUrl) + '&embedded=true';

                // Display the modal
                modal.style.display = 'block';
            }

            function closeModal() {
                // Hide the modal
                document.getElementById('pdfModal').style.display = 'none';
                // Stop the PDF from loading when the modal is closed
                document.getElementById('pdfIframe').src = '';
            }

            function downloadPDF() {
                // You can implement the logic to trigger a download here
                // For example, you can redirect the user to the PDF URL for download
                window.location.href = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';
            }
        </script>
        <script>
            function handleStatusChange(selectedValue, id, applicantId) {
                $('#myModal').modal('show');
            }



            function closeModal() {
                document.getElementById('pdfModal').style.display = 'none';
                document.getElementById('pdfIframe').src = '';
            }

            function downloadPDF() {
                window.location.href = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';
            }

            function updateApplicationsStatus(customStatus, status, sch_id, student_id) {
                var url = "{{ route('admin.updateScholorshipStatus') }}";
                var csrfToken = $('meta[name="csrf-token"]').attr('content');

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: {
                        customStatus: customStatus,
                        status: status,
                        sch_id: sch_id,
                        student_id: student_id,
                        _token: csrfToken,
                    },
                    success: function(data) {
                        if (data.message == 'Success') {
                            toastr.success('Status updated successfully', 'Success', {
                                positionClass: 'toast-top-right',
                                timeOut: 3000,
                            });
                        }
                    },
                    error: function(error) {
                        console.error('Error updating status');
                    }
                });
            }
        </script>
        <script>
function verifydoc(id){
    var url = "{{ route('admin.verifydoc') }}";
                var csrfToken = $('meta[name="csrf-token"]').attr('content');

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: {
                        
                        id: id,
                        
                        _token: csrfToken,
                    },
                    success: function(data) {
                        if (data.message == 'Success') {
                            toastr.success('Status updated successfully', 'Success', {
                                positionClass: 'toast-top-right',
                                timeOut: 3000,
                            });
                        }
                    },
                    error: function(error) {
                        console.error('Error updating status');
                    }
                });
}
            </script>
    @endsection
