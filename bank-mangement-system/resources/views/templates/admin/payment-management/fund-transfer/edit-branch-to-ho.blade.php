@extends('templates.admin.master')

@section('content')
    <div class="loader" style="display: none;"></div>
    <div class="content">
        @php
            $dropDown = $allCompany;
            $filedTitle = 'Company';
            $name = 'company_id';
        @endphp
        <div class="row">
            <div class="col-md-12">
                <!-- Basic layout-->
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <div class="card-body" id="branch-to-ho">
                            <form action="{{ route('admin.edit.fund.transfer.head.office') }}" method="post"
                                enctype="multipart/form-data" id="edit-fund-transfer-head-office"
                                name="edit-fund-transfer-head-office">
                                @csrf
                                <input type="hidden" name="id" id="id" value="{{ $detail->id }}">
                                <input type="hidden" name="transfer_type" id="transfer_type" value=0>
                                <input type="hidden" name="branch_id" id="branch_id" value="{{ $branch_id }}">

                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Select Date<sup>*</sup></label>
                                    <div class="col-lg-4">
                                        <input type="text" id="date" name="date" class="form-control"
                                            value="{{ date('d/m/Y', strtotime(convertDate($detail->transfer_date_time))) }}"
                                            readonly>
                                    </div>
                                    @include('templates.GlobalTempletes.role_type', [
                                        'dropDown' => $dropDown,
                                        'filedTitle' => $filedTitle,
                                        'name' => $name,
                                        'value' => '',
                                        'multiselect' => 'false',
                                        'apply_col_md' => false,
                                        'classes' => 'findBranh',
                                        'cid' => $detail->company_id,
                                    ])
                                </div>
                                <div class="form-group row">

                                    <label class="col-form-label col-lg-2">Branch<sup>*</sup></label>
                                    <div class="col-lg-4">
                                        <select name="branch_id" id="edit_branch" class="form-control" readonly>
                                            <option value="" disabled>---- Please Select----</option>
                                            @foreach($branches as $k => $branch)
                                                @if($branch->id == $detail->branch_id)
                                                    <option value="{{ $branch->id }}" selected data-value="{{ $branch->branch_code }}" >{{ $branch->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <label class="col-form-label col-lg-2">Branch Code<sup>*</sup></label>
                                    <div class="col-lg-4">
                                        <input type="text" name="branch_code" id="branch_code" class="form-control" placeholder="Branch Code" readonly="" value="{{ $detail->branch_code }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2"> Bank<sup>*</sup></label>
                                    <div class="col-lg-4">
                                        <select name="bank" id="bank" class="form-control">
                                            <option value="">---- Please Select----</option>
                                            @foreach ($banks as $bank)
                                                <option value="{{ $bank->id }}" @if ($detail->head_office_bank_id == $bank->id) selected @endif> {{ $bank->bank_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <label class="col-form-label col-lg-2">Bank A/c<sup>*</sup></label>
                                    <div class="col-lg-4">
                                        <SELECT name="from_Bank_account_no" id="from_Bank_account_no" class="form-control">
                                            <option value="">---- Please Select----</option>
                                            @foreach ($banks as $value)
                                                @if ($value['bankAccount'])
                                                    <option class="{{ $value->id }}-bank-account bank-account"
                                                        @if ($detail->head_office_bank_account_number === $value['bankAccount']['account_no']) selected @else style="display: none;" @endif>
                                                        {{ $value['bankAccount']['account_no'] }}</option>
                                                @endif
                                            @endforeach
                                        </SELECT>
                                    </div>

                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2"> Transfer Amount <sup>*</sup></label>
                                    <div class="col-lg-4">
                                        <input type="text" name="transfer_amount" id="transfer_amount"
                                            class="form-control" placeholder="0.00" value="{{ $detail->amount }}">
                                    </div>

                                    <label class="col-form-label col-lg-2">Confirm Amount<sup>*</sup></label>
                                    <div class="col-lg-4">
                                        <input type="text" name="conform_transfer_amount" id="conform_transfer_amount"
                                            class="form-control" placeholder="0.00" value="{{ $detail->amount }}">
                                    </div>

                                </div>
                                <div class="form-group row">

                                    <label class="col-form-label col-lg-2">Transfer Mode <sup>*</sup></label>
                                    <div class="col-lg-4">
                                        <select name="transfer_mode" id="transfer_mode" class="form-control">
                                            <option value="">----Select Transfer Mode----</option>
                                            <option value="1" {{ $detail->transfer_mode == 1 ? 'selected' : '' }}>Cash
                                            </option>
                                        </select>
                                    </div>
                                    <label class="col-form-label col-lg-2">Cash In Hand Amount <sup>*</sup></label>
                                    <div class="col-lg-4">
                                        <input type="text" name="micro_daybook_amount" id="micro_daybook_amount"
                                            class="form-control" placeholder="0.00" value="{{$cashInHandAmount??0}}" readonly="">
                                    </div>

                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Upload Bank Slip <sup>*</sup></label>
                                    <div class="col-lg-4">
                                        <span class="signature"><input type="file" class=""
                                                name="bank_slip" /></span>
                                        <input type="hidden" name="hidden_bank_slip" id="hidden_bank_slip" value="{{ $detail->bank_slip_id }}">                                        
                                    </div>
                                </div>
                                @if ($detail->bank_slip_id)
                                <?php $f = getFileData($detail->bank_slip_id)[0]['file_name']; ?>
                                <div class="form-group row">
                                    <div class="col-lg-4">
                                        @if(ImageUpload::fileExists('fund_transfer/'.$f))
                                        <a href="{{ImageUpload::generatePreSignedUrl('fund_transfer/'.$f)}}"  target="_blank" class="from-group" >
                                            <span>{{$f}}</span>                                        
                                        </a>
                                    @endif
                                    </div>
                                </div>
                                @endif                             
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

                                    <label class="col-form-label col-lg-2 online" style="display: none;">No/UTR No</label>
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
        </div>
    </div>
@stop
@section('script')
    @include('templates.admin.payment-management.fund-transfer.partials.script')
    <script>
        $(document).ready(function() {
            $('.findBranh').change(function(e) {
                e.preventDefault();
                var companyId = $(this).val();
                $.ajax({
                    type: "POST",
                    url: "{{ route('admin.fetchbranchbycompanyid') }}",
                    data: {
                        'company_id': companyId,
                        'branch': 'true',
                        'bank': 'true',
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
                                    `<option value="${element[0].id}"  data-value="${element[0].branch_code}">${element[0].name}</option>`;
                            });
                            $('#edit_branch').html(optionBranch);

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
            // $('#conform_transfer_amount').on('change',function(){
            //     var cashinhandamount = $('#micro_daybook_amount').val();
            //     var conform_transfer_amount = $(this).val();
            //     if(){

            //     }
            // });
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
