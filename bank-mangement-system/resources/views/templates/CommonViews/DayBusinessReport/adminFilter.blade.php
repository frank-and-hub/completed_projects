<?php
$data = '';
$startDatee = (checkMonthAvailability(date('d'), date('m'), date('Y'), 33));
$startDatee = $endDatee = date('d/m/Y', strtotime($startDatee));
?>
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        <input type="hidden" name="create_application_date" id="create_application_date" class="form-control  create_application_date">
                        @csrf
                        <div class="row">
                            <div class="col-lg-4 ">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12"> Company Name<sup>*</sup> </label>
                                    <div class="col-lg-12 error-msg">
                                        <select name="company_id" id="company_id" class="form-control">
                                            <option value="">----Please Select Company----</option>
                                            <option value="0">ALL Company</option>
                                            @foreach($company as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 ">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12"> Branch Name<sup>*</sup> </label>
                                    <div class="col-lg-12 error-msg">
                                        <select name="branch" id="branch" class="form-control">
                                            <option value="">----Please Select Branch----</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12"> Start Date<sup>*</sup></label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" class="form-control " name="start_date" id="start_date" autocomplete="off" readonly value="{{$startDatee}}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12"> End Date<sup>*</sup></label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" class="form-control " name="end_date" id="end_date" autocomplete="off" readonly value="{{$startDatee}}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group text-right">
                                    <div class="col-lg-12 page">
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()">Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div id="report_data"></div>
        </div>
    </div>
</div>
{{--@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        alert('lk;lk;kl;');
        $('.export').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            var formData = jQuery('#filter').serializeObject();
            var chunkAndLimit = 50;
            $(".spiners").css("display", "block");
            $(".loaders").text(Math.floor(Math.random() * 10));
            doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit, 1, '');
            $("#cover").fadeIn(100);
        });
        // function to trigger the ajax bit
        function doChunkedExport(start, limit, formData, chunkSize, page, fileName) {
            formData['start'] = start;
            formData['limit'] = limit;
            formData['page'] = page;
            formData['fileName'] = fileName;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('admin.dayBook.report.Export') !!}",
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.result == 'next') {
                        start = start + chunkSize;
                        page = page + 1;
                        doChunkedExport(start, limit, formData, chunkSize, page, fileName);
                        $(".loaders").text(response.percentage + "%");
                    } else {
                        var csv = response.fileName;
                        console.log('DOWNLOAD');
                        $(".spiners").css("display", "none");
                        $("#cover").fadeOut(100);
                        window.open(csv, '_blank');
                    }
                }
            });
        }
        jQuery.fn.serializeObject = function() {
            var o = {};
            var a = this.serializeArray();
            jQuery.each(a, function() {
                if (o[this.name] !== undefined) {
                    if (!o[this.name].push) {
                        o[this.name] = [o[this.name]];
                    }
                    o[this.name].push(this.value || '');
                } else {
                    o[this.name] = this.value || '';
                }
            });
            return o;
        };
    });
</script>
@endsection--}}