@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Credit Card Transactions List</h6>
						<form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
							@csrf
							<input type="hidden" name="credit_card_head_id" id="credit_card_head_id" value="<?php echo $credit_card_head_id; ?>"/>
							<input type="hidden" name="credit_card_id" id="credit_card_id" value="<?php echo $credit_card_id; ?>"/>
						</form>	
                        <div class="">
                        </div>
                    </div>
                    <div class="">
                        <table id="credit_card_transaction_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th class="text-center">S/N</th> 
                                    <th class="text-center">Created Date</th>
                                    <th class="text-center">Branch Name</th>
                                    <th class="text-center">Head name</th>
									<th class="text-center">Narration</th>
                                    <th class="text-center">Payment Mode</th>
                                    <th class="text-center">Debit</th>
                                    <th class="text-center">Credit</th>
                                    <th class="text-center">Balance</th>    
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.credit_card.script_list')
@stop