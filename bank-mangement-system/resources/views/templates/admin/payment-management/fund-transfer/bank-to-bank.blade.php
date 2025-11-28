@extends('templates.admin.master')



@section('content')

    <div class="content">
        @php
            $dropDown = $company;
            $filedTitle = 'Company';
            $name = 'company_id';
        @endphp
        <div class="row">

            <div class="col-md-12">

                <div class="card">

                    <div class="card-body">

                        <form action="{{ url('admin/update/bank_to_bank') }}" method="post" id="edit-fund-transfer-bank"
                            name="edit-fund-transfer-bank">

                            @csrf
                            <input type="hidden" name="created_at" class="created_at">
                            <input type="hidden" name="old_cheque"  value="@if(isset($chequeId)){{$chequeId}} @endif">
                            <input type="hidden" name="branch_id" class="global_branch_id">
                            <input type="hidden" name="id" class="global_branch_id" value="{{$data->id}}">

                            <div class="form-group row">
                                <div class="form-group col-lg-6">
                                    <div class="form-group row">
                                        <h4 class="mb-0">From Bank</h4>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Select Date <sup>*</sup></label>
                                        <div class="col-lg-8">
                                            <input 
                                            type="text" 
                                            name="date" 
                                            id="date" 
                                            class="form-control"
                                            placeholder="Select Date" 
                                            autocomplete="off"
                                            value="{{ date('d/m/Y', strtotime($data->transfer_date_time)) }}"
                                            readonly
                                            />
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        @include('templates.GlobalTempletes.role_type', [
                                            'dropDown' => $dropDown,
                                            'filedTitle' => $filedTitle,
                                            'name' => $name,
                                            'value' => '',
                                            'multiselect' => 'false',
                                            'col_4' => 'true',
                                            'classes' => 'findBranh',
                                            'cid' => $data->company_id ,
                                        ])
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Bank<sup>*</sup></label>
                                        <div class="col-lg-8">
                                            <select name="from_bank" id="from_bankk" class="form-control">
                                                <option value="">---- Please Select----</option>
                                                {{--@if (isset($fromBank))
                                                <option value="{{$fromBank->id}}" selected>{{$fromBank->bank_name}}</option>
                                                @endif--}}
                                                @foreach($banks as $k => $v)
                                                <option value="{{$v->id}}" {{ ($fromBank->id == $v->id) ? 'selected' : '' }} >{{$v->bank_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Bank A/c<sup>*</sup></label>
                                        <div class="col-lg-8">

                                            <select name="from_Bank_account_no" id="from_Bank_account_no"
                                                class="form-control">
                                                <option value="">---- Please Select----</option>
                                                @if (isset($fromBankno))
                                                <option value="{{$fromBankno->id}}" selected>{{$fromBankno->account_no}}</option>
                                                @endif
                                            </select>
                                            <span class="text-danger " id="msg"></span>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Current Bank Balance<sup>*</sup></label>
                                        <div class="col-lg-8">
                                            <input type="text" name="bank-current-balance" id="bank-current-balance" class="form-control" placeholder="Current Bank Balance" required="" autocomplete="off" readonly="" value="{{$data->micro_day_book_amount}}">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Transfer Mode<sup>*</sup></label>
                                        <div class="col-lg-8">
                                            <select class="form-control" id="transfer_mode" name="transfer_mode">
                                                <option value="">---- Please Select ----</option>
                                                <option {{ $data->btb_tranfer_mode == 0 ? 'selected' : '' }} value="0">
                                                    Cheque</option>
                                                <option {{ $data->btb_tranfer_mode == 1 ? 'selected' : '' }} value="1">
                                                    Online Transfer</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row"
                                        @if ($data->btb_tranfer_mode != 0) style="display: none;" @endif id="cheque_no">

                                        <label class="col-form-label col-lg-4">Transfer Cheque No <sup>*</sup></label>

                                        <div class="col-lg-8">

                                            <select class="form-control" id="from_cheque_number" name="from_cheque_number">

                                                <option value="">---- Please Select ----</option>
                                                @if (isset($data))
                                                <option value="{{$data->to_cheque_utr_no}}" selected>{{$data->to_cheque_utr_no}}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row utr_no"
                                        @if ($data->btb_tranfer_mode != 1) style="display: none;" @endif>

                                        <label class="col-form-label col-lg-4">UTR No <sup>*</sup></label>

                                        <div class="col-lg-8">

                                            @if ($data->btb_tranfer_mode == 1)
                                                <input type="text" name="from_utr_number" id="from_utr_number"
                                                    class="form-control" placeholder="UTR No"
                                                    value="{{ $data->from_cheque_utr_no }}" required=""
                                                    autocomplete="off">
                                            @else
                                                <input type="text" name="from_utr_number" id="from_utr_number"
                                                    class="form-control" placeholder="UTR No" required=""
                                                    autocomplete="off">
                                            @endif

                                        </div>

                                    </div>
                                    <div class="form-group row utr_no"
                                        @if ($data->btb_tranfer_mode != 1) style="display: none;" @endif>

                                        <label class="col-form-label col-lg-4">RTGS/NEFT Charge <sup>*</sup></label>

                                        <div class="col-lg-8">

                                            <input type="text" name="rtgs_neft_charge" id="rtgs_neft_charge"
                                                class="form-control" placeholder="RTGS/NEFT Charge"
                                                value="{{ $data->rtgs_neft_charge }}" required="" autocomplete="off">

                                        </div>

                                    </div>
                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-4">Transfer Amount<sup>*</sup></label>

                                        <div class="col-lg-8">

                                            <input type="text" name="bank_transfer_amount" id="bank_transfer_amount"
                                                class="form-control" value="{{ $data->transfer_amount }}"
                                                placeholder="Transfer Amount" required="" autocomplete="off">

                                        </div>

                                    </div>

                                </div>
                                <div class="form-group col-lg-6">
                                    <div class="form-group row">
                                        <h4 class="mb-0">To Bank</h4>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Bank<sup>*</sup></label>
                                        <div class="col-lg-8">
                                            <select name="to_bank" id="to_bank" class="form-control">
                                                <option value="">---- Please Select----</option>
                                                {{--@if (isset($toBank))
                                                <option value="{{$toBank->id}}" selected>{{$toBank->bank_name}}</option>
                                                @endif--}}
                                                @foreach($banks as $k => $v)
                                                <option value="{{$v->id}}" {{ $toBank->id == $v->id ? 'selected' : '' }} >{{$v->bank_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Bank A/c<sup>*</sup></label>
                                        <div class="col-lg-8">
                                            <select name="to_Bank_account_no" id="to_Bank_account_no"
                                                class="form-control">
                                                <option value="">---- Please Select----</option>
                                                @if (isset($toBankno))
                                                <option value="{{$toBankno->id}}" selected>{{$toBankno->account_no}}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row utr_no"    @if ($data->btb_tranfer_mode != 1) style="display: none;" @endif>
                                        <label class="col-form-label col-lg-4">Receive Cheque No/UTR No
                                            <sup>*</sup></label>
                                        <div class="col-lg-8">
                                            <input type="text" name="to_cheque_number" id="to_cheque_number"
                                                class="form-control" placeholder="Receive Cheque No/UTR No"
                                                required="" value="{{ $data->from_cheque_utr_no }}" readonly
                                                autocomplete="off">

                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Receive Amount<sup>*</sup></label>
                                        <div class="col-lg-8">

                                            <input type="text" name="bank_receive_amount" id="bank_receive_amount"
                                                class="form-control" placeholder="Receive Amount" required=""
                                                value="{{ $data->receive_amount }}" autocomplete="off" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Remark<sup>*</sup></label>
                                        <div class="col-lg-8">
                                            <textarea type="text" name="remark" id="remark" class="form-control" placeholder="Remark" required=""
                                                autocomplete="off">{{ $data->remark }}</textarea>
                                        </div>
                                    </div>
                                 
                                </div>
                            </div>


                            <div class="text-right">

                                <input type="submit" name="submitform" value="Submit"
                                    class="btn btn-primary submit-investment">

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

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
                        'bank': 'true',
                        'branch': 'no',
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        let myObj = JSON.parse(response);
                        if (myObj.bank) {
                            var optionBank = `<option value="">----Please Select----</option>`;
                            myObj.bank.forEach(element => {
                                optionBank +=
                                    `<option value="${element.id}">${element.bank_name}</option>`;
                            });
                            $('#from_bankk').html(optionBank);
                            $('#to_bank').html(optionBank);
                        }
                    }
                });
            });


            // get account from bank id 
            $('#from_bankk').change(function(e) {
                var bank_id = $(this).val();
                if ($('#to_bank').val() == bank_id) {
                    swal("Warning!", "Please select another bank!", "warning");
                    $('#from_bankk').val('');
                    return false;
                }
                $('#bank-current-balance').val('');
                getAccountNo(bank_id, '#from_Bank_account_no');

            });
            $('#to_bank').change(function(e) {
                var bank_id = $(this).val();
                if ($('#from_bankk').val() == bank_id) {
                    swal("Warning!", "Please select another bank!", "warning");
                    $('#to_bank').val('');
                    return false;
                }
                getAccountNo(bank_id, '#to_Bank_account_no');

            });

            function getAccountNo(id, inputId) {
                $.ajax({
                    type: "POST",
                    url: "{{ route('admin.getBankAccountNos') }}",
                    data: {
                        'bank_id': id,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        let data = JSON.parse(response)
                        let html = ` <option value="">---- Please Select----</option>`;
                        data.forEach(element => {
                            html +=
                                `<option value="${element.id}">${element.account_no}</option>`;
                        });
                        $(inputId).html(html);
                    }
                });
            }

            // get bank balance by bank to bank 
            $('#from_Bank_account_no').change(function() {
                let date = $('#date').val();
                let company_id = $('#company_id').val();
                let from_bankk = $('#from_bankk').val();
                let from_Bank_account_no = $('#from_Bank_account_no').val();
                if (date == "") {
                    swal("Warning!", "Please select date!", "warning");
                    return false;
                }
                $.ajax({
                    type: "POST",
                    url: "{{ route('admin.bankToBankBalance') }}",
                    data: {
                        'bank_id': from_bankk,
                        'company_id': company_id,
                        'date': date,
                        'account_no': from_Bank_account_no,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        // alert(response);
                        console.log(response);
                        $('#bank-current-balance').val(response);
                    }
                });

            });
            $('#transfer_mode').change(function() {
                let from_bankk = $('#from_bankk').val();
                let from_Bank_account_no = $('#from_Bank_account_no').val();
                if (date == "") {
                    swal("Warning!", "Please select date!", "warning");
                    return false;
                }
                $.ajax({
                    type: "POST",
                    url: "{{ route('admin.getchecks') }}",
                    data: {
                        'bank_id': from_bankk,
                        'account_no': from_Bank_account_no,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        let cheques = JSON.parse(response);
                        let html = `<option value="">---- Please Select ----</option>`;
                        console.log(cheques);
                        cheques.forEach(element => {
                            html +=
                                `<option value='${ element.cheque_no }'>${ element.cheque_no }</option>`;
                        });
                        $('#from_cheque_number').html(html);


                    }
                });

            });
        });
    </script>

@stop
