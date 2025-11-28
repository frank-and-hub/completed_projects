<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                @include('templates/CommonViews/Associate/form')
            </div>
        </div>
        <div class="col-md-12" id="table-data">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Associate Business Report</h6>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                    </div>
                </div>
                <div class="">
                    <table id="associate_bussiness_listing" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Company</th>
                                <th>Associate Code</th>
                                <th>Associate Name</th>
                                <th>Associate Branch</th>
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