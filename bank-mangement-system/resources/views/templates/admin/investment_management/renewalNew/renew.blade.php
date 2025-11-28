@extends('templates.admin.master')
@section('content')
    <style type="text/css">
        .greenRow {
            background-color: #00FF00;
        }
    </style>
    <div class="content comp">
        <div class="row">
            <div class="col-md-12">
                <!-- Basic layout-->
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h3 class="mb-0">Renewal Form</h3>
                        <div class="header-elements">
                            <div class="list-icons">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            @php
                                $dropDown = $company;
                                $filedTitle = 'Company';
                                $name = 'company_id';
                            @endphp

                            @include('templates.GlobalTempletes.role_type', [
                                'dropDown' => $dropDown,
                                'filedTitle' => $filedTitle,
                                'name' => $name,
                                'value' => '',
                                'multiselect' => 'false',
                                'apply_col_md' => false,
                                'classes' => 'findBranh',
                            ])
                            <label class="col-form-label col-lg-2">Branch<sup>*</sup></label>
                            <div class="col-lg-4">
                                <select name="branch_id" id="branch" class="form-control valid" title="Please select something!" aria-invalid="false">
                                    <option value="">----Please Select----</option>
                                </select>
                            </div>
                        </div>
                        {{-- @else
                        <div class="col-lg-4">
                            <select name="branch" class="form-control branch">
                                <option value="">Select Branch</option>
                                @foreach (App\Models\Branch::pluck('name', 'id') as $key => $val)
                                <option value="{{ $key }}">{{ $val }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif --}}

                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Investment Plan Type<sup>*</sup></label>
                            <div class="col-lg-4">
                                <select name="renewplan" id="renewplan" class="form-control">
                                    <option value="">Select Plan Type</option>
                                    <option data-val="daily-renew-section" value="0">Daily Renewal</option>
                                    <option data-val="rdfrd-renew-section" value="1">RD/FRD Renewal</option>
                                    <option data-val="deposite-saving-section" value="2">Deposite Saving Account
                                    </option>
                                    <!-- <option data-val="daily-renew-section" value="3">Daily Deposite Money Back Renewal -->
                                    </option>
                                </select>
                            </div>
                        </div>

                        {{Form::open(['url'=>route('admin.renew.new.storeajax'),'method'=>'POST','id'=>'renewal-form','name'=>'renewal-form'])}}
                            {{Form::hidden('renewplan_id','',['id'=>'renewplan_id'])}}
                            {{Form::hidden('transaction_status','',['id'=>'transaction_status'])}}
                            {{Form::hidden('renew_investment_plan_id','',['id'=>'renew_investment_plan_id'])}}
                            {{Form::hidden('member_id','',['id'=>'member_id'])}}
                            {{Form::hidden('deposite_by_name','',['id'=>'deposite_by_name'])}}
                            {{Form::hidden('scheme_name','',['id'=>'scheme_name'])}}
                            {{Form::hidden('branch_id','',['id'=>'branch_id'])}}
                            {{Form::hidden('company','',['id'=>'company'])}}
                            <input type="hidden" name="globalDate" id="create_application_date" class="create_application_date">
                            {{Form::hidden('collector_account_blance','',['id'=>'collector_account_blance'])}}
                            <!------------------- Daily Renewal ------------------->
                            <div class="form-group row daily-renew-section" style="display: none;">
                                <label class="col-form-label col-lg-2">Collector Code<sup>*</sup></label>
                                <div class="col-lg-4">
                                    {{Form::text('collector_code','',['id'=>'collector_code','class'=>'form-control associate-code','data-val'=>'daily-renewal-collector-name','autocomplete'=>'off','placeholder'=>'Collector Code'])}}
                                </div>
                                <label class="col-form-label col-lg-2">Collector Name</label>
                                <div class="col-lg-4">
                                    {{Form::text('collector_name','',['id'=>'collector_name','class'=>'form-control daily-renewal-collector-name','placeholder'=>'Collector Name','readonly'=>''])}}
                                </div>
                            </div>
                            <div class="form-group row daily-renew-section" style="display: none;">
                                <label class="col-form-label col-lg-2">Number of accounts<sup>*</sup></label>
                                <div class="col-lg-4">
                                    {{Form::text('daily_no_of_accounts','',['id'=>'','class'=>'form-control no-of-accounts daily-no-of-accounts','data-val'=>'daily-renew-input-number','autocomplete'=>'off','data-table-class'=>'daily-renew-investment-table','placeholder'=>'Number of accounts'])}}
                                </div>
                            </div>
                            <div class="daily-renew-investment-table renew-account-table" style="display: none;">
                                <table class="table table-flush">
                                    <thead class="">
                                        <tr>
                                            <th>Account Number</th>
                                            <th>Name</th>
                                            {{-- <th>Deno Amount</th> --}}
                                            <th>Maturity Date</th>
                                            <th>Amount</th>
                                            <!-- <th>Associate Code</th> -->
                                            <th>Associate Name</th>
                                        </tr>
                                    </thead>
                                    <tbody class="daily-renew-input-number">
                                    </tbody>
                                </table>
                            </div>
                            <!------------------- Daily Renewal ------------------->
                            <!------------------- RD/FRD Renewal ------------------->
                            <div class="form-group row rdfrd-renew-section" style="display: none;">
                                <label class="col-form-label col-lg-2">Number of accounts<sup>*</sup></label>
                                <div class="col-lg-4">
                                    <input type="text" data-val="rdfrd-renew-input-number" data-table-class="rdfrd-renew-investment-table" name="rdfrd_no_of_accounts" class="form-control no-of-accounts rdfrd-no-of-accounts" placeholder="Number of accounts" autocomplete="off">
                                </div>
                                <label class="col-form-label col-lg-2">Associate Code<sup>*</sup></label>
                                <div class="col-lg-4">
                                    <input type="text" data-val="rdfrd-renewal-associate-name" name="rdfrd_associate_code" id="rdfrd_associate_code" class="form-control associate-code" placeholder="Associate Code" value="" autocomplete="off" required="">
                                </div>
                            </div>
                            <div class="form-group row rdfrd-renew-section" style="display: none;">
                                <label class="col-form-label col-lg-2">Associate Name</label>
                                <div class="col-lg-10">
                                    <input type="text" name="rdfrd_associate_name" id="rdfrd_associate_name" class="form-control rdfrd-renewal-associate-name" placeholder="Associate Name" value="" readonly="">
                                </div>
                            </div>
                            <div class="rdfrd-renew-investment-table renew-account-table" style="display: none;">
                                <table class="table table-flush">
                                    <thead class="">
                                        <tr>
                                            <th>Account Number</th>
                                            <th>Name</th>
                                            <th>Maturity date</th>
                                            {{-- <th>Deno Amount</th> --}}
                                            <th>Amount</th>
                                            <th>Associate Name</th>
                                        </tr>
                                    </thead>
                                    <tbody class="rdfrd-renew-input-number">
                                    </tbody>
                                </table>
                            </div>
                            <!------------------- RD/FRD Renewal ------------------->
                            <!------------------- Deposite SAVING ACCOUNT ------------------->
                            <div class="form-group row deposite-saving-section" style="display: none;">
                                <label class="col-form-label col-lg-2">Associate Code<sup>*</sup></label>
                                <div class="col-lg-4">
                                    <input type="text" data-val="deposite-saving-associate-name" name="associate_code" id="associate_code" class="form-control associate-code" placeholder="Associate Code" value="" autocomplete="off">
                                </div>
                                <label class="col-form-label col-lg-2">Associate Name</label>
                                <div class="col-lg-4">
                                    <input type="text" name="associate_name" id="associate_name" class="form-control deposite-saving-associate-name" placeholder="Associate Name"  value="" readonly="">
                                </div>
                            </div>
                            <div class="deposite-saving-section" style="display: none;">
                                <table class="table table-flush">
                                    <thead class="">
                                        <tr>
                                            <th>Account Number<sup>*</sup></th>
                                            <th>Name</th>
                                            {{--<th>Deno Amount</th>--}}
                                            <th>Amount</th>
                                            <th>Total Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="deposite-saving-input-number">
                                        <tr>
                                            <td>
                                                <input type="text" data-val="0" name="account_number[0]"
                                                    class="form-control account-number account-number-0"
                                                    autocomplete="off" style="width:230px;">
                                                <input type="hidden" name="investment_id[0]" class="investment-id-0">
                                                <input type="hidden" name="investment_tenure[0]" class="investment-tenure-0">
                                                <input type="hidden" name="investment_member_phone_no[0]" class="investment_member_phone_no-0">
                                                <input type="hidden" name="saving_account_balance"
                                                    class="saving-account-balance-0">
                                                <input type="hidden" name="investment_member_id[0]"
                                                    class="investment-member-id-0">
                                            </td>
                                            <td>
                                                <input type="text" data-val="0" name="name[0]" class="form-control name-0" readonly="" tabIndex="-1" style="width:230px;">
                                            </td>
                                            {{--<td>
                                                <input type="text" data-val="0" name="deno_amount[0]" class="form-control deno_amount-0" readonly="" tabIndex="-1" style="width:230px;">
                                            </td>--}}
                                            <td>
                                                <div class="col-lg-12">
                                                    <div class="rupee-img">
                                                    </div>
                                                    <input type="text" data-val="0" name="amount[0]"
                                                        class="form-control saving-amount saving-amount-0 rupee-txt">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="col-lg-12">
                                                    <div class="rupee-img">
                                                    </div>
                                                    <input type="text" data-val="0" name="saving_tatal_amount[0]"
                                                        class="form-control saving-total-amount saving-total-amount-0 rupee-txt"
                                                        readonly="" tabIndex="-1">
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!------------------- Deposite SAVING ACCOUNT ------------------->
                            <div class="form-group row comman-section" style="display: none;">
                                <label class="col-form-label col-lg-2">Payment Mode<sup>*</sup></label>
                                <div class="col-lg-4">
                                    <select name="payment_mode" id="payment_mode" class="form-control"
                                        title="Please select something!">
                                        <option value="">Select Mode</option>
                                        <option data-val="cash" value="0">Cash</option>
                                        <option data-val="cheque-mode" value="1">Cheque</option>
                                        <!--<option data-val="ssb-account" value="2">DD</option>
                                                  <option data-val="online-transaction-mode" value="3">Online transaction</option>-->
                                        {{-- <option data-val="ssb" value="4">SSB</option> --}}
                                    </select>
                                </div>
                                <label class="col-form-label col-lg-2">Total Amount</label>
                                <div class="col-lg-4">
                                    <div class="rupee-img">
                                    </div>
                                    <input type="text" name="total_amount" id="total_amount"
                                        class="form-control total-renew-amount rupee-txt" readonly="">
                                    <label id="balance-error" class="error"></label>
                                </div>
                            </div>
                            <div class="form-group row comman-section" style="display: none;">
                                <label class="col-form-label col-lg-2">Renewal Date</label>
                                <div class="col-lg-4">
                                    <input type="text" name="renewal_date" id="renewal_date" class="form-control"
                                        readonly="">
                                </div>
                                <label class="col-form-label col-lg-2 cash">Available balance in SSB</label>
                                <div class="col-lg-4 cash">
                                    <input type="text" name="available_balance" id="available_balance"
                                        class="form-control" readonly="">
                                </div>
                            </div>
                            <div class="form-group row " id="cheque-detail" style="display: none;">
                                <h3 class="col-lg-12" style="">Cheque Detail</h3>
                                <label class="col-form-label col-lg-2">Cheque Number</label>
                                <div class="col-lg-4">
                                    <select name="cheque_id" id="cheque_id" class="form-control valid"
                                        title="Please select something!" aria-invalid="false">
                                        <option value="">Select cheque number</option>
                                    </select>
                                </div>
                            </div>
                            <div class="  " id="cheque-detail-show" style="display: none;">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Cheque Number</label>
                                    <div class="col-lg-4">
                                        <input type="text" name="cheque-number" id="cheque-number"
                                            class="form-control" readonly>
                                    </div>
                                    <label class="col-form-label col-lg-2">Bank Name</label>
                                    <div class="col-lg-4">
                                        <input type="text" name="bank-name" id="bank-name" class="form-control"
                                            readonly>
                                    </div>
                                </div>
                                <div class=" form-group row">
                                    <label class="col-form-label col-lg-2">Branch Name</label>
                                    <div class="col-lg-4">
                                        <input type="text" name="branch-name" id="branch-name" class="form-control"
                                            readonly>
                                    </div>
                                    <label class="col-form-label col-lg-2">Cheque Date</label>
                                    <div class="col-lg-4">
                                        <input type="text" name="cheque-date" id="cheque-date" class="form-control"
                                            readonly>
                                    </div>
                                </div>
                                <div class=" form-group row">
                                    <label class="col-form-label col-lg-2">Cheque Amount</label>
                                    <div class="col-lg-4">
                                        <div class="rupee-img"></div>
                                        <input type="text" name="cheque-amount" id="cheque-amount"
                                            class="form-control rupee-txt" readonly>
                                    </div>
                                    <label class="col-form-label col-lg-2">Deposit Bank</label>
                                    <div class="col-lg-4">
                                        <input type="text" name="deposit_bank_name" id="deposit_bank_name"
                                            class="form-control" readonly>
                                    </div>
                                </div>
                                <div class=" form-group row">
                                    <label class="col-form-label col-lg-2">Deposit bank Account</label>
                                    <div class="col-lg-4">
                                        <input type="text" name="deposit_bank_account" id="deposit_bank_account"
                                            class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <input type="submit" id="submitform" name="submitform" value="Submit"
                                    class="btn btn-primary submit-renew-form">
                            </div>
                        {{Form::close()}}
                    </div>
                </div>
                <!-- /basic layout -->
            </div>
        </div>
    </div>
    <div class="h1"></div>
@stop
@section('script')
    @include('templates.admin.investment_management.renewalNew.partials.script')
    <script>
        $(document).ready(function() {
            $('.findBranh').change(function(e) {
                $('#branch_code').val('');
                e.preventDefault();
                var companyId = $(this).val();
                $('#renewal-form').trigger('reset');
                $('#company').val(companyId);
                $.ajax({
                    type: "POST",
                    url: "{{ route('admin.fetchbranchbycompanyid') }}",
                    data: {
                        'company_id': companyId,
                        'branch': 'true',
                        'bank': 'false',
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        let myObj = JSON.parse(response);
                        console.log(myObj);
                        if (myObj.branch) {
                            var optionBranch =
                                `<option value="">----Please Select----</option>`;
                            myObj.branch.forEach(element => {
                                optionBranch +=
                                    `<option value="${element.id}"  data-value="${element.branch_code}">${element.name}</option>`;
                            });
                            $('#branch').html(optionBranch);

                        }
                        if (myObj.bank) {
                            var optionBank = `<option value="">----Please Select----</option>`;
                            myObj.bank.forEach(element => {
                                optionBank +=
                                    `<option value="${element.id}">${element.bank_name}</option>`;
                            });
                            $('#bank').html(optionBank);
                        }
                    }
                });
            });
            $('#bank').change(function() {
                var bankId = $(this).val();
                $.ajax({
                    type: "POST",
                    url: "{{ route('admin.getBankAccountNos') }}",
                    data: {
                        'bank_id': bankId,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        let data = JSON.parse(response)
                        let html = ` <option value="">---- Please Select----</option>`;
                        data.forEach(element => {
                            html +=
                                `<option value="${element.account_no}">${element.account_no}</option>`;
                        });
                        $('#from_Bank_account_no').html(html);
                    }
                });

            });


        });
    </script>
@stop
