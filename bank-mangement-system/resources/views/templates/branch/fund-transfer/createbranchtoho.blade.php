@extends('layouts/branch.dashboard')

@section('content')

    <div class="loader" style="display: none;"></div>

    <div class="container-fluid mt--6">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card bg-white">
                        <div class="card-body page-title">
                            <h3 class="">{{ $title }}</h3>
                            <a href="{!! route('branch.fundtransfer.branchtoho') !!}" style="float:right" class="btn btn-secondary">Back</a>
                            <!-- Validate error messages -->
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <!-- Validate error messages -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <!-- Basic layout-->
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <div class="card-body" id="branch-to-ho">
                                <form action="{{ route('branch.fund.transfer.head.office') }}" method="post"
                                    enctype="multipart/form-data" id="fund-transfer-head-office"
                                    name="fund-transfer-head-office">
                                    @csrf
                                    @php
                                        $stateid = getBranchStateByManagerId(Auth::user()->id);
                                    @endphp
                                    <input type="hidden" name="branch_id" id="branch_id" value="{{ $branch_id }}">
                                    <input type="hidden" name="created_at" id="created_at"
                                        value="{{ checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid) }}">

                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-2">Branch<sup>*</sup></label>
                                        <div class="col-lg-4">
                                            <select name="branch_id" id="" class="form-control" readonly>
                                                @foreach ($branches as $branch)
                                                    <option value="{{ $branch->id }}"
                                                        {{ $branch_id == $branch->id ? 'selected' : '' }}>
                                                        {{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <label class="col-form-label col-lg-2">Branch Code<sup>*</sup></label>
                                        <div class="col-lg-4">
                                            @php
                                                $branchLogin = null;
                                                foreach ($branches as $branch) {
                                                    if ($branch_id == $branch->id) {
                                                        $branchLogin = $branch;
                                                    }
                                                }
                                            @endphp
                                            <input type="text" name="branch_code" id="branch_code" class="form-control"
                                                placeholder="Branch Code" value="{{ $branchLogin->branch_code }}"
                                                readonly="">
                                        </div>



                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-2"> Date<sup>*</sup></label>
                                        @php
                                            $stateid = getBranchStateByManagerId(Auth::user()->id);
                                        @endphp
                                        <div class="col-lg-4">
                                            <input type="text" id="date" name="date" class="form-control "
                                                readonly=""
                                                value="{{ headerMonthAvailability(date('d'), date('m'), date('Y'), $stateid) }}">
                                        </div>
                                        <label class="col-form-label col-lg-2">Cash In Hand <sup>*</sup></label>
                                        <div class="col-lg-4">
                                            <input type="text" name="micro_daybook_amount" id="micro_daybook_amount"
                                                class="form-control" placeholder="0.00" readonly="">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-2"> Transfer Amount<sup>*</sup> </label>
                                        <div class="col-lg-4">
                                            <input type="text" name="transfer_amount" id="transfer_amount"
                                                class="form-control" placeholder="0.00">
                                        </div>
                                        <label class="col-form-label col-lg-2">Company<sup>*</sup></label>
                                        <div class="col-lg-4">
                                            <select name="company_id" id="company_id" class="form-control">
                                                <option value="">----Select Company----</option>
                                                @foreach ($company as $item)
                                                    @if (count($item))
                                                        <option value="{{ $item[0]->id }}">{{ $item[0]->name ?? 'n/a' }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>


                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-2">Confirm Amount<sup>*</sup></label>
                                        <div class="col-lg-4">
                                            <input type="text" name="conform_transfer_amount"
                                                id="conform_transfer_amount" class="form-control" placeholder="0.00">
                                        </div>
                                        <label class="col-form-label col-lg-2"> Deposit Bank<sup>*</sup></label>
                                        <div class="col-lg-4">
                                            <select name="bank" id="bank" class="form-control">
                                                <option value="">---- Please Select----</option>
                                            </select>
                                            <input type="hidden" id="bank_to_head_office" name="bank_to_head_office"
                                                value="{{ $banks[0]->account_number }}" />
                                        </div>

                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-2">Deposit Bank A/C<sup>*</sup></label>
                                        <div class="col-lg-4">
                                            <select name="from_Bank_account_no" id="from_Bank_account_no"
                                                class="form-control">
                                                <option value="">---- Please Select----</option>
                                            </select>
                                        </div>
                                        <label class="col-form-label col-lg-2">Transfer Mode <sup>*</sup></label>
                                        <div class="col-lg-4">
                                            <select name="transfer_mode" id="transfer_mode" class="form-control">
                                                <option value="">----Select Transfer Mode----</option>
                                                <!--                                                 <option value="0">Loan</option>
                             -->
                                                <option value="1">Cash</option>
                                            </select>
                                        </div>

                                        <label class="col-form-label col-lg-2 mt-3">Upload Bank Slip <sup>*</sup></label>
                                        <div class="col-lg-4 mt-4">
                                            <span class="signature"><input type="file" class=""
                                                    name="bank_slip" /></span>
                                        </div>

                                    </div>

                                    <!-- <div class="form-group row">
                                                                    
                                                                </div> -->

                                    <div class="form-group row bank" style="display: none;">
                                        <label class="col-form-label col-lg-2">Bank A/c</label>
                                        <div class="col-lg-4">
                                            <input type="text" name="bank_account_number" id="bank_account_number"
                                                class="form-control" placeholder="Bank A/c" readonly="">
                                        </div>

                                        <label class="col-form-label col-lg-2">Select Mode</label>
                                        <div class="col-lg-4">
                                            <select name="bank_mode" id="bank_mode" class="form-control">
                                                <option value="">----Please Select----</option>
                                                <option value="0">Cheque</option>
                                                <option value="1">Online</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-2 cheque" style="display: none;">Cheque
                                            Number</label>
                                        <div class="col-lg-4 cheque" style="display: none;">
                                            <select name="cheque_number" id="cheque_number" class="form-control">
                                                <option value="">----Please Select----</option>
                                                @foreach ($cheques as $key => $val)
                                                    <option value="{{ $val->cheque_no }}">{{ $val->cheque_no }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <label class="col-form-label col-lg-2 online" style="display: none;">No/UTR
                                            No</label>
                                        <div class="col-lg-4 online" style="display: none;">
                                            <input type="text" name="utr_no" id="utr_no" class="form-control">
                                        </div>

                                        <label class="col-form-label col-lg-2 online" style="display: none;">RTGS/NEFT
                                            Charge</label>
                                        <div class="col-lg-4 online" style="display: none;">
                                            <input type="text" name="rtgs_neft_charge" id="rtgs_neft_charge"
                                                class="form-control">
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <input type="submit" name="submitform" value="Submit"
                                            class="btn btn-primary submit-investment">
                                    </div>

                                </form>
                            </div>
                        </div>
                        <!-- /basic layout -->
                    </div>
                </div>
            @stop

            @section('script')
                @include('templates.branch.fund-transfer.partials.script')
                <script>
                    $(document).ready(function() {
                        $('#company_id').change(function(e) {
                            let branchId = $('#branch_id').val();
                            let company_id = $(this).val();
                            let date = $('#date').val();
                            $.ajax({
                                type: "POST",
                                url: "{{ route('branch.getbranchbankbalanceamount') }}",
                                dataType: 'JSON',
                                data: {
                                    'branch_id': branchId,
                                    'entrydate': date,
                                    'company_id': company_id
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                    // alert(response.balance);
                                    $('#micro_daybook_amount').val(response.balance);
                                }
                            });

                            $.ajax({
                                type: "POST",
                                url: "{!! route('branch.getBankListByCompanyId') !!}",
                                dataType: 'JSON',
                                data: {
                                    'company_id': company_id ,
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                  let option =  ` <option value="">---- Please Select----</option>`;
                                  response.forEach(element => {
                                    option +=  `<option value="${element.id}">${element.bank_name}</option>`;
                                  });
                                $('#bank').html(option);
                                }
                            });
                        });








                        $('#bank').change(function() {
                            var bankId = $(this).val();
                            $.ajax({
                                type: "POST",
                                url: "{{ route('branch.getBankAccountNo') }}",
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
                                    //             $('#from_Bank_account_no').html(`
                    // <option value="">---- Please Select----</option>
                    // <option  value="${response.account_no}">${response.account_no}</option>
                    // `);
                                }
                            });

                        });
                    });
                </script>
            @stop
            {{-- branch  --}}
