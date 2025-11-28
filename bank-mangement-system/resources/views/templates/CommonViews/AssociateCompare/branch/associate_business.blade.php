<div class="container-fluid mt--6">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h4 class="card-title font-weight-semibold">Search Filter</h4>
                </div>
                @include('templates/CommonViews/AssociateCompare/form')
            </div>
        </div>
        <div class="col-lg-12">
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
                                <th>Associate Branch</th>
                                <th>Daily NCC First</th>
                                <th>Daily Renewal First</th>
                                <th>Monthly NCC First</th>
                                <th>Monthly Renewal First</th>
                                <th>FD NCC First</th>
                                <th>NCC First</th>
                                <th>TCC First</th>
                                <th>SSB NCC First</th>
                                <th>SSB TCC First</th>
                                <th>SSB Renewal First</th>
                                <th>New Members First</th>
                                <th>New Associates First</th>
                                <th>New Loans (OTH) First</th>
                                <th>New Loan (LAD) First</th>
                                <th>Loan Amount First</th>
                                <th>LAD Amount First</th>
                                <th>Loan Recovery First</th>
                                <th>LAD Recovery First</th>
                                <th>Daily NCC Second</th>
                                <th>Daily Renewal Second</th>
                                <th>Monthly NCC Second</th>
                                <th>Monthly Renewal Second</th>
                                <th>FD NCC Second</th>
                                <th>NCC Second</th>
                                <th>TCC Second</th>
                                <th>SSB NCC Second</th>
                                <th>SSB TCC Second</th>
                                <th>SSB Renewal Second</th>
                                <th>New Members Second</th>
                                <th>New Associates Second</th>
                                <th>New Loans (OTH) Second</th>
                                <th>New Loan (LAD) Second</th>
                                <th>Loan Amount Second</th>
                                <th>LAD Amount Second</th>
                                <th>Loan Recovery Second</th>
                                <th>LAD Recovery Second</th>
                                <th>Daily NCC Diff.</th>
                                <th>Daily Renewal Diff.</th>
                                <th>Monthly NCC Diff.</th>
                                <th>Monthly Renewal Diff.</th>
                                <th>FD NCC Diff.</th>
                                <th>NCC Diff.</th>
                                <th>TCC Diff.</th>
                                <th>SSB NCC Diff.</th>
                                <th>SSB TCC Diff.</th>
                                <th>SSB Renewal Diff.</th>
                                <th>New Members Diff.</th>
                                <th>New Associates Diff.</th>
                                <th>New Loans (OTH) Diff.</th>
                                <th>New Loan (LAD) Diff.</th>
                                <th>Loan Amount Diff.</th>
                                <th>LAD Amount Diff.</th>
                                <th>Loan Recovery Diff.</th>
                                <th>LAD Recovery Diff.</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>