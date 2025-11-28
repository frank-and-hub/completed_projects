<?php
$startDatee = (checkMonthAvailability(date('d'), date('m'), date('Y'), 33));
$startDatee = $endDatee = date('d/m/Y', strtotime($startDatee));
?>
<div class="col-md-12">
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold">Search Filter</h6>

        </div>
        @php
        $reportName = Request::segment(3);
        @endphp
        <div class="card-body">
            <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                @csrf
                <input type="hidden" name="globalDate" class="create_application_date" id="globalDate">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-12">Start Date </label>
                            <div class="col-lg-12 error-msg">
                                <input type="text" class="form-control create_application_date" name="start_date" id="start_date">
                                <div class="input-group">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-12">End Date </label>
                            <div class="col-lg-12 error-msg">
                                <input type="text" class="form-control create_application_date" name="end_date" id="end_date">
                                <div class="input-group">
                                </div>
                            </div>
                        </div>
                    </div>
                    @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true])
                    <div class="col-md-12">
                        <div class="form-group row">
                            <div class="col-lg-12 text-right">
                                <input type="hidden" name="is_search" id="is_search" value="no">
                                <input type="hidden" name="export" id="export" value="">
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