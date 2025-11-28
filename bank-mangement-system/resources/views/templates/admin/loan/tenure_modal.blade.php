<div class="modal fade" id="tenureModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form name='loanform' id="loanform" method="post" novalidate="novalidate">
                    @csrf
                    <input type="hidden" name="create_application_date" class="create_application_date"
                        id="create_application_date" required>
                    <input type="hidden" name="id" class="id" id="editId" required>
                    <input type="hidden" name="loanId" class="loanId" id="loanId" required>
                    <input type="hidden" name="chargeMode" class="chargeMode chargedata" id="chargeMode" required>
                    <div class="form-group row tenureData">
                        <label class="col-form-label col-lg-3">Emi Option:</label>
                        <div class="col-lg-9">
                            <select class="form-control " name="emi_option" id="emi_option" required>
                                <option value="">--Please Select Emi Option -- </option>
                                <option value="1">Monthly
                                </option>
                                <option value="2">Weekly
                                </option>
                                <option value="3">Daily
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row tenureData">
                        <label class="col-form-label col-lg-3">Tenure:</label>
                        <div class="col-lg-9">
                            <input type="text" name="tenure" id="tenure" class="form-control tTenure" autocomplete="off"
                                required onkeypress="return isNumberKey(event)">
                        </div>
                    </div>
                    <div class="form-group row tenureData">
                        <label class="col-form-label col-lg-3">ROI:</label>
                        <div class="col-lg-9">
                            <input type="text" name="roi" id="roi" min="0" autocomplete="off" class="form-control"
                                required onkeypress="return isNumberKey(event)">
                        </div>
                    </div>
                    <div class="form-group row tenureData">
                        <label class="col-form-label col-lg-3">Effective From</label>
                        <div class="col-lg-9">
                            <input type="text" name="tenure_effective_from" id="tenure_effective_from"
                                autocomplete="off" class="form-control tenure_effective_from" required>
                        </div>
                    </div>

                    <div class="form-group row tenureData">
                        <label class="col-form-label col-lg-3">Effective To</label>
                        <div class="col-lg-9">
                            <input type="text" name="tenure_effective_to" id="tenure_effective_to" autocomplete="off"
                                class="form-control effective_to">
                        </div>
                    </div>

                      <div class="form-group row chargedata">
                        <label class="col-form-label col-lg-3">Emi Option:</label>
                        <div class="col-lg-9">
                            <select class="form-control " name="charges_emi_option" id="charges_emi_option" required>
                                <option value="">--Please Select Emi Option -- </option>
                                <option value="1">Monthly
                                </option>
                                <option value="2">Weekly
                                </option>
                                <option value="3">Daily
                                </option>
                            </select>
                        </div>
                      </div>
                      <div class="form-group row chargedata">
                      <label class="col-form-label col-lg-3">Tenure:</label>
                        <div class="col-lg-9">
                            <input type="text" name="charges_tenure" id="charges_tenure" class="form-control" autocomplete="off"
                                required onkeypress="return isNumberKey(event)">
                        </div>
                      </div>
                      <div class="form-group row chargedata">
                        <label class="col-form-label col-lg-3">Charge Type:</label>
                        <div class="col-lg-9">
                            <select class="form-control charge_type" name="charge_type" id="charge_type" required>
                                <option value="">--Please Select Charge Type -- </option>
                                <option value="0">Percentage
                                </option>
                                <option value="1">Fixed
                                </option>

                            </select>
                        </div>
                    </div>
                    <div class="form-group row chargedata">

                        <label class="col-form-label col-lg-3">Charge :</label>
                        <div class="col-lg-9">
                            <input type="text" name="charge" id="charge" class="form-control charge" required
                                onkeypress="return isNumberKey(event)">
                        </div>
                    </div>
                    <div class="form-group row chargedata">
                        <label class="col-form-label col-lg-3">Min Amount :</label>
                        <div class="col-lg-9">
                            <input type="text" name="min_amount" id="d_min_amount" class="form-control min_amount"
                                required onkeypress="return isNumberKey(event)">
                        </div>
                    </div>
                    <div class="form-group row chargedata">
                        <label class="col-form-label col-lg-3">Max Amount :</label>
                        <div class="col-lg-9">
                            <input type="text" name="max_amount" id="d_max_amount" class="form-control max_amount"
                                required onkeypress="return isNumberKey(event)">
                        </div>
                    </div>
                    <div class="form-group row chargedata">
                        <label class="col-form-label col-lg-3">Effective From :</label>
                        <div class="col-lg-9">
                            <input type="text" name="effective_from" id="d_effective_from"
                                class="form-control effective_from" autocomplete="off" required>
                        </div>
                    </div>
                    <div class="form-group row chargedata">

                        <label class="col-form-label col-lg-3">Effective To :</label>
                        <div class="col-lg-9">
                            <input type="text" name="effective_to" id="d_effective_to" class="form-control effective_to"
                                autocomplete="off">
                        </div>
                    </div>


                    <div class="text-right">
                        <button type="submit" class="btn bg-dark legitRipple">Submit<i
                                class="icon-paperplane ml-2"></i></button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('.tenureM').on('click', function() {


        const type = $(this).attr('data-type');
        const title = $(this).attr('data-title');
        const valueId = $(this).attr('data-Id');
        const chargeMode = $(this).attr('data-chargeMode');
        const Olddata = JSON.parse($(this).attr('data-OldData'));
        console.log("data");
        if (valueId != '' && type == 'Tenure') {
            const EmiOption = $('.emi_option_' + valueId).val();
            const EmiOptionValue = $('.emi_option_' + valueId).attr('data-value');
            const tenure = $('.tenure_' + valueId).val();
            const Roi = $('.roi_' + valueId).val();
            const TEF = $('.tenure_effective_from_' + valueId).val();
            const TET = $('.tenure_effective_to_' + valueId).val();
            $('#emi_option option[value=' + EmiOptionValue + ']').attr("selected", "selected");
            $('.tTenure').val(tenure);
            $('#roi').val(Roi);
            $('#tenure_effective_from').val(TEF);
            $('#tenure_effective_to').val(TET);
            $('#editId').val(valueId);
            const loanId = $('#loan_id').val();
            $('#loanId').val(loanId);

        } else if (Olddata._token && valueId == '' && type == 'Tenure') {
            $('#emi_option option[value=' + Olddata.emi_option + ']').attr("selected", "selected");
            $('#tenure').val(Olddata.tenure);
            $('#roi').val(Olddata.roi);
            $('#tenure_effective_from').val(Olddata.tenure_effective_from);
            $('#tenure_effective_to').val(Olddata.tenure_effective_to);

            const loanId = $('#loan_id').val();
            $('#loanId').val(loanId);

        }
        if (valueId != '' && type == 'charge') {
      
            const EmiOption = $('.emi_option_' + valueId).val();
            const EmiOptionValue = $('.emi_option_' + valueId).attr('data-value');
            const chargeType = $('.ins_charge_type_' + valueId).attr('data-value');
            const charge = $('.ins_charge_' + valueId).val();
            const tenure = $('.tenure_' + valueId).val();
            const minAmount = $('.ins_min_amount_' + valueId).val();
            const maxAmount = $('.ins_max_amount_' + valueId).val();
            const TEF = $('.ins_effective_from_' + valueId).val();
            const TET = $('.ins_effective_to_' + valueId).val();
            $('#charges_emi_option option[value=' + EmiOptionValue + ']').attr("selected", "selected");
            $('#charge_type option[value=' + chargeType + ']').attr("selected", "selected");
            $('#charge').val(charge);
            $('#charges_tenure').val(tenure);
            $('#d_min_amount').val(minAmount);
            $('#d_max_amount').val(maxAmount);
            $('#d_effective_from').val(TEF);
            $('#d_effective_to').val(TET);
            $('#editId').val(valueId);
            const loanId = $('#loan_id').val();
            $('#loanId').val(loanId);
            $('#chargeMode').val(chargeMode);
        } else if (Olddata._token && (valueId) == '' && type == 'charge') {
            $('#charges_emi_option option[value=' + Olddata.emi_option + ']').attr("selected", "selected");
            $('#charge_type charge_type[value=' + Olddata.charge_type + ']').attr("selected",
                "selected");
            $('#charge').val(Olddata.charge);
            $('#d_min_amount').val(Olddata.min_amount);
            $('#d_max_amount').val(Olddata.max_amount);
            $('#d_effective_from').val(Olddata.effective_from);
            $('#d_effective_to').val(Olddata.effective_to);

            const loanId = $('#loan_id').val();
            $('#loanId').val(loanId);
            $('#chargeMode').val(Olddata.chargeMode);

        }
        $('#exampleModalLabel').html(title);
        (type == 'Tenure') ? $('.chargedata').hide(): $('.tenureData').hide();
        (type == 'charge') ? $('.chargedata').show(): $('.tenureData').show();
        const url = (type == 'Tenure') ? "{!! route('admin.loan.updates') !!}" :
            "{!! route('admin.loan.loansettings.loancharges.store') !!}";
        $('#loanform').attr("action", url);


    })
    $.validator.addMethod("maxAmount", function(value, element, p) {
        const MinAmount = $('#min_amount').val();
        const maxAmount = $('#max_amount').val();
        const FileMinAmount = $('#file_min_amount').val();
        const FilemaxAmount = $('#file_max_amount').val();
        const InsMinAmount = $('#ins_min_amount').val();
        const InsmaxAmount = $('#ins_max_amount').val();
        const EditInsMinAmount = $('#d_min_amount').val();
        const EditInsmaxAmount = $('#d_max_amount').val();

        console.log(MinAmount, maxAmount, FileMinAmount, FilemaxAmount, InsMinAmount, InsmaxAmount);

        if (parseInt(MinAmount) > parseInt(maxAmount)) {

            result = false;
            $.validator.messages.maxAmount = "Max Amount Should be Greater Than Minimum Amount";
        }
        if (parseInt(FileMinAmount) > parseInt(FilemaxAmount)) {

            result = false;
            $.validator.messages.maxAmount = "Max Amount Should be Greater Than Minimum Amount";
        }
        if (parseInt(InsMinAmount) > parseInt(InsmaxAmount)) {

            result = false;
            $.validator.messages.maxAmount = "Max Amount Should be Greater Than Minimum Amount";
        }
        if (parseInt(EditInsMinAmount) > parseInt(EditInsmaxAmount)) {

            result = false;
            $.validator.messages.maxAmount = "Max Amount Should be Greater Than Minimum Amount";
        } else {
            result = true;
            $.validator.messages.maxAmount = "";
        }


        return result;
    }, "");
    $.validator.addMethod("zero", function(value, element, p) {
        if (parseFloat(value) > 0) {
            $.validator.messages.zero = "";
            result = true;
        } else {
            $.validator.messages.zero = "Value must be greater than 0.";
            result = false;
        }

        return result;
    }, "");
    $.validator.addMethod("decimal", function(value, element, p) {
        if (this.optional(element) || /^\d*\.?\d*$/g.test(value) == true) {
            $.validator.messages.decimal = "";
            result = true;
        } else {
            $.validator.messages.decimal = "Please enter valid numeric number.";
            result = false;
        }

        return result;
    }, "");
    $('#loanform').validate({
        rules: {
            name: {
                required: true,
            },
            code: {
                required: true,
                number: true
            },

            max_amount: {
                required: true,
                decimal: true,
                zero: true,
                maxAmount: true,
            },
            min_amount: {
                required: true,
                decimal: true,
                zero: true,
            },


            charge: {
                required: true,
                decimal: true,
                zero: true,

            },
            ins_charge: {
                required: true,
                decimal: true,
                zero: true,

            },
            tenure: {
                required: true,
                number: true,
            },
            roi: {
                required: true,
                max: 100,
                number: true,
                decimal: true,
            },

            'loan_type': 'required',
            'loan_category': 'required',
            'effective_from': 'required',
            'emi_option': 'required',
            'tenure_effective_from': 'required',
            'charge_type': 'required',
            'file_effective_from': 'required',
            'ins_charge_type': 'required',
            'ins_effective_from': 'required',

        }


    });




})

function isNumberKey(evt) {
    var charCode = (evt.which) ? evt.which : evt.keyCode;

    if (charCode > 31 && (charCode < 46 || charCode > 57)) {
        return false;
    } else if (charCode == 46) {
        return true;
    } else {
        return true;
    }

}
</script>