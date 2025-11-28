@php
    use App\Scopes\ActiveScope;
    // code is modify by Sourab on 20-10-2023 for making changes in global filter on every listing.
    // from now in all company filer only active company will show.
    // for indo changes please add "withoutGlobalScope(new ActiveScope())" in below variable only.
    $companyData = App\Models\Companies::/*withoutGlobalScope(new ActiveScope())
        ->*/select('id', 'name')
        ->get();
    $branch = App\Models\Branch::has('companybranchsAll.company')
    ->select('id', 'name', 'state_id','branch_code')
    ->with([
        'companybranchsAll.company:id,name,short_name'
    ])
    ->get()
    ->toArray();
    $branchName = (isset($branchName)) ? $branchName : "branch_id";  
    $branchId = (isset($branchId))?  $branchId : "branch";  
    $allCB = \App\Models\CompanyBranch::whereStatus('1')->pluck('company_id','branch_id');
@endphp
@if (!isset($all))
    @if (isset($branchShow))
        <div class="col-md-4">
            <div class="form-group row">
                <label class="col-form-label col-lg-12">Company Name</label>
                <div class="col-lg-12 error-msg">
                    <select class="form-control" name="company_id" id="company_id" title="Please Select Company" required="">
                        <option value="0">All Company</option>
                        @foreach ($companyData as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                    <div class="input-group">
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="col-md-4">
            <div class="form-group row">
                <label class="col-form-label col-lg-12">Company Name</label>
                <div class="col-lg-12 error-msg">
                    <select class="form-control" name="company_id" id="company_id" title="Please Select Company"
                        required="">
                        @if (isset($allNot))
                        <option value="" selected>---Please Select Company---</option>
                        @else
                        <option value="0">All Company</option>   
                        @endif
                        @foreach ($companyData as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                    <div class="input-group">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group row">
                <label class="col-form-label col-lg-12">Branch Name</label>
                <div class="col-lg-12 error-msg">
                    <div class="">
                        <select class="form-control" name="{{$branchName}}" id="{{$branchId}}" title="Please Select Branch">
                            <option value="">---Please Select Branch---</option>
                            @if (!isset($allNot))
                                <option value="0" data-id="0" >All Branch</option>
                            @endif
                            @foreach ($branch as $item)
                                @foreach ($item['companybranchs_all'] as $item2)
                                    <option value="{{ $item['id'] }}" data-id="{{ $item2['company_id'] }}" data-val="{{$item['state_id']}}" style="display:none;">{{ $item['name'] }}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <script>
        $(document).on('change', '#company_id', function() {
            let companyId = $(this).val();
            if (companyId == 0) {
                $("#{{$branchId}}").val(companyId);
                $("#{{$branchId}} option[data-id]").hide();
                $('#{{$branchId}} option[data-id="' + companyId + '"]').show();
            } else {
                $('#{{$branchId}}').val('');
                $("#{{$branchId}} option[data-id]").hide();
                $('#{{$branchId}} option[data-id="' + companyId + '"]').show();            
            }
        })
    </script>
@else
    @if (isset($branchShow))
            <div class="col-md-4">
                <div class="form-group row">
                    <label class="col-form-label col-lg-12">Company Name</label>
                    <div class="col-lg-12 error-msg">
                        <select class="form-control" name="company_id" id="company_id" title="Please Select Company" required="">
                            <option value="0">All Company</option>
                            @foreach ($companyData as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                        <div class="input-group">
                        </div>
                    </div>
                </div>
            </div>
        @else
    <div class="col-md-4">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">Company Name</label>
            <div class="col-lg-12 error-msg">
                <select class="form-control" name="company_id" id="company_id" title="Please Select Company"
                    required="">
                    @if (isset($allNot))
                    <option value="" selected>---Please Select Company---</option>
                    @else
                    <option value="0">All Company</option>   
                    @endif
                    @foreach ($companyData as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">Branch Name</label>
            <div class="col-lg-12 error-msg">
                <div class="">
                    <select class="form-control" name="{{$branchName}}" id="{{$branchId}}" title="Please Select Branch">
                        <option value="">---Please Select Branch---</option>
                        <option value="0" >All Branch</option>
                        @foreach ($branch as $item)
                            @foreach ($item['companybranchs_all'] as $item2)
                                <option value="{{ $item['id'] }}" data-id="{{ $item2['company_id'] }}" data-val="{{$item['state_id']}}" data-code="{{$item['branch_code']}}" style="display:none;">{{ $item['name'] }}</option>
                            @endforeach
                                <option value="{{ $item['id'] }}" data-id="{{ $allCB[$item['id']] }}" data-val="{{$item['state_id']}}" data-code="{{$item['branch_code']}}"  data-com="" >{{ $item['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    @endif
    <script>
        $(document).on('change', '#company_id', function() {
            let companyId = $(this).val();
            if (companyId == 0) {
                $("#{{$branchId}}").val(companyId);
                $("#{{$branchId}} option[data-id]").hide();
                // $('#{{$branchId}} option[data-id="' + companyId + '"]').hide();
                $("#{{$branchId}} option[data-com]").show();
            } else {
                $('#{{$branchId}}').val('');
                $("#{{$branchId}} option[data-id]").hide();
                $('#{{$branchId}} option[data-id="' + companyId + '"]').show();            
                $("#{{$branchId}} option[data-com]").hide();
            }
        })
    </script>
@endif

