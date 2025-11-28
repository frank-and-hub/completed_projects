@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Credit Cards List</h6>
                        <div class="">
						<?php if(check_my_permission( Auth::user()->id,"177") == "1"){ ?>
                            <a href="admin/credit-card/create"><button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Add Credit Card</button></a>
						<?php } ?>
                        </div>
                    </div>
                    <div class="">
                        <table id="credit_card_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th> 
                                    <th>Card Type</th>
                                    <th>Card Holder Name</th>
                                    <th>Card Number</th>
                                    <th>Bank Account Number</th>
                                    <th>Credit Card Bank</th>
									<?php if(check_my_permission( Auth::user()->id,"177") == "1" || check_my_permission( Auth::user()->id,"178") == "1" || check_my_permission( Auth::user()->id,"179") == "1" || check_my_permission( Auth::user()->id,"180") == "1"){ ?>
                                    <th class="text-center">Action</th>
									<?php } ?>
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