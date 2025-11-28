<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    {!! Form::open(['url' => null,'class'=>'row p-2','id' => 'filter']) !!}
                    @if($branch_chk == 0)
                    <div class="col-md-4">
                        <div class="col-md-12">
                            {!! Form::label('branch_id', 'Branch', ['class' => 'col-form-label']) !!}
                        </div>
                        <div class="col-md-12">
                            {!! Form::select('branch_id', $branches,'',['class' => 'form-control','id' => 'branch_id'])
                            !!}
                        </div>
                    </div>
                    @endif
                    <div class="col-md-4">
                        <div class="col-md-12">
                            {!! Form::label('member_id', 'Customer ID', ['class' => 'col-form-label']) !!}
                        </div>
                        <div class="col-md-12">
                            {!! Form::text('member_id', null, ['class' => 'form-control','id' => 'customer_id']) !!}
                        </div>
                    </div>
                    <div class="col-md-12 pt-3">
                        <div class="form-group row">
                            <div class="col-lg-12 text-right">
                                {!! Form::button('Submit', ['class' => 'btn btn-primary submit legitRipple', 'onclick'
                                =>
                                'searchForm()']) !!}
                                {!! Form::button('Reset', ['class' => 'btn btn-gray legitRipple', 'onclick' =>
                                'resetForm()']) !!}
                                {!! Form::hidden('is_search', 'no', ['id' => 'is_search']) !!}
                                {!! Form::hidden('branch_chk',$branch_chk, ['id' => 'branch_chk']) !!}
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
        <form action="#" method="post" enctype="multipart/form-data" id="member_filter" name="member_filter">
            @csrf
            <input type="hidden" name="is_search" id="is_search" value="no">
            <input type="hidden" name="member_export" id="member_export" value="">
            <input type="hidden" name="urls" id="urls" value="{{URL::to('/')}}">
        </form>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    @if($admin == true)
                    <h6 class="card-title font-weight-semibold">Blacklist Members List For Loan</h6>
                    @else
                    <div class="col-md-8">
                        <h3 class="mb-0 text-dark">Blacklist Members List For Loan</h3>
                    </div>
                    @endif
                    <div class="">
                        <button type="button" class="btn btn-primary legitRipple export_blacklist_member ml-2" data-extension="0" style="float: right;">Export xslx</button>
                       
                        @if(check_my_permission(Auth::user()->id,'235') == 1 && $admin == true)
                        <a href="@if(Auth::user()->role_id != 3){{URL::to('/')}}/admin/add-blacklistmember-on-loan @else {{URL::to('/')}}/branch/add-blacklist-member-on-loan @endif"><button type="button" class="btn btn-primary legitRipple export ml-2" data-extension="2" style="float: right;">Add Blacklist Member For Loan</button></a>
                        @elseif($admin == false && in_array('Add Members for loan blacklist', auth()->user()->getPermissionNames()->toArray()))

                        <a href="@if(Auth::user()->role_id != 3){{URL::to('/')}}/admin/add-blacklistmember-on-loan @else {{URL::to('/')}}/branch/add-blacklist-member-on-loan @endif"><button type="button" class="btn btn-primary legitRipple export ml-2" data-extension="2" style="float: right;">Add Blacklist Member For Loan</button></a>
                        @endif
 
                    </div>
                </div>
                <div class="" style="width: 100%; overflow-x: auto;">
                    <table id="member_blacklist_on_loan_listing" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Blacklisted On</th>
                                <th>BR Name</th>
                                <th>Customer ID</th>
                                <th>Customer Name</th>
                                <th>Father/ Husband's Name</th>
                                <th>Reason</th>
                                <th>Mobile No</th>
                                <th>Associate Code</th>
                                <th>Associate Name</th>
                                <th>Member Status For Loan</th>
                                <th>Address</th>
                                <th>Status</th>
                                @if( Auth::user()->role_id != 3)<th class="text-center">Action</th>@endif
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal" id="blockedDetail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="width: 800px;">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Blacklisted Reasons</h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            </div>
            <div class="modal-body">
                <div class="" style="width: 100%; overflow-x: auto;">
                    <table id="member_blacklist_details" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S. No.</th>
                                <th>Reason</th>
                                <th>Blacklisted by</th>
                                <th>Name</th>
                                <th>Date</th>
                                <th>Customer ID</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>