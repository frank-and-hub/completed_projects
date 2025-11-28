@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
    <style>
        .category-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
            /* justify-content: center; */

        }

        .category-card {
            background-color: white;
            width: 350px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }

        .cross-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: transparent;
            border: none;
            font-size: 10px;
            cursor: pointer;
        }

        .cross-btn:hover {
            color: red;
        }

        .category-header {
            background-color: #48D33A;
            color: white;
            padding-top: 3px;
            padding-bottom: 2px;
            text-align: center;
        }

        .category-content {
            padding: 15px;
            height: 140px;
            overflow-y: cover;
            overflow-x: hidden;
        }

        .category-content img {
            width: 40%;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .park-list {
            list-style: none;
            padding: 0;
        }

        .park-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }

        .park-item img {
            width: 50px;
            height: 50px;
            border-radius: 5px;
        }

        .park-item .cross-btn {
            background-color: transparent;
            border: none;
            font-size: 16px;
            cursor: pointer;
        }

        .park-item .cross-btn:hover {
            color: red;
        }



        /* Basic CSS for grid */
        .grid-container-category-park {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            padding: 10px;
            grid-template-columns: 1fr 1fr 1fr;
        }

        .grid-item-category-park {
            /* background-color: lightblue;
                                                                                                                                                                                                                                                                                                                                                                                                                                                    padding: 20px;
                                                                                                                                                                                                                                                                                                                                                                                                                                                    text-align: center;
                                                                                                                                                                                                                                                                                                                                                                                                                                                    border-radius: 10px;
                                                                                                                                                                                                                                                                                                                                                                                                                                                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); */
        }

        .wrap-normal {
            white-space: normal !important;
        }

        /* Optional: Add responsiveness for smaller screens */
        @media (max-width: 1100px) {
            .grid-container {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .grid-container {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media (max-width: 480px) {
            .grid-container {
                grid-template-columns: 1fr;
                /* 1 column on small screens */
            }


        }
    </style>
    <div class="d-flex align-items-center py-3 mb-4">
        <h5 class="fw-bold mb-0" style="flex: 1"><span><a href="{{ route('admin.dashboard') }}"`><u
                        class="text-primary fw-light">Dashboard</u>
                </a></span><span class="text-primary fw-light"> / </span><span>

            </span>{{ 'Edit City' }}</h5>

    </div>
    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ 'Edit New York city' }}</h5>

                </div>
                <div class="card-body table-responsive ">
                    <label class="form-label" for="basic-icon-default-fullname">Thumbnail Image<span
                            class="text-danger"> *</span></label>
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

                            <button type="button" class="btn btn-outline-secondary account-image-reset mb-4" id=""
                                link="" onclick="rst(this)" default-img-url="{{ asset('images/default.jpg') }}">
                                <i class="bx bx-reset d-block d-sm-none"></i>
                                <span class="d-none d-sm-block">Reset</span>
                            </button>

                            <p class="text-primary mb-0">{{ 'Allowed JPG or PNG. Max size of 2 MB' }}<br>

                            </p>
                        </div>

                    </div>
                    <br>
                    <label class="form-label" for="basic-icon-default-fullname">Banner Image<span
                            class="text-danger"> *</span></label>
                    <div class="d-flex align-items-start align-items-sm-center gap-4">
                        <img src="{{ asset('images/default.jpg') }}" alt="user-avatar" class="d-block rounded"
                            height="100" width="400" style="object-fit: cover;" id="uploadedAvatar" />
                        <div class="button-wrapper">
                            <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                                <span class="d-none d-sm-block">Upload new image</span>
                                <i class="bx bx-upload d-block d-sm-none"></i>
                                <input type="file" id="upload" class="account-file-input" hidden
                                    accept="image/png, image/jpeg" name="image" />
                            </label>

                            <button type="button" class="btn btn-outline-secondary account-image-reset mb-4" id=""
                                link="" onclick="rst(this)" default-img-url="{{ asset('images/default.jpg') }}">
                                <i class="bx bx-reset d-block d-sm-none"></i>
                                <span class="d-none d-sm-block">Reset</span>
                            </button>

                            <p class="text-primary mb-0">{{ 'Allowed JPG or PNG. Max size of 2 MB' }}<br>

                            </p>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label" for="basic-icon-default-fullname">Title<span
                                    class="text-danger"> *</span></label>

                            <input type="text" class="form-control" id="basic-icon-default-fullname"
                                placeholder="Enter Name" aria-label=""required name="name"
                                aria-describedby="basic-icon-default-fullname2" />

                        </div>

                        <div class="col-6 mb-3">
                            <label class="form-label" for="basic-icon-default-fullname">Subtitle<span
                                    class="text-danger"> *</span></label>

                            <input type="text" class="form-control" id="basic-icon-default-fullname"
                                placeholder="Enter Name" aria-label=""required name="name"
                                aria-describedby="basic-icon-default-fullname2" />

                        </div>
                    </div>



                    <div class="d-flex justify-content-center my-4">
                        <button type="button" class="btn btn-primary account-image-reset  "
                            style="justify-content: center; display:flex;" link=""
                            default-img-url="{{ asset('images/default.jpg') }}">
                            <i class="bx bx-reset d-block d-sm-none"></i>
                            <span class="d-none d-sm-block">Save city</span>
                        </button>
                    </div>
                    <hr />

                    <div class="row my-4">
                        <h4 style="flex:1">Container Categories</h4>

                        <a href="{{ route('cities.cat.c') }}" class="btn btn-primary" style="width: auto">
                            <div class="d-flex align-items-center"><i class='bx bx-plus-medical'></i>&nbsp; Add New
                                Container Categories
                            </div>
                        </a>
                    </div>

                    <div class="grid-container-category-park ">
                        <!-- Category Card 2 -->
                        <div class="category-card ">
                            <button class="cross-btn"></button>
                            <div class="category-header">
                                <h4>General</h4>
                            </div>
                            <div class="category-content">
                                <div class="row">
                                    <div class="col-4">
                                        <img src="https://s3-alpha-sig.figma.com/img/54cc/c28d/0a00c8e1d41c8343f7ae9caa27827541?Expires=1739145600&Key-Pair-Id=APKAQ4GOSFWCVNEHN3O4&Signature=otVnQUgZzMbsdOZlKRbo3cekqx4S83G3-~vQv3cG7QuNiVacS61e2Af53JgqsDfhoUa8blBfJGTYJNVpVqIi5gpu8aD4r8x0wpp1K6542CKO2qWGiYsqd1n4encf2h4kVKBhkhATa2cIIxqXeVT-sgMgf3BC21Z44U6~clck1e5TpnxPoeyVH-qacHHKP4oNUrDx4X05YQwJ0EMTBL2VXKZz3ymgMY39BYfHyKt-A7ZfXaUchkT-8s8TTjRqdJ~GDtAJGImCq~QhM5H9kIBm5lovWyQp-2vXoAPttSt4djXcmv00F~j8yPGJo2m~AoIgrINbqFiT~ZJXDz5cKxy5oA__"
                                            alt="Category Image"
                                            style="border:1px solid black; padding:10px; border-radius:4px; object-fit:contain; width: 100%"
                                            class="img-fluid">
                                    </div>
                                    <div class="col-8">
                                        <p
                                            style="overflow: hidden;
text-overflow: ellipsis;
display: -webkit-box;
-webkit-line-clamp: 2;
-webkit-box-orient: vertical;
white-space: normal
">
                                            Best Places in boston </p>
                                        <p> Total Parks : 7</p>
                                    </div>
                                </div>
                                {{-- <p class="wrap-normal">Description: This is a description of Category This is a description
                                    of Category </p> --}}



                            </div>
                        </div>

                        <!-- Category Card 2 -->
                        <div class="category-card ">
                            <button class="cross-btn"></button>
                            <div class="category-header">
                                <h4>Football</h4>
                            </div>
                            <div class="category-content">
                                <div class="row">
                                    <div class="col-4">
                                        <img src="https://s3-alpha-sig.figma.com/img/7131/13a4/ac521dd6be2062a35cfb8035c31f5ac4?Expires=1739145600&Key-Pair-Id=APKAQ4GOSFWCVNEHN3O4&Signature=Zfl-BAAEu6kFMeI~8OwQxPYvPOcFkqMyI0XtQ~0zyZPqcUAC7al6HelstzW5grUFi4wuHQXkAaXRf8fxvdxYZTUXCu9Qr7TZhMZr1aRoXQ1cPvINkj2YwNb1p6whMJ-ImBCPkfAx4T1Gkqp9sgT~MaF3zWyxPmJkR7qfbcrfK9AiFB5OQt5EMe2Y6EC-6kICzbDCpzVnnIhVA7DpYSsJLE0bsqSw2uAq5wkVooKWr2BYw46K3I42qfd6sHl-S96-RVk~uCw472geWwsiO9QyyY2~s6fbbxHctSqhJ5yMk0fCSpooXa5Vf~d7CMh8gFs8UXiBgQWle0Ql7iAdw0N0NA__"
                                            alt="Category Image"
                                            style="border:1px solid black; padding:10px; border-radius:4px; object-fit:contain; width: 100%"
                                            class="img-fluid">
                                    </div>
                                    <div class="col-8">
                                        <p
                                            style="overflow: hidden;
text-overflow: ellipsis;
display: -webkit-box;
-webkit-line-clamp: 2;
-webkit-box-orient: vertical;
white-space: normal
">
                                            Best Football ground in boston </p>
                                        <p> Total Parks : 7</p>
                                    </div>
                                </div>
                                {{-- <p class="wrap-normal">Description: This is a description of Category This is a description
                                    of Category </p> --}}



                            </div>
                        </div>

                        <!-- Category Card 3 -->

                    </div>
                    <br>


                    <div id="form-modal" style="display: none;">
                        <h2>Add Category</h2>
                        <form id="city-form">
                            <div class="row">
                                <div class="d-flex align-items-start align-items-sm-center gap-4">
                                    <img src="{{ asset('images/default.jpg') }}" alt="user-avatar"
                                        class="d-block rounded" height="100" width="100" style="object-fit: cover;"
                                        id="uploadedAvatar" />
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
                                            <h5 class="mb-0">Parks ( Total selected parks: 10 )</h5>
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
                                            </tbody>
                                        </table>
                                        <div id="table-info" style="margin-top: 10px;"></div>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary" type="submit">Submit</button>
                            <button class="btn btn-primary"type="button" id="close-form">Close</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        $('#open-form').on('click', function() {
            $('#form-modal').show();
        });
        $('#close-form').on('click', function() {
            $('#form-modal').hide();
        });
    </script>
@endpush
