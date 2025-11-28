@extends('templates.admin.master')

@section('content')
    @php
        $dropDown = $allCompany;
        $filedTitle = 'Company';
        $name = 'company_id';
    @endphp
    <div class="loader" style="display: none;"></div>
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- Basic layout-->
                <div class="card my-4">
                    <div class="card-header header-elements-inline">
                        <div class="card-body" id="bank-to-bank">
                            <form action="{{ route('admin.fund.transfer.bankTobank') }}" method="post"
                                id="fund-transfer-bank" name="fund-transfer-bank">
                                @csrf

                                <input type="hidden" name="created_at" class="created_at">
                                <input type="hidden" name="branch_id" class="global_branch_id">
                                <input type="hidden" name="create_application_date" id="create_application_date" class="form-control  create_application_date" readonly>

                                <div class="form-group row">
                                    <div class="form-group col-lg-6">
                                        <div class="form-group row">
                                            <h4 class="mb-0">From Bank</h4>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Select Date <sup>*</sup></label>
                                            <div class="col-lg-8">
                                                <input type="text" name="date" id="date" class="form-control"
                                                    placeholder="Select Date" readonly="" autocomplete="off" required>
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
                                            ])
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Bank<sup>*</sup></label>
                                            <div class="col-lg-8">
                                                <select name="from_bankk" id="from_bankk" class="form-control" required>
                                                    <option value="">---- Please Select----</option>

                                                </select>
                                               
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Bank A/c<sup>*</sup></label>
                                            <div class="col-lg-8">

                                                <select name="from_Bank_account_no" id="from_Bank_account_no"
                                                    class="form-control">
                                                    <option value="">---- Please Select----</option>
                                                </select>
                                                <span class="text-danger " id="msg"></span>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Current Bank Balance<sup>*</sup></label>
                                            <div class="col-lg-8">
                                                <input type="text" name="bank-current-balance" id="bank-current-balance" class="form-control" placeholder="Current Bank Balance" required="" autocomplete="off" readonly="">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Transfer Mode<sup>*</sup></label>
                                            <div class="col-lg-8">
                                                <select class="form-control" id="transfer_mode" name="transfer_mode" required>
                                                    <option value="">---- Please Select ----</option>
                                                    <option value="0">Cheque</option>
                                                    <option value="1">Online Transfer</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row" style="display: none;" id="cheque_no">
                                            <label class="col-form-label col-lg-4">Transfer Cheque No <sup>*</sup></label>
                                            <div class="col-lg-8">
                                                <select class="form-control" id="from_cheque_number"
                                                    name="from_cheque_number">
                                                    <option value="">---- Please Select ----</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row utr_no" style="display: none;">
                                            <label class="col-form-label col-lg-4">UTR No <sup>*</sup></label>
                                            <div class="col-lg-8">
                                                <input type="text" name="from_utr_number" id="from_utr_number"
                                                    class="form-control" placeholder="UTR No" required=""
                                                    autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="form-group row utr_no" style="display: none;">
                                            <label class="col-form-label col-lg-4">RTGS/NEFT Charge <sup>*</sup></label>
                                            <div class="col-lg-8">
                                                <input type="text" name="rtgs_neft_charge" id="rtgs_neft_charge"
                                                    class="form-control" placeholder="RTGS/NEFT Charge" required=""
                                                    autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Transfer Amount<sup>*</sup></label>
                                            <div class="col-lg-8">
                                                <input type="text" name="bank_transfer_amount"
                                                    id="bank_transfer_amount" class="form-control"
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
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Bank A/c<sup>*</sup></label>
                                            <div class="col-lg-8">
                                                <select name="to_Bank_account_no" id="to_Bank_account_no"
                                                    class="form-control">
                                                    <option value="">---- Please Select----</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row utr_no">
                                            <label class="col-form-label col-lg-4">Receive Cheque No/UTR No
                                                <sup>*</sup></label>
                                            <div class="col-lg-8">
                                                <input type="text" name="to_cheque_number" id="to_cheque_number"
                                                    class="form-control" placeholder="Receive Cheque No/UTR No"
                                                    required="" readonly autocomplete="off">
                                            </div>
                                        </div>
                                        {{-- <label class="col-form-label col-lg-12">RTGS/NEFT Charge <sup>*</sup></label>
                                         <div class="col-lg-12">
                                             <input type="text" name="ssb_account_number" id="ssb_account_number" class="form-control" placeholder="SSB Account" required="" autocomplete="off">
                                         </div> --}}
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Receive Amount<sup>*</sup></label>
                                            <div class="col-lg-8">
                                                <input type="text" name="bank_receive_amount" id="bank_receive_amount"
                                                    class="form-control" placeholder="Receive Amount" required=""
                                                    autocomplete="off" readonly>
                                            </div>
                                        </div>
                                       
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Remark<sup>*</sup></label>
                                            <div class="col-lg-8">
                                                <textarea type="text" name="remark" id="remark" class="form-control" placeholder="Remark" required=""
                                                    autocomplete="off"></textarea>
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
                // if ($('#to_bank').val() == bank_id) {
                //     swal("Warning!", "Please select another bank!", "warning");
                //     $('#from_bankk').val('');
                //     return false;  
                // }
                // $('#bank-current-balance').val('');
                getAccountNo(bank_id,'#from_Bank_account_no');

            });
            $('#to_bank').change(function(e) {
                var bank_id = $(this).val();
                // if ($('#from_bankk').val() == bank_id) {
                //     swal("Warning!", "Please select another bank!", "warning");
                //     $('#to_bank').val('');
                //     return false;  
                // }
                getAccountNo(bank_id,'#to_Bank_account_no');

            });
            $('#from_Bank_account_no').change(function () {
                let frombankacc = $(this).val();
                let tobankacc = $('#to_Bank_account_no').val();
                if(frombankacc == tobankacc){
                    swal("Warning!", "Please select another bank account!", "warning");
                    $(this).val('');
                }
            })
            $('#to_Bank_account_no').change(function () {
                let tobankacc = $(this).val();
                let frombankacc = $('#from_Bank_account_no').val();
                if(frombankacc == tobankacc){
                    swal("Warning!", "Please select another bank account!", "warning");
                    $(this).val('');
                }
            })
            function getAccountNo(id, inputId){
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
            $('#from_Bank_account_no').change(function () { 
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
                        $('#bank-current-balance').val(response);
                    
                    }
                });
                
            });
            $('#transfer_mode').change(function () { 
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
                            html += `<option value='${ element.cheque_no }'>${ element.cheque_no }</option>`;
                        });
                        $('#from_cheque_number').html(html);
                       
                    
                    }
                });
                
            });
        });
    </script>
@stop
