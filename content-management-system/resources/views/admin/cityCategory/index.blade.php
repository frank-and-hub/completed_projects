@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
    <div class="d-flex align-items-center py-3 mb-4">
        <h5 class="fw-bold mb-0" style="flex: 1"><span><a href="{{ route('admin.dashboard') }}"`><u
                        class="text-primary fw-light">Dashboard</u>

                </a></span><span class="text-primary fw-light"> / </span>New York Categories</h5>
        <div class=" justify-content-end">
            <a href="{{ route('cities.cat.c') }}" class="btn btn-primary">
                <div class="d-flex align-items-center"><i class='bx bx-plus-medical'></i>&nbsp; Add New Categories</div>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Categories (New York )</h5>
                    <input type="text" id="search-box" placeholder="Search..."
                        style="margin-bottom: 10px; display: block;">
                </div>

                <table class="table table-striped table-hover w-100" id="feature-table">
                    <thead>
                        <tr>
                            <th>Category Name</th>
                            <th>Title</th>
                            <th> Total Parks </th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><img src='https://s3-alpha-sig.figma.com/img/54cc/c28d/0a00c8e1d41c8343f7ae9caa27827541?Expires=1739145600&Key-Pair-Id=APKAQ4GOSFWCVNEHN3O4&Signature=otVnQUgZzMbsdOZlKRbo3cekqx4S83G3-~vQv3cG7QuNiVacS61e2Af53JgqsDfhoUa8blBfJGTYJNVpVqIi5gpu8aD4r8x0wpp1K6542CKO2qWGiYsqd1n4encf2h4kVKBhkhATa2cIIxqXeVT-sgMgf3BC21Z44U6~clck1e5TpnxPoeyVH-qacHHKP4oNUrDx4X05YQwJ0EMTBL2VXKZz3ymgMY39BYfHyKt-A7ZfXaUchkT-8s8TTjRqdJ~GDtAJGImCq~QhM5H9kIBm5lovWyQp-2vXoAPttSt4djXcmv00F~j8yPGJo2m~AoIgrINbqFiT~ZJXDz5cKxy5oA__'
                                    alt='Logo' height='50px' width='50px' style='border-radius: 10px;'> <a
                                    class='text-reset' style='text-decoration:none;' rel='tooltip'
                                    title='Go To Details'>General</a></td>
                            <td>Best General in boston</td>
                            <td> 2</td>
                            <td>
                                <a class="btn btn-icon btn-primary" href='{{ route('cities.edit') }}'><span
                                        class="tf-icons bx bx-edit"></span></a>

                            </td>
                        </tr>
                        <tr>
                            <td><img src='https://s3-alpha-sig.figma.com/img/7131/13a4/ac521dd6be2062a35cfb8035c31f5ac4?Expires=1739145600&Key-Pair-Id=APKAQ4GOSFWCVNEHN3O4&Signature=Zfl-BAAEu6kFMeI~8OwQxPYvPOcFkqMyI0XtQ~0zyZPqcUAC7al6HelstzW5grUFi4wuHQXkAaXRf8fxvdxYZTUXCu9Qr7TZhMZr1aRoXQ1cPvINkj2YwNb1p6whMJ-ImBCPkfAx4T1Gkqp9sgT~MaF3zWyxPmJkR7qfbcrfK9AiFB5OQt5EMe2Y6EC-6kICzbDCpzVnnIhVA7DpYSsJLE0bsqSw2uAq5wkVooKWr2BYw46K3I42qfd6sHl-S96-RVk~uCw472geWwsiO9QyyY2~s6fbbxHctSqhJ5yMk0fCSpooXa5Vf~d7CMh8gFs8UXiBgQWle0Ql7iAdw0N0NA__'
                                    alt='Logo' height='50px' width='50px' style='border-radius: 10px;'> <a
                                    class='text-reset' style='text-decoration:none;' rel='tooltip'
                                    title='Go To Details'>Football</a></td>
                            <td>Best Football Grounds in boston</td>
                            <td> 10</td>
                            <td>
                                <a class="btn btn-icon btn-primary" href='{{ route('cities.edit') }}'><span
                                        class="tf-icons bx bx-edit"></span></a>

                            </td>
                        </tr>
                        <tr>
                            <td><img src='https://parkscape-live.s3.us-east-1.amazonaws.com/0/parks/siuNyLD0jHIonJZ8rGlWs4SgyImWMMGdXJTzOQr1.jpg'
                                    alt='Logo' height='50px' width='50px' style='border-radius: 10px;'> <a
                                    class='text-reset' style='text-decoration:none;' rel='tooltip'
                                    title='Go To Details'>Playground</a></td>
                            <td>Best Playground in boston</td>
                            <td> 20</td>
                            <td>
                                <a class="btn btn-icon btn-primary" href='{{ route('cities.edit') }}'><span
                                        class="tf-icons bx bx-edit"></span></a>

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
