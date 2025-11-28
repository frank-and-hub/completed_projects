<div class="container-fluid mt--6">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h4 class="card-title font-weight-semibold">Search Filter</h4>
                </div>
                @include('templates/CommonViews/Associate/form')
            </div>
        </div>
        <div class="col-lg-12" id="table-data">
            <div class="card bg-white shadow">
                <div class="card-header bg-transparent">
                    <div class="row">
                        <div class="col-md-8">
                            <h3 class="mb-0 text-dark">Associate Business Report</h3>
                        </div>
                        <div class="col-md-4 text-right">
                            <button type="button" class="btn btn-primary legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="associate_bussiness_listing" class="table datatable-show-all">
                    <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Company</th>
                                <th>Associate Code</th>
                                <th>Associate Name</th>
                                {{-- <th>Associate Branch</th> --}}
                                <th>Daily NCC</th>
                                <th>Daily Renewal</th>
                                <th>Monthly NCC</th>
                                <th>Monthly Renewal</th>
                                <th>FD NCC</th>
                                <th>NCC</th>
                                <th>TCC</th>
                                <th>SSB NCC</th>
                                <th>SSB Renewal</th>
                                <th>Total NCC</th>
                                <th>Total TCC</th>
                                <th>New Loans (OTH)</th>
                                <th>Loan Amount</th>
                                <th>Loan Recovery</th>
                                <th>New Loan (LAD)</th>
                                <th>LAD Amount</th>
                                <th>LAD Recovery</th>
                                <th>Maturity Payment</th>
                                <th>New Members</th>
                                <th>New Associates</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>