@extends('templates.admin.master')

@section('content')
@section('css')
<style>
    .hideTableData {
        display: none;
    }
</style>
@endsection
    <div class="content">
        <div class="row">
			<?php 
			$style = '';
			if(isset($page_id)){
				$style = 'style="display:none"';
			} ?>
			<div class="col-md-12" <?=$style?>>
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter </h6>
                    </div>
					
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        	@csrf
							<input type="hidden" name="page_id" value="<?php if(isset($page_id)) echo $page_id; ?>" />
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Card Number </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="card_no" id="card_no" class="form-control"  > 
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Member SSB A/C </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="ssb_ac" id="ssb_ac1" class="form-control"  > 
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm1()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm1()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-12 table-section hideTableData">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Card Payment History</h6>
                    </div>
                    <div class="">
                        <table id="debit_card_transaction_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th> 
                                    <th>Card No.</th>
									<th>Member SSB Account</th>
                                    <th>Amount</th>
                                    <th>Payment Type</th>
                                    <th>Status</th>
                                    <th>Date</th>   
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.debit_card.script_list')
@stop