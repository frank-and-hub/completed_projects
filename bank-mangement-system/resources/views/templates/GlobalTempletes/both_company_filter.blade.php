@php
    use App\Scopes\ActiveScope;
    $companyData = App\Models\Companies::withoutGlobalScope(new ActiveScope())
        ->select('id', 'name')
        ->get();
    $branch = App\Models\Branch::select('id', 'name','state_id')
        ->with('companybranchsAll:id,company_id,branch_id')
        ->get()
        ->toArray();
    $branchName = (isset($branchName))?  $branchName : "branch_id";  
    $branchId = (isset($branchId))?  $branchId : "branch";  
@endphp
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
                            <option value="0" data-id="0">All Branch</option> 
                        @endif
                        @foreach ($branch as $item)
                            @foreach ($item['companybranchs_all'] as $item2)
                                <option value="{{ $item['id'] }}" data-id="{{ $item2['company_id'] }}" data-val="{{$item['state_id']}}"
                                    style="display:none;">{{ $item['name'] }}</option>
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