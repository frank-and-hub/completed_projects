@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
    <div class="d-flex align-items-center py-3 mb-4">
        <h5 class="fw-bold mb-0" style="flex: 1"><span><a href="{{ route('admin.dashboard') }}"`><u
                        class="text-primary fw-light">Dashboard</u>

                </a></span><span class="text-primary fw-light"> / </span>Cities</h5>

    </div>

    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Cities</h5>
                    <input type="text" id="search-box" placeholder="Search..."
                        style="margin-bottom: 10px; display: block;">
                </div>

                <table class="table table-striped table-hover w-100" id="feature-table">
                    <thead>
                        <tr>
                            <th>City Name</th>
                            <th>State</th>
                            <th>Country</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>New York</td>
                            <td>New York</td>
                            <td>USA</td>
                            <td>
                                <label class="switch" rel="tooltip">
                                    <input type="checkbox">
                                    <span class="slider round"></span>
                                </label>
                                <a class="btn btn-icon btn-primary" href='{{ route('cities.edit') }}'><span
                                        class="tf-icons bx bx-edit"></span></a>


                                <a class="btn btn-icon btn-primary" href="{{ route('cities.cat') }}"><span
                                        class="tf-icons bx bx-plus"></span></a>
                            </td>
                        </tr>
                        <tr>
                            <td>Los Angeles</td>
                            <td>California</td>
                            <td>USA</td>
                            <td>
                                <label class="switch" rel="tooltip">
                                    <input type="checkbox">
                                    <span class="slider round"></span>
                                </label>
                                <button class="btn btn-icon btn-primary"><span class="tf-icons bx bx-edit"></span></button>


                                <a class="btn btn-icon btn-primary" href="{{ route('cities.cat') }}"><span
                                        class="tf-icons bx bx-plus"></span></a>
                            </td>
                        </tr>
                        <tr>
                            <td>Toronto</td>
                            <td>Ontario</td>
                            <td>Canada</td>
                            <td>
                                <label class="switch" rel="tooltip">
                                    <input type="checkbox">
                                    <span class="slider round"></span>
                                </label>
                                <button class="btn btn-icon btn-primary"><span class="tf-icons bx bx-edit"></span></button>


                                <a class="btn btn-icon btn-primary" href="{{ route('cities.cat') }}"><span
                                        class="tf-icons bx bx-plus"></span></a>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div id="table-info" style="margin-top: 10px;"></div>
            </div>
        </div>
    </div>


    <script>
        $(document).ready(function() {
            $('#city-table').DataTable();
        });
    </script>
@endsection
