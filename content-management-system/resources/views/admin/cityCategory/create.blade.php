@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
    <div class="d-flex align-items-center py-3 mb-4">
        <h5 class="fw-bold mb-0" style="flex: 1"><span><a href="{{ route('admin.dashboard') }}"`><u
                        class="text-primary fw-light">Dashboard</u>

                </a></span><span class="text-primary fw-light"> / </span>Add Container New York Categories</h5>

    </div>
    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">


                </div>
                <div id="form-modal " class="card-body">
                    <h2>Add Container Category (New York )</h2>
                    <form id="city-form">
                        <div class="row">
                            <div class="d-flex align-items-start align-items-sm-center gap-4">
                                <img src="{{ asset('images/default.jpg') }}" alt="user-avatar" class="d-block rounded"
                                    height="100" width="100" style="object-fit: cover;" id="uploadedAvatar" />
                                <div class="button-wrapper">
                                    <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                                        <span class="d-none d-sm-block">Upload new image</span>
                                        <i class="bx bx-upload d-block d-sm-none"></i>
                                        <input type="file" id="upload" class="account-file-input" hidden
                                            accept="image/png, image/jpeg" name="image" />
                                    </label>

                                    <button type="button" class="btn btn-outline-secondary account-image-reset mb-4"
                                        id="" link="" onclick="rst(this)"
                                        default-img-url="{{ asset('images/default.jpg') }}">
                                        <i class="bx bx-reset d-block d-sm-none"></i>
                                        <span class="d-none d-sm-block">Reset</span>
                                    </button>

                                    <p class="text-primary mb-0">{{ 'Allowed JPG or PNG. Max size of 2 MB' }}<br>

                                    </p>
                                </div>

                            </div>
                            <div class="col-6 mb-3">
                                <label for="title" class="form-label">Name:</label>
                                <input type="text" id="title" name="title" class="form-control" required>
                            </div>

                            <div class="col-6 mb-3">
                                <label for="title" class="form-label">Title:</label>
                                <input type="text" id="title" name="title" class="form-control" required>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Description:</label>
                                <textarea id="description" name="description" class="form-control"required></textarea>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-xl">
                                <div class="card mb-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Parks ( Total selected parks: 10/100)</h5>
                                        <input type="text" id="search-box" placeholder="Search..."
                                            style="margin-bottom: 10px; display: block;">
                                    </div>

                                    <table class="table table-striped table-hover w-100" id="feature-table">
                                        <thead>
                                            <tr>
                                                <th>Park Name</th>
                                                <th>Address</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><img src='https://parkscape-live.s3.us-east-1.amazonaws.com/0/parks/siuNyLD0jHIonJZ8rGlWs4SgyImWMMGdXJTzOQr1.jpg'
                                                        alt='Logo' height='50px' width='50px'
                                                        style='border-radius: 10px;'> <a class='text-reset'
                                                        style='text-decoration:none;' rel='tooltip'
                                                        title='Go To Details'>10th Avenue & Clement
                                                        Mini Park</a></td>
                                                <td>350 10th Avenue, San Francisco, California 94118, United States</td>

                                                <td>
                                                    <label class="switch" rel="tooltip">
                                                        <input type="checkbox">
                                                        <span class="slider round"></span>
                                                    </label>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><img src='https://parkscape-live.s3.us-east-1.amazonaws.com/0/parks/siuNyLD0jHIonJZ8rGlWs4SgyImWMMGdXJTzOQr1.jpg'
                                                        alt='Logo' height='50px' width='50px'
                                                        style='border-radius: 10px;'> <a class='text-reset'
                                                        style='text-decoration:none;' rel='tooltip'
                                                        title='Go To Details'>10th Avenue & Clement
                                                        Mini Park</a></td>
                                                <td>350 10th Avenue, San Francisco, California 94118, United States</td>

                                                <td>
                                                    <label class="switch" rel="tooltip">
                                                        <input type="checkbox">
                                                        <span class="slider round"></span>
                                                    </label>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><img src='https://parkscape-live.s3.us-east-1.amazonaws.com/0/parks/siuNyLD0jHIonJZ8rGlWs4SgyImWMMGdXJTzOQr1.jpg'
                                                        alt='Logo' height='50px' width='50px'
                                                        style='border-radius: 10px;'>
                                                    <a class='text-reset' style='text-decoration:none;' rel='tooltip'
                                                        title='Go To Details'>10th Avenue & Clement
                                                        Mini Park</a>
                                                </td>
                                                <td>350 10th Avenue, San Francisco, California 94118, United States</td>

                                                <td>
                                                    <label class="switch" rel="tooltip">
                                                        <input type="checkbox">
                                                        <span class="slider round"></span>
                                                    </label>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div id="table-info" style="margin-top: 10px;"></div>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-primary" type="submit">Submit</button>
                        {{-- <button class="btn btn-primary"type="button" id="close-form">Close</button> --}}
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
