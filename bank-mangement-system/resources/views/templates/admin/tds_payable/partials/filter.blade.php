<div class="card-header header-elements-inline">
    <h6 class="card-title font-weight-semibold">Search Filter</h6>
    <div class="col-md-8">
    </div>
</div>
    <div class="card-body">
    {{ Form::open(['url' => '#', 'method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'tds_filter', 'class' => 'card-body', 'name' => 'tds_filter']) }}
    <div class="row">
        <div class="col-md-4">
            <div class="form-group row">
                <label class="col-form-label col-lg-12">Transfer Date </label>
                <div class="col-lg-12 error-msg">
                    <div class="input-group">
                        {{Form::text('transfer_date','',['id'=>'transfer_date','class'=>'form-control'])}}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group row">
                <label class="col-form-label col-lg-12">Select TDS Head </label>
                <div class="col-lg-12  error-msg">
                    <select class="form-control " id="tds_head" name="tds_head">
                        <option value="">---- Please Select ----</option>
                        @forelse ($tdsHeads as $key=>$val)
                         <option value="{{ $key }}">{{ ucwords($val) }}</option>
                        @empty
                        <option value=""></option>
                        @endforelse
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group row">
                <div class="col-lg-12 text-right">
                    {{Form::hidden('is_search','no',['id'=>'is_search','class'=>''])}}
                    {{Form::hidden('searchform','Yes',['id'=>'searchform','class'=>''])}}
                    {{Form::hidden('created_at','',['id'=>'created_at','class'=>'created_at'])}}
                    <button type="button" class=" btn bg-dark legitRipple" id="sfilter">Submit</button>
                    <button type="button" class="btn btn-gray legitRipple" id="resetFilter">Reset</button>
                </div>
            </div>
        </div>
    </div>
    {{ Form::close() }}
</div>
